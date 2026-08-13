<?php
/**
 * Vista de Login — Panel de Administración
 * Sin layout: pantalla completa centrada.
 */
use App\Core\View;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($title ?? 'Iniciar sesión') ?> | Barbería Style Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: '#F59E0B', goldlight: '#FBBF24', golddark: '#B45309',
                        cream: '#FEF9C3', dark: '#2D3748', darksoft: '#1F2937', darkdeep: '#111827',
                    },
                    fontFamily: { display: ['"Playfair Display"', 'Georgia', 'serif'] },
                },
            },
        };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body class="min-h-screen bg-darkdeep text-white flex items-center justify-center px-4 relative overflow-hidden">

    <!-- Fondo decorativo -->
    <div class="hero-bg absolute inset-0"></div>
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-gold/10 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-gold/5 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="text-center mb-8">
            <span class="inline-flex w-16 h-16 items-center justify-center rounded-full border border-gold/40 bg-gold/5">
                <i class="fa-solid fa-scissors text-gold text-2xl"></i>
            </span>
            <h1 class="mt-5 font-display text-3xl font-semibold text-goldlight">Barbería Style</h1>
            <p class="mt-1 text-[11px] uppercase tracking-[.35em] text-cream/50">Panel de administración</p>
        </div>

        <?php foreach ($flashes as $flash): ?>
            <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-xl border text-sm <?= $flash['type'] === 'error' ? 'bg-red-500/10 border-red-500/30 text-red-300' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' ?>">
                <i class="fa-solid <?= $flash['type'] === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-check' ?>"></i>
                <span><?= View::e($flash['message']) ?></span>
            </div>
        <?php endforeach; ?>

        <form method="POST" action="<?= ADMIN_URL ?>/login" class="bg-darksoft/80 backdrop-blur-xl rounded-2xl border border-gold/20 p-8 shadow-2xl shadow-black/50">
            <input type="hidden" name="_csrf" value="<?= View::e($csrf) ?>">

            <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="email">
                <i class="fa-solid fa-envelope mr-2 text-gold"></i>Email
            </label>
            <input type="email" id="email" name="email" required autofocus
                   placeholder="admin@barberiastyle.com"
                   class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none transition focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">

            <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2 mt-5" for="password">
                <i class="fa-solid fa-lock mr-2 text-gold"></i>Contraseña
            </label>
            <div class="relative">
                <input type="password" id="password" name="password" required
                       placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none transition focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                <button type="button" id="toggle-password" class="absolute right-4 top-1/2 -translate-y-1/2 text-cream/50 hover:text-goldlight" aria-label="Mostrar contraseña">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <button type="submit" class="mt-7 w-full inline-flex items-center justify-center gap-3 px-6 py-3.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20 btn-shine">
                <i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión
            </button>

            <a href="<?= APP_URL ?>/" class="mt-5 flex items-center justify-center gap-2 text-xs text-cream/50 hover:text-goldlight transition">
                <i class="fa-solid fa-arrow-left-long"></i> Volver al sitio web
            </a>
        </form>

        <p class="mt-6 text-center text-[11px] text-cream/30">
            &copy; <?= date('Y') ?> Barbería Style · Acceso restringido
        </p>
    </div>

    <script>
        document.getElementById('toggle-password')?.addEventListener('click', () => {
            const input = document.getElementById('password');
            const icon = document.querySelector('#toggle-password i');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });
    </script>
</body>
</html>
