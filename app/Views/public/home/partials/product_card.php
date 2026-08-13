<?php
/**
 * Tarjeta premium de producto.
 * Espera la variable $item (instancia App\Models\Product).
 */
use App\Core\View;
use App\Helpers\Money;
?>
<article class="card group bg-darksoft rounded-2xl overflow-hidden border border-white/5 hover:border-gold/40 flex flex-col">
    <div class="relative h-44 overflow-hidden">
        <?php if ($item->getAttribute('image')): ?>
            <img src="<?= UPLOAD_DIR . View::e($item->getAttribute('image')) ?>" alt="<?= View::e($item->getAttribute('name')) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
        <?php else: ?>
            <div class="w-full h-full bg-gradient-to-br from-dark via-darksoft to-darkdeep flex items-center justify-center">
                <span class="w-16 h-16 rounded-full border border-gold/30 flex items-center justify-center">
                    <i class="fa-solid fa-bottle-droplet text-gold text-2xl"></i>
                </span>
            </div>
        <?php endif; ?>
    </div>
    <div class="p-5 flex flex-col flex-1">
        <h3 class="font-display text-lg font-semibold text-white"><?= View::e($item->getAttribute('name')) ?></h3>
        <p class="mt-2 text-sm text-cream/70 leading-relaxed flex-1"><?= View::e($item->getAttribute('description')) ?></p>
        <div class="mt-4 pt-4 border-t border-white/5 flex items-center justify-between">
            <span class="text-lg font-bold text-goldlight"><?= Money::format((float) $item->getAttribute('price')) ?></span>
            <?php if ((int) $item->getAttribute('stock') > 0): ?>
                <span class="text-[11px] uppercase tracking-wider text-emerald-400"><i class="fa-solid fa-circle-check mr-1"></i>Stock</span>
            <?php else: ?>
                <span class="text-[11px] uppercase tracking-wider text-red-400"><i class="fa-solid fa-circle-xmark mr-1"></i>Agotado</span>
            <?php endif; ?>
        </div>
    </div>
</article>
