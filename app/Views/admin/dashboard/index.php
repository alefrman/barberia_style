<?php
/**
 * Dashboard — Panel de Administración
 * KPIs financieros y gráficas de ingresos vs gastos (Chart.js).
 */
use App\Core\View;
use App\Helpers\Money;

$totalServices = $totalServices ?? 0;
$totalProducts = $totalProducts ?? 0;
$totalExpenses = $totalExpenses ?? 0;
$todayAppointments = $todayAppointments ?? 0;

$monthIncome = (float) ($monthIncome ?? 0);
$monthExpenses = (float) ($monthExpenses ?? 0);
$monthProfit = (float) ($monthProfit ?? 0);
$monthTicket = (float) ($monthTicket ?? 0);
?>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 flex items-center gap-4 hover:border-gold/30 transition">
        <span class="w-12 h-12 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-calendar-check text-gold"></i>
        </span>
        <div>
            <p class="font-display text-2xl font-semibold text-white"><?= $todayAppointments ?></p>
            <p class="text-[11px] uppercase tracking-widest text-cream/50">Citas de hoy</p>
        </div>
    </div>

    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 flex items-center gap-4 hover:border-gold/30 transition">
        <span class="w-12 h-12 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-scissors text-gold"></i>
        </span>
        <div>
            <p class="font-display text-2xl font-semibold text-white"><?= $totalServices ?></p>
            <p class="text-[11px] uppercase tracking-widest text-cream/50">Servicios</p>
        </div>
    </div>

    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 flex items-center gap-4 hover:border-gold/30 transition">
        <span class="w-12 h-12 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-boxes-stacked text-gold"></i>
        </span>
        <div>
            <p class="font-display text-2xl font-semibold text-white"><?= $totalProducts ?></p>
            <p class="text-[11px] uppercase tracking-widest text-cream/50">Productos</p>
        </div>
    </div>

    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 flex items-center gap-4 hover:border-gold/30 transition">
        <span class="w-12 h-12 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-money-bill-transfer text-gold"></i>
        </span>
        <div>
            <p class="font-display text-2xl font-semibold text-white"><?= $totalExpenses ?></p>
            <p class="text-[11px] uppercase tracking-widest text-cream/50">Gastos</p>
        </div>
    </div>
</div>

<!-- ============ KPIs FINANCIEROS ============ -->
<div class="mt-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 hover:border-emerald-500/30 transition">
        <p class="text-[11px] uppercase tracking-widest text-cream/50">Ingresos del mes</p>
        <p class="mt-2 font-display text-2xl font-semibold text-emerald-400"><?= Money::format($monthIncome) ?></p>
        <p class="mt-1 text-xs text-cream/40">Solo citas completadas</p>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 hover:border-red-500/30 transition">
        <p class="text-[11px] uppercase tracking-widest text-cream/50">Gastos del mes</p>
        <p class="mt-2 font-display text-2xl font-semibold text-red-400"><?= Money::format($monthExpenses) ?></p>
        <p class="mt-1 text-xs text-cream/40">Registrados en gastos</p>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 hover:border-gold/30 transition">
        <p class="text-[11px] uppercase tracking-widest text-cream/50">Ganancia neta</p>
        <p class="mt-2 font-display text-2xl font-semibold <?= $monthProfit >= 0 ? 'text-goldlight' : 'text-red-400' ?>"><?= Money::format($monthProfit) ?></p>
        <p class="mt-1 text-xs text-cream/40">Ingresos − gastos</p>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 hover:border-sky-500/30 transition">
        <p class="text-[11px] uppercase tracking-widest text-cream/50">Ticket promedio</p>
        <p class="mt-2 font-display text-2xl font-semibold text-sky-400"><?= Money::format($monthTicket) ?></p>
        <p class="mt-1 text-xs text-cream/40">Por cita completada</p>
    </div>
</div>

<!-- ============ GRÁFICAS ============ -->
<div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-5">

    <div class="lg:col-span-2 bg-darksoft rounded-2xl border border-white/5 p-6">
        <h3 class="font-display text-lg font-semibold text-white">Ingresos vs Gastos</h3>
        <p class="text-xs text-cream/50 mt-1">Comparativa mensual de citas completadas contra gastos registrados.</p>
        <div class="h-72 mt-4">
            <canvas id="chart-income-expense"></canvas>
        </div>
    </div>

    <div class="bg-darksoft rounded-2xl border border-white/5 p-6">
        <h3 class="font-display text-lg font-semibold text-white">Ganancia neta mensual</h3>
        <p class="text-xs text-cream/50 mt-1">Ingresos menos gastos por mes.</p>
        <div class="h-72 mt-4">
            <canvas id="chart-profit"></canvas>
        </div>
    </div>

    <div class="bg-darksoft rounded-2xl border border-white/5 p-6">
        <h3 class="font-display text-lg font-semibold text-white">Gastos por categoría</h3>
        <p class="text-xs text-cream/50 mt-1">Distribución de gastos del mes.</p>
        <div class="h-64 mt-4">
            <canvas id="chart-category"></canvas>
        </div>
    </div>

    <div class="bg-darksoft rounded-2xl border border-white/5 p-6">
        <h3 class="font-display text-lg font-semibold text-white">Gastos por método de pago</h3>
        <p class="text-xs text-cream/50 mt-1">Cómo se pagan los gastos del mes.</p>
        <div class="h-64 mt-4">
            <canvas id="chart-method"></canvas>
        </div>
    </div>

    <div class="bg-darksoft rounded-2xl border border-white/5 p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="font-display text-lg font-semibold text-white">Estados de cita</h3>
                <p class="text-xs text-cream/50 mt-1"><?= View::e($statusRange) ?></p>
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="?period=month" class="px-3 py-1.5 rounded-lg text-[11px] uppercase tracking-widest font-bold transition <?= $statusPeriod === 'month' ? 'bg-gold text-darkdeep' : 'bg-dark border border-white/10 text-cream/70 hover:border-gold/40' ?>">Mes</a>
                <a href="?period=week" class="px-3 py-1.5 rounded-lg text-[11px] uppercase tracking-widest font-bold transition <?= $statusPeriod === 'week' ? 'bg-gold text-darkdeep' : 'bg-dark border border-white/10 text-cream/70 hover:border-gold/40' ?>">Semana</a>
            </div>
        </div>
        <div class="h-64 mt-4">
            <canvas id="chart-status"></canvas>
        </div>
    </div>

    <div class="lg:col-span-2 bg-darksoft rounded-2xl border border-white/5 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="font-display text-lg font-semibold text-white">Top productos más vendidos</h3>
                <p class="text-xs text-cream/50 mt-1">Unidades vendidas por producto en citas completadas.</p>
            </div>
            <div class="shrink-0 rounded-xl bg-gold/10 border border-gold/30 px-4 py-2 text-center">
                <p class="font-display text-xl font-semibold text-goldlight"><?= number_format((int) $topProductsTotal) ?></p>
                <p class="text-[10px] uppercase tracking-widest text-cream/50">unidades totales</p>
            </div>
        </div>
        <div class="h-72 mt-4">
            <canvas id="chart-top-products"></canvas>
        </div>
    </div>
</div>

<div class="mt-8 bg-darksoft rounded-2xl border border-white/5 p-8">
    <div class="text-center">
        <span class="inline-flex w-14 h-14 items-center justify-center rounded-full border border-gold/30 bg-gold/5">
            <i class="fa-solid fa-user-gear text-gold text-xl"></i>
        </span>
        <h2 class="mt-4 font-display text-2xl font-semibold text-goldlight">Bienvenido, <?= View::e($user->getAttribute('name')) ?></h2>
        <p class="mt-2 text-sm text-cream/60 max-w-lg mx-auto">
            El panel de administración está listo. Desde el menú lateral podrás gestionar
            citas, inventario, servicios, barberos y gastos.
        </p>
        <p class="mt-3 text-[11px] uppercase tracking-widest text-cream/40">
            Rol: <?= View::e($user->roleName() ?? 'Sin rol') ?>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        const cream = 'rgba(254, 249, 195, 0.6)';
        const borderColor = 'rgba(255, 255, 255, 0.08)';

        const palette = ['#F59E0B', '#FBBF24', '#F87171', '#60A5FA', '#34D399', '#C084FC', '#F472B6', '#94A3B8'];

        const baseTooltip = {
            backgroundColor: '#1F2937',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            titleColor: '#FBBF24',
            bodyColor: '#FEF9C3',
            padding: 10,
            cornerRadius: 10,
        };

        const axisStyle = {
            grid: { color: borderColor },
            ticks: { color: cream, font: { family: 'Inter', size: 11 } },
        };

        // 1. Barras: Ingresos vs Gastos
        new Chart(document.getElementById('chart-income-expense'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [
                    { label: 'Ingresos', data: <?= json_encode($chartIncome) ?>, backgroundColor: 'rgba(251, 191, 36, 0.85)', borderRadius: 6, maxBarThickness: 26 },
                    { label: 'Gastos', data: <?= json_encode($chartExpenses) ?>, backgroundColor: 'rgba(248, 113, 113, 0.85)', borderRadius: 6, maxBarThickness: 26 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: cream, font: { family: 'Inter', size: 12 } } }, tooltip: { ...baseTooltip, callbacks: { label: (c) => c.dataset.label + ': $' + Number(c.parsed.y).toFixed(2) } } },
                scales: { x: axisStyle, y: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, callback: (v) => '$' + Number(v).toFixed(0) } } },
            },
        });

        // 2. Línea: Ganancia neta
        new Chart(document.getElementById('chart-profit'), {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Ganancia neta',
                    data: <?= json_encode($chartProfit) ?>,
                    borderColor: '#34D399',
                    backgroundColor: 'rgba(52, 211, 153, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#34D399',
                    pointRadius: 3,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: cream, font: { family: 'Inter', size: 12 } } }, tooltip: { ...baseTooltip, callbacks: { label: (c) => c.dataset.label + ': $' + Number(c.parsed.y).toFixed(2) } } },
                scales: { x: axisStyle, y: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, callback: (v) => '$' + Number(v).toFixed(0) } } },
            },
        });

        // 3. Dona: Gastos por categoría
        const categoryRows = <?= json_encode($expensesByCategory) ?>;
        new Chart(document.getElementById('chart-category'), {
            type: 'doughnut',
            data: {
                labels: categoryRows.map(r => r.name),
                datasets: [{
                    data: categoryRows.map(r => Number(r.total)),
                    backgroundColor: palette,
                    borderColor: '#111827',
                    borderWidth: 2,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: cream, font: { family: 'Inter', size: 12 }, boxWidth: 12, padding: 12 } },
                    tooltip: { ...baseTooltip, callbacks: { label: (c) => c.label + ': $' + Number(c.parsed).toFixed(2) } },
                },
            },
        });

        // 4. Dona: Gastos por método de pago
        const methodRows = <?= json_encode($expensesByMethod) ?>;
        new Chart(document.getElementById('chart-method'), {
            type: 'doughnut',
            data: {
                labels: methodRows.map(r => r.name),
                datasets: [{
                    data: methodRows.map(r => Number(r.total)),
                    backgroundColor: ['#34D399', '#60A5FA', '#FBBF24', '#C084FC'],
                    borderColor: '#111827',
                    borderWidth: 2,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: cream, font: { family: 'Inter', size: 12 }, boxWidth: 12, padding: 12 } },
                    tooltip: { ...baseTooltip, callbacks: { label: (c) => c.label + ': $' + Number(c.parsed).toFixed(2) } },
                },
            },
        });

        // 5. Barras: Estados de cita (Completada / No asistió / Cancelada)
        new Chart(document.getElementById('chart-status'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($statusLabels) ?>,
                datasets: [{
                    label: 'Citas',
                    data: <?= json_encode($statusCounts) ?>,
                    backgroundColor: ['rgba(52, 211, 153, 0.85)', 'rgba(148, 163, 184, 0.85)', 'rgba(248, 113, 113, 0.85)'],
                    borderRadius: 6,
                    maxBarThickness: 42,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { ...baseTooltip, callbacks: { label: (c) => c.dataset.label + ': ' + c.parsed.y } } },
                scales: { x: axisStyle, y: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, stepSize: 1 } } },
            },
        });

        // Plugin inline: muestra el valor al final de cada barra horizontal
        const endLabels = {
            id: 'endLabels',
            defaults: { color: 'rgba(254, 249, 195, 0.85)', font: 'bold 11px Inter', format: (v) => v },
            afterDatasetsDraw(chart, _args, opts) {
                const { ctx } = chart;
                chart.data.datasets.forEach((ds, di) => {
                    const meta = chart.getDatasetMeta(di);
                    meta.data.forEach((bar, i) => {
                        const value = Number(ds.data[i]);
                        if (value === 0) return;
                        ctx.save();
                        ctx.fillStyle = opts.color;
                        ctx.font = opts.font;
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(opts.format(value), bar.x + 8, bar.y);
                        ctx.restore();
                    });
                });
            },
        };

        // 6. Barras horizontales: Top productos más vendidos
        const topProducts = <?= json_encode($topProducts) ?>;
        new Chart(document.getElementById('chart-top-products'), {
            type: 'bar',
            data: {
                labels: topProducts.map(r => r.name),
                datasets: [{
                    label: 'Unidades',
                    data: topProducts.map(r => Number(r.units)),
                    backgroundColor: 'rgba(251, 191, 36, 0.85)',
                    borderRadius: 6,
                    maxBarThickness: 18,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { left: 8, right: 28 } },
                plugins: {
                    legend: { display: false },
                    tooltip: { ...baseTooltip, callbacks: { label: (c) => 'Ventas: $' + Number(topProducts[c.dataIndex].money).toFixed(2) } },
                    endLabels: { color: '#FBBF24' },
                },
                scales: {
                    x: { ...axisStyle, beginAtZero: true, ticks: { ...axisStyle.ticks, stepSize: 1 } },
                    y: {
                        ...axisStyle,
                        ticks: { ...axisStyle.ticks, autoSkip: false },
                        afterFit: (axis) => { axis.width = Math.max(axis.width, 150); },
                    },
                },
            },
            plugins: [endLabels],
        });
    })();
</script>
