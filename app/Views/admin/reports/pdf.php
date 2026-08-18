<?php
/**
 * Plantilla PDF — Reporte Financiero (dompdf)
 * Diseño claro y corporativo. CSS compatible con dompdf (sin flexbox).
 */
use App\Core\View;
use App\Helpers\Money;

$siteName    = $siteName ?? 'Barbería Style';
$siteAddress = $siteAddress ?? '';
$logo        = $logo ?? '';
$fromDisplay = $fromDisplay ?? '';
$toDisplay   = $toDisplay ?? '';
$now         = $now ?? '';

$servicesRevenue = $servicesRevenue ?? 0;
$productsRevenue = $productsRevenue ?? 0;
$totalExpenses   = $totalExpenses ?? 0;
$netProfit       = $netProfit ?? 0;
$completedCount  = $completedCount ?? 0;
$topServices     = $topServices ?? [];
$expensesByCategory = $expensesByCategory ?? [];

$topServicesRows = '';
if ($topServices !== []) {
    foreach ($topServices as $ts) {
        $topServicesRows .= '<tr>'
            . '<td>' . View::e($ts['name']) . '</td>'
            . '<td class="c">' . (int) $ts['count'] . '</td>'
            . '<td class="r">' . Money::format((float) $ts['total']) . '</td>'
            . '</tr>';
    }
    $topServicesTotal = array_sum(array_map(fn ($r) => (float) $r['total'], $topServices));
    $topServicesRows .= '<tr class="row-total">'
        . '<td>Total</td>'
        . '<td class="c">' . array_sum(array_map(fn ($r) => (int) $r['count'], $topServices)) . '</td>'
        . '<td class="r">' . Money::format($topServicesTotal) . '</td>'
        . '</tr>';
} else {
    $topServicesRows = '<tr><td colspan="3" class="empty">Sin datos en este período</td></tr>';
}

$expensesRows = '';
if ($expensesByCategory !== []) {
    $totalForPct = max($totalExpenses, 0.01);
    foreach ($expensesByCategory as $ec) {
        $pct = round(((float) $ec['total'] / $totalForPct) * 100, 1);
        $expensesRows .= '<tr>'
            . '<td>' . View::e($ec['name']) . '</td>'
            . '<td class="r">' . Money::format((float) $ec['total']) . '</td>'
            . '<td class="r">' . $pct . '%</td>'
            . '</tr>';
    }
    $expensesRows .= '<tr class="row-total">'
        . '<td>Total</td>'
        . '<td class="r">' . Money::format($totalExpenses) . '</td>'
        . '<td class="r">100%</td>'
        . '</tr>';
} else {
    $expensesRows = '<tr><td colspan="3" class="empty">Sin gastos en este período</td></tr>';
}

$logoHtml = $logo !== ''
    ? '<img src="' . $logo . '" style="height:42px;margin-right:10px;vertical-align:middle;">'
    : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: letter portrait;
            margin: 18mm 16mm 22mm 16mm;
            @bottom-center {
                content: "Página " counter(page) " de " counter(pages);
                font-family: Helvetica, Arial, sans-serif;
                font-size: 9px;
                color: #b0b7c0;
            }
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.45;
        }
        /* ---------- Encabezado ---------- */
        .header-band {
            border-bottom: 3px solid #1f3a5f;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-title { font-size: 22px; font-weight: bold; color: #1f3a5f; }
        .header-site { font-size: 12px; color: #666; margin-top: 2px; }
        .header-meta { text-align: right; font-size: 10px; color: #888; }
        /* ---------- Tarjetas resumen ---------- */
        .cards { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px 6px -8px; }
        .card {
            border: 1px solid #e3e8ef;
            border-top: 3px solid #1f3a5f;
            background: #f8fafc;
            padding: 10px 8px;
            text-align: center;
            vertical-align: top;
        }
        .card-label { font-size: 9px; text-transform: uppercase; letter-spacing: .08em; color: #6b7280; }
        .card-value { font-size: 18px; font-weight: bold; margin-top: 4px; }
        .v-green { color: #047857; }
        .v-blue  { color: #1d4ed8; }
        .v-red   { color: #b91c1c; }
        .v-navy  { color: #1f3a5f; }
        /* ---------- Secciones ---------- */
        .section-title {
            font-size: 14px; font-weight: bold; color: #1f3a5f;
            border-left: 4px solid #1f3a5f; padding-left: 8px; margin: 18px 0 8px;
        }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th {
            background: #1f3a5f; color: #fff; text-align: left;
            padding: 8px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: .05em;
        }
        table.data th.c, table.data td.c { text-align: center; }
        table.data th.r, table.data td.r { text-align: right; }
        table.data td { padding: 7px 12px; border-bottom: 1px solid #e5e7eb; }
        table.data tbody tr:nth-child(even) { background: #f8fafc; }
        .row-total { font-weight: bold; background: #eef2f7 !important; }
        .row-total td { border-top: 2px solid #1f3a5f; }
        .empty { text-align: center; color: #9ca3af; padding: 16px 12px; }
        /* ---------- Pie ---------- */
        .footer {
            margin-top: 24px; padding-top: 10px;
            border-top: 1px solid #e5e7eb; text-align: center;
            font-size: 9px; color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header-band">
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="vertical-align:middle;">
                    <?= $logoHtml ?>
                    <span class="header-title">Reporte Financiero</span>
                    <div class="header-site"><?= View::e($siteName) ?></div>
                </td>
                <td class="header-meta" style="vertical-align:middle;">
                    Período: <?= View::e($fromDisplay) ?> — <?= View::e($toDisplay) ?><br>
                    Generado: <?= View::e($now) ?>
                </td>
            </tr>
        </table>
    </div>

    <table class="cards">
        <tr>
            <td class="card">
                <div class="card-label">Ingresos Servicios</div>
                <div class="card-value v-green"><?= Money::format($servicesRevenue) ?></div>
            </td>
            <td class="card">
                <div class="card-label">Ingresos Productos</div>
                <div class="card-value v-blue"><?= Money::format($productsRevenue) ?></div>
            </td>
            <td class="card">
                <div class="card-label">Gastos</div>
                <div class="card-value v-red"><?= Money::format($totalExpenses) ?></div>
            </td>
            <td class="card">
                <div class="card-label">Ganancia Neta</div>
                <div class="card-value v-navy"><?= Money::format($netProfit) ?></div>
            </td>
        </tr>
    </table>

    <table style="width:100%;border-collapse:collapse;margin:2px 0 0;">
        <tr>
            <td style="font-size:10px;color:#6b7280;">
                Citas completadas en el período: <strong><?= (int) $completedCount ?></strong>
            </td>
        </tr>
    </table>

    <div class="section-title">Top Servicios</div>
    <table class="data">
        <thead>
            <tr><th>Servicio</th><th class="c">Citas</th><th class="r">Ingreso</th></tr>
        </thead>
        <tbody><?= $topServicesRows ?></tbody>
    </table>

    <div class="section-title">Gastos por Categoría</div>
    <table class="data">
        <thead>
            <tr><th>Categoría</th><th class="r">Total</th><th class="r">%</th></tr>
        </thead>
        <tbody><?= $expensesRows ?></tbody>
    </table>

    <div class="footer">
        <?= View::e($siteName) ?><?= $siteAddress !== '' ? ' — ' . View::e($siteAddress) : '' ?>
    </div>
</body>
</html>