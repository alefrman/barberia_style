<?php
/**
 * Página de Servicios — Barbería Style (Premium)
 */
use App\Core\View;
?>
<!-- Header -->
<section class="hero-bg relative pt-40 pb-20">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="reveal inline-flex items-center gap-3 text-goldlight eyebrow">
            <span class="h-px w-10 bg-gold/60"></span>Lo que hacemos
        </span>
        <h1 class="reveal mt-4 font-display text-5xl md:text-6xl font-semibold text-white" style="--delay:100ms">
            Nuestros <span class="text-gold-grad">servicios</span>
        </h1>
        <p class="reveal mt-5 max-w-xl text-lg text-cream/70" style="--delay:200ms">
            Cada servicio está pensado para brindar una experiencia de corte premium.
        </p>
    </div>
</section>

<!-- Grid -->
<section class="py-16 md:py-20 bg-darkdeep">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($services === []): ?>
                <p class="reveal text-center text-cream/50 col-span-full py-10">Aún no hay servicios registrados.</p>
            <?php else: ?>
                <?php foreach ($services as $i => $item): ?>
                    <div class="reveal" style="--delay:<?= ($i % 3) * 120 ?>ms">
                        <?php include __DIR__ . '/../home/partials/service_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
