<?php
/**
 * Tarjeta premium de servicio.
 * Espera la variable $item (instancia App\Models\Service).
 */
use App\Core\View;
use App\Helpers\Money;
?>
<article class="card group bg-darksoft rounded-2xl overflow-hidden border border-white/5 hover:border-gold/40">
    <div class="relative h-56 overflow-hidden">
        <?php if ($item->getAttribute('image')): ?>
            <img src="<?= UPLOAD_DIR . View::e($item->getAttribute('image')) ?>" alt="<?= View::e($item->getAttribute('name')) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
        <?php else: ?>
            <div class="w-full h-full bg-gradient-to-br from-dark via-darksoft to-darkdeep flex items-center justify-center">
                <span class="w-20 h-20 rounded-full border border-gold/30 flex items-center justify-center">
                    <i class="fa-solid fa-scissors text-gold text-2xl"></i>
                </span>
            </div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-darkdeep/80 via-transparent to-transparent"></div>
        <div class="absolute top-4 right-4 px-4 py-1.5 rounded-full bg-gold text-darkdeep text-sm font-bold shadow-lg shadow-black/30">
            <?= Money::format((float) $item->getAttribute('price')) ?>
        </div>
    </div>
    <div class="p-6">
        <h3 class="font-display text-xl font-semibold text-white"><?= View::e($item->getAttribute('name')) ?></h3>
        <div class="mt-2 h-px w-10 bg-gold/60 transition-all duration-500 group-hover:w-16"></div>
        <p class="mt-3 text-sm leading-relaxed text-cream/70"><?= View::e($item->getAttribute('description')) ?></p>
        <?php if ($item->getAttribute('duration')): ?>
            <p class="mt-4 text-[11px] uppercase tracking-[.2em] text-goldlight/80">
                <i class="fa-regular fa-clock mr-2"></i><?= View::e($item->getAttribute('duration')) ?> min
            </p>
        <?php endif; ?>
    </div>
</article>
