<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Auth;
use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Service;

/**
 * DashboardController
 *
 * Vista principal del panel de administración, con KPIs financieros
 * y gráficas de ingresos vs gastos (Chart.js).
 */
class DashboardController extends Controller
{
    /**
     * Muestra el dashboard con datos financieros para el dueño.
     */
    public function index(Request $request, array $params): Response
    {
        $user = Auth::user();
        $periodInput = (string) $request->input('period', 'month');
        $period = in_array($periodInput, ['month', 'week'], true) ? $periodInput : 'month';

        // Ingreso = únicamente citas con estado "Completada"
        $monthIncome = (float) Database::fetchValue(
            "SELECT COALESCE(SUM(a.total), 0)
             FROM appointments a
             INNER JOIN appointment_statuses s ON s.id = a.status_id
             WHERE LOWER(s.name) = 'completada'
               AND DATE_FORMAT(a.appointment_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
        );

        $monthExpenses = (float) Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0)
             FROM expenses
             WHERE DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
        );

        $monthTicket = (float) (Database::fetchValue(
            "SELECT COALESCE(SUM(a.total) / NULLIF(COUNT(*), 0), 0)
             FROM appointments a
             INNER JOIN appointment_statuses s ON s.id = a.status_id
             WHERE LOWER(s.name) = 'completada'
               AND DATE_FORMAT(a.appointment_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')"
        ) ?? 0);

        // Series mensuales (últimos 12 meses)
        [$labels, $incomeByMonth, $expensesByMonth] = $this->monthlySeries();

        // Gastos del mes por categoría y método de pago
        $byCategory = Database::fetchAll(
            "SELECT COALESCE(c.name, 'Sin categoría') AS name, SUM(e.amount) AS total
             FROM expenses e
             LEFT JOIN expense_categories c ON c.id = e.category_id
             WHERE DATE_FORMAT(e.expense_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
             GROUP BY e.category_id, c.name
             ORDER BY total DESC"
        );

        $byMethod = Database::fetchAll(
            "SELECT payment_method AS name, SUM(amount) AS total
             FROM expenses
             WHERE DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
             GROUP BY payment_method
             ORDER BY total DESC"
        );

        // Conteo por estado de cita (Completada / No asistió / Cancelada) con filtro mes o semana
        [$statusRange, $statusLabels, $statusCounts] = $this->statusCounts($period);

        // Productos: más vendidos y ganancia pendiente
        $topProducts = $this->topProducts();
        $topProductsTotal = (int) (Database::fetchValue(
            "SELECT COALESCE(SUM(ap.quantity), 0)
             FROM appointment_products ap
             INNER JOIN appointments a          ON a.id = ap.appointment_id
             INNER JOIN appointment_statuses s  ON s.id = a.status_id
             WHERE LOWER(s.name) = 'completada'"
        ) ?? 0);

        return $this->view('admin/dashboard/index', [
            'title'            => 'Dashboard',
            'user'             => $user,
            'active'           => 'dashboard',
            'totalServices'    => Service::count(),
            'totalProducts'    => Product::count(),
            'totalExpenses'    => Expense::count(),
            'todayAppointments' => Appointment::count(['appointment_date' => date('Y-m-d')]),
            'monthIncome'      => $monthIncome,
            'monthExpenses'    => $monthExpenses,
            'monthProfit'      => $monthIncome - $monthExpenses,
            'monthTicket'      => $monthTicket,
            'chartLabels'      => $labels,
            'chartIncome'      => $incomeByMonth,
            'chartExpenses'    => $expensesByMonth,
            'chartProfit'      => array_map(fn(float $i, float $e): float => $i - $e, $incomeByMonth, $expensesByMonth),
            'expensesByCategory' => $byCategory,
            'expensesByMethod'   => $byMethod,
            'statusPeriod'     => $period,
            'statusRange'      => $statusRange,
            'statusLabels'     => $statusLabels,
            'statusCounts'     => $statusCounts,
            'topProducts'      => $topProducts,
            'topProductsTotal' => $topProductsTotal,
        ], 'admin');
    }

    /**
     * Etiquetas y series mensuales de ingresos/gastos para los últimos 12 meses.
     *
     * @return array{0: string[], 1: float[], 2: float[]}
     */
    private function monthlySeries(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("-{$i} months"));
            $months[$key] = [
                'label' => $this->shortMonth($key),
                'income' => 0.0,
                'expenses' => 0.0,
            ];
        }

        $incomeRows = Database::fetchAll(
            "SELECT DATE_FORMAT(a.appointment_date, '%Y-%m') AS ym, SUM(a.total) AS total
             FROM appointments a
             INNER JOIN appointment_statuses s ON s.id = a.status_id
             WHERE LOWER(s.name) = 'completada'
               AND a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
             GROUP BY ym"
        );
        foreach ($incomeRows as $row) {
            if (isset($months[$row['ym']])) {
                $months[$row['ym']]['income'] = (float) $row['total'];
            }
        }

        $expenseRows = Database::fetchAll(
            "SELECT DATE_FORMAT(expense_date, '%Y-%m') AS ym, SUM(amount) AS total
             FROM expenses
             WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
             GROUP BY ym"
        );
        foreach ($expenseRows as $row) {
            if (isset($months[$row['ym']])) {
                $months[$row['ym']]['expenses'] = (float) $row['total'];
            }
        }

        $labels = [];
        $income = [];
        $expenses = [];
        foreach ($months as $m) {
            $labels[] = $m['label'];
            $income[] = $m['income'];
            $expenses[] = $m['expenses'];
        }

        return [$labels, $income, $expenses];
    }

    /**
     * Etiqueta corta en español para una clave 'YYYY-MM'.
     */
    private function shortMonth(string $key): string
    {
        [$year, $month] = array_map('intval', explode('-', $key));
        $names = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $name = $names[$month] ?? (string) $month;
        return $name . ' ' . substr((string) $year, 2);
    }

    /**
     * Conteo de citas por estado (Completada / No asistió / Cancelada)
     * para el mes o la semana actual. Retorna [rango, etiquetas, conteos].
     *
     * @return array{0: string, 1: string[], 2: int[]}
     */
    private function statusCounts(string $period): array
    {
        $names = ['Completada', 'No asistió', 'Cancelada'];

        if ($period === 'week') {
            $from = date('Y-m-d', strtotime('monday this week'));
            $to = date('Y-m-d', strtotime('monday this week +7 days'));
            $range = date('d/m', strtotime($from)) . ' – ' . date('d/m', strtotime($to . ' -1 day'));
        } else {
            $from = date('Y-m-01');
            $to = date('Y-m-01', strtotime('first day of next month'));
            $months = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
            $range = $months[date('m')] . ' ' . date('Y');
        }

        $counts = array_fill(0, count($names), 0);
        $rows = Database::fetchAll(
            "SELECT s.name, COUNT(*) AS c
             FROM appointments a
             INNER JOIN appointment_statuses s ON s.id = a.status_id
             WHERE a.appointment_date >= :from AND a.appointment_date < :to
               AND LOWER(s.name) IN ('completada', 'no asistió', 'cancelada')
             GROUP BY s.name",
            ['from' => $from, 'to' => $to]
        );

        foreach ($rows as $row) {
            $index = array_search($row['name'], $names, true);
            if ($index !== false) {
                $counts[$index] = (int) $row['c'];
            }
        }

        return [$range, $names, $counts];
    }

    /**
     * Top 10 productos más vendidos en unidades y en dinero (solo citas completadas).
     *
     * @return array<int, array{name: string, units: int, money: float}>
     */
    private function topProducts(): array
    {
        return Database::fetchAll(
            "SELECT p.name AS name,
                    COALESCE(SUM(ap.quantity), 0)       AS units,
                    COALESCE(SUM(ap.price * ap.quantity), 0) AS money
             FROM appointment_products ap
             INNER JOIN appointments a          ON a.id = ap.appointment_id
             INNER JOIN appointment_statuses s  ON s.id = a.status_id
             INNER JOIN products p              ON p.id = ap.product_id
             WHERE LOWER(s.name) = 'completada'
             GROUP BY p.id, p.name
             ORDER BY units DESC
             LIMIT 10"
        );
    }
}
