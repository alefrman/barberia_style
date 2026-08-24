<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Auth;
use App\Helpers\Money;
use App\Helpers\Session;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Product;

/**
 * InvoiceController
 *
 * Punto de venta: facturas rápidas de productos sin servicio.
 */
class InvoiceController extends Controller
{
    /**
     * Listado de facturas.
     */
    public function index(Request $request, array $params): Response
    {
        $invoices = Database::fetchAll(
            "SELECT i.*, 
                    (SELECT COUNT(*) FROM invoice_products ip WHERE ip.invoice_id = i.id) AS items_count
             FROM invoices i
             ORDER BY i.id DESC"
        );

        return $this->view('admin/invoices/index', [
            'title'   => 'Punto de venta',
            'user'    => Auth::user(),
            'active'  => 'invoices',
            'invoices' => $invoices,
        ], 'admin');
    }

    /**
     * Formulario de nueva factura (POS rápido).
     */
    public function create(Request $request, array $params): Response
    {
        $products = Product::all('name', 'ASC');
        $activeProducts = array_filter($products, fn($p) => (int) $p->getAttribute('is_active') === 1);

        $data = [
            'title'    => 'Punto de venta',
            'user'     => Auth::user(),
            'active'   => 'invoices',
            'products' => array_values($activeProducts),
        ];

        $isModal = ($request->input('modal') ?? '') === '1';
        $data['isModal'] = $isModal;
        if ($isModal) {
            return $this->viewRaw('admin/invoices/create', $data);
        }

        return $this->view('admin/invoices/create', $data, 'admin');
    }

    /**
     * Guardar nueva factura.
     */
    public function store(Request $request, array $params): Response
    {
        $isAjax = stripos((string) ($request->header('X-Requested-With') ?? ''), 'xmlhttprequest') !== false;

        if (!$this->validCsrf($request)) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Token de seguridad inválido.'], 403);
            }
            return $this->redirect('/admin.php/invoices');
        }

        $clientName  = trim((string) $request->input('client_name', ''));
        $clientPhone = trim((string) $request->input('client_phone', ''));
        $paymentMethod = trim((string) $request->input('payment_method', 'Efectivo'));
        $notes       = trim((string) $request->input('notes', ''));

        $productIds = $request->input('product_id', []);
        $quantities = $request->input('quantity', []);

        $errors = [];
        if ($clientName === '') {
            $errors[] = 'El nombre del cliente es obligatorio.';
        }
        if (strlen($clientName) > 150) {
            $errors[] = 'El nombre del cliente no puede exceder 150 caracteres.';
        }
        if (!is_array($productIds) || $productIds === []) {
            $errors[] = 'Debe agregar al menos un producto.';
        }

        $validMethods = ['Efectivo', 'Tarjeta', 'Transferencia', 'Otro'];
        if (!in_array($paymentMethod, $validMethods, true)) {
            $paymentMethod = 'Efectivo';
        }

        if ($errors !== []) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => $errors[0]]);
            }
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/invoices/create');
        }

        $lines = [];
        foreach ($productIds as $i => $pid) {
            $pid = (int) $pid;
            $qty = max(1, min(99, (int) ($quantities[$i] ?? 1)));
            $product = Product::find($pid);
            if ($product === null || (int) $product->getAttribute('is_active') !== 1) {
                continue;
            }
            $price = (float) $product->getAttribute('price');
            $stock = (int) $product->getAttribute('stock');
            if ($qty > $stock) {
                $msg = "Stock insuficiente para \"{$product->getAttribute('name')}\". Disponible: {$stock}.";
                if ($isAjax) {
                    return $this->json(['success' => false, 'error' => $msg]);
                }
                Session::flash('error', $msg);
                return $this->redirect('/admin.php/invoices/create');
            }
            $lines[] = ['product' => $product, 'qty' => $qty, 'price' => $price];
        }

        if ($lines === []) {
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Debe agregar al menos un producto válido.']);
            }
            Session::flash('error', 'Debe agregar al menos un producto válido.');
            return $this->redirect('/admin.php/invoices/create');
        }

        $subtotal = 0.0;
        foreach ($lines as $line) {
            $subtotal += $line['price'] * $line['qty'];
        }

        $invoiceNumber = $this->nextInvoiceNumber();

        Database::beginTransaction();
        try {
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'client_name'    => $clientName,
                'client_phone'   => $clientPhone !== '' ? $clientPhone : null,
                'payment_method' => $paymentMethod,
                'subtotal'       => $subtotal,
                'total'          => $subtotal,
                'notes'          => $notes !== '' ? $notes : null,
                'created_by'     => Auth::id(),
            ]);

            $invoiceId = (int) $invoice->getAttribute('id');

            foreach ($lines as $line) {
                InvoiceProduct::create([
                    'invoice_id' => $invoiceId,
                    'product_id' => (int) $line['product']->getAttribute('id'),
                    'quantity'   => $line['qty'],
                    'price'      => $line['price'],
                ]);

                $product = $line['product'];
                $before = (int) $product->getAttribute('stock');
                $after = $before - $line['qty'];
                Product::updateWhere(['id' => (int) $product->getAttribute('id')], ['stock' => $after]);

                Database::execute(
                    "INSERT INTO inventory_movements (product_id, type, quantity, stock_before, stock_after, note, created_by)
                     VALUES (:pid, 'venta', :qty, :before, :after, :note, :uid)",
                    [
                        'pid'    => (int) $product->getAttribute('id'),
                        'qty'    => -$line['qty'],
                        'before' => $before,
                        'after'  => $after,
                        'note'   => "Factura {$invoiceNumber}",
                        'uid'    => Auth::id(),
                    ]
                );
            }

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            if ($isAjax) {
                return $this->json(['success' => false, 'error' => 'Error al guardar la factura: ' . $e->getMessage()]);
            }
            Session::flash('error', 'Error al guardar la factura: ' . $e->getMessage());
            return $this->redirect('/admin.php/invoices/create');
        }

        if ($isAjax) {
            return $this->json(['success' => true, 'redirect' => ADMIN_URL . '/invoices/show/' . $invoiceId]);
        }

        Session::flash('success', "Factura {$invoiceNumber} registrada.");
        return $this->redirect('/admin.php/invoices/show/' . $invoiceId);
    }

    /**
     * Ver detalle de factura.
     */
    public function show(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $invoice = Invoice::find($id);

        if ($invoice === null) {
            Session::flash('error', 'Factura no encontrada.');
            return $this->redirect('/admin.php/invoices');
        }

        $items = Database::fetchAll(
            "SELECT ip.*, p.name AS product_name
             FROM invoice_products ip
             LEFT JOIN products p ON p.id = ip.product_id
             WHERE ip.invoice_id = :id ORDER BY ip.id",
            ['id' => $id]
        );

        $cashier = $invoice->getAttribute('created_by')
            ? (Database::fetch("SELECT name FROM users WHERE id = :id", ['id' => (int) $invoice->getAttribute('created_by')]) ?? ['name' => '—'])['name']
            : '—';

        return $this->view('admin/invoices/show', [
            'title'   => 'Factura ' . $invoice->getAttribute('invoice_number'),
            'user'    => Auth::user(),
            'active'  => 'invoices',
            'invoice' => $invoice,
            'items'   => $items,
            'cashier' => $cashier,
        ], 'admin');
    }

    /**
     * Eliminar factura y restaurar stock.
     */
    public function destroy(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/invoices');
        }

        $id = (int) ($params['id'] ?? 0);
        $invoice = Invoice::find($id);

        if ($invoice === null) {
            Session::flash('error', 'Factura no encontrada.');
            return $this->redirect('/admin.php/invoices');
        }

        $invoiceNumber = (string) $invoice->getAttribute('invoice_number');

        Database::beginTransaction();
        try {
            // Restaurar stock de cada producto
            $items = Database::fetchAll(
                "SELECT * FROM invoice_products WHERE invoice_id = :id",
                ['id' => $id]
            );

            foreach ($items as $item) {
                $product = Product::find((int) $item['product_id']);
                if ($product !== null) {
                    $before = (int) $product->getAttribute('stock');
                    $after = $before + (int) $item['quantity'];
                    Product::updateWhere(['id' => (int) $product->getAttribute('id')], ['stock' => $after]);

                    Database::execute(
                        "INSERT INTO inventory_movements (product_id, type, quantity, stock_before, stock_after, note, created_by)
                         VALUES (:pid, 'reversión', :qty, :before, :after, :note, :uid)",
                        [
                            'pid'    => (int) $item['product_id'],
                            'qty'    => (int) $item['quantity'],
                            'before' => $before,
                            'after'  => $after,
                            'note'   => "Reversión {$invoiceNumber}",
                            'uid'    => Auth::id(),
                        ]
                    );
                }
            }

            // Eliminar detalles y factura (CASCADE)
            Database::execute("DELETE FROM invoices WHERE id = :id", ['id' => $id]);

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::flash('error', 'Error al eliminar la factura: ' . $e->getMessage());
            return $this->redirect('/admin.php/invoices');
        }

        Session::flash('success', "Factura {$invoiceNumber} eliminada. Stock restaurado.");
        return $this->redirect('/admin.php/invoices');
    }

    /**
     * Genera el siguiente número de factura (FAC-0001, FAC-0002...).
     */
    private function nextInvoiceNumber(): string
    {
        $prefix = (string) Database::fetchValue("SELECT setting_value FROM settings WHERE setting_key = 'invoice_prefix'") ?: 'FAC';
        $next = (int) Database::fetchValue("SELECT setting_value FROM settings WHERE setting_key = 'invoice_next_number'");

        $number = $prefix . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);

        Database::execute(
            "UPDATE settings SET setting_value = :val WHERE setting_key = 'invoice_next_number'",
            ['val' => (string) ($next + 1)]
        );

        return $number;
    }

    /**
     * Verifica el token CSRF.
     */
    private function validCsrf(Request $request): bool
    {
        $token = $request->input('_csrf');
        if (Session::verifyCsrf(is_string($token) ? $token : null)) {
            return true;
        }
        Session::flash('error', 'Token de seguridad inválido.');
        return false;
    }
}
