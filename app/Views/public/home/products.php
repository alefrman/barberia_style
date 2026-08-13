<?php
/**
 * Página de Productos — Barbería Style (Premium)
 */
use App\Core\View;
?>
<!-- Header -->
<section class="hero-bg relative pt-40 pb-20">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="reveal inline-flex items-center gap-3 text-goldlight eyebrow">
            <span class="h-px w-10 bg-gold/60"></span>Tienda
        </span>
        <h1 class="reveal mt-4 font-display text-5xl md:text-6xl font-semibold text-white" style="--delay:100ms">
            Productos <span class="text-gold-grad">premium</span>
        </h1>
        <p class="reveal mt-5 max-w-xl text-lg text-cream/70" style="--delay:200ms">
            Ceras, gelatina, lociones y más. Los mismos productos que usamos en cada corte.
        </p>
    </div>
</section>

<!-- Grid -->
<section class="py-16 md:py-20 bg-darkdeep">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if ($products === []): ?>
                <p class="reveal text-center text-cream/50 col-span-full py-10">Aún no hay productos registrados.</p>
            <?php else: ?>
                <?php foreach ($products as $i => $item): ?>
                    <div class="reveal" style="--delay:<?= ($i % 4) * 100 ?>ms">
                        <?php include __DIR__ . '/../home/partials/product_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
