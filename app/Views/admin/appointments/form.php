<?php
/**
 * Formulario de Cita (crear/editar) — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Session;
use App\Helpers\Money;

$isEditing = $editing !== null;
$submitUrl = $isEditing
    ? ADMIN_URL . '/appointments/update/' . (int) $editing->getAttribute('id')
    : ADMIN_URL . '/appointments/store';
?>
<div class="max-w-5xl">
    <div class="flex items-center gap-4 mb-8">
        <a href="<?= ADMIN_URL ?>/appointments" class="w-10 h-10 rounded-xl bg-darksoft border border-white/10 flex items-center justify-center text-cream/70 hover:text-goldlight hover:border-gold/40 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="font-display text-2xl font-semibold text-white"><?= View::e($title) ?></h2>
            <p class="mt-1 text-sm text-cream/50">Registra el cliente, los servicios y los productos de la visita.</p>
        </div>
    </div>

    <form method="POST" action="<?= $submitUrl ?>" class="space-y-6" id="appointment-form" novalidate data-inline-validation="off">
        <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

        <!-- ======== Datos del cliente y cita ======== -->
        <div class="bg-darksoft rounded-2xl border border-white/5 p-8">
            <h3 class="font-display text-lg font-semibold text-goldlight mb-6"><i class="fa-solid fa-user mr-3"></i>Datos de la cita</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="client_name">Nombre del cliente *</label>
                    <input type="text" id="client_name" name="client_name" required value="<?= View::e($values['client_name'] ?? '') ?>" placeholder="Ej: Marco Silva" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="client_phone">Teléfono <span class="text-cream/40 normal-case">(opcional)</span></label>
                    <input type="tel" id="client_phone" name="client_phone" value="<?= View::e($values['client_phone'] ?? '') ?>" placeholder="+503 0000-0000" pattern="\+503 \d{4}-\d{4}" title="Formato: +503 0000-0000" inputmode="tel" autocomplete="tel" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="client_email">Email <span class="text-cream/40 normal-case">(opcional)</span></label>
                    <input type="email" id="client_email" name="client_email" value="<?= View::e($values['client_email'] ?? '') ?>" placeholder="cliente@correo.com" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30">
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="appointment_date">Fecha *</label>
                    <input type="date" id="appointment_date" name="appointment_date" required min="<?= date('Y-m-d') ?>" value="<?= View::e($values['appointment_date'] ?? date('Y-m-d')) ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="appointment_time">Hora *</label>
                    <input type="time" id="appointment_time" name="appointment_time" required step="60" min="<?= ($values['appointment_date'] ?? '') === date('Y-m-d') ? date('H:i', strtotime('+1 hour')) : '' ?>" value="<?= View::e($values['appointment_time'] ?? '') ?>" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20">
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="type_id">Tipo *</label>
                    <select id="type_id" name="type_id" required class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
                        <?php foreach ($types as $t): ?>
                            <option value="<?= (int) $t->getAttribute('id') ?>" <?= (int) ($values['type_id'] ?? 0) === (int) $t->getAttribute('id') ? 'selected' : '' ?>><?= View::e($t->getAttribute('name')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="status_id">Estado *</label>
                    <select id="status_id" name="status_id" required class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= (int) $s->getAttribute('id') ?>" <?= (int) ($values['status_id'] ?? 0) === (int) $s->getAttribute('id') ? 'selected' : '' ?>><?= View::e($s->getAttribute('name')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-[11px] uppercase tracking-[.2em] text-cream/60 mb-2" for="notes">Descripción <span class="text-cream/40 normal-case">(opcional)</span></label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Indicaciones, preferencias, etc." class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 focus:ring-2 focus:ring-gold/20 placeholder:text-cream/30"><?= View::e($values['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ======== Servicios ======== -->
        <div class="bg-darksoft rounded-2xl border border-white/5 p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-lg font-semibold text-goldlight"><i class="fa-solid fa-scissors mr-3"></i>Servicios</h3>
                <button type="button" onclick="addServiceRow()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gold/40 text-goldlight text-xs font-bold uppercase tracking-widest hover:bg-gold/10 transition">
                    <i class="fa-solid fa-plus"></i> Agregar
                </button>
            </div>

            <?php if ($services === []): ?>
                <p class="text-sm text-cream/50 bg-dark/40 rounded-xl px-4 py-3 border border-dashed border-white/10">
                    <i class="fa-solid fa-circle-info text-gold mr-2"></i>
                    Aún no hay servicios registrados. Puedes crear la cita sin servicios y agregarlos desde el módulo Servicios.
                </p>
            <?php endif; ?>

            <div id="services-container" class="space-y-3">
                <?php foreach ($selectedServices as $i => $sv): ?>
                <div class="service-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="lg:col-span-2">
                        <select name="service_id[]" required class="service-select w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
                            <option value="">Selecciona un servicio</option>
                            <?php foreach ($services as $srv): ?>
                                <option value="<?= (int) $srv->getAttribute('id') ?>" data-price="<?= (float) $srv->getAttribute('price') ?>" <?= (int) $sv['service_id'] === (int) $srv->getAttribute('id') ? 'selected' : '' ?>>
                                    <?= View::e($srv->getAttribute('name')) ?> — <?= Money::format((float) $srv->getAttribute('price')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select name="barber_id[]" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
                            <option value="0">Sin barbero</option>
                            <?php foreach ($barbers as $b): ?>
                                <option value="<?= (int) $b->getAttribute('id') ?>" <?= (int) ($sv['barber_id'] ?? 0) === (int) $b->getAttribute('id') ? 'selected' : '' ?>><?= View::e($b->getAttribute('name')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <span class="service-price flex-1 inline-flex items-center px-4 py-3 rounded-xl bg-gold/5 border border-gold/20 text-goldlight text-sm font-semibold"><?= Money::format((float) ($sv['price'] ?? 0)) ?></span>
                        <button type="button" onclick="this.closest('.service-row').remove(); updateTotals();" class="w-11 rounded-xl border border-white/10 text-cream/70 hover:text-red-400 hover:border-red-500/40 transition"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ======== Productos ======== -->
        <div class="bg-darksoft rounded-2xl border border-white/5 p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-display text-lg font-semibold text-goldlight"><i class="fa-solid fa-boxes-stacked mr-3"></i>Productos</h3>
                <button type="button" onclick="addProductRow()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gold/40 text-goldlight text-xs font-bold uppercase tracking-widest hover:bg-gold/10 transition">
                    <i class="fa-solid fa-plus"></i> Agregar
                </button>
            </div>

            <?php if ($products === []): ?>
                <p class="text-sm text-cream/50 bg-dark/40 rounded-xl px-4 py-3 border border-dashed border-white/10">
                    <i class="fa-solid fa-circle-info text-gold mr-2"></i>
                    Aún no hay productos en el inventario. Puedes crear la cita sin productos.
                </p>
            <?php endif; ?>

            <div id="products-container" class="space-y-3">
                <?php foreach ($selectedProducts as $i => $pv): ?>
                <div class="product-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="lg:col-span-2">
                        <select name="product_id[]" required class="product-select w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
                            <option value="">Selecciona un producto</option>
                            <?php foreach ($products as $prd): ?>
                                <?php $prdStock = (int) $prd->getAttribute('stock'); ?>
                                <?php $prdSelected = (int) $pv['product_id'] === (int) $prd->getAttribute('id'); ?>
                                <option value="<?= (int) $prd->getAttribute('id') ?>" data-price="<?= (float) $prd->getAttribute('price') ?>" data-stock="<?= $prdStock ?>" <?= $prdSelected ? 'selected' : '' ?> <?= $prdStock <= 0 && !$prdSelected ? 'disabled' : '' ?>>
                                    <?= View::e($prd->getAttribute('name')) ?> — <?= Money::format((float) $prd->getAttribute('price')) ?> (stock <?= $prdStock ?><?= $prdStock <= 0 ? ' · agotado' : '' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <input type="number" name="quantity[]" min="1" max="99" value="<?= (int) ($pv['quantity'] ?? 1) ?>" placeholder="Cant." class="product-qty w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 placeholder:text-cream/30">
                    </div>
                    <div class="flex gap-2">
                        <span class="product-line-price flex-1 inline-flex items-center px-4 py-3 rounded-xl bg-gold/5 border border-gold/20 text-goldlight text-sm font-semibold"><?= Money::format((float) (($pv['price'] ?? 0) * ($pv['quantity'] ?? 1))) ?></span>
                        <button type="button" onclick="this.closest('.product-row').remove(); updateTotals();" class="w-11 rounded-xl border border-white/10 text-cream/70 hover:text-red-400 hover:border-red-500/40 transition"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ======== Totales ======== -->
        <div class="bg-darksoft rounded-2xl border border-gold/20 p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="text-[11px] uppercase tracking-widest text-cream/50">Total estimado</p>
                <p id="total-estimate" class="font-display text-3xl font-semibold text-goldlight">$0.00</p>
                <p class="text-xs text-cream/40 mt-1">Calculado en el servidor al guardar.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <a href="<?= ADMIN_URL ?>/appointments" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl border border-white/10 text-cream/70 font-semibold text-xs uppercase tracking-widest hover:border-gold/40 hover:text-goldlight transition">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-gold text-darkdeep font-bold uppercase text-xs tracking-[.2em] hover:bg-goldlight transition shadow-lg shadow-gold/20 btn-shine">
                    <i class="fa-solid <?= $isEditing ? 'fa-floppy-disk' : 'fa-calendar-plus' ?>"></i>
                    <?= $isEditing ? 'Guardar cambios' : 'Crear cita' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
const fmt = n => '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const servicesOptions = `<?php
    $opts = '<option value="">Selecciona un servicio</option>';
    foreach ($services as $srv) {
        $opts .= '<option value="' . (int) $srv->getAttribute('id') . '" data-price="' . (float) $srv->getAttribute('price') . '">' . htmlspecialchars((string) $srv->getAttribute('name')) . ' — ' . App\Helpers\Money::format((float) $srv->getAttribute('price')) . '</option>';
    }
    echo $opts;
?>`;
const barbersOptions = `<?php
    $opts = '<option value="0">Sin barbero</option>';
    foreach ($barbers as $b) {
        $opts .= '<option value="' . (int) $b->getAttribute('id') . '">' . htmlspecialchars((string) $b->getAttribute('name')) . '</option>';
    }
    echo $opts;
?>`;
const productsOptions = `<?php
    $opts = '<option value="">Selecciona un producto</option>';
    foreach ($products as $prd) {
        $stock = (int) $prd->getAttribute('stock');
        $opts .= '<option value="' . (int) $prd->getAttribute('id') . '" data-price="' . (float) $prd->getAttribute('price') . '" data-stock="' . $stock . '"' . ($stock <= 0 ? ' disabled' : '') . '>' . htmlspecialchars((string) $prd->getAttribute('name')) . ' — ' . App\Helpers\Money::format((float) $prd->getAttribute('price')) . ' (stock ' . $stock . ($stock <= 0 ? ' · agotado' : '') . ')</option>';
    }
    echo $opts;
?>`;

function formatPhone(input) {
    let digits = input.value.replace(/\D/g, '');
    if (!digits.startsWith('503')) digits = '503' + digits;
    digits = digits.slice(0, 11);
    let out = '+' + digits.slice(0, 3);
    if (digits.length > 3) out += ' ' + digits.slice(3, 7);
    if (digits.length > 7) out += '-' + digits.slice(7, 11);
    input.value = out;
}

function addServiceRow() {
    const row = document.createElement('div');
    row.className = 'service-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3';
    row.innerHTML = `
        <div class="lg:col-span-2">
            <select name="service_id[]" required class="service-select w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer" onchange="updateTotals()">${servicesOptions}</select>
        </div>
        <div>
            <select name="barber_id[]" class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">${barbersOptions}</select>
        </div>
        <div class="flex gap-2">
            <span class="service-price flex-1 inline-flex items-center px-4 py-3 rounded-xl bg-gold/5 border border-gold/20 text-goldlight text-sm font-semibold">$0.00</span>
            <button type="button" onclick="this.closest('.service-row').remove(); updateTotals();" class="w-11 rounded-xl border border-white/10 text-cream/70 hover:text-red-400 hover:border-red-500/40 transition"><i class="fa-solid fa-xmark"></i></button>
        </div>`;
    document.getElementById('services-container').appendChild(row);
    updateTotals();
}

function addProductRow() {
    const row = document.createElement('div');
    row.className = 'product-row grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3';
    row.innerHTML = `
        <div class="lg:col-span-2">
            <select name="product_id[]" required class="product-select w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer" onchange="updateTotals()">${productsOptions}</select>
        </div>
        <div>
            <input type="number" name="quantity[]" min="1" max="99" value="1" class="product-qty w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 placeholder:text-cream/30" oninput="updateTotals()">
        </div>
        <div class="flex gap-2">
            <span class="product-line-price flex-1 inline-flex items-center px-4 py-3 rounded-xl bg-gold/5 border border-gold/20 text-goldlight text-sm font-semibold">$0.00</span>
            <button type="button" onclick="this.closest('.product-row').remove(); updateTotals();" class="w-11 rounded-xl border border-white/10 text-cream/70 hover:text-red-400 hover:border-red-500/40 transition"><i class="fa-solid fa-xmark"></i></button>
        </div>`;
    document.getElementById('products-container').appendChild(row);
    updateTotals();
}

function updateTotals() {
    let total = 0;
    document.querySelectorAll('.service-row').forEach(row => {
        const sel = row.querySelector('.service-select');
        const priceEl = row.querySelector('.service-price');
        const price = sel.selectedIndex > 0 ? parseFloat(sel.selectedOptions[0].dataset.price || 0) : 0;
        priceEl.textContent = fmt(price);
        total += price;
    });
    document.querySelectorAll('.product-row').forEach(row => {
        const sel = row.querySelector('.product-select');
        const qtyEl = row.querySelector('.product-qty');
        const lineEl = row.querySelector('.product-line-price');
        const price = sel.selectedIndex > 0 ? parseFloat(sel.selectedOptions[0].dataset.price || 0) : 0;
        const qty = Math.max(1, parseInt(qtyEl.value || 1, 10));
        const line = price * qty;
        lineEl.textContent = fmt(line);
        total += line;
    });
    document.getElementById('total-estimate').textContent = fmt(total);
}

document.addEventListener('input', e => {
    if (e.target.classList.contains('product-qty')) updateTotals();
    if (e.target.id === 'client_phone') formatPhone(e.target);
    if (e.target.classList.contains('field-invalid')) {
        e.target.classList.remove('field-invalid', 'border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
        const err = e.target.nextElementSibling;
        if (err && err.classList.contains('field-error')) err.remove();
    }
});
document.addEventListener('change', e => {
    if (e.target.classList.contains('service-select') || e.target.classList.contains('product-select')) updateTotals();
});

const dateInput = document.getElementById('appointment_date');
const timeInput = document.getElementById('appointment_time');

function todayStr() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function minTimeStr() {
    const d = new Date(Date.now() + 60 * 60 * 1000);
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
}

function updateTimeMin() {
    if (dateInput && timeInput) {
        timeInput.min = dateInput.value === todayStr() ? minTimeStr() : '';
    }
}

function syncTypeWithDate() {
    const typeSelect = document.getElementById('type_id');
    if (!typeSelect || !dateInput) return;
    const targetName = dateInput.value === todayStr() ? 'Ahora' : 'Programada';
    for (const opt of typeSelect.options) {
        if (opt.textContent.trim() === targetName) {
            typeSelect.value = opt.value;
            break;
        }
    }
}

if (dateInput) {
    dateInput.addEventListener('change', updateTimeMin);
    dateInput.addEventListener('change', syncTypeWithDate);
}
document.addEventListener('DOMContentLoaded', updateTimeMin);
document.addEventListener('DOMContentLoaded', syncTypeWithDate);

document.addEventListener('DOMContentLoaded', updateTotals);

const appointmentForm = document.getElementById('appointment-form');

function clearFieldErrors() {
    document.querySelectorAll('.field-error').forEach(el => el.remove());
    document.querySelectorAll('.field-invalid').forEach(el => el.classList.remove('field-invalid', 'border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20'));
}

appointmentForm.addEventListener('submit', function (e) {
    clearFieldErrors();
    let hasErrors = false;
    let firstError = null;

    const showError = (field, message) => {
        hasErrors = true;
        if (!firstError) firstError = field;
        field.classList.add('field-invalid', 'border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
        const p = document.createElement('p');
        p.className = 'field-error mt-2 text-xs text-red-400';
        p.textContent = message;
        field.insertAdjacentElement('afterend', p);
    };

    const requiredFields = {
        client_name: 'Nombre del cliente',
        appointment_date: 'Fecha',
        appointment_time: 'Hora',
        type_id: 'Tipo',
        status_id: 'Estado',
    };
    for (const [id, label] of Object.entries(requiredFields)) {
        const el = document.getElementById(id);
        if (!el || el.value.trim() === '') showError(el, 'El campo ' + label + ' es obligatorio.');
    }

    const phoneEl = document.getElementById('client_phone');
    const phone = phoneEl.value.trim();
    if (phone !== '' && !/^\+503 \d{4}-\d{4}$/.test(phone)) {
        showError(phoneEl, 'El teléfono debe tener el formato +503 0000-0000.');
    }

    const emailEl = document.getElementById('client_email');
    const email = emailEl.value.trim();
    if (email !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError(emailEl, 'Ingresa un email de cliente válido.');
    }

    if (dateInput.value < todayStr()) {
        showError(dateInput, 'No puedes registrar citas en fechas pasadas.');
    } else if (dateInput.value === todayStr() && timeInput.value !== '' && timeInput.value < minTimeStr()) {
        showError(timeInput, 'La hora debe ser al menos 1 hora después de la hora actual.');
    }

    const hasService = [...document.querySelectorAll('.service-select')].some(s => Number(s.value) > 0);
    const hasProduct = [...document.querySelectorAll('.product-select')].some(p => Number(p.value) > 0);
    if (!hasService && !hasProduct) {
        showError(document.getElementById('services-container'), 'Debes agregar al menos un servicio o un producto a la cita.');
    }

    document.querySelectorAll('.product-row').forEach(row => {
        const sel = row.querySelector('.product-select');
        const qtyEl = row.querySelector('.product-qty');
        if (Number(sel.value) <= 0) return;
        const opt = sel.selectedOptions[0];
        const stock = opt ? Number(opt.dataset.stock || 0) : 0;
        const qty = Math.max(1, parseInt(qtyEl.value || 1, 10));
        if (stock <= 0) {
            showError(sel, 'El producto seleccionado está agotado y no se puede agregar a la cita.');
        } else if (qty > stock) {
            showError(qtyEl, 'Solo hay ' + stock + ' unidades disponibles de este producto.');
        }
    });

    if (hasErrors) {
        e.preventDefault();
        if (firstError) {
            firstError.scrollIntoView({ block: 'center', behavior: 'smooth' });
            firstError.focus({ preventScroll: true });
        }
    }
});
</script>
