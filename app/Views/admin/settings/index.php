<?php
/**
 * Configuración del sitio — Panel de Administración (solo Superadmin)
 */
use App\Core\View;
use App\Helpers\Session;
use App\Models\SocialLink;
?>
<div class="max-w-4xl">
    <div class="flex items-center gap-4 mb-8">
        <div class="w-10 h-10 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-gear text-gold"></i>
        </div>
        <div>
            <h2 class="font-display text-2xl font-semibold text-white">Configuración</h2>
            <p class="mt-1 text-sm text-cream/50">Contenido del sitio, contacto, horarios y redes sociales del footer.</p>
        </div>
    </div>

    <!-- ============ CONTENIDO DEL SITIO ============ -->
    <form method="POST" action="<?= ADMIN_URL ?>/settings/content" class="settings-section bg-darksoft rounded-2xl border border-white/5 p-6 sm:p-8 mb-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
        <button type="button" class="settings-head w-full flex items-center gap-3 pb-4 text-left group">
            <i class="fa-solid fa-pen-ruler text-gold"></i>
            <div class="flex-1">
                <h3 class="text-sm font-bold uppercase tracking-widest text-cream">Contenido del sitio</h3>
                <p class="text-xs text-cream/40">Marca, lema, descripción y bloque de boletín.</p>
            </div>
            <i class="settings-chevron fa-solid fa-chevron-down text-cream/40 transition-transform duration-200 group-hover:text-gold"></i>
        </button>

        <div class="settings-body pt-5 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="site_name">Nombre del sitio *</label>
                    <input type="text" id="site_name" name="site_name" required value="<?= View::e($values['site_name']) ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="site_tagline">Lema</label>
                    <input type="text" id="site_tagline" name="site_tagline" value="<?= View::e($values['site_tagline']) ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
                </div>
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="site_description">Descripción</label>
                <textarea id="site_description" name="site_description" rows="3" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20"><?= View::e($values['site_description']) ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="newsletter_title">Título del boletín</label>
                    <input type="text" id="newsletter_title" name="newsletter_title" value="<?= View::e($values['newsletter_title']) ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
                </div>
                <div class="flex items-end pb-2">
                    <label class="inline-flex items-center gap-3 text-sm text-cream/80 cursor-pointer">
                        <input type="checkbox" name="newsletter_enabled" value="1" <?= (int) $values['newsletter_enabled'] === 1 ? 'checked' : '' ?> class="w-5 h-5 rounded border-white/20 bg-dark accent-gold cursor-pointer">
                        Mostrar bloque de boletín
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="newsletter_text">Texto del boletín</label>
                <textarea id="newsletter_text" name="newsletter_text" rows="2" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20"><?= View::e($values['newsletter_text']) ?></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar contenido
                </button>
            </div>
        </div>
    </form>

    <!-- ============ LOGO ============ -->
    <form method="POST" action="<?= ADMIN_URL ?>/settings/logo" enctype="multipart/form-data" class="settings-section bg-darksoft rounded-2xl border border-white/5 p-6 sm:p-8 mb-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
        <button type="button" class="settings-head w-full flex items-center gap-3 pb-4 text-left group">
            <i class="fa-solid fa-image text-gold"></i>
            <div class="flex-1">
                <h3 class="text-sm font-bold uppercase tracking-widest text-cream">Logo</h3>
                <p class="text-xs text-cream/40">Se muestra en la navbar, el footer y el panel de administración.</p>
            </div>
            <i class="settings-chevron fa-solid fa-chevron-down text-cream/40 transition-transform duration-200 group-hover:text-gold"></i>
        </button>

        <div class="settings-body pt-5 space-y-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                <div class="w-20 h-20 rounded-full border border-gold/40 bg-dark/60 flex items-center justify-center overflow-hidden shrink-0">
                    <?php if (($values['logo'] ?? '') !== ''): ?>
                        <img src="<?= UPLOAD_DIR . View::e($values['logo']) ?>" alt="Logo actual" class="w-full h-full object-cover" id="logo-preview">
                    <?php else: ?>
                        <span class="flex items-center justify-center w-full h-full" id="logo-preview"><i class="fa-solid fa-scissors text-gold"></i></span>
                    <?php endif; ?>
                </div>
                <div class="flex-1 space-y-3">
                    <div>
                        <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="logo">Imagen del logo (JPG, PNG, WEBP o GIF · máx 2 MB)</label>
                        <input type="file" id="logo" name="logo" accept="image/*" class="w-full text-sm text-cream/70 file:mr-4 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-gold/10 file:text-goldlight file:text-xs file:font-bold file:uppercase file:tracking-widest file:cursor-pointer cursor-pointer bg-dark/60 border border-white/10 rounded-xl px-3 py-2">
                    </div>
                    <label class="inline-flex items-center gap-3 text-sm text-cream/80 cursor-pointer">
                        <input type="checkbox" name="remove_logo" value="1" class="w-5 h-5 rounded border-white/20 bg-dark accent-red-500 cursor-pointer">
                        Quitar logo actual
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar logo
                </button>
            </div>
        </div>
    </form>

    <!-- ============ PORTADA (INICIO) ============ -->
    <form method="POST" action="<?= ADMIN_URL ?>/settings/hero" enctype="multipart/form-data" class="settings-section bg-darksoft rounded-2xl border border-white/5 p-6 sm:p-8 mb-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
        <button type="button" class="settings-head w-full flex items-center gap-3 pb-4 text-left group">
            <i class="fa-solid fa-house text-gold"></i>
            <div class="flex-1">
                <h3 class="text-sm font-bold uppercase tracking-widest text-cream">Portada (inicio)</h3>
                <p class="text-xs text-cream/40">Textos e imagen de la portada de la página pública.</p>
            </div>
            <i class="settings-chevron fa-solid fa-chevron-down text-cream/40 transition-transform duration-200 group-hover:text-gold"></i>
        </button>

        <div class="settings-body pt-5 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="hero_eyebrow">Eyebrow (línea superior)</label>
                    <input type="text" id="hero_eyebrow" name="hero_eyebrow" value="<?= View::e($values['hero_eyebrow']) ?>" placeholder="Cortes y estética masculina" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="hero_image">Imagen de portada (JPG, PNG, WEBP o GIF · máx 2 MB)</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-lg border border-white/10 bg-dark/60 overflow-hidden shrink-0 flex items-center justify-center">
                            <?php if (($values['hero_image'] ?? '') !== ''): ?>
                                <img src="<?= UPLOAD_DIR . View::e($values['hero_image']) ?>" alt="Imagen de portada actual" class="w-full h-full object-cover" id="hero-preview">
                            <?php else: ?>
                                <span class="flex items-center justify-center w-full h-full text-cream/40" id="hero-preview"><i class="fa-solid fa-image text-lg"></i></span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 space-y-2">
                            <input type="file" id="hero_image" name="hero_image" accept="image/*" class="w-full text-sm text-cream/70 file:mr-4 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-gold/10 file:text-goldlight file:text-xs file:font-bold file:uppercase file:tracking-widest file:cursor-pointer cursor-pointer bg-dark/60 border border-white/10 rounded-xl px-3 py-2">
                            <label class="inline-flex items-center gap-3 text-sm text-cream/80 cursor-pointer">
                                <input type="checkbox" name="remove_hero_image" value="1" class="w-5 h-5 rounded border-white/20 bg-dark accent-red-500 cursor-pointer">
                                Quitar imagen (volver a la predeterminada)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="hero_title">Título *</label>
                <textarea id="hero_title" name="hero_title" rows="3" required placeholder="Elegancia, Profesionalismo en cada trabajo&#10;Realizado con *estilo*" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30 font-mono"><?= View::e($values['hero_title']) ?></textarea>
                <p class="mt-1 text-[11px] text-cream/40">Escribe normal: cada línea es una línea. Envuelve en *asteriscos* la parte que quieras en dorado.</p>
                <div id="hero-preview-box" class="hidden mt-3 rounded-xl border border-gold/20 bg-dark/40 p-4">
                    <p class="text-[10px] uppercase tracking-[.2em] text-cream/40 mb-2">Vista previa</p>
                    <div id="hero-preview-title" class="font-display text-2xl font-semibold leading-snug text-white"></div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="hero_subtitle">Descripción breve</label>
                <textarea id="hero_subtitle" name="hero_subtitle" rows="3" placeholder="Cortes de precisión, barba esculpida y productos premium. Un equipo de barberos apasionados que transforma tu imagen en cada visita." class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30"><?= View::e($values['hero_subtitle']) ?></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar portada
                </button>
            </div>
        </div>
    </form>

    <!-- ============ CINTA ANIMADA ============ -->
    <form method="POST" action="<?= ADMIN_URL ?>/settings/marquee" class="settings-section bg-darksoft rounded-2xl border border-white/5 p-6 sm:p-8 mb-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
        <button type="button" class="settings-head w-full flex items-center gap-3 pb-4 text-left group">
            <i class="fa-solid fa-film text-gold"></i>
            <div class="flex-1">
                <h3 class="text-sm font-bold uppercase tracking-widest text-cream">Cinta animada</h3>
                <p class="text-xs text-cream/40">Las palabras que se desplazan en la franja dorada del inicio. Separa cada elemento con una coma (máx 12).</p>
            </div>
            <i class="settings-chevron fa-solid fa-chevron-down text-cream/40 transition-transform duration-200 group-hover:text-gold"></i>
        </button>

        <div class="settings-body pt-5 space-y-5">
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="marquee_items">Items</label>
                <textarea id="marquee_items" name="marquee_items" rows="3" placeholder="Barba, Corte, Tinte, Skincare" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30 font-mono"><?= View::e(implode(', ', $marqueeItems)) ?></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cinta
                </button>
            </div>
        </div>
    </form>

    <!-- ============ CONTACTO ============ -->
    <form method="POST" action="<?= ADMIN_URL ?>/settings/contact" class="settings-section bg-darksoft rounded-2xl border border-white/5 p-6 sm:p-8 mb-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
        <button type="button" class="settings-head w-full flex items-center gap-3 pb-4 text-left group">
            <i class="fa-brands fa-whatsapp text-gold"></i>
            <div class="flex-1">
                <h3 class="text-sm font-bold uppercase tracking-widest text-cream">Contacto</h3>
                <p class="text-xs text-cream/40">Aparece en el footer y en la portada.</p>
            </div>
            <i class="settings-chevron fa-solid fa-chevron-down text-cream/40 transition-transform duration-200 group-hover:text-gold"></i>
        </button>

        <div class="settings-body pt-5 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="whatsapp">Número de WhatsApp *</label>
                    <input type="tel" id="whatsapp" name="whatsapp" required value="<?= View::e($values['whatsapp']) ?>" placeholder="+503 0000-0000" pattern="\+503 \d{4}-\d{4}" title="El número debe tener el formato +503 0000-0000." inputmode="tel" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30 format-phone">
                    <p class="mt-1 text-[11px] text-cream/40">Usado para el link directo wa.me del botón "Agendar ahora" y del footer.</p>
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="phone">Teléfono</label>
                    <input type="tel" id="phone" name="phone" value="<?= View::e($values['phone']) ?>" placeholder="+503 0000-0000" pattern="\+503 \d{4}-\d{4}" title="El teléfono debe tener el formato +503 0000-0000." inputmode="tel" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30 format-phone">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="email">Correo de contacto *</label>
                    <input type="email" id="email" name="email" required value="<?= View::e($values['email']) ?>" placeholder="contacto@barberiastyle.com" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="address">Dirección</label>
                    <input type="text" id="address" name="address" value="<?= View::e($values['address']) ?>" placeholder="Av. Principal 123, San Salvador" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar contacto
                </button>
            </div>
        </div>
    </form>

    <!-- ============ HORARIOS ============ -->
    <form method="POST" action="<?= ADMIN_URL ?>/settings/hours" class="settings-section bg-darksoft rounded-2xl border border-white/5 p-6 sm:p-8 mb-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
        <button type="button" class="settings-head w-full flex items-center gap-3 pb-4 text-left group">
            <i class="fa-solid fa-clock text-gold"></i>
            <div class="flex-1">
                <h3 class="text-sm font-bold uppercase tracking-widest text-cream">Horarios de atención</h3>
                <p class="text-xs text-cream/40">Marca los días como cerrados y define apertura y cierre para los días atendidos.</p>
            </div>
            <i class="settings-chevron fa-solid fa-chevron-down text-cream/40 transition-transform duration-200 group-hover:text-gold"></i>
        </button>

        <div class="settings-body pt-5 space-y-5">
            <div class="space-y-3">
                <?php foreach ($days as $dayKey => $dayLabel): ?>
                    <?php $open = (string) ($hours[$dayKey]['open'] ?? ''); $close = (string) ($hours[$dayKey]['close'] ?? ''); ?>
                    <?php $closed = $open === '' && $close === ''; ?>
                    <div class="flex flex-wrap items-center gap-4 rounded-xl bg-dark/40 border border-white/10 px-4 py-3" id="day-row-<?= $dayKey ?>">
                        <span class="w-28 text-sm font-medium text-cream/80"><?= $dayLabel ?></span>
                        <label class="inline-flex items-center gap-2 text-xs text-cream/60 cursor-pointer">
                            <input type="checkbox" name="closed_<?= $dayKey ?>" value="1" <?= $closed ? 'checked' : '' ?> class="w-4 h-4 rounded border-white/20 bg-dark accent-red-500 cursor-pointer day-closed" data-day="<?= $dayKey ?>">
                            Cerrado
                        </label>
                        <div class="flex items-center gap-2 day-times" data-day="<?= $dayKey ?>">
                            <input type="time" name="open_<?= $dayKey ?>" value="<?= View::e($open) ?>" <?= $closed ? 'disabled' : '' ?> class="px-3 py-2 rounded-lg bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
                            <span class="text-cream/40 text-xs">a</span>
                            <input type="time" name="close_<?= $dayKey ?>" value="<?= View::e($close) ?>" <?= $closed ? 'disabled' : '' ?> class="px-3 py-2 rounded-lg bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar horarios
                </button>
            </div>
        </div>
    </form>

    <!-- ============ REDES SOCIALES ============ -->
    <div class="settings-section bg-darksoft rounded-2xl border border-white/5 p-6 sm:p-8">
        <button type="button" class="settings-head w-full flex items-center gap-3 pb-4 text-left group">
            <i class="fa-solid fa-share-nodes text-gold"></i>
            <div class="flex-1">
                <h3 class="text-sm font-bold uppercase tracking-widest text-cream">Redes sociales</h3>
                <p class="text-xs text-cream/40">Se muestran como iconos en el footer. Agrega y quita según necesites.</p>
            </div>
            <i class="settings-chevron fa-solid fa-chevron-down text-cream/40 transition-transform duration-200 group-hover:text-gold"></i>
        </button>

        <div class="settings-body pt-5 space-y-5">
            <?php if ($socials !== []): ?>
                <div class="space-y-2">
                    <?php foreach ($socials as $social): ?>
                        <?php $platform = (string) $social->getAttribute('platform'); $url = (string) $social->getAttribute('url'); ?>
                        <div class="flex items-center gap-3 rounded-xl bg-dark/40 border border-white/10 px-4 py-3">
                            <span class="w-9 h-9 rounded-lg bg-gold/10 border border-gold/30 flex items-center justify-center text-gold">
                                <i class="fa-brands <?= SocialLink::iconFor($platform) ?>"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-cream"><?= SocialLink::labelFor($platform) ?></p>
                                <p class="text-xs text-cream/40 truncate"><?= $url !== '' ? View::e($url) : '<span class="text-amber-300">Sin link configurado</span>' ?></p>
                            </div>
                            <form method="POST" action="<?= ADMIN_URL ?>/settings/social/delete/<?= (int) $social->getAttribute('id') ?>" onsubmit="return confirm('¿Eliminar esta red social?');">
                                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                                <button type="submit" title="Eliminar" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-red-400 hover:border-red-500/40 transition">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-cream/50">Aún no hay redes sociales configuradas.</p>
            <?php endif; ?>

            <form method="POST" action="<?= ADMIN_URL ?>/settings/social/store" class="border-t border-white/5 pt-5">
                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="platform">Red social</label>
                        <select id="platform" name="platform" required class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 cursor-pointer">
                            <option value="">Selecciona una red</option>
                            <?php foreach ($platforms as $platformKey => $platformLabel): ?>
                                <option value="<?= View::e($platformKey) ?>"><?= View::e($platformLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="url">Link</label>
                        <input type="text" id="url" name="url" required placeholder="https://instagram.com/tuperfil" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20">
                        <i class="fa-solid fa-plus"></i> Agregar red
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.settings-section').forEach(section => {
        const head = section.querySelector('.settings-head');
        const body = section.querySelector('.settings-body');
        const chevron = section.querySelector('.settings-chevron');
        if (!head || !body || !chevron) return;
        head.addEventListener('click', () => {
            const collapsed = body.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180', collapsed);
        });
    });

    function formatPhone(input) {
        let digits = input.value.replace(/\D/g, '');
        if (!digits.startsWith('503')) digits = '503' + digits;
        digits = digits.slice(0, 11);
        let out = '+' + digits.slice(0, 3);
        if (digits.length > 3) out += ' ' + digits.slice(3, 7);
        if (digits.length > 7) out += '-' + digits.slice(7, 11);
        input.value = out;
    }

    document.querySelectorAll('.format-phone').forEach(input => {
        input.addEventListener('input', () => {
            formatPhone(input);
            if (input.classList.contains('field-invalid')) {
                input.classList.remove('field-invalid', 'border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
                const err = input.nextElementSibling;
                if (err && err.classList.contains('field-error')) err.remove();
            }
        });
    });

    document.querySelectorAll('.day-closed').forEach(cb => {
        cb.addEventListener('change', () => {
            const day = cb.dataset.day;
            document.querySelectorAll('.day-times[data-day="' + day + '"] input').forEach(input => {
                input.disabled = cb.checked;
                if (cb.checked) input.value = '';
            });
        });
    });

    function bindImagePreview(inputId, previewId, fallback) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.replaceWith(Object.assign(document.createElement('img'), {
                    src: e.target.result,
                    alt: 'Preview',
                    className: 'w-full h-full object-cover',
                    id: previewId,
                }));
            };
            reader.readAsDataURL(file);
        });
    }

    bindImagePreview('logo', 'logo-preview');
    bindImagePreview('hero_image', 'hero-preview');

    const heroTitleInput = document.getElementById('hero_title');
    const heroPreviewBox = document.getElementById('hero-preview-box');
    const heroPreviewTitle = document.getElementById('hero-preview-title');
    if (heroTitleInput && heroPreviewBox && heroPreviewTitle) {
        const escapeHtml = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const renderPreview = (s) => escapeHtml(s).replace(/\*([^*]+)\*/g, '<em class="text-gold-grad italic">$1</em>').replace(/\n/g, '<br>');
        const updatePreview = () => {
            const v = heroTitleInput.value.trim();
            if (v === '') {
                heroPreviewBox.classList.add('hidden');
                return;
            }
            heroPreviewBox.classList.remove('hidden');
            heroPreviewTitle.innerHTML = renderPreview(v);
        };
        heroTitleInput.addEventListener('input', updatePreview);
        updatePreview();
    }
</script>
