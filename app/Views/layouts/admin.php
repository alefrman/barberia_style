<?php
/**
 * Layout Admin — Barbería Style
 * Panel de administración con sidebar lateral y navbar superior.
 */
use App\Core\View;
use App\Helpers\Session;

$pageTitle = $title ?? 'Panel de administración';
$activeAdmin = $active ?? 'dashboard';
$userName = ($user ?? null) ? View::e($user->getAttribute('name')) : 'Usuario';
$userRole = ($user ?? null) ? View::e($user->roleName() ?? '') : '';
$isSuperAdmin = strcasecmp((string) $userRole, 'Superadmin') === 0;
$flashes = Session::getFlashes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($pageTitle) ?> | Barbería Style Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

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
    <script>
        (function () {
            try {
                if (localStorage.getItem('bs_admin_sidebar') === 'collapsed') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body class="bg-darkdeep text-white min-h-screen">

    <!-- ============ SIDEBAR (desktop) ============ -->
    <aside class="admin-sidebar hidden lg:flex fixed inset-y-0 left-0 flex-col bg-darksoft border-r border-gold/10 z-40">
        <div class="admin-brand flex items-center gap-3 px-6 h-18 py-5 border-b border-white/5">
            <span class="w-10 h-10 rounded-full border border-gold/40 bg-gold/5 flex items-center justify-center">
                <i class="fa-solid fa-scissors text-gold"></i>
            </span>
            <div class="admin-brand-text">
                <p class="font-display text-base font-semibold text-goldlight leading-tight">Barbería Style</p>
                <p class="text-[10px] uppercase tracking-[.25em] text-cream/50">Panel admin</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <p class="nav-section-label px-3 pb-2 text-[10px] uppercase tracking-[.25em] text-cream/40">Gestión</p>
            <a href="<?= ADMIN_URL ?>/dashboard" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition <?= $activeAdmin === 'dashboard' ? 'bg-gold/10 text-goldlight border border-gold/30' : 'text-cream/70 hover:bg-gold/5 hover:text-goldlight' ?>">
                <i class="fa-solid fa-gauge-high w-5"></i><span class="nav-link-text">Dashboard</span>
            </a>
            <a href="<?= ADMIN_URL ?>/appointments" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition <?= $activeAdmin === 'appointments' ? 'bg-gold/10 text-goldlight border border-gold/30' : 'text-cream/70 hover:bg-gold/5 hover:text-goldlight' ?>">
                <i class="fa-solid fa-calendar-check w-5"></i><span class="nav-link-text">Citas</span>
            </a>
            <a href="<?= ADMIN_URL ?>/inventory" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition <?= $activeAdmin === 'inventory' ? 'bg-gold/10 text-goldlight border border-gold/30' : 'text-cream/70 hover:bg-gold/5 hover:text-goldlight' ?>">
                <i class="fa-solid fa-boxes-stacked w-5"></i><span class="nav-link-text">Inventario</span>
            </a>
            <a href="<?= ADMIN_URL ?>/services" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition <?= $activeAdmin === 'services' ? 'bg-gold/10 text-goldlight border border-gold/30' : 'text-cream/70 hover:bg-gold/5 hover:text-goldlight' ?>">
                <i class="fa-solid fa-scissors w-5"></i><span class="nav-link-text">Servicios</span>
            </a>
            <a href="<?= ADMIN_URL ?>/team" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition <?= $activeAdmin === 'team' ? 'bg-gold/10 text-goldlight border border-gold/30' : 'text-cream/70 hover:bg-gold/5 hover:text-goldlight' ?>">
                <i class="fa-solid fa-user-tie w-5"></i><span class="nav-link-text">Barberos</span>
            </a>
            <a href="<?= ADMIN_URL ?>/expenses" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition <?= $activeAdmin === 'expenses' ? 'bg-gold/10 text-goldlight border border-gold/30' : 'text-cream/70 hover:bg-gold/5 hover:text-goldlight' ?>">
                <i class="fa-solid fa-money-bill-transfer w-5"></i><span class="nav-link-text">Gastos</span>
            </a>
            <a href="<?= ADMIN_URL ?>/gallery" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition <?= $activeAdmin === 'gallery' ? 'bg-gold/10 text-goldlight border border-gold/30' : 'text-cream/70 hover:bg-gold/5 hover:text-goldlight' ?>">
                <i class="fa-solid fa-images w-5"></i><span class="nav-link-text">Galería</span>
            </a>

            <p class="nav-section-label px-3 pt-6 pb-2 text-[10px] uppercase tracking-[.25em] text-cream/40">Sistema</p>
            <?php if ($isSuperAdmin): ?>
            <a href="<?= ADMIN_URL ?>/users" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition <?= $activeAdmin === 'users' ? 'bg-gold/10 text-goldlight border border-gold/30' : 'text-cream/70 hover:bg-gold/5 hover:text-goldlight' ?>">
                <i class="fa-solid fa-user-shield w-5"></i><span class="nav-link-text">Usuarios</span>
            </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/" target="_blank" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition text-cream/70 hover:bg-gold/5 hover:text-goldlight">
                <i class="fa-solid fa-globe w-5"></i><span class="nav-link-text">Ver sitio web</span>
            </a>
            <a href="<?= ADMIN_URL ?>/logout" class="admin-nav-link relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition text-red-400 hover:bg-red-500/10">
                <i class="fa-solid fa-right-from-bracket w-5"></i><span class="nav-link-text">Cerrar sesión</span>
            </a>
        </nav>
    </aside>

    <!-- ============ BARRA SUPERIOR ============ -->
    <header class="admin-topbar fixed top-0 inset-x-0 z-30 bg-darkdeep/90 backdrop-blur-xl border-b border-gold/10">
        <div class="flex items-center justify-between h-16 px-4 sm:px-6">
            <div class="flex items-center gap-2">
                <button id="btn-admin-menu" class="lg:hidden text-goldlight text-xl p-2" aria-label="Abrir menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <button id="btn-sidebar-toggle" class="admin-sidebar-toggle hidden lg:flex items-center justify-center w-9 h-9 rounded-lg border border-gold/30 text-gold hover:text-goldlight hover:border-gold/60 hover:bg-gold/10 transition" aria-label="Alternar menú">
                    <i class="fa-solid fa-angles-left text-sm"></i>
                </button>
            </div>
            <h1 class="font-display text-lg sm:text-xl font-semibold text-white hidden sm:block">
                <?= View::e($pageTitle) ?>
            </h1>
            <div class="flex items-center gap-3">
                <span class="hidden sm:flex flex-col items-end leading-tight">
                    <span class="text-sm font-semibold text-cream"><?= $userName ?></span>
                    <span class="text-[10px] uppercase tracking-widest text-goldlight"><?= $userRole ?></span>
                </span>
                <span class="w-10 h-10 rounded-full bg-gold/10 border border-gold/40 flex items-center justify-center">
                    <i class="fa-solid fa-user text-gold"></i>
                </span>
            </div>
        </div>
    </header>

    <!-- ============ MENÚ MÓVIL ============ -->
    <div id="admin-mobile-menu" class="hidden fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0 bg-black/60" onclick="document.getElementById('admin-mobile-menu').classList.add('hidden')"></div>
        <div class="absolute inset-y-0 left-0 w-72 bg-darksoft border-r border-gold/10 p-6 overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <span class="font-display text-lg font-semibold text-goldlight">Menú</span>
                <button onclick="document.getElementById('admin-mobile-menu').classList.add('hidden')" class="text-cream/70 text-xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <nav class="space-y-1 text-sm">
                <a href="<?= ADMIN_URL ?>/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-cream/80 hover:bg-gold/5">Dashboard</a>
                <a href="<?= ADMIN_URL ?>/appointments" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-cream/80 hover:bg-gold/5">Citas</a>
                <a href="<?= ADMIN_URL ?>/inventory" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-cream/80 hover:bg-gold/5">Inventario</a>
                <a href="<?= ADMIN_URL ?>/services" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-cream/80 hover:bg-gold/5">Servicios</a>
                <a href="<?= ADMIN_URL ?>/team" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-cream/80 hover:bg-gold/5">Barberos</a>
                <a href="<?= ADMIN_URL ?>/expenses" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-cream/80 hover:bg-gold/5">Gastos</a>
                <a href="<?= ADMIN_URL ?>/gallery" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-cream/80 hover:bg-gold/5">Galería</a>
                <?php if ($isSuperAdmin): ?>
                <a href="<?= ADMIN_URL ?>/users" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-cream/80 hover:bg-gold/5">Usuarios</a>
                <?php endif; ?>
                <div class="border-t border-white/5 pt-3 mt-3">
                    <a href="<?= ADMIN_URL ?>/logout" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-400">Cerrar sesión</a>
                </div>
            </nav>
        </div>
    </div>

    <!-- ============ CONTENIDO ============ -->
    <main class="admin-main pt-16 min-h-screen">
        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
            <?php
            // Mensajes flash
            foreach ($flashes as $flash):
                $isError = $flash['type'] === 'error';
            ?>
            <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl border text-sm <?= $isError ? 'bg-red-500/10 border-red-500/30 text-red-300' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' ?>">
                <i class="fa-solid <?= $isError ? 'fa-triangle-exclamation' : 'fa-circle-check' ?>"></i>
                <span><?= View::e($flash['message']) ?></span>
            </div>
            <?php endforeach; ?>

            <?= $content ?>
        </div>
    </main>

    <script>
        document.getElementById('btn-admin-menu')?.addEventListener('click', () => {
            document.getElementById('admin-mobile-menu')?.classList.toggle('hidden');
        });

        document.getElementById('btn-sidebar-toggle')?.addEventListener('click', () => {
            const root = document.documentElement;
            root.classList.toggle('sidebar-collapsed');
            try {
                localStorage.setItem('bs_admin_sidebar', root.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
            } catch (e) {}
        });

        const adminTooltip = document.createElement('div');
        adminTooltip.className = 'admin-tooltip';
        document.body.appendChild(adminTooltip);

        const showAdminTooltip = (link) => {
            if (!document.documentElement.classList.contains('sidebar-collapsed')) return;
            const label = link.querySelector('.nav-link-text');
            if (!label) return;
            adminTooltip.textContent = label.textContent.trim();
            const r = link.getBoundingClientRect();
            adminTooltip.style.left = (r.right + 12) + 'px';
            adminTooltip.style.top = (r.top + r.height / 2) + 'px';
            adminTooltip.classList.add('show');
        };
        const hideAdminTooltip = () => adminTooltip.classList.remove('show');

        document.querySelectorAll('.admin-nav-link').forEach((link) => {
            link.addEventListener('mouseenter', () => showAdminTooltip(link));
            link.addEventListener('mouseleave', hideAdminTooltip);
        });
    </script>
    <script src="<?= APP_URL ?>/assets/js/form-validation.js"></script>
</body>
</html>
