<?php
/**
 * Listado de Usuarios — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Session;
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h2 class="font-display text-2xl font-semibold text-white">Usuarios del sistema</h2>
        <p class="mt-1 text-sm text-cream/50">Crea y administra los accesos al panel (Superadmin y Administrador).</p>
    </div>
    <a href="<?= ADMIN_URL ?>/users/create" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-widest hover:bg-goldlight transition btn-shine">
        <i class="fa-solid fa-user-plus"></i> Nuevo usuario
    </a>
</div>

<div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-widest text-cream/50 border-b border-white/10">
                    <th class="px-6 py-4">Usuario</th>
                    <th class="px-6 py-4">Rol</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4">Último acceso</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $item): ?>
                <?php
                    $isSelf = (int) $item->getAttribute('id') === $currentUserId;
                    $isActive = (int) $item->getAttribute('is_active') === 1;
                    $roleName = $roles[$item->getAttribute('role_id')] ?? '—';
                    $isSuper = strcasecmp((string) $roleName, 'Superadmin') === 0;
                ?>
                <tr class="border-b border-white/5 hover:bg-gold/5 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full <?= $isSuper ? 'bg-gold/15 border border-gold/40' : 'bg-dark border border-white/10' ?> flex items-center justify-center">
                                <i class="fa-solid fa-user text-gold"></i>
                            </span>
                            <div>
                                <p class="font-medium text-white">
                                    <?= View::e($item->getAttribute('name')) ?>
                                    <?php if ($isSelf): ?>
                                        <span class="ml-1 text-[10px] uppercase tracking-wider text-goldlight">(tú)</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-cream/50"><?= View::e($item->getAttribute('email')) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-medium <?= $isSuper ? 'bg-gold/10 text-goldlight border border-gold/30' : 'bg-dark text-cream/70 border border-white/10' ?>">
                            <?= View::e($roleName) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($isActive): ?>
                            <span class="flex items-center gap-2 text-emerald-400"><i class="fa-solid fa-circle text-[8px]"></i>Activo</span>
                        <?php else: ?>
                            <span class="flex items-center gap-2 text-red-400"><i class="fa-solid fa-circle text-[8px]"></i>Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-cream/60">
                        <?= $item->getAttribute('last_login') ? View::e(substr((string) $item->getAttribute('last_login'), 0, 16)) : '—' ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= ADMIN_URL ?>/users/edit/<?= (int) $item->getAttribute('id') ?>" title="Editar" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </a>
                            <?php if (!$isSelf): ?>
                            <form method="POST" action="<?= ADMIN_URL ?>/users/delete/<?= (int) $item->getAttribute('id') ?>" onsubmit="return confirm('¿Eliminar a <?= addslashes((string) $item->getAttribute('name')) ?>? Esta acción no se puede deshacer.');">
                                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                                <button type="submit" title="Eliminar" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-red-400 hover:border-red-500/40 transition">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($users === []): ?>
        <div class="p-10 text-center text-cream/50">
            <i class="fa-solid fa-users text-3xl text-gold/40 mb-3"></i>
            <p>Aún no hay usuarios registrados.</p>
        </div>
    <?php endif; ?>
</div>
