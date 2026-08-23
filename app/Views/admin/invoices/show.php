<?php
/**
 * Detalle de factura.
 */
use App\Core\View;
use App\Helpers\Money;
use App\Helpers\Session;

$invoice = $invoice ?? null;
$items   = $items ?? [];
$cashier = $cashier ?? '—';
?>

<a href="<?= ADMIN_URL ?>/invoices" class="inline-flex items-center gap-2 text-cream/50 hover:text-goldlight transition mb-6">
    <i class="fa-solid fa-arrow-left"></i> Volver al listado
</a>

<?php if ($invoice !== null): ?>
<div class="bg-darksoft rounded-2xl border border-white/5 p-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="font-display text-2xl font-semibold text-white">Factura <?= View::e($invoice->getAttribute('invoice_number')) ?></h1>
                <span class="px-3 py-1 rounded-full text-xs font-medium border bg-gold/10 text-goldlight border-gold/30">
                    <?= View::e($invoice->getAttribute('payment_method')) ?>
                </span>
            </div>
            <p class="mt-1 text-sm text-cream/50">
                <?= date('d/m/Y H:i', strtotime((string) $invoice->getAttribute('created_at'))) ?> · Cajero: <?= View::e($cashier) ?>
            </p>
        </div>
        <form method="POST" action="<?= ADMIN_URL ?>/invoices/delete/<?= (int) $invoice->getAttribute('id') ?>"
              onsubmit="return confirm('¿Eliminar esta factura? Se restaurará el stock de los productos.');">
            <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-red-500/30 text-red-400 text-xs font-bold uppercase tracking-widest hover:bg-red-500/10 transition">
                <i class="fa-solid fa-trash-can"></i> Eliminar
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-dark rounded-xl border border-white/5 p-5">
            <p class="text-[11px] uppercase tracking-widest text-cream/50 mb-1">Cliente</p>
            <p class="font-medium text-white"><?= View::e($invoice->getAttribute('client_name')) ?></p>
            <?php $phone = $invoice->getAttribute('client_phone'); ?>
            <?php if ($phone): ?>
                <p class="text-sm text-cream/50 mt-1"><?= View::e($phone) ?></p>
            <?php endif; ?>
        </div>
        <div class="bg-dark rounded-xl border border-white/5 p-5">
            <p class="text-[11px] uppercase tracking-widest text-cream/50 mb-1">Método de pago</p>
            <p class="font-medium text-white"><?= View::e($invoice->getAttribute('payment_method')) ?></p>
        </div>
        <div class="bg-dark rounded-xl border border-gold/30 p-5 text-center">
            <p class="text-[11px] uppercase tracking-widest text-cream/50 mb-1">Total</p>
            <p class="font-display text-3xl font-bold text-goldlight"><?= Money::format((float) $invoice->getAttribute('total')) ?></p>
        </div>
    </div>

    <h2 class="font-display text-base font-semibold text-goldlight mb-3"><i class="fa-solid fa-box-open mr-2"></i>Productos</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-widest text-cream/50 border-b border-white/10">
                    <th class="pb-3">Producto</th>
                    <th class="pb-3 text-center">Cant.</th>
                    <th class="pb-3 text-right">Precio</th>
                    <th class="pb-3 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr class="border-b border-white/5">
                    <td class="py-3 font-medium text-white"><?= View::e($item['product_name'] ?? '—') ?></td>
                    <td class="py-3 text-center text-cream/70"><?= (int) $item['quantity'] ?></td>
                    <td class="py-3 text-right text-cream/70"><?= Money::format((float) $item['price']) ?></td>
                    <td class="py-3 text-right font-semibold text-goldlight"><?= Money::format((float) $item['price'] * (int) $item['quantity']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php $notes = $invoice->getAttribute('notes'); ?>
    <?php if ($notes): ?>
    <div class="mt-6 bg-dark rounded-xl border border-white/5 p-5">
        <p class="text-[11px] uppercase tracking-widest text-cream/50 mb-1">Notas</p>
        <p class="text-sm text-cream/70"><?= nl2br(View::e($notes)) ?></p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
