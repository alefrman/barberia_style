<?php
/**
 * Listado de Gastos — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Money;
use App\Helpers\Session;

$f = $filters;
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h2 class="font-display text-2xl font-semibold text-white">Gastos</h2>
        <p class="mt-1 text-sm text-cream/50">Registro de salidas de efectivo del negocio.</p>
    </div>
    <a href="<?= ADMIN_URL ?>/expenses/create" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-widest hover:bg-goldlight transition btn-shine">
        <i class="fa-solid fa-money-bill-transfer"></i> Nuevo gasto
    </a>
</div>

<!-- Resumen -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400"><i class="fa-solid fa-calendar-day"></i></div>
            <div>
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Gastos hoy</p>
                <p class="font-display text-2xl font-semibold text-white"><?= Money::format($summary['today']) ?></p>
            </div>
        </div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400"><i class="fa-solid fa-calendar-week"></i></div>
            <div>
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Total del mes</p>
                <p class="font-display text-2xl font-semibold text-white"><?= Money::format($summary['month']) ?></p>
            </div>
        </div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center text-goldlight"><i class="fa-solid fa-receipt"></i></div>
            <div>
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Gastos del mes</p>
                <p class="font-display text-2xl font-semibold text-white"><?= (int) $summary['count'] ?></p>
            </div>
        </div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-amber-400/10 border border-amber-400/30 flex items-center justify-center text-amber-300"><i class="fa-solid fa-piggy-bank"></i></div>
            <div>
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Total acumulado</p>
                <p class="font-display text-2xl font-semibold text-amber-300"><?= Money::format($summary['total']) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="<?= ADMIN_URL ?>/expenses" class="bg-darksoft rounded-2xl border border-white/5 p-4 mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <div class="relative lg:col-span-1">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-cream/40 text-sm"></i>
        <input type="text" name="q" value="<?= View::e($f['q']) ?>" placeholder="Buscar gasto..." class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 placeholder:text-cream/30">
    </div>
    <select name="category_id" class="px-4 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
        <option value="0">Todas las categorías</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c->getAttribute('id') ?>" <?= (int) $f['category_id'] === (int) $c->getAttribute('id') ? 'selected' : '' ?>><?= View::e($c->getAttribute('name')) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="date" name="from" value="<?= View::e($f['from']) ?>" class="px-4 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60" title="Desde">
    <input type="date" name="to" value="<?= View::e($f['to']) ?>" class="px-4 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60" title="Hasta">
    <div class="flex gap-2">
        <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-widest hover:bg-goldlight transition">Filtrar</button>
        <?php if ($f['q'] !== '' || (int) $f['category_id'] > 0 || $f['from'] !== '' || $f['to'] !== ''): ?>
            <a href="<?= ADMIN_URL ?>/expenses" class="px-4 py-2.5 rounded-xl border border-white/10 text-cream/70 text-xs font-bold uppercase tracking-widest hover:border-gold/40 hover:text-goldlight transition inline-flex items-center justify-center">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-widest text-cream/50 border-b border-white/10">
                    <th class="px-6 py-4">Fecha</th>
                    <th class="px-6 py-4">Descripción</th>
                    <th class="px-6 py-4">Categoría</th>
                    <th class="px-6 py-4">Método</th>
                    <th class="px-6 py-4">Monto</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr class="border-b border-white/5 hover:bg-gold/5 transition">
                    <td class="px-6 py-4 text-cream/70 whitespace-nowrap"><?= View::e(date('d/m/Y', strtotime((string) $r['expense_date']))) ?></td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-white"><?= View::e($r['description']) ?></p>
                        <?php if ($r['notes']): ?>
                            <p class="text-xs text-cream/50 max-w-xs truncate"><?= View::e($r['notes']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($r['category_name']): ?>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-gold/10 text-goldlight border border-gold/30"><?= View::e($r['category_name']) ?></span>
                        <?php else: ?>
                            <span class="text-cream/40">Sin categoría</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-cream/70"><?= View::e($r['payment_method']) ?></td>
                    <td class="px-6 py-4 font-semibold text-red-400 whitespace-nowrap">- <?= Money::format((float) $r['amount']) ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= ADMIN_URL ?>/expenses/edit/<?= (int) $r['id'] ?>" title="Editar" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="<?= ADMIN_URL ?>/expenses/delete/<?= (int) $r['id'] ?>" onsubmit="return confirm('¿Eliminar el gasto <?= addslashes((string) $r['description']) ?>?');">
                                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                                <button type="submit" title="Eliminar" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-red-400 hover:border-red-500/40 transition">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($rows === []): ?>
        <div class="p-12 text-center text-cream/50">
            <i class="fa-solid fa-money-bill-transfer text-4xl text-gold/40 mb-4"></i>
            <p class="font-display text-lg text-white/80">No hay gastos registrados</p>
            <p class="text-sm mt-1">Registra tus gastos para llevar el control de salidas de efectivo.</p>
        </div>
    <?php endif; ?>
</div>
