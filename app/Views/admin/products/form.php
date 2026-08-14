<?php
/**
 * Formulario de Producto (crear/editar) — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Session;

$isEditing = $editing !== null;
$submitUrl = $isEditing
    ? ADMIN_URL . '/inventory/update/' . (int) $editing->getAttribute('id')
    : ADMIN_URL . '/inventory/store';
$values = $isEditing ? $editing->toArray() : [
    'name' => '', 'description' => '', 'price' => '', 'cost' => 0,
    'stock' => 1, 'min_stock' => 5, 'category_id' => 0, 'image' => '',
    'is_active' => 1, 'sort_order' => 0,
];
?>
<div class="max-w-2xl">
    <div class="flex items-center gap-4 mb-8">
        <a href="<?= ADMIN_URL ?>/inventory" class="w-10 h-10 rounded-xl bg-darksoft border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-display text-2xl font-semibold text-white"><?= View::e($title) ?></h2>
            <p class="mt-1 text-sm text-cream/50">Datos del producto y control de stock.</p>
        </div>
    </div>

    <form method="POST" action="<?= $submitUrl ?>" enctype="multipart/form-data" class="bg-darksoft rounded-2xl border border-white/5 p-8 space-y-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

        <!-- Imagen -->
        <div class="flex items-center gap-5">
            <div id="image-preview" class="w-24 h-24 rounded-2xl overflow-hidden bg-dark border border-white/10 flex items-center justify-center flex-shrink-0">
                <?php if (!empty($values['image'])): ?>
                    <img id="preview-img" src="<?= UPLOAD_DIR . View::e($values['image']) ?>" alt="Imagen" class="w-full h-full object-cover">
                <?php else: ?>
                    <i id="preview-icon" class="fa-solid fa-box text-gold/50 text-2xl"></i>
                <?php endif; ?>
            </div>
            <div class="flex-1">
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="image">Imagen <span class="text-cream/40 normal-case">(opcional)</span></label>
                <input type="file" id="image" name="image" accept="image/*" class="w-full text-sm text-cream/70 file:mr-4 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-dark file:text-goldlight file:text-xs file:uppercase file:tracking-widest file:cursor-pointer hover:file:bg-gold/10 cursor-pointer">
                <p class="mt-1 text-[11px] text-cream/40">JPG, PNG, WEBP o GIF · máx 2 MB</p>
                <?php if ($isEditing && !empty($values['image'])): ?>
                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-red-400 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="accent-red-500"> Quitar imagen actual
                    </label>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="name">Nombre *</label>
            <input type="text" id="name" name="name" required value="<?= View::e($values['name'] ?? '') ?>" placeholder="Ej: Cera de peinado" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
        </div>

        <div>
            <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="description">Descripción <span class="text-cream/40 normal-case">(opcional)</span></label>
            <textarea id="description" name="description" rows="3" placeholder="Breve descripción del producto" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30"><?= View::e($values['description'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="price">Precio (USD) *</label>
                <input type="number" id="price" name="price" required step="0.01" min="0" value="<?= View::e($values['price'] ?? '') ?>" placeholder="0.00" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="cost">Costo (USD) <span class="text-cream/40 normal-case">(opcional)</span></label>
                <input type="number" id="cost" name="cost" step="0.01" min="0" value="<?= View::e($values['cost'] ?? '') ?>" placeholder="0.00" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="sort_order">Orden <span class="text-cream/40 normal-case">(opcional)</span></label>
                <input type="number" id="sort_order" name="sort_order" value="<?= (int) ($values['sort_order'] ?? 0) ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="stock">Stock (unidades) *</label>
                <input type="number" id="stock" name="stock" required min="<?= $isEditing ? 0 : 1 ?>" value="<?= (int) ($values['stock'] ?? 1) ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="min_stock">Stock mínimo <span class="text-cream/40 normal-case">(aviso)</span></label>
                <input type="number" id="min_stock" name="min_stock" min="0" value="<?= (int) ($values['min_stock'] ?? 5) ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
            </div>
        </div>

        <!-- Categoría -->
        <div>
            <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="category_id">Categoría <span class="text-cream/40 normal-case">(opcional)</span></label>
            <div id="category-select-wrap">
                <div class="flex gap-2">
                    <select id="category_id" name="category_id" class="flex-1 px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
                        <option value="0">Sin categoría</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c->getAttribute('id') ?>" <?= (int) ($values['category_id'] ?? 0) === (int) $c->getAttribute('id') ? 'selected' : '' ?>><?= View::e($c->getAttribute('name')) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" onclick="toggleNewCategory()" class="px-4 rounded-xl border border-gold/40 text-goldlight text-xs font-bold uppercase tracking-widest hover:bg-gold/10 transition whitespace-nowrap">
                        <i class="fa-solid fa-plus mr-1"></i>Nueva
                    </button>
                </div>
            </div>
            <div id="new-category-wrap" class="hidden mt-3">
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="new_category">Nombre de la nueva categoría</label>
                <div class="flex gap-2">
                    <input type="text" id="new_category" name="new_category" placeholder="Ej: Cuidado del cabello" class="flex-1 px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                    <button type="button" onclick="cancelNewCategory()" class="px-4 rounded-xl border border-white/10 text-cream/70 text-xs font-bold uppercase tracking-widest hover:border-red-500/40 hover:text-red-400 transition whitespace-nowrap">Cancelar</button>
                </div>
            </div>
        </div>

        <input type="hidden" name="is_active" value="<?= (int) ($values['is_active'] ?? 1) ?>">

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20 btn-shine">
                <i class="fa-solid <?= $isEditing ? 'fa-floppy-disk' : 'fa-plus' ?>"></i>
                <?= $isEditing ? 'Guardar cambios' : 'Crear producto' ?>
            </button>
            <a href="<?= ADMIN_URL ?>/inventory" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl border border-white/10 text-cream/70 font-semibold text-xs uppercase tracking-widest hover:border-gold/40 hover:text-goldlight transition">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function toggleNewCategory() {
    document.getElementById('category-select-wrap').classList.add('hidden');
    document.getElementById('new-category-wrap').classList.remove('hidden');
    document.getElementById('new_category').focus();
}
function cancelNewCategory() {
    document.getElementById('new-category-wrap').classList.add('hidden');
    document.getElementById('category-select-wrap').classList.remove('hidden');
    document.getElementById('new_category').value = '';
}
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
            cont.innerHTML = '<img id="preview-img" src="' + ev.target.result + '" alt="Imagen" class="w-full h-full object-cover">';
        }
        if (icon) icon.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>
