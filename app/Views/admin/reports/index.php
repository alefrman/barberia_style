<?php
use App\Core\View;
use App\Helpers\Money;
?>
<!-- ============ REPORTE FINANCIERO ============ -->
<div class="max-w-6xl mx-auto">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
                <i class="fa-solid fa-chart-pie text-gold"></i>
            </div>
            <div>
                <h2 class="font-display text-2xl font-semibold text-white">Reporte Financiero</h2>
                <p class="mt-1 text-sm text-cream/50">Ingresos por servicios, productos y gastos en un período.</p>
            </div>
        </div>
        <a href="<?= ADMIN_URL ?>/reports/pdf?from=<?= View::e($from) ?>&to=<?= View::e($to) ?>" target="_blank"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20">
            <i class="fa-solid fa-file-pdf"></i> Descargar PDF
        </a>
    </div>

    <!-- Filtro de fechas -->
    <form method="GET" action="<?= ADMIN_URL ?>/reports" class="bg-darksoft rounded-2xl border border-white/5 p-6 sm:p-8 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="from">Desde</label>
                <input type="date" id="from" name="from" value="<?= View::e($from) ?>"
                       class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 transition">
            </div>
            <div class="flex-1">
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="to">Hasta</label>
                <input type="date" id="to" name="to" value="<?= View::e($to) ?>"
                       class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 transition">
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold/10 border border-gold/30 text-goldlight text-xs font-bold uppercase tracking-[.2em] hover:bg-gold/20 transition whitespace-nowrap">
                <i class="fa-solid fa-magnifying-glass"></i> Generar
            </button>
        </div>
    </form>

    <!-- Tarjetas resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-darksoft rounded-2xl border border-white/5 p-6 text-center">
            <p class="text-[11px] uppercase tracking-[.2em] text-cream/50 mb-2">Servicios</p>
            <p class="font-display text-3xl font-bold text-emerald-400"><?= Money::format($servicesRevenue) ?></p>
            <p class="mt-1 text-[11px] text-cream/40">Ingresos por cortes y servicios</p>
        </div>
        <div class="bg-darksoft rounded-2xl border border-white/5 p-6 text-center">
            <p class="text-[11px] uppercase tracking-[.2em] text-cream/50 mb-2">Productos</p>
            <p class="font-display text-3xl font-bold text-sky-400"><?= Money::format($productsRevenue) ?></p>
            <p class="mt-1 text-[11px] text-cream/40">Ingresos por venta de productos</p>
        </div>
        <div class="bg-darksoft rounded-2xl border border-white/5 p-6 text-center">
            <p class="text-[11px] uppercase tracking-[.2em] text-cream/50 mb-2">Gastos</p>
            <p class="font-display text-3xl font-bold text-red-400"><?= Money::format($totalExpenses) ?></p>
            <p class="mt-1 text-[11px] text-cream/40">Total de gastos del período</p>
        </div>
        <div class="bg-darksoft rounded-2xl border border-white/5 p-6 text-center">
            <p class="text-[11px] uppercase tracking-[.2em] text-cream/50 mb-2">Ganancia Neta</p>
            <p class="font-display text-3xl font-bold <?= $netProfit >= 0 ? 'text-goldlight' : 'text-red-400' ?>"><?= Money::format($netProfit) ?></p>
            <p class="mt-1 text-[11px] text-cream/40">(Servicios + Productos) − Gastos</p>
        </div>
    </div>

    <!-- Citas por barbero (dona) -->
    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 mb-8">
        <h3 class="font-display text-lg font-semibold text-white"><i class="fa-solid fa-users mr-2"></i>Citas por Barbero</h3>
        <p class="text-xs text-cream/50 mt-1">Distribución de citas completadas por barbero en el período.</p>
        <div class="flex flex-col sm:flex-row items-center gap-6 mt-4">
            <div class="h-56 flex-1 w-full">
                <canvas id="chart-barbers"></canvas>
            </div>
            <?php if ($barberStats !== []): ?>
            <div class="shrink-0 w-full sm:w-auto">
                <table class="w-full text-sm">
                    <tbody>
                    <?php $barberTotal = max(array_sum(array_map(fn ($b) => (int) $b['total'], $barberStats)), 1); ?>
                    <?php foreach ($barberStats as $bs): ?>
                    <tr class="border-b border-white/5 last:border-0">
                        <td class="py-2 pr-4 font-medium text-white"><?= View::e($bs['name']) ?></td>
                        <td class="py-2 pr-3 text-center text-cream/60"><?= (int) $bs['total'] ?></td>
                        <td class="py-2 text-right text-cream/40 text-xs"><?= round(((int) $bs['total'] / $barberTotal) * 100, 1) ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tablas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top servicios -->
        <div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h3 class="font-display text-base font-semibold text-goldlight"><i class="fa-solid fa-scissors mr-2"></i>Top Servicios</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="px-6 py-3 text-left text-[11px] uppercase tracking-widest text-cream/50">Servicio</th>
                        <th class="px-6 py-3 text-center text-[11px] uppercase tracking-widest text-cream/50">Citas</th>
                        <th class="px-6 py-3 text-right text-[11px] uppercase tracking-widest text-cream/50">Ingreso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($topServices === []): ?>
                    <tr><td colspan="3" class="px-6 py-10 text-center text-cream/40">Sin datos en este período</td></tr>
                    <?php else: ?>
                    <?php foreach ($topServices as $ts): ?>
                    <tr class="border-b border-white/5 last:border-0 hover:bg-gold/5 transition">
                        <td class="px-6 py-3.5 font-medium text-white"><?= View::e($ts['name']) ?></td>
                        <td class="px-6 py-3.5 text-center text-cream/60"><?= (int) $ts['count'] ?></td>
                        <td class="px-6 py-3.5 text-right font-semibold text-emerald-400"><?= Money::format((float) $ts['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Gastos por categoría -->
        <div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h3 class="font-display text-base font-semibold text-goldlight"><i class="fa-solid fa-receipt mr-2"></i>Gastos por Categoría</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="px-6 py-3 text-left text-[11px] uppercase tracking-widest text-cream/50">Categoría</th>
                        <th class="px-6 py-3 text-right text-[11px] uppercase tracking-widest text-cream/50">Total</th>
                        <th class="px-6 py-3 text-right text-[11px] uppercase tracking-widest text-cream/50">%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($expensesByCategory === []): ?>
                    <tr><td colspan="3" class="px-6 py-10 text-center text-cream/40">Sin gastos en este período</td></tr>
                    <?php else: ?>
                    <?php $totalPctBase = max($totalExpenses, 0.01); ?>
                    <?php foreach ($expensesByCategory as $ec): ?>
                    <?php $pct = round(((float) $ec['total'] / $totalPctBase) * 100, 1); ?>
                    <tr class="border-b border-white/5 last:border-0 hover:bg-gold/5 transition">
                        <td class="px-6 py-3.5 font-medium text-white"><?= View::e($ec['name']) ?></td>
                        <td class="px-6 py-3.5 text-right font-semibold text-red-400"><?= Money::format((float) $ec['total']) ?></td>
                        <td class="px-6 py-3.5 text-right text-cream/50"><?= $pct ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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

    const barberRows = <?= json_encode($barberStats) ?>;
    new Chart(document.getElementById('chart-barbers'), {
        type: 'doughnut',
        data: {
            labels: barberRows.map(r => r.name),
            datasets: [{
                data: barberRows.map(r => Number(r.total)),
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
                tooltip: { ...baseTooltip, callbacks: { label: (c) => c.label + ': ' + Number(c.parsed) + ' citas' } },
            },
        },
    });
})();
</script>
