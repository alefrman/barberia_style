<?php
/**
 * Formulario de Usuario (crear/editar) — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Session;

$isEditing = $editing !== null;
$values = $isEditing ? $editing->toArray() : [];
$submitUrl = $isEditing
    ? ADMIN_URL . '/users/update/' . (int) $editing->getAttribute('id')
    : ADMIN_URL . '/users/store';
?>
<div class="max-w-2xl">
    <div class="flex items-center gap-4 mb-8">
        <a href="<?= ADMIN_URL ?>/users" class="w-10 h-10 rounded-xl bg-darksoft border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-display text-2xl font-semibold text-white">
                <?= $isEditing ? 'Editar usuario' : 'Nuevo usuario' ?>
            </h2>
            <p class="mt-1 text-sm text-cream/50"><?= $isEditing ? 'Modifica los datos del administrador.' : 'Crea un acceso para el panel de administración.' ?></p>
        </div>
    </div>

    <form method="POST" action="<?= $submitUrl ?>" class="bg-darksoft rounded-2xl border border-white/5 p-8 space-y-6">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="name">
                    <i class="fa-solid fa-user mr-2 text-gold"></i>Nombre completo
                </label>
                <input type="text" id="name" name="name" required
                       value="<?= View::e($values['name'] ?? '') ?>"
                       placeholder="Ej: Juan Pérez"
                       class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none transition focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="email">
                    <i class="fa-solid fa-envelope mr-2 text-gold"></i>Email
                </label>
                <input type="email" id="email" name="email" required
                       value="<?= View::e($values['email'] ?? '') ?>"
                       placeholder="admin@barberiastyle.com"
                       class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none transition focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="role_id">
                    <i class="fa-solid fa-shield-halved mr-2 text-gold"></i>Rol
                </label>
                <select id="role_id" name="role_id" required
                        class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none transition focus:border-gold/60 focus:ring-2 focus:ring-gold/20 appearance-none cursor-pointer">
                    <option value="" disabled <?= !$isEditing ? 'selected' : '' ?>>Selecciona un rol</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= (int) $role->getAttribute('id') ?>" <?= (int) ($values['role_id'] ?? 0) === (int) $role->getAttribute('id') ? 'selected' : '' ?>>
                            <?= View::e($role->getAttribute('name')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="password">
                    <i class="fa-solid fa-lock mr-2 text-gold"></i><?= $isEditing ? 'Nueva contraseña (opcional)' : 'Contraseña' ?>
                </label>
                <input type="password" id="password" name="password"
                       placeholder="<?= $isEditing ? 'Dejar vacío para no cambiar' : 'Mínimo 6 caracteres' ?>"
                       class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none transition focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <input type="checkbox" id="is_active" name="is_active" value="1" <?= ($values['is_active'] ?? '1') == 1 ? 'checked' : '' ?>
                   class="w-5 h-5 rounded border-white/20 bg-dark accent-gold cursor-pointer">
            <label for="is_active" class="text-sm text-cream/80 cursor-pointer">Usuario activo (puede iniciar sesión)</label>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20 btn-shine">
                <i class="fa-solid <?= $isEditing ? 'fa-floppy-disk' : 'fa-user-plus' ?>"></i>
                <?= $isEditing ? 'Guardar cambios' : 'Crear usuario' ?>
            </button>
            <a href="<?= ADMIN_URL ?>/users" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl border border-white/10 text-cream/70 font-semibold text-xs uppercase tracking-widest hover:border-gold/40 hover:text-goldlight transition">
                Cancelar
            </a>
        </div>
    </form>
</div>
