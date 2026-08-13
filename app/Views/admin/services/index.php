<?php
/**
 * Listado de Servicios — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Money;
use App\Helpers\Session;
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h2 class="font-display text-2xl font-semibold text-white">Servicios</h2>
        <p class="mt-1 text-sm text-cream/50">Catálogo de servicios que se ofrecen en la barbería.</p>
    </div>
    <a href="<?= ADMIN_URL ?>/services/create" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-widest hover:bg-goldlight transition btn-shine">
        <i class="fa-solid fa-scissors"></i> Nuevo servicio
    </a>
</div>

<div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-widest text-cream/50 border-b border-white/10">
                    <th class="px-6 py-4">Servicio</th>
                    <th class="px-6 py-4">Categoría</th>
                    <th class="px-6 py-4">Duración</th>
                    <th class="px-6 py-4">Precio</th>
                    <th class="px-6 py-4">Orden</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <tr class="border-b border-white/5 hover:bg-gold/5 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-dark border border-white/10 flex-shrink-0">
                                <?php if ($r['image']): ?>
                                    <img src="<?= UPLOAD_DIR . View::e($r['image']) ?>" alt="<?= View::e($r['name']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center"><i class="fa-solid fa-scissors text-gold/60"></i></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-medium text-white"><?= View::e($r['name']) ?></p>
                                <?php if ($r['description']): ?>
                                    <p class="text-xs text-cream/50 max-w-xs truncate"><?= View::e($r['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($r['category_name']): ?>
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-gold/10 text-goldlight border border-gold/30"><?= View::e($r['category_name']) ?></span>
                        <?php else: ?>
                            <span class="text-cream/40">Sin categoría</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-cream/70">
                        <?= (int) $r['duration'] ?> min
                    </td>
                    <td class="px-6 py-4 font-semibold text-goldlight"><?= Money::format((float) $r['price']) ?></td>
                    <td class="px-6 py-4 text-cream/70"><?= (int) $r['sort_order'] ?></td>
                    <td class="px-6 py-4">
                        <?php if ((int) $r['is_active'] === 1): ?>
                            <span class="flex items-center gap-2 text-emerald-400"><i class="fa-solid fa-circle text-[8px]"></i>Activo</span>
                        <?php else: ?>
                            <span class="flex items-center gap-2 text-red-400"><i class="fa-solid fa-circle text-[8px]"></i>Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= ADMIN_URL ?>/services/edit/<?= (int) $r['id'] ?>" title="Editar" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="<?= ADMIN_URL ?>/services/delete/<?= (int) $r['id'] ?>" onsubmit="return confirm('¿Eliminar el servicio <?= addslashes((string) $r['name']) ?>?');">
                                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                                <button type="submit" title="Eliminar" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-red-400 hover:border-red-500/40 transition">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($rows === []): ?>
        <div class="p-12 text-center text-cream/50">
            <i class="fa-solid fa-scissors text-4xl text-gold/40 mb-4"></i>
            <p class="font-display text-lg text-white/80">Aún no hay servicios registrados</p>
            <p class="text-sm mt-1">Crea tu primer servicio para mostrarlo en el sitio web y usarlo en las citas.</p>
        </div>
    <?php endif; ?>
</div>
