<?php
/**
 * Ítem premium de galería.
 * Espera la variable $item (instancia App\Models\Gallery).
 * La imagen se abre en un lightbox (GLightbox) con título y descripción.
 */
use App\Core\View;

$galleryTitle = trim((string) $item->getAttribute('title'));
$galleryTitle = $galleryTitle !== '' ? $galleryTitle : 'Trabajo de Barbería Style';
$galleryDesc = trim((string) $item->getAttribute('description'));
$galleryImage = (string) $item->getAttribute('image');
?>
<figure class="gallery-item rounded-xl overflow-hidden border border-white/5">
    <?php if ($galleryImage !== ''): ?>
        <a class="gallery-lightbox" href="<?= UPLOAD_DIR . View::e($galleryImage) ?>" data-title="<?= View::e($galleryTitle) ?>" <?= $galleryDesc !== '' ? 'data-description="' . View::e($galleryDesc) . '"' : '' ?>>
            <img src="<?= UPLOAD_DIR . View::e($galleryImage) ?>" alt="<?= View::e($galleryTitle) ?>" class="w-full h-48 md:h-56 object-cover">
        </a>
    <?php else: ?>
        <div class="w-full h-48 md:h-56 bg-gradient-to-br from-dark via-darksoft to-darkdeep flex items-center justify-center">
            <i class="fa-solid fa-image text-gold/60 text-3xl"></i>
        </div>
    <?php endif; ?>
    <figcaption class="gallery-overlay">
        <div class="space-y-1">
            <span class="flex items-center gap-2 text-sm font-medium text-goldlight">
                <i class="fa-solid fa-scissors"></i><?= View::e($galleryTitle) ?>
            </span>
            <?php if ($galleryDesc !== ''): ?>
                <p class="text-xs text-cream/70 leading-snug"><?= View::e($galleryDesc) ?></p>
            <?php endif; ?>
        </div>
    </figcaption>
</figure>
