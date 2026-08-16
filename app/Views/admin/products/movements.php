<?php
/**
 * Historial de movimientos de inventario — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Money;
use App\Helpers\Session;

$pid = (int) $product->getAttribute('id');
$productName = (string) $product->getAttribute('name');
$minStock = (int) $product->getAttribute('min_stock');

$typeMeta = [
    'creation' => ['label' => 'Creación',  'class' => 'bg-gold/10 text-goldlight border-gold/30',    'icon' => 'fa-box-open'],
    'restock'  => ['label' => 'Reposición', 'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30', 'icon' => 'fa-boxes-packing'],
    'edit'     => ['label' => 'Ajuste',    'class' => 'bg-sky-500/10 text-sky-400 border-sky-500/30', 'icon' => 'fa-pen'],
];
?>
<div class="max-w-5xl">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="<?= ADMIN_URL ?>/inventory" class="w-10 h-10 rounded-xl bg-darksoft border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="font-display text-2xl font-semibold text-white">Historial de stock</h2>
                <p class="mt-1 text-sm text-cream/50"><?= View::e($productName) ?></p>
            </div>
        </div>
        <a href="<?= ADMIN_URL ?>/inventory/edit/<?= $pid ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 text-cream/70 font-bold uppercase text-xs tracking-widest hover:border-gold/40 hover:text-goldlight transition">
            <i class="fa-solid fa-pen"></i> Editar producto
        </a>
    </div>

    <!-- Resumen del producto -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center text-goldlight"><i class="fa-solid fa-cubes"></i></div>
                <div>
                    <p class="text-[11px] uppercase tracking-widest text-cream/50">Stock actual</p>
                    <p class="font-display text-2xl font-semibold text-white"><?= $stock ?> <span class="text-sm font-normal text-cream/40">/ min <?= $minStock ?></span></p>
                </div>
            </div>
        </div>
        <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center text-goldlight"><i class="fa-solid fa-chart-line"></i></div>
                <div>
                    <p class="text-[11px] uppercase tracking-widest text-cream/50">Ganancia pendiente</p>
                    <p class="font-display text-2xl font-semibold <?= $profit < 0 ? 'text-red-400' : 'text-goldlight' ?>"><?= Money::format($profit) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center text-goldlight"><i class="fa-solid fa-tags"></i></div>
                <div>
                    <p class="text-[11px] uppercase tracking-widest text-cream/50">Precio</p>
                    <p class="font-display text-2xl font-semibold text-white"><?= Money::format((float) $product->getAttribute('price')) ?></p>
                </div>
            </div>
        </div>
        <div class="bg-darksoft rounded-2xl border border-white/5 p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center text-goldlight"><i class="fa-solid fa-money-bill-wave"></i></div>
                <div>
                    <p class="text-[11px] uppercase tracking-widest text-cream/50">Costo</p>
                    <p class="font-display text-2xl font-semibold text-white"><?= Money::format((float) $product->getAttribute('cost')) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Reponer stock -->
    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 mb-8">
        <h3 class="font-display text-lg font-semibold text-goldlight flex items-center gap-2">
            <i class="fa-solid fa-boxes-packing"></i> Reponer stock
        </h3>
        <p class="mt-1 text-sm text-cream/50">Suma unidades al inventario actual y se registrarán en el historial.</p>
        <form method="POST" action="<?= ADMIN_URL ?>/inventory/<?= $pid ?>/restock" class="mt-5 flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
            <div class="flex-1">
                <input type="number" id="quantity" name="quantity" required min="1" max="99999" value="1" placeholder="Cantidad" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>
            <div class="flex-[2]">
                <input type="text" name="note" maxlength="255" placeholder="Nota (opcional): compra a proveedor, reposición..." class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>
            <button type="submit" class="px-6 py-3 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-widest hover:bg-goldlight transition btn-shine whitespace-nowrap">
                <i class="fa-solid fa-plus mr-1"></i> Reponer
            </button>
        </form>
    </div>

    <!-- Movimientos -->
    <div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-widest text-cream/50 border-b border-white/10">
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Tipo</th>
                        <th class="px-6 py-4">Cantidad</th>
                        <th class="px-6 py-4">Stock</th>
                        <th class="px-6 py-4">Nota</th>
                        <th class="px-6 py-4">Registró</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $m): ?>
                    <?php
                    $qty = (int) $m['quantity'];
                    $meta = $typeMeta[$m['type']] ?? ['label' => ucfirst((string) $m['type']), 'class' => 'bg-dark text-cream/70 border-white/10', 'icon' => 'fa-circle'];
                    ?>
                    <tr class="border-b border-white/5 hover:bg-gold/5 transition">
                        <td class="px-6 py-4 text-cream/70 whitespace-nowrap"><?= View::e(date('d/m/Y H:i', strtotime((string) $m['created_at']))) ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border <?= $meta['class'] ?>">
                                <i class="fa-solid <?= $meta['icon'] ?>"></i> <?= View::e($meta['label']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 font-semibold <?= $qty > 0 ? 'text-emerald-400' : ($qty < 0 ? 'text-red-400' : 'text-cream/70') ?>">
                            <?= $qty > 0 ? '+' : '' ?><?= $qty ?>
                        </td>
                        <td class="px-6 py-4 text-cream/70 whitespace-nowrap"><?= (int) $m['stock_before'] ?> → <?= (int) $m['stock_after'] ?></td>
                        <td class="px-6 py-4 text-cream/70 max-w-xs truncate"><?= $m['note'] !== null && $m['note'] !== '' ? View::e($m['note']) : '<span class="text-cream/40">—</span>' ?></td>
                        <td class="px-6 py-4 text-cream/70"><?= $m['user_name'] !== null ? View::e($m['user_name']) : '<span class="text-cream/40">—</span>' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($rows === []): ?>
            <div class="p-12 text-center text-cream/50">
                <i class="fa-solid fa-clock-rotate-left text-4xl text-gold/40 mb-4"></i>
                <p class="font-display text-lg text-white/80">Sin movimientos registrados</p>
                <p class="text-sm mt-1">Repón stock o edita el producto para comenzar el historial.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
