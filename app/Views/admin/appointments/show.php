<?php
/**
 * Detalle de Cita — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Session;
use App\Helpers\Money;

$statusBadge = fn(string $name): string => match (strtolower($name)) {
    'pendiente' => 'bg-amber-500/10 text-amber-300 border-amber-500/30',
    'confirmada' => 'bg-sky-500/10 text-sky-300 border-sky-500/30',
    'completada' => 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
    'cancelada' => 'bg-red-500/10 text-red-300 border-red-500/30',
    'no asistió' => 'bg-slate-500/10 text-slate-300 border-slate-500/30',
    default => 'bg-white/10 text-cream/70 border-white/20',
};
?>
<div class="max-w-5xl">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="<?= ADMIN_URL ?>/appointments" class="w-10 h-10 rounded-xl bg-darksoft border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-display text-2xl font-semibold text-white">Cita #<?= (int) $row['id'] ?></h2>
                    <span class="px-3 py-1 rounded-full text-xs font-medium border <?= $statusBadge($row['status_name']) ?>"><?= View::e($row['status_name']) ?></span>
                </div>
                <p class="mt-1 text-sm text-cream/50">
                    <?= View::e(date('d/m/Y', strtotime($row['appointment_date']))) ?> a las <?= View::e(substr((string) $row['appointment_time'], 0, 5)) ?> hrs · <?= View::e($row['type_name']) ?>
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="<?= ADMIN_URL ?>/appointments/edit/<?= (int) $row['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gold/40 text-goldlight text-xs font-bold uppercase tracking-widest hover:bg-gold/10 transition">
                <i class="fa-solid fa-pen"></i> Editar
            </a>
            <form method="POST" action="<?= ADMIN_URL ?>/appointments/delete/<?= (int) $row['id'] ?>" onsubmit="return confirm('¿Eliminar esta cita?');">
                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-red-500/30 text-red-400 text-xs font-bold uppercase tracking-widest hover:bg-red-500/10 transition">
                    <i class="fa-solid fa-trash-can"></i> Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Datos -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-darksoft rounded-2xl border border-white/5 p-6">
                <h3 class="font-display text-base font-semibold text-goldlight mb-4"><i class="fa-solid fa-user mr-2"></i>Cliente</h3>
                <p class="font-semibold text-white"><?= View::e($row['client_name']) ?></p>
                <?php if ($row['client_phone']): ?><p class="mt-1 text-sm text-cream/60"><i class="fa-solid fa-phone mr-2 text-cream/40"></i><?= View::e($row['client_phone']) ?></p><?php endif; ?>
                <?php if ($row['client_email']): ?><p class="mt-1 text-sm text-cream/60"><i class="fa-solid fa-envelope mr-2 text-cream/40"></i><?= View::e($row['client_email']) ?></p><?php endif; ?>
                <?php if ($row['notes']): ?>
                    <div class="mt-4 pt-4 border-t border-white/5">
                        <p class="text-[11px] uppercase tracking-widest text-cream/50 mb-1">Notas</p>
                        <p class="text-sm text-cream/70"><?= View::e($row['notes']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-darksoft rounded-2xl border border-white/5 p-6">
                <h3 class="font-display text-base font-semibold text-goldlight mb-4"><i class="fa-solid fa-info-circle mr-2"></i>Información</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-cream/50">Tipo</dt><dd class="text-white font-medium"><?= View::e($row['type_name']) ?></dd></div>
                    <div class="flex justify-between"><dt class="text-cream/50">Creada por</dt><dd class="text-white font-medium"><?= $creator ? View::e($creator) : '—' ?></dd></div>
                    <div class="flex justify-between"><dt class="text-cream/50">Registrada</dt><dd class="text-white font-medium"><?= View::e(date('d/m/Y H:i', strtotime($row['created_at']))) ?></dd></div>
                </dl>
            </div>
        </div>

        <!-- Detalle de la venta -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h3 class="font-display text-base font-semibold text-goldlight"><i class="fa-solid fa-scissors mr-2"></i>Servicios</h3>
                </div>
                <table class="w-full text-sm">
                    <tbody>
                        <?php foreach ($services as $sv): ?>
                        <tr class="border-b border-white/5 last:border-0">
                            <td class="px-6 py-3.5 text-white font-medium"><?= View::e($sv['service_name'] ?? 'Servicio eliminado') ?></td>
                            <td class="px-6 py-3.5 text-cream/60"><?= $sv['barber_name'] ? 'Barbero: ' . View::e($sv['barber_name']) : '—' ?></td>
                            <td class="px-6 py-3.5 text-right text-goldlight font-semibold"><?= Money::format((float) $sv['price']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if ($services === []): ?>
                        <tr><td class="px-6 py-6 text-center text-cream/40">Sin servicios en esta cita.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h3 class="font-display text-base font-semibold text-goldlight"><i class="fa-solid fa-boxes-stacked mr-2"></i>Productos</h3>
                </div>
                <table class="w-full text-sm">
                    <tbody>
                        <?php foreach ($products as $prd): ?>
                        <tr class="border-b border-white/5 last:border-0">
                            <td class="px-6 py-3.5 text-white font-medium"><?= View::e($prd['product_name'] ?? 'Producto eliminado') ?></td>
                            <td class="px-6 py-3.5 text-cream/60">x<?= (int) $prd['quantity'] ?></td>
                            <td class="px-6 py-3.5 text-right text-goldlight font-semibold"><?= Money::format((float) ($prd['price'] * $prd['quantity'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if ($products === []): ?>
                        <tr><td class="px-6 py-6 text-center text-cream/40">Sin productos en esta cita.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-darksoft rounded-2xl border border-gold/20 p-6 flex items-center justify-between">
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Total de la cita</p>
                <p class="font-display text-3xl font-semibold text-goldlight"><?= Money::format((float) $row['total']) ?></p>
            </div>
        </div>
    </div>
</div>
