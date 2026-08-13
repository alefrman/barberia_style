<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Models\Expense;
use App\Models\ExpenseCategory;

/**
 * ExpenseController
 *
 * Módulo de Gastos: control de salidas de efectivo con categorías.
 */
class ExpenseController extends Controller
{
    private const PAYMENT_METHODS = ['Efectivo', 'Tarjeta', 'Transferencia', 'Otro'];

    /**
     * Listado de gastos con filtros y resumen del mes.
     */
    public function index(Request $request, array $params): Response
    {
        $q = trim((string) $request->input('q', ''));
        $categoryId = (int) $request->input('category_id', 0);
        $from = trim((string) $request->input('from', ''));
        $to = trim((string) $request->input('to', ''));

        $where = [];
        $bind = [];

        if ($q !== '') {
            $where[] = '(e.description LIKE :q OR e.notes LIKE :q2)';
            $bind['q'] = '%' . $q . '%';
            $bind['q2'] = '%' . $q . '%';
        }
        if ($categoryId > 0) {
            $where[] = 'e.category_id = :cat';
            $bind['cat'] = $categoryId;
        }
        if ($from !== '') {
            $where[] = 'e.expense_date >= :from';
            $bind['from'] = $from;
        }
        if ($to !== '') {
            $where[] = 'e.expense_date <= :to';
            $bind['to'] = $to;
        }

        $rows = Database::fetchAll(
            "SELECT e.*, c.name AS category_name
             FROM expenses e
             LEFT JOIN expense_categories c ON c.id = e.category_id"
             . ($where !== [] ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY e.expense_date DESC, e.id DESC',
            $bind
        );

        $month = (string) (Database::fetchValue("SELECT COALESCE(SUM(amount), 0) AS t FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')") ?? 0);
        $today = (string) (Database::fetchValue("SELECT COALESCE(SUM(amount), 0) AS t FROM expenses WHERE expense_date = CURDATE()") ?? 0);
        $monthCount = (int) (Database::fetchValue("SELECT COUNT(*) AS c FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')") ?? 0);
        $totalAll = (string) (Database::fetchValue("SELECT COALESCE(SUM(amount), 0) AS t FROM expenses") ?? 0);

        return $this->view('admin/expenses/index', [
            'title'      => 'Gastos',
            'user'       => Auth::user(),
            'active'     => 'expenses',
            'rows'       => $rows,
            'categories' => ExpenseCategory::all('name', 'ASC'),
            'filters'    => ['q' => $q, 'category_id' => $categoryId, 'from' => $from, 'to' => $to],
            'summary'    => [
                'month'  => (float) $month,
                'today'  => (float) $today,
                'count'  => $monthCount,
                'total'  => (float) $totalAll,
            ],
        ], 'admin');
    }

    /**
     * Formulario de creación.
     */
    public function create(Request $request, array $params): Response
    {
        return $this->formView(null, 'Nuevo gasto');
    }

    /**
     * Guarda un gasto nuevo.
     */
    public function store(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/expenses');
        }

        $data = $this->extractExpense($request);
        $errors = $this->validateExpense($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/expenses/create');
        }

        Expense::create([
            'category_id'    => $data['category_id'],
            'description'    => $data['description'],
            'amount'         => $data['amount'],
            'expense_date'   => $data['expense_date'],
            'payment_method' => $data['payment_method'],
            'notes'          => $data['notes'],
            'created_by'     => Auth::id(),
        ]);

        Session::flash('success', 'Gasto registrado correctamente.');
        return $this->redirect('/admin.php/expenses');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $expense = Expense::find($id);

        if ($expense === null) {
            Session::flash('error', 'Gasto no encontrado.');
            return $this->redirect('/admin.php/expenses');
        }

        return $this->formView($expense, 'Editar gasto');
    }

    /**
     * Actualiza un gasto.
     */
    public function update(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/expenses');
        }

        $id = (int) ($params['id'] ?? 0);
        $expense = Expense::find($id);

        if ($expense === null) {
            Session::flash('error', 'Gasto no encontrado.');
            return $this->redirect('/admin.php/expenses');
        }

        $data = $this->extractExpense($request);
        $errors = $this->validateExpense($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/expenses/edit/' . $id);
        }

        Expense::updateWhere(['id' => $id], [
            'category_id'    => $data['category_id'],
            'description'    => $data['description'],
            'amount'         => $data['amount'],
            'expense_date'   => $data['expense_date'],
            'payment_method' => $data['payment_method'],
            'notes'          => $data['notes'],
        ]);

        Session::flash('success', 'Gasto actualizado correctamente.');
        return $this->redirect('/admin.php/expenses');
    }

    /**
     * Elimina un gasto.
     */
    public function destroy(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/expenses');
        }

        $id = (int) ($params['id'] ?? 0);
        $expense = Expense::find($id);

        if ($expense === null) {
            Session::flash('error', 'Gasto no encontrado.');
            return $this->redirect('/admin.php/expenses');
        }

        $expense->delete();

        Session::flash('success', 'Gasto eliminado correctamente.');
        return $this->redirect('/admin.php/expenses');
    }

    /* ============================================================
     * HELPERS
     * ========================================================== */

    private function formView(?Expense $expense, string $title): Response
    {
        $categories = ExpenseCategory::all('name', 'ASC');

        return $this->view('admin/expenses/form', [
            'title'      => $title,
            'user'       => Auth::user(),
            'active'     => 'expenses',
            'editing'    => $expense,
            'categories' => $categories,
            'methods'    => self::PAYMENT_METHODS,
        ], 'admin');
    }

    private function extractExpense(Request $request): array
    {
        $categoryId = (int) $request->input('category_id', 0);
        $newCategory = trim((string) $request->input('new_category', ''));

        if ($newCategory !== '') {
            $existing = ExpenseCategory::whereFirst(['name' => $newCategory]);
            if ($existing !== null) {
                $categoryId = (int) $existing->getAttribute('id');
            } else {
                $created = ExpenseCategory::create(['name' => $newCategory]);
                $categoryId = (int) $created->getAttribute('id');
            }
        }

        return [
            'category_id'    => $categoryId > 0 ? $categoryId : null,
            'description'    => trim((string) $request->input('description', '')),
            'amount'         => round((float) $request->input('amount', 0), 2),
            'expense_date'   => trim((string) $request->input('expense_date', date('Y-m-d'))),
            'payment_method' => trim((string) $request->input('payment_method', 'Efectivo')),
            'notes'          => trim((string) $request->input('notes', '')),
        ];
    }

    private function validateExpense(array $data): array
    {
        $errors = [];

        if ($data['description'] === '') {
            $errors[] = 'La descripción del gasto es obligatoria.';
        } elseif (mb_strlen($data['description']) > 255) {
            $errors[] = 'La descripción no puede superar 255 caracteres.';
        }

        if ($data['amount'] <= 0) {
            $errors[] = 'El monto debe ser mayor a cero.';
        }

        if ($data['expense_date'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['expense_date'])) {
            $errors[] = 'La fecha es obligatoria y debe tener formato válido.';
        }

        if (!in_array($data['payment_method'], self::PAYMENT_METHODS, true)) {
            $errors[] = 'Selecciona un método de pago válido.';
        }

        if ($data['category_id'] > 0 && ExpenseCategory::find($data['category_id']) === null) {
            $errors[] = 'Selecciona una categoría válida.';
        }

        return $errors;
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
