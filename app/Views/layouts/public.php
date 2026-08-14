<?php
/**
 * Layout Público — Barbería Style (Diseño Premium)
 * Paleta: #2D3748 | #F59E0B | #FBBF24 | #FEF9C3
 */
use App\Core\View;
use App\Helpers\Settings;
use App\Models\SocialLink;

$pageTitle = $title ?? 'Cortes y estética masculina';
$activeNav = $active ?? '';

$siteName        = (string) Settings::get('site_name', APP_NAME);
$siteTagline     = (string) Settings::get('site_tagline', 'Estética masculina');
$siteDescription = (string) Settings::get('site_description', 'Calidad, precisión y estilo en cada corte. La barbería clásica con un toque moderno que marca la diferencia.');
$contactPhone    = (string) Settings::get('phone', '+503 0000-0000');
$contactEmail    = (string) Settings::get('email', 'contacto@barberiastyle.com');
$contactAddress  = (string) Settings::get('address', 'Av. Principal 123, San Salvador');
$whatsapp        = (string) Settings::get('whatsapp', '+503 0000-0000');
$whatsappDigits  = preg_replace('/\D+/', '', $whatsapp);
$socials         = SocialLink::active();
$newsletterEnabled = Settings::getBool('newsletter_enabled', true);
$newsletterTitle = (string) Settings::get('newsletter_title', 'Boletín');
$newsletterText  = (string) Settings::get('newsletter_text', 'Recibe novedades, promociones y tips de estilo.');

$hours = Settings::businessHours();
$dayLabels = [
    'monday'    => 'Lunes',
    'tuesday'   => 'Martes',
    'wednesday' => 'Miércoles',
    'thursday'  => 'Jueves',
    'friday'    => 'Viernes',
    'saturday'  => 'Sábado',
    'sunday'    => 'Domingo',
];
$weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
$allWeekdaysSame = true;
$firstHours = $hours['monday'] ?? ['open' => '', 'close' => ''];
foreach ($weekdays as $d) {
    if (($hours[$d] ?? ['open' => '', 'close' => '']) !== $firstHours) {
        $allWeekdaysSame = false;
        break;
    }
}
$hourRows = [];
if ($allWeekdaysSame) {
    $hourRows[] = ['label' => 'Lunes — Viernes', 'open' => $firstHours['open'], 'close' => $firstHours['close']];
} else {
    foreach ($weekdays as $d) {
        $h = $hours[$d] ?? ['open' => '', 'close' => ''];
        $hourRows[] = ['label' => $dayLabels[$d], 'open' => $h['open'], 'close' => $h['close']];
    }
}
foreach (['saturday', 'sunday'] as $d) {
    $h = $hours[$d] ?? ['open' => '', 'close' => ''];
    $hourRows[] = ['label' => $dayLabels[$d], 'open' => $h['open'], 'close' => $h['close']];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Barbería Style — Cortes clásicos y modernos. Servicios profesionales, productos premium y un equipo experto.">
    <title><?= View::e($pageTitle) ?> | <?= View::e($siteName) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: '#F59E0B',
                        goldlight: '#FBBF24',
                        golddark: '#B45309',
                        cream: '#FEF9C3',
                        dark: '#2D3748',
                        darksoft: '#1F2937',
                        darkdeep: '#111827',
                    },
                    fontFamily: {
                        display: ['"Playfair Display"', 'Georgia', 'serif'],
                    },
                },
            },
        };
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
</head>
<body class="bg-darkdeep text-white font-sans flex flex-col min-h-screen">

    <!-- ============ NAVBAR ============ -->
    <header id="navbar" class="navbar fixed top-0 inset-x-0 z-50 bg-darkdeep/80 backdrop-blur-xl border-b border-gold/10">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 py-3">
                <a href="<?= APP_URL ?>/" class="flex items-center gap-3 group">
                    <span class="w-11 h-11 rounded-full border border-gold/40 bg-gold/5 flex items-center justify-center transition group-hover:bg-gold/15">
                        <i class="fa-solid fa-scissors text-gold"></i>
                    </span>
                    <span class="leading-tight">
                        <span class="block font-display text-lg font-semibold tracking-wide text-goldlight"><?= View::e($siteName) ?></span>
                        <span class="block text-[10px] uppercase tracking-[.3em] text-cream/50"><?= View::e($siteTagline) ?></span>
                    </span>
                </a>

                <div class="hidden lg:flex items-center gap-8 text-[13px] uppercase tracking-widest font-medium">
                    <a href="<?= APP_URL ?>/" class="nav-link <?= $activeNav === 'home' ? 'active' : '' ?> text-cream/80 hover:text-goldlight">Inicio</a>
                    <a href="<?= APP_URL ?>/services" class="nav-link <?= $activeNav === 'services' ? 'active' : '' ?> text-cream/80 hover:text-goldlight">Servicios</a>
                    <a href="<?= APP_URL ?>/products" class="nav-link <?= $activeNav === 'products' ? 'active' : '' ?> text-cream/80 hover:text-goldlight">Productos</a>
                    <a href="<?= APP_URL ?>/team" class="nav-link <?= $activeNav === 'team' ? 'active' : '' ?> text-cream/80 hover:text-goldlight">Equipo</a>
                    <a href="<?= APP_URL ?>/gallery" class="nav-link <?= $activeNav === 'gallery' ? 'active' : '' ?> text-cream/80 hover:text-goldlight">Galería</a>
                </div>

                <div class="flex items-center gap-3">
                    <a href="<?= APP_URL ?>/admin.php" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-gold text-darkdeep text-xs font-bold uppercase tracking-widest hover:bg-goldlight transition btn-shine">
                        <i class="fa-solid fa-user-shield"></i> Acceso
                    </a>
                    <button id="btn-mobile-menu" class="lg:hidden text-goldlight text-xl p-2" aria-label="Abrir menú">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                </div>
            </div>

            <div id="mobile-menu" class="hidden lg:hidden pb-5 space-y-1 text-sm">
                <a href="<?= APP_URL ?>/" class="block px-4 py-3 rounded-lg hover:bg-gold/10 text-cream">Inicio</a>
                <a href="<?= APP_URL ?>/services" class="block px-4 py-3 rounded-lg hover:bg-gold/10 text-cream">Servicios</a>
                <a href="<?= APP_URL ?>/products" class="block px-4 py-3 rounded-lg hover:bg-gold/10 text-cream">Productos</a>
                <a href="<?= APP_URL ?>/team" class="block px-4 py-3 rounded-lg hover:bg-gold/10 text-cream">Equipo</a>
                <a href="<?= APP_URL ?>/gallery" class="block px-4 py-3 rounded-lg hover:bg-gold/10 text-cream">Galería</a>
                <a href="<?= APP_URL ?>/admin.php" class="block px-4 py-3 rounded-lg bg-gold text-darkdeep font-semibold text-center mt-2">
                    <i class="fa-solid fa-user-shield mr-2"></i>Panel de administración
                </a>
            </div>
        </nav>
    </header>

    <!-- ============ CONTENIDO ============ -->
    <main class="flex-1">
        <?= $content ?>
    </main>

    <!-- ============ FOOTER ============ -->
    <footer class="bg-darkdeep border-t border-gold/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
            <div>
                <a href="<?= APP_URL ?>/" class="flex items-center gap-3">
                    <span class="w-11 h-11 rounded-full border border-gold/40 bg-gold/5 flex items-center justify-center">
                        <i class="fa-solid fa-scissors text-gold"></i>
                    </span>
                    <span>
                        <span class="block font-display text-lg font-semibold text-goldlight"><?= View::e($siteName) ?></span>
                        <span class="block text-[10px] uppercase tracking-[.3em] text-cream/50"><?= View::e($siteTagline) ?></span>
                    </span>
                </a>
                <p class="mt-5 text-sm leading-relaxed text-cream/60">
                    <?= View::e($siteDescription) ?>
                </p>
                <?php if ($whatsappDigits !== '' || $socials !== []): ?>
                <div class="mt-6 flex gap-3">
                    <?php if ($whatsappDigits !== ''): ?>
                    <a href="https://wa.me/<?= View::e($whatsappDigits) ?>" target="_blank" rel="noopener" aria-label="WhatsApp" title="WhatsApp" class="w-10 h-10 rounded-lg border border-gold/20 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/50 hover:bg-gold/5 transition">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <?php endif; ?>
                    <?php foreach ($socials as $social): ?>
                        <?php
                        $platform = (string) $social->getAttribute('platform');
                        if ($platform === 'whatsapp') { continue; }
                        $url = (string) $social->getAttribute('url');
                        ?>
                        <a href="<?= View::e($url) ?>" target="_blank" rel="noopener" aria-label="<?= View::e(SocialLink::labelFor($platform)) ?>" title="<?= View::e(SocialLink::labelFor($platform)) ?>" class="w-10 h-10 rounded-lg border border-gold/20 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/50 hover:bg-gold/5 transition">
                            <i class="fa-brands <?= SocialLink::iconFor($platform) ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-[.25em] text-goldlight mb-5">Contacto</h4>
                <ul class="space-y-4 text-sm text-cream/70">
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-phone text-gold mt-1"></i>
                        <span><?= View::e($contactPhone) ?></span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-envelope text-gold mt-1"></i>
                        <span><?= View::e($contactEmail) ?></span>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fa-solid fa-location-dot text-gold mt-1"></i>
                        <span><?= View::e($contactAddress) ?></span>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="text-xs font-bold uppercase tracking-[.25em] text-goldlight mb-5">Horarios</h4>
                <ul class="space-y-3 text-sm text-cream/70">
                    <?php foreach ($hourRows as $row): ?>
                        <li class="flex items-center justify-between gap-4 border-b border-white/5 pb-3">
                            <span><?= View::e($row['label']) ?></span>
                            <span class="<?= $row['open'] !== '' ? 'text-goldlight font-medium' : 'text-cream/40' ?>">
                                <?= $row['open'] !== '' ? View::e($row['open'] . ' – ' . $row['close']) : 'Cerrado' ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if ($newsletterEnabled): ?>
            <div>
                <h4 class="text-xs font-bold uppercase tracking-[.25em] text-goldlight mb-5"><?= View::e($newsletterTitle) ?></h4>
                <p class="text-sm text-cream/60 leading-relaxed"><?= View::e($newsletterText) ?></p>
                <form class="mt-5 flex rounded-lg overflow-hidden border border-gold/20 focus-within:border-gold/60 transition">
                    <input type="email" placeholder="Tu email" class="w-full bg-dark/60 px-4 py-3 text-sm outline-none placeholder:text-cream/30">
                    <button type="submit" class="px-4 bg-gold text-darkdeep font-semibold hover:bg-goldlight transition">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <div class="border-t border-white/5 py-5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-cream/40">
                <p>&copy; <?= date('Y') ?> <?= View::e($siteName) ?>. Todos los derechos reservados.</p>
                <p class="flex items-center gap-1.5"><i class="fa-solid fa-scissors text-gold/60"></i> Hecho con estilo</p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar shadow on scroll
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        });

        // Menú móvil
        document.getElementById('btn-mobile-menu')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });

        // Reveal on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    </script>

    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script src="<?= APP_URL ?>/assets/js/gallery.js"></script>
</body>
</html>
