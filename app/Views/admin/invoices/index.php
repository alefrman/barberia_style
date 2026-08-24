<?php
/**
 * Listado de facturas — Punto de venta.
 */
use App\Core\View;
use App\Helpers\Money;
use App\Helpers\Session;

$invoices = $invoices ?? [];
?>

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <span class="w-10 h-10 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-cash-register text-gold"></i>
        </span>
        <h1 class="font-display text-2xl font-semibold text-white">Punto de venta</h1>
    </div>
    <button type="button" id="open-pos-btn"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gold text-darkdeep text-xs font-bold uppercase tracking-widest hover:bg-goldlight transition">
        <i class="fa-solid fa-plus"></i> Nueva venta
    </button>
</div>

<div class="bg-darksoft rounded-2xl border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-widest text-cream/50 border-b border-white/10">
                    <th class="px-6 py-4">#</th>
                    <th class="px-6 py-4">Fecha</th>
                    <th class="px-6 py-4">Cliente</th>
                    <th class="px-6 py-4 text-center">Productos</th>
                    <th class="px-6 py-4">Método</th>
                    <th class="px-6 py-4 text-right">Total</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                <tr class="border-b border-white/5 hover:bg-gold/5 transition">
                    <td class="px-6 py-4 font-medium text-goldlight"><?= View::e($inv['invoice_number']) ?></td>
                    <td class="px-6 py-4 text-cream/70"><?= date('d/m/Y H:i', strtotime($inv['created_at'])) ?></td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-white"><?= View::e($inv['client_name']) ?></p>
                        <?php if (!empty($inv['client_phone'])): ?>
                            <p class="text-xs text-cream/50"><?= View::e($inv['client_phone']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center text-cream/70"><?= (int) $inv['items_count'] ?></td>
                    <td class="px-6 py-4 text-cream/70"><?= View::e($inv['payment_method']) ?></td>
                    <td class="px-6 py-4 text-right font-semibold text-goldlight"><?= Money::format((float) $inv['total']) ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= ADMIN_URL ?>/invoices/show/<?= (int) $inv['id'] ?>" title="Ver"
                               class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>
                            <form method="POST" action="<?= ADMIN_URL ?>/invoices/delete/<?= (int) $inv['id'] ?>"
                                  onsubmit="return confirm('¿Eliminar esta factura? Se restaurará el stock.');">
                                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                                <button type="submit" title="Eliminar"
                                        class="w-9 h-9 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/70 hover:text-red-400 hover:border-red-500/40 transition">
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

    <?php if ($invoices === []): ?>
        <div class="p-12 text-center text-cream/50">
            <i class="fa-solid fa-cash-register text-4xl text-gold/40 mb-4"></i>
            <p class="text-sm">No hay facturas registradas.</p>
            <button type="button" onclick="document.getElementById('open-pos-btn').click()"
                    class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gold text-darkdeep text-xs font-bold uppercase tracking-widest hover:bg-goldlight transition">
                <i class="fa-solid fa-plus"></i> Nueva venta
            </button>
        </div>
    <?php endif; ?>
</div>

<div id="pos-modal" class="fixed inset-0 z-[9999] hidden bg-black/70 backdrop-blur-sm">
    <div class="absolute inset-0 flex items-start justify-center pt-8 pb-8 overflow-y-auto">
        <div class="relative w-full max-w-4xl bg-darksoft rounded-2xl border border-white/10 shadow-2xl mx-4">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-gold/10 border border-gold/30 flex items-center justify-center">
                        <i class="fa-solid fa-cash-register text-gold text-sm"></i>
                    </span>
                    <h2 class="font-display text-lg font-semibold text-white">Punto de venta</h2>
                </div>
                <button type="button" id="close-pos-modal"
                        class="w-8 h-8 rounded-lg bg-dark border border-white/10 flex items-center justify-center text-cream/50 hover:text-red-400 hover:border-red-500/40 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
            <div id="pos-modal-body" class="p-6">
                <div class="flex items-center justify-center py-12">
                    <i class="fa-solid fa-spinner fa-spin text-gold text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modal     = document.getElementById('pos-modal');
    const modalBody = document.getElementById('pos-modal-body');
    const openBtn   = document.getElementById('open-pos-btn');
    const closeBtn  = document.getElementById('close-pos-modal');
    let loaded = false;

    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (!loaded) {
            fetch('<?= ADMIN_URL ?>/invoices/create?modal=1')
                .then(r => r.text())
                .then(html => {
                    modalBody.innerHTML = html;
                    modalBody.querySelectorAll('script').forEach(old => {
                        const s = document.createElement('script');
                        s.textContent = old.textContent;
                        old.replaceWith(s);
                    });
                    loaded = true;
                })
                .catch(() => {
                    modalBody.innerHTML = '<p class="text-center text-red-400 py-8">Error al cargar el formulario.</p>';
                });
        }
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('hidden') && e.key === 'Escape') closeModal();
    });
})();
</script>
