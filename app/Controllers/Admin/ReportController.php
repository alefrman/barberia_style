<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\Auth;
use App\Helpers\Money;
use App\Helpers\Settings;

/**
 * ReportController
 *
 * Reporte financiero: ingresos por servicios, ingresos por productos
 * y gastos en un período dado. Generación de PDF con dompdf.
 */
class ReportController extends Controller
{
    /**
     * Página HTML del reporte financiero.
     */
    public function index(Request $request, array $params): Response
    {
        $user = Auth::user();
        $from = trim((string) $request->input('from', date('Y-m-01')));
        $to   = trim((string) $request->input('to', date('Y-m-d')));

        $data = $this->fetchReportData($from, $to);

        return $this->view('admin/reports/index', [
            'title'     => 'Reportes',
            'user'      => $user,
            'active'    => 'reports',
            'from'      => $from,
            'to'        => $to,
        ] + $data, 'admin');
    }

    /**
     * Genera y descarga el PDF del reporte financiero.
     */
    public function pdf(Request $request, array $params): Response
    {
        $from = trim((string) $request->input('from', date('Y-m-01')));
        $to   = trim((string) $request->input('to', date('Y-m-d')));

        $data = $this->fetchReportData($from, $to);

        $siteName    = (string) Settings::get('site_name', 'Barbería Style');
        $siteAddress = (string) Settings::get('address', '');

        $logo = '';
        if (extension_loaded('gd')) {
            $logoPath = (string) Settings::get('logo', '');
            if ($logoPath !== '' && file_exists(BASE_PATH . '/public/assets/uploads/' . $logoPath)) {
                $logoData = file_get_contents(BASE_PATH . '/public/assets/uploads/' . $logoPath);
                if ($logoData !== false) {
                    $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                    $mime = match ($ext) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png'         => 'image/png',
                        'webp'        => 'image/webp',
                        default       => 'image/png',
                    };
                    $logo = 'data:image/' . $mime . ';base64,' . base64_encode($logoData);
                }
            }
        }

        $fromDisplay = date('d/m/Y', strtotime($from));
        $toDisplay   = date('d/m/Y', strtotime($to));
        $now         = date('d/m/Y H:i');

        $servicesRevenue = $data['servicesRevenue'];
        $productsRevenue = $data['productsRevenue'];
        $totalExpenses   = $data['totalExpenses'];
        $netProfit       = $data['netProfit'];
        $topServices     = $data['topServices'];
        $expensesByCat   = $data['expensesByCategory'];

        $topServicesHtml = '';
        if ($topServices !== []) {
            foreach ($topServices as $ts) {
                $topServicesHtml .= '<tr>'
                    . '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;">' . View::e($ts['name']) . '</td>'
                    . '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;text-align:center;">' . (int) $ts['count'] . '</td>'
                    . '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:600;">$' . number_format((float) $ts['total'], 2) . '</td>'
                    . '</tr>';
            }
        } else {
            $topServicesHtml = '<tr><td colspan="3" style="padding:12px;text-align:center;color:#9ca3af;">Sin datos en este período</td></tr>';
        }

        $expensesCatHtml = '';
        if ($expensesByCat !== []) {
            $totalForPct = max($totalExpenses, 0.01);
            foreach ($expensesByCat as $ec) {
                $pct = round(((float) $ec['total'] / $totalForPct) * 100, 1);
                $expensesCatHtml .= '<tr>'
                    . '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;">' . View::e($ec['name']) . '</td>'
                    . '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:600;">$' . number_format((float) $ec['total'], 2) . '</td>'
                    . '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;text-align:right;color:#6b7280;">' . $pct . '%</td>'
                    . '</tr>';
            }
        } else {
            $expensesCatHtml = '<tr><td colspan="3" style="padding:12px;text-align:center;color:#9ca3af;">Sin gastos en este período</td></tr>';
        }

        $logoHtml = '';
        if ($logo !== '') {
            $logoHtml = '<img src="' . $logo . '" style="height:48px;margin-right:12px;">';
        }

        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; color: #1f2937; margin: 0; padding: 24px; font-size: 13px; }
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #c9a84c; padding-bottom: 16px; margin-bottom: 24px; }
        .header-left { display: flex; align-items: center; }
        .header-right { text-align: right; color: #6b7280; font-size: 12px; }
        .header h1 { font-size: 20px; margin: 0; color: #1f2937; }
        .header p { margin: 4px 0 0; font-size: 12px; color: #6b7280; }
        .cards { display: flex; gap: 16px; margin-bottom: 24px; }
        .card { flex: 1; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; text-align: center; }
        .card-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 4px; }
        .card-value { font-size: 22px; font-weight: 700; }
        .card-value.green { color: #059669; }
        .card-value.blue { color: #2563eb; }
        .card-value.red { color: #dc2626; }
        .card-value.gold { color: #b8860b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { background: #f9fafb; padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
        th:nth-child(2) { text-align: center; }
        th:nth-child(3) { text-align: right; }
        .section-title { font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #1f2937; }
        .footer { border-top: 1px solid #e5e7eb; padding-top: 12px; text-align: center; font-size: 10px; color: #9ca3af; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            ' . $logoHtml . '
            <div>
                <h1>Reporte Financiero</h1>
                <p>' . View::e($siteName) . '</p>
            </div>
        </div>
        <div class="header-right">
            Período: ' . $fromDisplay . ' — ' . $toDisplay . '<br>
            Generado: ' . $now . '
        </div>
    </div>

    <div class="cards">
        <div class="card">
            <div class="card-label">Ingresos Servicios</div>
            <div class="card-value green">$' . number_format($servicesRevenue, 2) . '</div>
        </div>
        <div class="card">
            <div class="card-label">Ingresos Productos</div>
            <div class="card-value blue">$' . number_format($productsRevenue, 2) . '</div>
        </div>
        <div class="card">
            <div class="card-label">Gastos</div>
            <div class="card-value red">$' . number_format($totalExpenses, 2) . '</div>
        </div>
        <div class="card">
            <div class="card-label">Ganancia Neta</div>
            <div class="card-value gold">$' . number_format($netProfit, 2) . '</div>
        </div>
    </div>

    <div class="section-title">Top Servicios</div>
    <table>
        <thead>
            <tr><th>Servicio</th><th>Citas</th><th>Ingreso</th></tr>
        </thead>
        <tbody>' . $topServicesHtml . '</tbody>
    </table>

    <div class="section-title">Gastos por Categoría</div>
    <table>
        <thead>
            <tr><th>Categoría</th><th style="text-align:right;">Total</th><th style="text-align:right;">%</th></tr>
        </thead>
        <tbody>' . $expensesCatHtml . '</tbody>
    </table>

    <div class="footer">
        ' . View::e($siteName) . ' — ' . View::e($siteAddress) . '<br>
        Documento generado el ' . $now . '
    </div>
</body>
</html>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = 'reporte_financiero_' . $from . '_' . $to . '.pdf';

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            ]
        );
    }

    /**
     * Consulta la data financiera para un período dado.
     */
    private function fetchReportData(string $from, string $to): array
    {
        $whereAppointment = "LOWER(s.name) = 'completada' AND a.appointment_date >= :from AND a.appointment_date <= :to";
        $bind = ['from' => $from, 'to' => $to];

        $servicesRevenue = (float) Database::fetchValue(
            "SELECT COALESCE(SUM(aps.price), 0)
             FROM appointment_services aps
             INNER JOIN appointments a         ON a.id = aps.appointment_id
             INNER JOIN appointment_statuses s ON s.id = a.status_id
             WHERE {$whereAppointment}",
            $bind
        );

        $productsRevenue = (float) Database::fetchValue(
            "SELECT COALESCE(SUM(ap.price * ap.quantity), 0)
             FROM appointment_products ap
             INNER JOIN appointments a         ON a.id = ap.appointment_id
             INNER JOIN appointment_statuses s ON s.id = a.status_id
             WHERE {$whereAppointment}",
            $bind
        );

        $totalExpenses = (float) Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0)
             FROM expenses
             WHERE expense_date >= :from AND expense_date <= :to",
            $bind
        );

        $topServices = Database::fetchAll(
            "SELECT sv.name AS name,
                    COUNT(DISTINCT a.id) AS `count`,
                    COALESCE(SUM(aps.price), 0) AS total
             FROM appointment_services aps
             INNER JOIN appointments a         ON a.id = aps.appointment_id
             INNER JOIN appointment_statuses s ON s.id = a.status_id
             INNER JOIN services sv            ON sv.id = aps.service_id
             WHERE {$whereAppointment}
             GROUP BY sv.id, sv.name
             ORDER BY total DESC
             LIMIT 10",
            $bind
        );

        $expensesByCategory = Database::fetchAll(
            "SELECT COALESCE(c.name, 'Sin categoría') AS name, SUM(e.amount) AS total
             FROM expenses e
             LEFT JOIN expense_categories c ON c.id = e.category_id
             WHERE e.expense_date >= :from AND e.expense_date <= :to
             GROUP BY e.category_id, c.name
             ORDER BY total DESC",
            $bind
        );

        return [
            'servicesRevenue'   => $servicesRevenue,
            'productsRevenue'   => $productsRevenue,
            'totalExpenses'     => $totalExpenses,
            'netProfit'         => $servicesRevenue + $productsRevenue - $totalExpenses,
            'topServices'       => $topServices,
            'expensesByCategory' => $expensesByCategory,
        ];
    }
}
