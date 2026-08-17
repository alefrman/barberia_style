<?php
/**
 * Listado de Citas — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Session;
use App\Helpers\Money;

$f = $filters;
$statusBadge = fn(string $name): string => match (strtolower($name)) {
    'pendiente' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
    'confirmada' => 'bg-sky-500/10 text-sky-300 border-sky-500/30',
    'completada' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
    'cancelada' => 'bg-red-500/10 text-red-300 border-red-500/30',
    'no asistió' => 'bg-slate-500/10 text-slate-300 border-slate-500/30',
    default => 'bg-white/10 text-cream/70 border-white/20',
};
?>
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
    <div>
        <h2 class="font-display text-2xl font-semibold text-white">Citas / Turnos</h2>
        <p class="mt-1 text-sm text-cream/50">Administra los turnos, servicios y ventas de cada cita.</p>
    </div>
    <a href="<?= ADMIN_URL ?>/appointments/create" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-widest hover:bg-goldlight transition btn-shine">
        <i class="fa-solid fa-calendar-plus"></i> Nueva cita
    </a>
</div>

<!-- Tarjetas resumen -->
<div class="grid grid-cols-2 xl:grid-cols-5 gap-4 mb-8">
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5 flex items-center gap-3">
        <span class="w-10 h-10 rounded-lg bg-gold/10 border border-gold/30 flex items-center justify-center"><i class="fa-solid fa-calendar-day text-gold"></i></span>
        <div><p class="font-display text-2xl font-semibold text-white"><?= $counts['today'] ?></p><p class="text-[10px] uppercase tracking-widest text-cream/50">Hoy</p></div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5 flex items-center gap-3">
        <span class="w-10 h-10 rounded-lg bg-amber-500/10 border border-amber-500/30 flex items-center justify-center"><i class="fa-solid fa-clock text-amber-400"></i></span>
        <div><p class="font-display text-2xl font-semibold text-white"><?= $counts['pending'] ?></p><p class="text-[10px] uppercase tracking-widest text-cream/50">Pendientes</p></div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5 flex items-center gap-3">
        <span class="w-10 h-10 rounded-lg bg-sky-500/10 border border-sky-500/30 flex items-center justify-center"><i class="fa-solid fa-check-double text-sky-400"></i></span>
        <div><p class="font-display text-2xl font-semibold text-white"><?= $counts['confirmed'] ?></p><p class="text-[10px] uppercase tracking-widest text-cream/50">Confirmadas</p></div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5 flex items-center gap-3">
        <span class="w-10 h-10 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center"><i class="fa-solid fa-circle-check text-emerald-400"></i></span>
        <div><p class="font-display text-2xl font-semibold text-white"><?= $counts['completed'] ?></p><p class="text-[10px] uppercase tracking-widest text-cream/50">Completadas</p></div>
    </div>
    <div class="bg-darksoft rounded-2xl border border-white/5 p-5 flex items-center gap-3 col-span-2 xl:col-span-1">
        <span class="w-10 h-10 rounded-lg bg-white/10 border border-white/10 flex items-center justify-center"><i class="fa-solid fa-list-check text-cream/70"></i></span>
        <div><p class="font-display text-2xl font-semibold text-white"><?= $counts['total'] ?></p><p class="text-[10px] uppercase tracking-widest text-cream/50">Total</p></div>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="<?= ADMIN_URL ?>/appointments" class="bg-darksoft rounded-2xl border border-white/5 p-5 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
    <div>
        <label class="block text-[10px] uppercase tracking-widest text-cream/50 mb-1.5">Buscar</label>
        <input type="text" name="q" value="<?= View::e($f['q']) ?>" placeholder="Cliente, teléfono, email"
               class="w-full px-3 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 placeholder:text-cream/30">
    </div>
    <div>
        <label class="block text-[10px] uppercase tracking-widest text-cream/50 mb-1.5">Estado</label>
        <select name="status_id" class="w-full px-3 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
            <option value="0">Todos</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= (int) $s->getAttribute('id') ?>" <?= (int) $f['status_id'] === (int) $s->getAttribute('id') ? 'selected' : '' ?>><?= View::e($s->getAttribute('name')) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-[10px] uppercase tracking-widest text-cream/50 mb-1.5">Desde</label>
        <input type="date" name="date_from" value="<?= View::e($f['date_from']) ?>" class="w-full px-3 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60">
    </div>
    <div>
        <label class="block text-[10px] uppercase tracking-widest text-cream/50 mb-1.5">Hasta</label>
        <input type="date" name="date_to" value="<?= View::e($f['date_to']) ?>" class="w-full px-3 py-2.5 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60">
    </div>
    <div class="flex gap-2">
        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-widest hover:bg-goldlight transition">
            <i class="fa-solid fa-filter"></i> Filtrar
        </button>
        <a href="<?= ADMIN_URL ?>/appointments" title="Limpiar filtros" class="px-4 py-2.5 rounded-xl border border-white/10 text-cream/70 hover:border-gold/40 hover:text-goldlight transition">
            <i class="fa-solid fa-rotate-left"></i>
        </a>
    </div>
</form>

<div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-widest text-cream/50 border-b border-white/10">
                    <th class="px-6 py-4">Fecha</th>
                    <th class="px-6 py-4">Cliente</th>
                    <th class="px-6 py-4">Tipo</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4">Servicios</th>
                    <th class="px-6 py-4">Productos</th>
                    <th class="px-6 py-4 text-right">Total</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                <?php $isCompleted = strtolower($r['status_name']) === 'completada'; ?>
                <tr class="border-b border-white/5 hover:bg-gold/5 transition">
                    <td class="px-6 py-4">
                        <p class="font-medium text-white"><?= View::e(date('d/m/Y', strtotime($r['appointment_date']))) ?></p>
                        <p class="text-xs text-cream/50"><?= View::e(substr((string) $r['appointment_time'], 0, 5)) ?> hrs</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-white"><?= View::e($r['client_name']) ?></p>
                        <p class="text-xs text-cream/50"><?= View::e($r['client_phone']) ?></p>
                    </td>
                    <td class="px-6 py-4 text-cream/70"><?= View::e($r['type_name']) ?></td>
                    <td class="px-6 py-4">
                        <select data-status-select data-current-status="<?= View::e(strtolower($r['status_name'])) ?>"
                                data-csrf="<?= View::e(Session::csrfToken()) ?>"
                                data-url="<?= ADMIN_URL ?>/appointments/status/<?= (int) $r['id'] ?>"
                                <?= $isCompleted ? 'disabled' : '' ?>
                                class="px-3 py-1 rounded-full text-xs font-medium border cursor-pointer outline-none appearance-none text-center <?= $statusBadge($r['status_name']) ?>">
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= (int) $s->getAttribute('id') ?>" <?= (int) $r['status_id'] === (int) $s->getAttribute('id') ? 'selected' : '' ?>><?= View::e($s->getAttribute('name')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="px-6 py-4 text-cream/70"><?= (int) $r['services_count'] ?> serv.</td>
                    <td class="px-6 py-4 text-cream/70"><?= (int) $r['products_count'] ?> prod.</td>
                    <td class="px-6 py-4 text-right font-semibold text-goldlight">
                        <?= Money::format((float) $r['total']) ?>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= ADMIN_URL ?>/appointments/show/<?= (int) $r['id'] ?>" title="Ver" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            <?php if (!$isCompleted): ?>
                            <a href="<?= ADMIN_URL ?>/appointments/edit/<?= (int) $r['id'] ?>" title="Editar" class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="<?= ADMIN_URL ?>/appointments/delete/<?= (int) $r['id'] ?>" onsubmit="return confirm('¿Eliminar la cita de <?= addslashes((string) $r['client_name']) ?>? Se restaurará el stock de los productos.');">
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

    <?php if ($rows === []): ?>
        <div class="p-12 text-center text-cream/50">
            <i class="fa-solid fa-calendar-xmark text-4xl text-gold/40 mb-4"></i>
            <p class="font-display text-lg text-white/80">No hay citas que coincidan</p>
            <p class="text-sm mt-1">Prueba limpiando los filtros o crea una nueva cita.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmación -->
<div id="app-confirm-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/70 backdrop-blur-sm">
    <div class="bg-darksoft border border-white/10 rounded-2xl shadow-2xl shadow-black/40 w-full max-w-md mx-4 p-8 text-center">
        <div class="w-14 h-14 rounded-full bg-gold/10 border border-gold/30 flex items-center justify-center mx-auto mb-5">
            <i class="fa-solid fa-triangle-exclamation text-gold text-xl"></i>
        </div>
        <h3 class="font-display text-xl font-semibold text-white mb-2">Confirmar acción</h3>
        <p id="app-confirm-message" class="text-sm text-cream/60 mb-6">¿Estás seguro?</p>
        <div class="mb-5">
            <label class="block text-[11px] uppercase tracking-[.2em] text-cream/50 mb-2">Escribí <span class="font-bold text-gold">SI</span> para confirmar</label>
            <input id="app-confirm-input" type="text" maxlength="2" autocomplete="off"
                   class="w-24 mx-auto text-center text-lg font-bold uppercase tracking-widest px-4 py-3 rounded-xl bg-dark border border-white/10 text-white outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 transition placeholder:text-cream/30"
                   placeholder="—">
        </div>
        <div class="flex gap-3">
            <button id="app-confirm-cancel" type="button" class="flex-1 px-4 py-3 rounded-xl border border-white/10 text-cream/60 text-xs font-bold uppercase tracking-widest hover:bg-white/5 hover:text-white transition">
                Cancelar
            </button>
            <button id="app-confirm-ok" type="button" disabled
                    class="flex-1 px-4 py-3 rounded-xl bg-gold text-darkdeep text-xs font-bold uppercase tracking-widest transition opacity-40 cursor-not-allowed">
                Confirmar
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        /* ---- Modal de confirmación ---- */
        const modal       = document.getElementById('app-confirm-modal');
        const modalMsg    = document.getElementById('app-confirm-message');
        const modalInput  = document.getElementById('app-confirm-input');
        const modalOk     = document.getElementById('app-confirm-ok');
        const modalCancel = document.getElementById('app-confirm-cancel');
        let modalResolve  = null;

        function openModal(message) {
            return new Promise((resolve) => {
                modalResolve = resolve;
                modalMsg.textContent = message;
                modalInput.value = '';
                modalOk.disabled = true;
                modalOk.className = 'flex-1 px-4 py-3 rounded-xl bg-gold text-darkdeep text-xs font-bold uppercase tracking-widest transition opacity-40 cursor-not-allowed';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => modalInput.focus(), 50);
            });
        }

        function closeModal(value) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (modalResolve) { modalResolve(value); modalResolve = null; }
        }

        modalInput.addEventListener('input', () => {
            const match = modalInput.value.trim().toUpperCase() === 'SI';
            modalOk.disabled = !match;
            modalOk.className = match
                ? 'flex-1 px-4 py-3 rounded-xl bg-gold text-darkdeep text-xs font-bold uppercase tracking-widest transition hover:bg-goldlight cursor-pointer'
                : 'flex-1 px-4 py-3 rounded-xl bg-gold text-darkdeep text-xs font-bold uppercase tracking-widest transition opacity-40 cursor-not-allowed';
        });

        modalOk.addEventListener('click', () => closeModal(true));
        modalCancel.addEventListener('click', () => closeModal(false));
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(false); });
        modalInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !modalOk.disabled) closeModal(true);
            if (e.key === 'Escape') closeModal(false);
        });

        window.showAppConfirm = openModal;

        /* ---- Status dropdown ---- */
        const classesByStatus = {
            'pendiente': 'bg-amber-500/10 text-amber-300 border-amber-500/30',
            'confirmada': 'bg-sky-500/10 text-sky-300 border-sky-500/30',
            'completada': 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
            'cancelada': 'bg-red-500/10 text-red-300 border-red-500/30',
            'no asistió': 'bg-slate-500/10 text-slate-300 border-slate-500/30',
        };
        const baseClass = 'bg-white/10 text-cream/70 border-white/20';

        document.querySelectorAll('[data-status-select]').forEach((select) => {
            select.dataset.previous = select.value;

            select.addEventListener('change', async () => {
                const previous = select.dataset.previous;
                const newVal = select.value;
                const currentStatus = select.dataset.currentStatus;

                if (newVal === '3' && currentStatus !== 'completada') {
                    const confirmed = await showAppConfirm('¿Marcar esta cita como completada? Una vez completada no se podrá editar ni modificar.');
                    if (!confirmed) {
                        select.value = previous;
                        return;
                    }
                }

                select.disabled = true;

                const applyColors = () => {
                    const label = (select.selectedOptions[0]?.textContent || '').toLowerCase();
                    select.className = 'px-3 py-1 rounded-full text-xs font-medium border cursor-pointer outline-none appearance-none text-center ' +
                        (classesByStatus[label] || baseClass);
                };

                try {
                    const res = await fetch(select.dataset.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            _csrf: select.dataset.csrf,
                            status_id: select.value,
                        }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.ok) {
                        throw new Error(data.message || 'No se pudo actualizar el estado.');
                    }
                    select.dataset.previous = select.value;
                    applyColors();
                    setTimeout(() => window.location.reload(), 350);
                } catch (err) {
                    alert(err.message || 'No se pudo actualizar el estado.');
                    select.value = previous;
                    applyColors();
                } finally {
                    select.disabled = false;
                }
            });
        });
    })();
</script>
