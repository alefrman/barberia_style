<?php
/**
 * Vista de Inicio — Barbería Style (Diseño Premium)
 * Contenido 100% extraído de la base de datos.
 */
use App\Core\View;
?>
<!-- ============ HERO ============ -->
<section class="hero-bg relative min-h-screen flex items-center pt-28 pb-20">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7">
                <span class="reveal inline-flex items-center gap-3 text-goldlight eyebrow">
                    <span class="h-px w-10 bg-gold/60"></span>
                    Cortes y estética masculina
                </span>

                <h1 class="reveal mt-6 font-display text-5xl sm:text-6xl lg:text-7xl font-semibold leading-[1.05]" style="--delay:100ms">
                    Elegancia que<br>
                    se lleva <em class="text-gold-grad italic">con actitud</em>
                </h1>

                <p class="reveal mt-6 max-w-xl text-lg leading-relaxed text-cream/75" style="--delay:200ms">
                    Cortes de precisión, barba esculpida y productos premium. Un equipo de
                    barberos apasionados que transforma tu imagen en cada visita.
                </p>

                <div class="reveal mt-10 flex flex-wrap gap-4" style="--delay:300ms">
                    <a href="<?= APP_URL ?>/services" class="inline-flex items-center gap-3 px-8 py-4 rounded-lg bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20 btn-shine">
                        <i class="fa-solid fa-scissors"></i> Ver servicios
                    </a>
                    <a href="<?= APP_URL ?>/team" class="inline-flex items-center gap-3 px-8 py-4 rounded-lg border border-gold/40 text-goldlight font-bold uppercase text-xs tracking-[.2em] hover:bg-gold/10 transition">
                        <i class="fa-solid fa-user-group"></i> Conoce al equipo
                    </a>
                </div>
            </div>

            <div class="lg:col-span-5 hidden lg:block">
                <div class="reveal relative ml-auto max-w-sm" style="--delay:250ms">
                    <div class="absolute -inset-4 border border-gold/30 rounded-3xl rotate-3"></div>
                    <div class="absolute -inset-2 rounded-3xl border border-gold/20"></div>
                    <figure class="relative group overflow-hidden rounded-3xl border border-gold/40 shadow-2xl shadow-black/60">
                        <img src="<?= APP_URL ?>/assets/img/cortehombre1.png" alt="Corte de autor — Barbería Style" class="w-full h-auto object-cover transition duration-700 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-darkdeep/75 via-darkdeep/10 to-transparent"></div>
                        <figcaption class="absolute bottom-0 inset-x-0 p-6 flex items-end justify-between">
                            <div>
                                <p class="font-display text-2xl text-goldlight font-semibold">Corte de autor</p>
                                <p class="mt-1 text-[11px] uppercase tracking-[.3em] text-cream/70">Barbería Style · Desde 2012</p>
                            </div>
                            <span class="w-12 h-12 rounded-full bg-gold/10 border border-gold/40 flex items-center justify-center">
                                <i class="fa-solid fa-scissors text-gold"></i>
                            </span>
                        </figcaption>
                    </figure>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ MARQUEE ============ -->
<div class="marquee border-y border-gold/10 bg-darksoft py-5">
    <div class="marquee-track text-goldlight eyebrow">
        <?php for ($i = 0; $i < 2; $i++): ?>
            <span class="flex items-center gap-14">
                <span>Cortes de autor</span><i class="fa-solid fa-diamond text-[8px] text-gold/50"></i>
                <span>Barba</span><i class="fa-solid fa-diamond text-[8px] text-gold/50"></i>
                <span>Tinte</span><i class="fa-solid fa-diamond text-[8px] text-gold/50"></i>
                <span>Acabados premium</span><i class="fa-solid fa-diamond text-[8px] text-gold/50"></i>
                <span>Productos originales</span><i class="fa-solid fa-diamond text-[8px] text-gold/50"></i>
            </span>
        <?php endfor; ?>
    </div>
</div>

<!-- ============ SERVICIOS ============ -->
<section class="py-24 bg-darkdeep">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center max-w-2xl mx-auto">
            <span class="eyebrow text-goldlight"><i class="fa-solid fa-scissors mr-2"></i>Lo que hacemos</span>
            <h2 class="mt-4 font-display text-4xl md:text-5xl font-semibold text-white">Nuestros <span class="text-gold-grad">servicios</span></h2>
            <div class="mt-6 flex items-center justify-center gap-3">
                <span class="h-px w-16 bg-gradient-to-r from-transparent to-gold/60"></span>
                <span class="w-2 h-2 bg-gold rotate-45"></span>
                <span class="h-px w-16 bg-gradient-to-l from-transparent to-gold/60"></span>
            </div>
        </div>

        <div class="mt-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($services === []): ?>
                <p class="reveal text-center text-cream/50 col-span-full py-10">Próximamente disponibles nuestros servicios.</p>
            <?php else: ?>
                <?php foreach ($services as $i => $item): ?>
                    <div class="reveal" style="--delay:<?= ($i % 3) * 120 ?>ms">
                        <?php include __DIR__ . '/partials/service_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============ PRODUCTOS ============ -->
<section class="py-24 bg-darksoft border-y border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-14">
            <div>
                <span class="eyebrow text-goldlight"><i class="fa-solid fa-bottle-droplet mr-2"></i>Shop</span>
                <h2 class="mt-4 font-display text-4xl md:text-5xl font-semibold text-white">Productos <span class="text-gold-grad">premium</span></h2>
            </div>
            <a href="<?= APP_URL ?>/products" class="inline-flex items-center gap-2 text-sm uppercase tracking-widest text-goldlight hover:text-gold transition">
                Ver todos <i class="fa-solid fa-arrow-right-long"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if ($products === []): ?>
                <p class="reveal text-center text-cream/50 col-span-full py-10">Próximamente nuestra tienda.</p>
            <?php else: ?>
                <?php foreach (array_slice($products, 0, 4) as $i => $item): ?>
                    <div class="reveal" style="--delay:<?= ($i % 4) * 120 ?>ms">
                        <?php include __DIR__ . '/partials/product_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============ EQUIPO ============ -->
<section class="py-24 bg-darkdeep">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal text-center max-w-2xl mx-auto">
            <span class="eyebrow text-goldlight"><i class="fa-solid fa-user-group mr-2"></i>El equipo</span>
            <h2 class="mt-4 font-display text-4xl md:text-5xl font-semibold text-white">Barberos que <span class="text-gold-grad">dominan su arte</span></h2>
            <div class="mt-6 flex items-center justify-center gap-3">
                <span class="h-px w-16 bg-gradient-to-r from-transparent to-gold/60"></span>
                <span class="w-2 h-2 bg-gold rotate-45"></span>
                <span class="h-px w-16 bg-gradient-to-l from-transparent to-gold/60"></span>
            </div>
        </div>

        <div class="mt-14 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($team === []): ?>
                <p class="reveal text-center text-cream/50 col-span-full py-10">Próximamente nuestro equipo.</p>
            <?php else: ?>
                <?php foreach ($team as $i => $item): ?>
                    <div class="reveal" style="--delay:<?= ($i % 3) * 120 ?>ms">
                        <?php include __DIR__ . '/partials/team_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============ GALERÍA ============ -->
<section class="py-24 bg-darksoft border-y border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-14">
            <div>
                <span class="eyebrow text-goldlight"><i class="fa-regular fa-images mr-2"></i>Portafolio</span>
                <h2 class="mt-4 font-display text-4xl md:text-5xl font-semibold text-white">Trabajos <span class="text-gold-grad">recientes</span></h2>
            </div>
            <a href="<?= APP_URL ?>/gallery" class="inline-flex items-center gap-2 text-sm uppercase tracking-widest text-goldlight hover:text-gold transition">
                Ver galería <i class="fa-solid fa-arrow-right-long"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if ($gallery === []): ?>
                <p class="reveal text-center text-cream/50 col-span-full py-10">Próximamente nuestra galería.</p>
            <?php else: ?>
                <?php foreach (array_slice($gallery, 0, 4) as $i => $item): ?>
                    <div class="reveal" style="--delay:<?= ($i % 4) * 100 ?>ms">
                        <?php include __DIR__ . '/partials/gallery_item.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============ ESTADÍSTICAS ============ -->
<?php if ($stats['barbers'] > 0 || $stats['styles'] > 0): ?>
<section class="relative py-20 bg-gradient-to-r from-darkdeep via-dark to-darkdeep overflow-hidden">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-10 text-center">
            <div class="reveal">
                <p class="font-display text-4xl md:text-5xl font-semibold text-gold-grad"><?= $stats['services'] ?></p>
                <p class="mt-2 text-[11px] uppercase tracking-[.25em] text-cream/60">Servicios</p>
            </div>
            <div class="reveal" style="--delay:100ms">
                <p class="font-display text-4xl md:text-5xl font-semibold text-gold-grad"><?= $stats['products'] ?></p>
                <p class="mt-2 text-[11px] uppercase tracking-[.25em] text-cream/60">Productos</p>
            </div>
            <div class="reveal" style="--delay:200ms">
                <p class="font-display text-4xl md:text-5xl font-semibold text-gold-grad"><?= $stats['barbers'] ?></p>
                <p class="mt-2 text-[11px] uppercase tracking-[.25em] text-cream/60">Barberos</p>
            </div>
            <div class="reveal" style="--delay:300ms">
                <p class="font-display text-4xl md:text-5xl font-semibold text-gold-grad"><?= $stats['styles'] ?></p>
                <p class="mt-2 text-[11px] uppercase tracking-[.25em] text-cream/60">Estilos</p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============ CTA ============ -->
<section class="py-24 bg-darkdeep">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="reveal relative rounded-3xl overflow-hidden bg-gradient-to-r from-gold to-goldlight p-10 md:p-16 text-center">
            <div class="absolute inset-0 opacity-20" style="background-image:linear-gradient(rgba(17,24,39,.25) 1px, transparent 1px),linear-gradient(90deg,rgba(17,24,39,.25) 1px, transparent 1px);background-size:40px 40px"></div>
            <div class="relative">
                <span class="inline-block px-4 py-1.5 rounded-full bg-darkdeep/10 text-darkdeep text-[11px] font-bold uppercase tracking-[.25em]">Reserva tu hora</span>
                <h2 class="mt-5 font-display text-4xl md:text-5xl font-semibold text-darkdeep">¿Listo para tu próximo corte?</h2>
                <p class="mt-4 max-w-lg mx-auto text-darkdeep/80">Agenda con nosotros y descubre el nivel de detalle que marca la diferencia.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="https://wa.me/<?= View::e(preg_replace('/\D+/', '', (string) App\Helpers\Settings::get('whatsapp', ''))) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-3 px-8 py-4 rounded-lg bg-darkdeep text-goldlight font-bold uppercase text-xs tracking-[.2em] hover:bg-dark transition shadow-xl btn-shine">
                        <i class="fa-brands fa-whatsapp"></i> Agendar ahora
                    </a>
                    <a href="tel:<?= View::e(App\Helpers\Settings::get('phone', '')) ?>" class="inline-flex items-center gap-3 px-8 py-4 rounded-lg border-2 border-darkdeep text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-darkdeep hover:text-goldlight transition">
                        <i class="fa-solid fa-phone"></i> <?= View::e(App\Helpers\Settings::get('phone', '+503 0000-0000')) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
