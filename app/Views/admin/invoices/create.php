<?php
/**
 * Punto de venta — Formulario de nueva factura.
 */
use App\Core\View;
use App\Helpers\Money;
use App\Helpers\Session;

$products = $products ?? [];
$isModal = $isModal ?? false;
?>

<?php if (!$isModal): ?>
<a href="<?= ADMIN_URL ?>/invoices" class="inline-flex items-center gap-2 text-cream/50 hover:text-goldlight transition mb-6">
    <i class="fa-solid fa-arrow-left"></i> Volver al listado
</a>

<div class="flex items-center gap-3 mb-6">
    <span class="w-10 h-10 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
        <i class="fa-solid fa-cash-register text-gold"></i>
    </span>
    <h1 class="font-display text-2xl font-semibold text-white">Punto de venta</h1>
</div>
<?php endif; ?>

<form method="POST" action="<?= ADMIN_URL ?>/invoices/store" id="invoice-form">
    <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

    <!-- Cliente -->
    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 mb-6">
        <h2 class="font-display text-base font-semibold text-goldlight mb-4"><i class="fa-solid fa-user mr-2"></i>Cliente</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs uppercase tracking-widest text-cream/50 mb-1">Nombre *</label>
                <input type="text" name="client_name" required maxlength="150"
                       class="w-full bg-dark border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:border-gold/50 focus:ring-1 focus:ring-gold/30 outline-none transition"
                       placeholder="Nombre del cliente">
            </div>
            <div>
                <label class="block text-xs uppercase tracking-widest text-cream/50 mb-1">Teléfono</label>
                <input type="tel" name="client_phone" maxlength="20"
                       class="w-full bg-dark border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:border-gold/50 focus:ring-1 focus:ring-gold/30 outline-none transition"
                       placeholder="+503 0000-0000">
            </div>
        </div>
    </div>

    <!-- Productos -->
    <div class="bg-darksoft rounded-2xl border border-white/5 p-6 mb-6">
        <h2 class="font-display text-base font-semibold text-goldlight mb-4"><i class="fa-solid fa-box-open mr-2"></i>Productos</h2>

        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <div class="flex-1">
                <select id="product-select"
                        class="w-full bg-dark border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:border-gold/50 focus:ring-1 focus:ring-gold/30 outline-none transition appearance-none">
                    <option value="">Seleccionar producto...</option>
                    <?php foreach ($products as $p): ?>
                        <?php $stock = (int) $p->getAttribute('stock'); ?>
                        <option value="<?= (int) $p->getAttribute('id') ?>"
                                data-price="<?= View::e((string) $p->getAttribute('price')) ?>"
                                data-stock="<?= $stock ?>"
                                <?= $stock <= 0 ? 'disabled' : '' ?>>
                            <?= View::e($p->getAttribute('name')) ?> — <?= Money::format((float) $p->getAttribute('price')) ?> (Stock: <?= $stock ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-full sm:w-24">
                <input type="number" id="product-qty" min="1" max="99" value="1"
                       class="w-full bg-dark border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white text-center focus:border-gold/50 focus:ring-1 focus:ring-gold/30 outline-none transition">
            </div>
            <button type="button" id="add-product-btn"
                    class="px-6 py-2.5 rounded-lg bg-gold text-darkdeep text-sm font-bold uppercase tracking-widest hover:bg-goldlight transition">
                Agregar
            </button>
        </div>

        <!-- Lista de productos agregados -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="items-table" style="display: none;">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-widest text-cream/50 border-b border-white/10">
                        <th class="pb-2">Producto</th>
                        <th class="pb-2 text-center">Cant.</th>
                        <th class="pb-2 text-right">Precio</th>
                        <th class="pb-2 text-right">Subtotal</th>
                        <th class="pb-2 w-10"></th>
                    </tr>
                </thead>
                <tbody id="items-body"></tbody>
            </table>
            <p id="empty-msg" class="text-cream/40 text-sm py-4 text-center">Agregá productos para comenzar.</p>
        </div>
    </div>

    <!-- Notas + Método de pago + Total -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-1">
            <div class="bg-darksoft rounded-2xl border border-white/5 p-6 h-full">
                <h2 class="font-display text-base font-semibold text-goldlight mb-4"><i class="fa-solid fa-sticky-note mr-2"></i>Notas</h2>
                <textarea name="notes" rows="3" maxlength="255"
                          class="w-full bg-dark border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:border-gold/50 focus:ring-1 focus:ring-gold/30 outline-none transition resize-none"
                          placeholder="Observaciones (opcional)"></textarea>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-darksoft rounded-2xl border border-white/5 p-6 h-full">
                <h2 class="font-display text-base font-semibold text-goldlight mb-4"><i class="fa-solid fa-credit-card mr-2"></i>Método de pago</h2>
                <select name="payment_method"
                        class="w-full bg-dark border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:border-gold/50 focus:ring-1 focus:ring-gold/30 outline-none transition appearance-none">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-darksoft rounded-2xl border border-gold/30 p-6 h-full flex flex-col justify-center">
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Total</p>
                <p class="mt-2 font-display text-3xl font-bold text-goldlight" id="total-display">$0.00</p>
                <p class="mt-1 text-xs text-cream/40" id="items-count">0 productos</p>
            </div>
        </div>
    </div>

    <!-- Botón enviar -->
    <div class="flex justify-end">
        <button type="submit" id="submit-btn"
                class="px-8 py-3 rounded-xl bg-gold text-darkdeep text-sm font-bold uppercase tracking-widest hover:bg-goldlight transition disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fa-solid fa-check mr-2"></i>Generar venta
        </button>
    </div>
</form>

<script>
(function () {
    const select     = document.getElementById('product-select');
    const qtyInput   = document.getElementById('product-qty');
    const addBtn     = document.getElementById('add-product-btn');
    const table      = document.getElementById('items-table');
    const tbody      = document.getElementById('items-body');
    const emptyMsg   = document.getElementById('empty-msg');
    const totalDisp  = document.getElementById('total-display');
    const countDisp  = document.getElementById('items-count');
    const submitBtn  = document.getElementById('submit-btn');
    const form       = document.getElementById('invoice-form');
    const isModal    = <?= $isModal ? 'true' : 'false' ?>;

    let items = [];

    const fmt = n => '$' + Number(n).toFixed(2);

    function render() {
        tbody.innerHTML = '';
        items.forEach((item, i) => {
            const lineTotal = item.price * item.qty;
            const tr = document.createElement('tr');
            tr.className = 'border-b border-white/5';
            tr.innerHTML = `
                <td class="py-3">
                    <p class="text-white font-medium">${item.name}</p>
                    <input type="hidden" name="product_id[]" value="${item.id}">
                </td>
                <td class="py-3 text-center">
                    <input type="number" name="quantity[]" value="${item.qty}" min="1" max="99"
                           data-idx="${i}" class="qty-input w-16 bg-dark border border-white/10 rounded px-2 py-1 text-center text-sm text-white outline-none focus:border-gold/50">
                </td>
                <td class="py-3 text-right text-cream/70">${fmt(item.price)}</td>
                <td class="py-3 text-right font-semibold text-goldlight">${fmt(lineTotal)}</td>
                <td class="py-3 text-center">
                    <button type="button" data-remove="${i}" class="text-red-400 hover:text-red-300 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        tbody.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('change', () => {
                const idx = parseInt(input.dataset.idx);
                const newQty = Math.max(1, Math.min(99, parseInt(input.value) || 1));
                items[idx].qty = newQty;
                render();
            });
        });

        tbody.querySelectorAll('[data-remove]').forEach(btn => {
            btn.addEventListener('click', () => {
                items.splice(parseInt(btn.dataset.remove), 1);
                render();
            });
        });

        let total = 0;
        items.forEach(item => total += item.price * item.qty);
        totalDisp.textContent = fmt(total);
        countDisp.textContent = items.length + (items.length === 1 ? ' producto' : ' productos');

        table.style.display = items.length > 0 ? 'table' : 'none';
        emptyMsg.style.display = items.length > 0 ? 'none' : 'block';
        submitBtn.disabled = items.length === 0;
    }

    addBtn.addEventListener('click', () => {
        const opt = select.options[select.selectedIndex];
        if (!opt || opt.value === '') {
            select.classList.add('border-red-400');
            select.focus();
            setTimeout(() => select.classList.remove('border-red-400'), 1500);
            return;
        }

        const id    = parseInt(opt.value);
        const name  = opt.textContent.split(' — ')[0].trim();
        const price = parseFloat(opt.dataset.price);
        const stock = parseInt(opt.dataset.stock);
        const qty   = Math.max(1, Math.min(stock, parseInt(qtyInput.value) || 1));

        const existing = items.find(it => it.id === id);
        if (existing) {
            existing.qty = Math.min(stock, existing.qty + qty);
        } else {
            items.push({ id, name, price, qty, stock });
        }

        select.selectedIndex = 0;
        qtyInput.value = 1;
        render();
    });

    if (isModal && form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (submitBtn.disabled) return;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Procesando...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.error || 'Error al procesar la venta.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Generar venta';
                }
            })
            .catch(() => {
                alert('Error de conexión.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-check mr-2"></i>Generar venta';
            });
        });
    }
})();
</script>
