<?php
/**
 * Tarjeta premium de barbero.
 * Espera la variable $item (instancia App\Models\Team).
 */
use App\Core\View;
?>
<article class="card group bg-darksoft rounded-2xl overflow-hidden border border-white/5 hover:border-gold/40">
    <div class="relative h-64 overflow-hidden">
        <?php if ($item->getAttribute('image')): ?>
            <img src="<?= UPLOAD_DIR . View::e($item->getAttribute('image')) ?>" alt="<?= View::e($item->getAttribute('name')) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
        <?php else: ?>
            <div class="w-full h-full bg-gradient-to-br from-dark via-darksoft to-darkdeep flex items-center justify-center">
                <span class="w-20 h-20 rounded-full border border-gold/30 flex items-center justify-center">
                    <i class="fa-solid fa-user-tie text-gold text-3xl"></i>
                </span>
            </div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-darkdeep/90 via-darkdeep/20 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 p-5 text-center">
            <h3 class="font-display text-xl font-semibold text-white"><?= View::e($item->getAttribute('name')) ?></h3>
            <p class="mt-1 text-[11px] uppercase tracking-[.25em] text-goldlight"><?= View::e($item->getAttribute('position')) ?></p>
        </div>
    </div>
    <div class="p-5 text-center">
        <p class="text-sm text-cream/70 leading-relaxed"><?= View::e($item->getAttribute('description')) ?></p>
    </div>
</article>
