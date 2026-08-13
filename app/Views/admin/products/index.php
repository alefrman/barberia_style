<?php
/**
 * Listado de Inventario — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Money;
use App\Helpers\Session;

$f = $filters;
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h2 class="font-display text-2xl font-semibold text-white">Inventario</h2>
        <p class="mt-1 text-sm text-cream/50">Productos en venta con control de stock.</p>
    </div>
    <a href="<?= ADMIN_URL ?>/inventory/create" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-widest hover:bg-goldlight transition btn-shine">
        <i class="fa-solid fa-boxes-stacked"></i> Nuevo producto
    </a>
</div>

<!-- Resumen -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center text-goldlight"><i class="fa-solid fa-box-open"></i></div>
            <div>
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Productos</p>
                <p class="font-display text-2xl font-semibold text-white"><?= (int) $summary['total'] ?></p>
            </div>
        </div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center text-goldlight"><i class="fa-solid fa-cubes"></i></div>
            <div>
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Unidades</p>
                <p class="font-display text-2xl font-semibold text-white"><?= (int) $summary['units'] ?></p>
            </div>
        </div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-amber-400/10 border border-amber-400/30 flex items-center justify-center text-amber-300"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Stock bajo</p>
                <p class="font-display text-2xl font-semibold text-amber-300"><?= (int) $summary['low'] ?></p>
            </div>
        </div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400"><i class="fa-solid fa-box-archive"></i></div>
            <div>
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Agotados</p>
                <p class="font-display text-2xl font-semibold text-red-400"><?= (int) $summary['out'] ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="<?= ADMIN_URL ?>/inventory" class="bg-darksoft rounded-2xl border border-white/5 p-4 mb-8 flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-cream/40 text-sm"></i>
        <input type="text" name="q" value="<?= View::e($f['q']) ?>" placeholder="Buscar producto..." class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 placeholder:text-cream/30">
    </div>
    <select name="category_id" class="sm:w-56 px-4 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
        <option value="0">Todas las categorías</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c->getAttribute('id') ?>" <?= (int) $f['category_id'] === (int) $c->getAttribute('id') ? 'selected' : '' ?>><?= View::e($c->getAttribute('name')) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="stock" class="sm:w-44 px-4 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
        <option value="">Todo el stock</option>
        <option value="low" <?= $f['stock'] === 'low' ? 'selected' : '' ?>>Stock bajo</option>
        <option value="out" <?= $f['stock'] === 'out' ? 'selected' : '' ?>>Agotados</option>
    </select>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-widest hover:bg-goldlight transition">Filtrar</button>
    <?php if ($f['q'] !== '' || (int) $f['category_id'] > 0 || $f['stock'] !== ''): ?>
        <a href="<?= ADMIN_URL ?>/inventory" class="px-4 py-2.5 rounded-xl border border-white/10 text-cream/70 text-xs font-bold uppercase tracking-widest hover:border-gold/40 hover:text-goldlight transition inline-flex items-center justify-center">Limpiar</a>
    <?php endif; ?>
</form>

<div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-widest text-cream/50 border-b border-white/10">
                    <th class="px-6 py-4">Producto</th>
                    <th class="px-6 py-4">Categoría</th>
                    <th class="px-6 py-4">Precio</th>
                    <th class="px-6 py-4">Costo</th>
                    <th class="px-6 py-4">Stock</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <?php $stock = (int) $r['stock']; $min = (int) $r['min_stock']; ?>
                <tr class="border-b border-white/5 hover:bg-gold/5 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-dark border border-white/10 flex-shrink-0">
                                <?php if ($r['image']): ?>
                                    <img src="<?= UPLOAD_DIR . View::e($r['image']) ?>" alt="<?= View::e($r['name']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-box text-gold/60"></i></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-medium text-white"><?= View::e($r['name']) ?></p>
                                <?php if ($r['description']): ?>
                                    <p class="text-xs text-cream/50 max-w-xs truncate"><?= View::e($r['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($r['category_name']): ?>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-gold/10 text-goldlight border border-gold/30"><?= View::e($r['category_name']) ?></span>
                        <?php else: ?>
                            <span class="text-cream/40">Sin categoría</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 font-semibold text-goldlight"><?= Money::format((float) $r['price']) ?></td>
                    <td class="px-6 py-4 text-cream/70"><?= Money::format((float) $r['cost']) ?></td>
                    <td class="px-6 py-4">
                        <span class="<?= $stock <= 0 ? 'text-red-400' : ($stock <= $min ? 'text-amber-300' : 'text-emerald-400') ?> font-semibold">
                            <?= $stock ?> <span class="font-normal text-cream/40 text-xs">/ min <?= $min ?></span>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ((int) $r['is_active'] === 1): ?>
                            <span class="flex items-center gap-2 text-emerald-400"><i class="fa-solid fa-circle text-[8px]"></i>Activo</span>
                        <?php else: ?>
                            <span class="flex items-center gap-2 text-red-400"><i class="fa-solid fa-circle text-[8px]"></i>Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= ADMIN_URL ?>/inventory/edit/<?= (int) $r['id'] ?>" title="Editar" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="<?= ADMIN_URL ?>/inventory/delete/<?= (int) $r['id'] ?>" onsubmit="return confirm('¿Eliminar el producto <?= addslashes((string) $r['name']) ?>?');">
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
            <i class="fa-solid fa-boxes-stacked text-4xl text-gold/40 mb-4"></i>
            <p class="font-display text-lg text-white/80">No hay productos registrados</p>
            <p class="text-sm mt-1">Crea tu primer producto para venderlo en el sitio web y usarlo en las citas.</p>
        </div>
    <?php endif; ?>
</div>
