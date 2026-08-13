<?php
/**
 * Formulario de Barbero (crear/editar) — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Session;

$isEditing = $editing !== null;
$submitUrl = $isEditing
    ? ADMIN_URL . '/team/update/' . (int) $editing->getAttribute('id')
    : ADMIN_URL . '/team/store';
$values = $isEditing ? $editing->toArray() : [
    'name' => '', 'position' => '', 'description' => '',
    'image' => '', 'is_active' => 1, 'sort_order' => 0,
];
?>
<div class="max-w-2xl">
    <div class="flex items-center gap-4 mb-8">
        <a href="<?= ADMIN_URL ?>/team" class="w-10 h-10 rounded-xl bg-darksoft border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-display text-2xl font-semibold text-white"><?= View::e($title) ?></h2>
            <p class="mt-1 text-sm text-cream/50">Datos del barbero y su foto para el sitio web.</p>
        </div>
    </div>

    <form method="POST" action="<?= $submitUrl ?>" enctype="multipart/form-data" class="bg-darksoft rounded-2xl border border-white/5 p-8 space-y-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

        <!-- Imagen -->
        <div class="flex items-center gap-5">
            <div id="image-preview" class="w-24 h-24 rounded-full overflow-hidden bg-dark border border-white/10 flex items-center justify-center flex-shrink-0">
                <?php if (!empty($values['image'])): ?>
                    <img id="preview-img" src="<?= UPLOAD_DIR . View::e($values['image']) ?>" alt="Foto" class="w-full h-full object-cover">
                <?php else: ?>
                    <i id="preview-icon" class="fa-solid fa-user-tie text-gold/50 text-2xl"></i>
                <?php endif; ?>
            </div>
            <div class="flex-1">
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="image">Foto <span class="text-cream/40 normal-case">(opcional)</span></label>
                <input type="file" id="image" name="image" accept="image/*" class="w-full text-sm text-cream/70 file:mr-4 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-dark file:text-goldlight file:text-xs file:uppercase file:tracking-widest file:cursor-pointer hover:file:bg-gold/10 cursor-pointer">
                <p class="mt-1 text-[11px] text-cream/40">JPG, PNG, WEBP o GIF · máx 2 MB</p>
                <?php if ($isEditing && !empty($values['image'])): ?>
                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-red-400 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="accent-red-500"> Quitar foto actual
                    </label>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="name">Nombre *</label>
                <input type="text" id="name" name="name" required value="<?= View::e($values['name'] ?? '') ?>" placeholder="Ej: Carlos Hernández" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="position">Cargo *</label>
                <input type="text" id="position" name="position" required value="<?= View::e($values['position'] ?? '') ?>" placeholder="Ej: Barbero maestro" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>
        </div>

        <div>
            <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="description">Descripción <span class="text-cream/40 normal-case">(opcional)</span></label>
            <textarea id="description" name="description" rows="3" placeholder="Breve perfil del barbero" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30"><?= View::e($values['description'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="sort_order">Orden <span class="text-cream/40 normal-case">(opcional)</span></label>
                <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($values['sort_order'] ?? 0) ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <input type="checkbox" id="is_active" name="is_active" value="1" <?= (int) ($values['is_active'] ?? 1) === 1 ? 'checked' : '' ?> class="w-5 h-5 rounded border-white/20 bg-dark accent-gold cursor-pointer">
            <label for="is_active" class="text-sm text-cream/80 cursor-pointer">Barbero activo (visible en el sitio web)</label>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20 btn-shine">
                <i class="fa-solid <?= $isEditing ? 'fa-floppy-disk' : 'fa-plus' ?>"></i>
                <?= $isEditing ? 'Guardar cambios' : 'Crear barbero' ?>
            </button>
            <a href="<?= ADMIN_URL ?>/team" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl border border-white/10 text-cream/70 font-semibold text-xs uppercase tracking-widest hover:border-gold/40 hover:text-goldlight transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
const fileInput = document.getElementById('image');
fileInput?.addEventListener('change', e => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        const img = document.getElementById('preview-img');
        const icon = document.getElementById('preview-icon');
        if (img) { img.src = ev.target.result; }
        else {
            const cont = document.getElementById('image-preview');
            cont.innerHTML = '<img id="preview-img" src="' + ev.target.result + '" alt="Foto" class="w-full h-full object-cover">';
        }
        if (icon) icon.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>
