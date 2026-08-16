<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductCategory;

/**
 * ProductController
 *
 * Módulo de Inventario: productos en venta con stock y costo.
 */
class ProductController extends Controller
{
    /**
     * Listado de productos con filtros y resumen de inventario.
     */
    public function index(Request $request, array $params): Response
    {
        $q = trim((string) $request->input('q', ''));
        $categoryId = (int) $request->input('category_id', 0);
        $stockFilter = (string) $request->input('stock', '');

        $where = [];
        $bind = [];

        if ($q !== '') {
            $where[] = '(p.name LIKE :q OR p.description LIKE :q2)';
            $bind['q'] = '%' . $q . '%';
            $bind['q2'] = '%' . $q . '%';
        }
        if ($categoryId > 0) {
            $where[] = 'p.category_id = :cat';
            $bind['cat'] = $categoryId;
        }
        if ($stockFilter === 'low') {
            $where[] = 'p.stock <= p.min_stock';
        } elseif ($stockFilter === 'out') {
            $where[] = 'p.stock <= 0';
        }

        $rows = Database::fetchAll(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN product_categories c ON c.id = p.category_id"
             . ($where !== [] ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY p.sort_order ASC, p.name ASC',
            $bind
        );

        $total = (int) (Database::fetch("SELECT COUNT(*) AS c FROM products")['c'] ?? 0);
        $units = (int) (Database::fetch("SELECT COALESCE(SUM(stock), 0) AS c FROM products")['c'] ?? 0);
        $lowStock = (int) (Database::fetch("SELECT COUNT(*) AS c FROM products WHERE stock <= min_stock")['c'] ?? 0);
        $outStock = (int) (Database::fetch("SELECT COUNT(*) AS c FROM products WHERE stock <= 0")['c'] ?? 0);
        $profitTotal = (float) (Database::fetch("SELECT COALESCE(SUM((price - cost) * stock), 0) AS c FROM products")['c'] ?? 0);

        return $this->view('admin/products/index', [
            'title'     => 'Inventario',
            'user'      => Auth::user(),
            'active'    => 'inventory',
            'rows'      => $rows,
            'categories'=> ProductCategory::all('name', 'ASC'),
            'filters'   => ['q' => $q, 'category_id' => $categoryId, 'stock' => $stockFilter],
            'summary'   => [
                'total'    => $total,
                'units'    => $units,
                'low'      => $lowStock,
                'out'      => $outStock,
                'profit'   => $profitTotal,
            ],
        ], 'admin');
    }

    /**
     * Formulario de creación.
     */
    public function create(Request $request, array $params): Response
    {
        return $this->formView(null, 'Nuevo producto');
    }

    /**
     * Guarda un producto nuevo.
     */
    public function store(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/inventory');
        }

        $data = $this->extractProduct($request);
        $errors = $this->validateProduct($data, 1);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/inventory/create');
        }

        $image = $this->handleImageUpload($request, null);
        if (is_string($image) && $image === 'UPLOAD_ERROR') {
            Session::flash('error', $this->uploadError ?? 'Error al subir la imagen.');
            return $this->redirect('/admin.php/inventory/create');
        }

        $product = Product::create([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'description' => $data['description'],
            'price'       => $data['price'],
            'cost'        => $data['cost'],
            'stock'       => $data['stock'],
            'min_stock'   => $data['min_stock'],
            'image'       => $image ?: null,
            'is_active'   => $data['is_active'],
            'sort_order'  => $data['sort_order'],
        ]);

        $this->logMovement((int) $product->getAttribute('id'), 'creation', (int) $data['stock'], 0, (int) $data['stock'], null);

        Session::flash('success', 'Producto creado correctamente.');
        return $this->redirect('/admin.php/inventory');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $product = Product::find($id);

        if ($product === null) {
            Session::flash('error', 'Producto no encontrado.');
            return $this->redirect('/admin.php/inventory');
        }

        return $this->formView($product, 'Editar producto');
    }

    /**
     * Actualiza un producto.
     */
    public function update(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/inventory');
        }

        $id = (int) ($params['id'] ?? 0);
        $product = Product::find($id);

        if ($product === null) {
            Session::flash('error', 'Producto no encontrado.');
            return $this->redirect('/admin.php/inventory');
        }

        $data = $this->extractProduct($request);
        $errors = $this->validateProduct($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/inventory/edit/' . $id);
        }

        $oldImage = (string) $product->getAttribute('image');
        $removeImage = (int) $request->input('remove_image', 0) === 1;
        $image = $this->handleImageUpload($request, $removeImage ? null : $oldImage);

        if (is_string($image) && $image === 'UPLOAD_ERROR') {
            Session::flash('error', $this->uploadError ?? 'Error al subir la imagen.');
            return $this->redirect('/admin.php/inventory/edit/' . $id);
        }

        if ($removeImage && $oldImage !== '') {
            $this->deleteImageFile($oldImage);
        }

        $oldStock = (int) $product->getAttribute('stock');

        Product::updateWhere(['id' => $id], [
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'description' => $data['description'],
            'price'       => $data['price'],
            'cost'        => $data['cost'],
            'stock'       => $data['stock'],
            'min_stock'   => $data['min_stock'],
            'image'       => $image ?: ($removeImage ? null : $oldImage),
            'is_active'   => $data['is_active'],
            'sort_order'  => $data['sort_order'],
        ]);

        if ((int) $data['stock'] !== $oldStock) {
            $this->logMovement($id, 'edit', (int) $data['stock'] - $oldStock, $oldStock, (int) $data['stock'], 'Edición del producto');
        }

        Session::flash('success', 'Producto actualizado correctamente.');
        return $this->redirect('/admin.php/inventory');
    }

    /**
     * Activa/desactiva un producto desde el listado.
     */
    public function toggle(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/inventory');
        }

        $id = (int) ($params['id'] ?? 0);
        $product = Product::find($id);

        if ($product === null) {
            Session::flash('error', 'Producto no encontrado.');
            return $this->redirect('/admin.php/inventory');
        }

        $active = (int) $product->getAttribute('is_active') === 1 ? 0 : 1;
        Product::updateWhere(['id' => $id], ['is_active' => $active]);

        Session::flash('success', $active ? 'Producto activado.' : 'Producto desactivado.');
        return $this->redirect('/admin.php/inventory');
    }

    /**
     * Historial de movimientos de stock de un producto.
     */
    public function movements(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $product = Product::find($id);

        if ($product === null) {
            Session::flash('error', 'Producto no encontrado.');
            return $this->redirect('/admin.php/inventory');
        }

        $rows = Database::fetchAll(
            "SELECT m.*, u.name AS user_name
             FROM inventory_movements m
             LEFT JOIN users u ON u.id = m.created_by
             WHERE m.product_id = :pid
             ORDER BY m.created_at DESC, m.id DESC",
            ['pid' => $id]
        );

        $stock = (int) $product->getAttribute('stock');
        $price = (float) $product->getAttribute('price');
        $cost = (float) $product->getAttribute('cost');

        return $this->view('admin/products/movements', [
            'title'   => 'Historial de inventario',
            'user'    => Auth::user(),
            'active'  => 'inventory',
            'product' => $product,
            'rows'    => $rows,
            'stock'   => $stock,
            'profit'  => ($price - $cost) * $stock,
        ], 'admin');
    }

    /**
     * Repone stock de un producto (registra el movimiento).
     */
    public function restock(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/inventory');
        }

        $id = (int) ($params['id'] ?? 0);
        $product = Product::find($id);

        if ($product === null) {
            Session::flash('error', 'Producto no encontrado.');
            return $this->redirect('/admin.php/inventory');
        }

        $qty = (int) $request->input('quantity', 0);
        $note = trim((string) $request->input('note', ''));

        if ($qty < 1) {
            Session::flash('error', 'La cantidad a reponer debe ser al menos 1 unidad.');
            return $this->redirect('/admin.php/inventory/' . $id . '/movements');
        }

        if ($qty > 99999) {
            Session::flash('error', 'La cantidad es demasiado grande (máx 99999).');
            return $this->redirect('/admin.php/inventory/' . $id . '/movements');
        }

        $before = (int) $product->getAttribute('stock');
        $after = $before + $qty;

        Product::updateWhere(['id' => $id], ['stock' => $after]);
        $this->logMovement($id, 'restock', $qty, $before, $after, $note !== '' ? $note : null);

        Session::flash('success', 'Stock repuesto: +' . $qty . ' unidad(es) de "' . (string) $product->getAttribute('name') . '".');
        return $this->redirect('/admin.php/inventory/' . $id . '/movements');
    }

    /**
     * Elimina un producto.
     */
    public function destroy(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/inventory');
        }

        $id = (int) ($params['id'] ?? 0);
        $product = Product::find($id);

        if ($product === null) {
            Session::flash('error', 'Producto no encontrado.');
            return $this->redirect('/admin.php/inventory');
        }

        $image = (string) $product->getAttribute('image');

        try {
            $product->delete();
        } catch (\PDOException $e) {
            Session::flash('error', 'No se puede eliminar: el producto está asociado a una o más citas.');
            return $this->redirect('/admin.php/inventory');
        }

        if ($image !== '') {
            $this->deleteImageFile($image);
        }

        Session::flash('success', 'Producto eliminado correctamente.');
        return $this->redirect('/admin.php/inventory');
    }

    /* ============================================================
     * HELPERS
     * ========================================================== */

    /**
     * Registra un movimiento de stock en el historial del producto.
     */
    private function logMovement(int $productId, string $type, int $quantity, int $before, int $after, ?string $note): void
    {
        InventoryMovement::create([
            'product_id'   => $productId,
            'type'         => $type,
            'quantity'     => $quantity,
            'stock_before' => $before,
            'stock_after'  => $after,
            'note'         => $note,
            'created_by'   => Auth::id(),
        ]);
    }

    private function formView(?Product $product, string $title): Response
    {
        $categories = ProductCategory::all('name', 'ASC');

        return $this->view('admin/products/form', [
            'title'      => $title,
            'user'       => Auth::user(),
            'active'     => 'inventory',
            'editing'    => $product,
            'categories' => $categories,
        ], 'admin');
    }

    private function extractProduct(Request $request): array
    {
        $categoryId = (int) $request->input('category_id', 0);
        $newCategory = trim((string) $request->input('new_category', ''));

        if ($newCategory !== '') {
            $existing = ProductCategory::whereFirst(['name' => $newCategory]);
            if ($existing !== null) {
                $categoryId = (int) $existing->getAttribute('id');
            } else {
                $created = ProductCategory::create(['name' => $newCategory]);
                $categoryId = (int) $created->getAttribute('id');
            }
        }

        return [
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'name'        => trim((string) $request->input('name', '')),
            'description' => trim((string) $request->input('description', '')),
            'price'       => round((float) $request->input('price', 0), 2),
            'cost'        => round((float) $request->input('cost', 0), 2),
            'stock'       => (int) $request->input('stock', 0),
            'min_stock'   => (int) $request->input('min_stock', 5),
            'is_active'   => $request->input('is_active') ? 1 : 0,
            'sort_order'  => (int) $request->input('sort_order', 0),
        ];
    }

    private function validateProduct(array $data, int $stockMin = 0): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'El nombre del producto es obligatorio.';
        } elseif (mb_strlen($data['name']) > 150) {
            $errors[] = 'El nombre no puede superar 150 caracteres.';
        }

        if ($data['price'] < 0) {
            $errors[] = 'El precio no puede ser negativo.';
        }

        if ($data['cost'] < 0) {
            $errors[] = 'El costo no puede ser negativo.';
        }

        if ($data['stock'] < $stockMin) {
            $errors[] = $stockMin > 0
                ? 'El stock debe ser al menos ' . $stockMin . ' unidad.'
                : 'El stock no puede ser negativo.';
        }

        if ($data['min_stock'] < 0) {
            $errors[] = 'El stock mínimo no puede ser negativo.';
        }

        if ($data['category_id'] > 0 && ProductCategory::find($data['category_id']) === null) {
            $errors[] = 'Selecciona una categoría válida.';
        }

        return $errors;
    }

    private ?string $uploadError = null;

    /**
     * Procesa la subida de una imagen.
     * Retorna la ruta relativa (products/archivo) o null si no hay archivo.
     * Retorna 'UPLOAD_ERROR' (y setea $this->uploadError) si falla.
     */
    private function handleImageUpload(Request $request, ?string $currentImage): ?string
    {
        if (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $currentImage;
        }

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = 'Error al subir el archivo (código ' . $file['error'] . ').';
            return 'UPLOAD_ERROR';
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            $this->uploadError = 'La imagen no puede superar los 2 MB.';
            return 'UPLOAD_ERROR';
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            $this->uploadError = 'El archivo debe ser una imagen válida.';
            return 'UPLOAD_ERROR';
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            $this->uploadError = 'Formato no permitido (usa JPG, PNG, WEBP o GIF).';
            return 'UPLOAD_ERROR';
        }

        $dir = UPLOAD_PATH . 'products';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            $this->uploadError = 'No se pudo crear el directorio de imágenes.';
            return 'UPLOAD_ERROR';
        }

        $filename = 'prd_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $destination = $dir . '/' . $filename;
        $moved = move_uploaded_file($file['tmp_name'], $destination);
        if (!$moved) {
            // Fallback para entornos donde move_uploaded_file no aplica
            $moved = @rename($file['tmp_name'], $destination);
        }

        if (!$moved) {
            $this->uploadError = 'No se pudo mover el archivo al directorio de imágenes.';
            return 'UPLOAD_ERROR';
        }

        if ($currentImage !== null && $currentImage !== '' && basename($currentImage) !== $filename) {
            $this->deleteImageFile($currentImage);
        }

        return 'products/' . $filename;
    }

    private function deleteImageFile(string $filename): void
    {
        $clean = str_replace(['\\', '..'], '', $filename);
        $path = UPLOAD_PATH . ltrim($clean, '/');
        if (is_file($path)) {
            @unlink($path);
        }
    }

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
