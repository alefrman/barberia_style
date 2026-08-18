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

        $html = View::render('admin/reports/pdf', [
            'siteName'       => $siteName,
            'siteAddress'    => $siteAddress,
            'logo'           => $logo,
            'fromDisplay'    => date('d/m/Y', strtotime($from)),
            'toDisplay'      => date('d/m/Y', strtotime($to)),
            'now'            => date('d/m/Y H:i'),
        ] + $data);

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

        $completedCount = (int) Database::fetchValue(
            "SELECT COUNT(DISTINCT a.id)
             FROM appointments a
             INNER JOIN appointment_statuses s ON s.id = a.status_id
             WHERE LOWER(s.name) = 'completada' AND a.appointment_date >= :from AND a.appointment_date <= :to",
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
            'completedCount'    => $completedCount,
            'topServices'       => $topServices,
            'expensesByCategory' => $expensesByCategory,
        ];
    }
}
