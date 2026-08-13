<?php
/**
 * Formulario de Gasto (crear/editar) — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Session;

$isEditing = $editing !== null;
$submitUrl = $isEditing
    ? ADMIN_URL . '/expenses/update/' . (int) $editing->getAttribute('id')
    : ADMIN_URL . '/expenses/store';
$values = $isEditing ? $editing->toArray() : [
    'description' => '', 'amount' => '', 'expense_date' => date('Y-m-d'),
    'payment_method' => 'Efectivo', 'notes' => '', 'category_id' => 0,
];
?>
<div class="max-w-2xl">
    <div class="flex items-center gap-4 mb-8">
        <a href="<?= ADMIN_URL ?>/expenses" class="w-10 h-10 rounded-xl bg-darksoft border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-display text-2xl font-semibold text-white"><?= View::e($title) ?></h2>
            <p class="mt-1 text-sm text-cream/50">Registra la salida de efectivo.</p>
        </div>
    </div>

    <form method="POST" action="<?= $submitUrl ?>" class="bg-darksoft rounded-2xl border border-white/5 p-8 space-y-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

        <div>
            <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="description">Descripción *</label>
            <input type="text" id="description" name="description" required value="<?= View::e($values['description'] ?? '') ?>" placeholder="Ej: Compra de insumos" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="amount">Monto (USD) *</label>
                <input type="number" id="amount" name="amount" required step="0.01" min="0.01" value="<?= View::e($values['amount'] ?? '') ?>" placeholder="0.00" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="expense_date">Fecha *</label>
                <input type="date" id="expense_date" name="expense_date" required value="<?= View::e($values['expense_date'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="payment_method">Método de pago</label>
                <select id="payment_method" name="payment_method" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
                    <?php foreach ($methods as $m): ?>
                        <option value="<?= View::e($m) ?>" <?= ($values['payment_method'] ?? 'Efectivo') === $m ? 'selected' : '' ?>><?= View::e($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
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
                    <div class="flex gap-2">
                        <input type="text" id="new_category" name="new_category" placeholder="Nombre de la nueva categoría" class="flex-1 px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                        <button type="button" onclick="cancelNewCategory()" class="px-4 rounded-xl border border-white/10 text-cream/70 text-xs font-bold uppercase tracking-widest hover:border-red-500/40 hover:text-red-400 transition whitespace-nowrap">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="notes">Notas <span class="text-cream/40 normal-case">(opcional)</span></label>
            <textarea id="notes" name="notes" rows="3" placeholder="Detalles adicionales del gasto" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30"><?= View::e($values['notes'] ?? '') ?></textarea>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20 btn-shine">
                <i class="fa-solid <?= $isEditing ? 'fa-floppy-disk' : 'fa-plus' ?>"></i>
                <?= $isEditing ? 'Guardar cambios' : 'Registrar gasto' ?>
            </button>
            <a href="<?= ADMIN_URL ?>/expenses" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl border border-white/10 text-cream/70 font-semibold text-xs uppercase tracking-widest hover:border-gold/40 hover:text-goldlight transition">
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
</script>
