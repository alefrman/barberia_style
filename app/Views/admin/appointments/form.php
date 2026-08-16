<?php
/**
 * Formulario de Cita (crear/editar) — Panel de Administración
 */
use App\Core\View;
use App\Helpers\Session;
use App\Helpers\Money;
use App\Helpers\Settings;

$isEditing = $editing !== null;
$submitUrl = $isEditing
    ? ADMIN_URL . '/appointments/update/' . (int) $editing->getAttribute('id')
    : ADMIN_URL . '/appointments/store';

$dayToIndex = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];
$jsHours = [];
foreach (Settings::businessHours() as $dayKey => $range) {
    $jsHours[$dayToIndex[$dayKey]] = [
        'open'  => (string) ($range['open'] ?? ''),
        'close' => (string) ($range['close'] ?? ''),
    ];
}

// Franjas de hora (cada 15 min) según el horario del día seleccionado.
$timeOptions = '';
$timeDate = (string) ($values['appointment_date'] ?? date('Y-m-d'));
$timeSelected = substr((string) ($values['appointment_time'] ?? ''), 0, 5);
$tDayKey = ['sun' => 'sunday', 'mon' => 'monday', 'tue' => 'tuesday', 'wed' => 'wednesday', 'thu' => 'thursday', 'fri' => 'friday', 'sat' => 'saturday'][strtolower(date('D', strtotime($timeDate)))] ?? '';
$tRange = $tDayKey !== '' ? (Settings::businessHours()[$tDayKey] ?? ['open' => '', 'close' => '']) : ['open' => '', 'close' => ''];
$tOpen = (string) ($tRange['open'] ?? '');
$tClose = (string) ($tRange['close'] ?? '');
if ($tOpen !== '' && $tClose !== '' && preg_match('/^\d{2}:\d{2}$/', $tOpen) && preg_match('/^\d{2}:\d{2}$/', $tClose)) {
    [$tOh, $tOm] = array_map('intval', explode(':', $tOpen));
    [$tCh, $tCm] = array_map('intval', explode(':', $tClose));
    $tStart = $tOh * 60 + $tOm;
    $tEnd = $tCh * 60 + $tCm;
    $tIsToday = $timeDate === date('Y-m-d');
    $tMin = -1;
    if ($tIsToday) {
        $tNowPlus1 = strtotime('+1 hour');
        if (date('Y-m-d', $tNowPlus1) === date('Y-m-d')) {
            [$tNh, $tNm] = array_map('intval', explode(':', date('H:i', $tNowPlus1)));
            $tMin = $tNh * 60 + $tNm;
        } else {
            $tMin = PHP_INT_MAX;
        }
    }
    for ($t = $tStart; $t < $tEnd; $t += 15) {
        if ($tIsToday && $t < $tMin) {
            continue;
        }
        $hh = str_pad((string) intdiv($t, 60), 2, '0', STR_PAD_LEFT);
        $mm = str_pad((string) ($t % 60), 2, '0', STR_PAD_LEFT);
        $opt = $hh . ':' . $mm;
        $timeOptions .= '<option value="' . $opt . '"' . ($opt === $timeSelected ? ' selected' : '') . '>' . $opt . '</option>';
    }
}
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

        <div id="availability-banner" class="hidden bg-red-500/10 border border-red-500/30 text-red-300 text-sm rounded-xl px-4 py-3">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>
            <span id="availability-banner-text"></span>
        </div>

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
                    <select id="appointment_time" name="appointment_time" required <?= $timeOptions === '' ? 'disabled' : '' ?> class="w-full px-4 py-3 rounded-xl bg-dark/60 border border-white/10 text-white text-sm outline-none focus:border-gold/60 cursor-pointer">
                        <?= $timeOptions !== '' ? $timeOptions : '<option value="">Sin horarios disponibles — elige otra fecha</option>' ?>
                    </select>
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
                                <option value="<?= (int) $srv->getAttribute('id') ?>" data-price="<?= (float) $srv->getAttribute('price') ?>" data-duration="<?= (int) $srv->getAttribute('duration') ?>" <?= (int) $sv['service_id'] === (int) $srv->getAttribute('id') ? 'selected' : '' ?>>
                                    <?= View::e($srv->getAttribute('name')) ?> — <?= Money::format((float) $srv->getAttribute('price')) ?> · <?= (int) $srv->getAttribute('duration') ?> min
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
        $opts .= '<option value="' . (int) $srv->getAttribute('id') . '" data-price="' . (float) $srv->getAttribute('price') . '" data-duration="' . (int) $srv->getAttribute('duration') . '">' . htmlspecialchars((string) $srv->getAttribute('name')) . ' — ' . App\Helpers\Money::format((float) $srv->getAttribute('price')) . ' · ' . (int) $srv->getAttribute('duration') . ' min</option>';
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

function dateStr(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function todayStr() {
    return dateStr(new Date());
}

function minTimeStr() {
    const d = new Date(Date.now() + 60 * 60 * 1000);
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
}

const businessHours = <?= json_encode($jsHours, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const weekdayNames = { 0: 'domingo', 1: 'lunes', 2: 'martes', 3: 'miércoles', 4: 'jueves', 5: 'viernes', 6: 'sábado' };

function dayIndexOf(dateStr) {
    return new Date(dateStr + 'T00:00:00').getDay();
}

function rangeFor(dateStr) {
    return businessHours[dayIndexOf(dateStr)] || { open: '', close: '' };
}

function isClosedDay(dateStr) {
    const r = rangeFor(dateStr);
    return r.open === '' || r.close === '';
}

function rebuildTimeOptions() {
    if (!dateInput || !timeInput) return;
    const date = dateInput.value;
    if (date === '') {
        timeInput.innerHTML = '<option value="">Sin horarios disponibles — elige una fecha</option>';
        timeInput.disabled = true;
        return;
    }
    const r = rangeFor(date);
    if (r.open === '' || r.close === '') {
        timeInput.innerHTML = '<option value="">Día cerrado — elige otra fecha</option>';
        timeInput.disabled = true;
        return;
    }
    const prev = timeInput.value;
    const openMin = Number(r.open.slice(0, 2)) * 60 + Number(r.open.slice(3, 5));
    const closeMin = Number(r.close.slice(0, 2)) * 60 + Number(r.close.slice(3, 5));
    const isToday = date === todayStr();
    const nowPlus1 = new Date(Date.now() + 60 * 60 * 1000);
    const nowPlus1Min = nowPlus1.getHours() * 60 + nowPlus1.getMinutes();
    const allPastToday = isToday && dateStr(nowPlus1) !== todayStr();
    let opts = '';
    for (let cur = openMin; cur < closeMin; cur += 15) {
        if (allPastToday || (isToday && cur < nowPlus1Min)) continue;
        const hh = String(Math.floor(cur / 60)).padStart(2, '0');
        const mm = String(cur % 60).padStart(2, '0');
        const t = hh + ':' + mm;
        opts += '<option value="' + t + '"' + (t === prev ? ' selected' : '') + '>' + t + '</option>';
    }
    timeInput.innerHTML = opts !== ''
        ? opts
        : '<option value="">Sin horarios disponibles para este día — elige otra fecha</option>';
    timeInput.disabled = opts === '';
    if (opts !== '' && timeInput.value === '') {
        timeInput.value = timeInput.querySelector('option').value;
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
    dateInput.addEventListener('change', rebuildTimeOptions);
    dateInput.addEventListener('change', syncTypeWithDate);
}
document.addEventListener('DOMContentLoaded', rebuildTimeOptions);
document.addEventListener('DOMContentLoaded', syncTypeWithDate);

document.addEventListener('DOMContentLoaded', updateTotals);

const appointmentForm = document.getElementById('appointment-form');

/* ======== Verificación de disponibilidad en vivo (AJAX) ======== */
const availabilityUrl = ADMIN_URL + '/appointments/availability';
const csrfToken = appointmentForm.querySelector('input[name="_csrf"]').value;
const excludeId = <?= $isEditing ? (int) $editing->getAttribute('id') : 0 ?>;
let availabilityTimer = null;
let conflictActive = false;

const availabilityBanner = document.getElementById('availability-banner');
const availabilityBannerText = document.getElementById('availability-banner-text');

function collectAvailabilityServices() {
    const rows = [];
    document.querySelectorAll('.service-row').forEach(row => {
        const serviceSel = row.querySelector('.service-select');
        const barberSel = row.querySelector('[name="barber_id[]"]');
        const serviceId = Number(serviceSel.value);
        const barberId = Number(barberSel.value);
        if (serviceId > 0 && barberId > 0) {
            rows.push({ service_id: serviceId, barber_id: barberId });
        }
    });
    return rows;
}

function clearAvailabilityMarks() {
    document.querySelectorAll('[name="barber_id[]"]').forEach(sel => {
        sel.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
        const msg = sel.nextElementSibling;
        if (msg && msg.classList.contains('availability-msg')) msg.remove();
    });
}

function renderAvailability(conflicts) {
    clearAvailabilityMarks();
    if (!conflicts.length) {
        conflictActive = false;
        availabilityBanner.classList.add('hidden');
        return;
    }
    conflictActive = true;
    const texts = [];
    conflicts.forEach(c => {
        texts.push(c.barber + ' a las ' + c.time + (c.next_available ? ' (disponible a partir de las ' + c.next_available + ')' : ''));
        document.querySelectorAll('.service-row').forEach(row => {
            const barberSel = row.querySelector('[name="barber_id[]"]');
            if (barberSel && Number(barberSel.value) === c.barber_id) {
                barberSel.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20');
                if (!barberSel.nextElementSibling || !barberSel.nextElementSibling.classList.contains('availability-msg')) {
                    const p = document.createElement('p');
                    p.className = 'availability-msg mt-2 text-xs text-red-400';
                    let text = c.barber + ' ya está ocupado a las ' + c.time + ' (cita #' + c.appointment_id + '). Cambia la hora o el barbero';
                    text += c.next_available ? ', o agenda a partir de las ' + c.next_available + '.' : '.';
                    p.appendChild(document.createTextNode(text));
                    if (c.next_available) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'ml-1 px-2 py-1 rounded-lg border border-gold/40 text-goldlight text-[11px] font-bold hover:bg-gold/10 transition';
                        btn.textContent = 'Usar ' + c.next_available;
                        btn.addEventListener('click', () => {
                            timeInput.value = c.next_available;
                            checkAvailability();
                        });
                        p.appendChild(btn);
                    }
                    barberSel.insertAdjacentElement('afterend', p);
                }
            }
        });
    });
    availabilityBannerText.textContent = 'Hay choques de horario: ' + texts.join('. ') + '. Cambia la hora o el barbero para continuar.';
    availabilityBanner.classList.remove('hidden');
}

function checkAvailability() {
    const services = collectAvailabilityServices();
    const date = dateInput.value;
    const time = timeInput.value;
    if (date === '' || time === '' || services.length === 0) {
        clearAvailabilityMarks();
        availabilityBanner.classList.add('hidden');
        conflictActive = false;
        return;
    }
    fetch(availabilityUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ _csrf: csrfToken, date, time, services, exclude_id: excludeId }),
    })
    .then(r => r.json())
    .then(data => renderAvailability(data.conflicts || []))
    .catch(() => { /* el servidor valida al guardar */ });
}

function scheduleAvailabilityCheck() {
    clearTimeout(availabilityTimer);
    availabilityTimer = setTimeout(checkAvailability, 400);
}

if (dateInput) dateInput.addEventListener('change', scheduleAvailabilityCheck);
if (timeInput) timeInput.addEventListener('change', scheduleAvailabilityCheck);
document.addEventListener('change', e => {
    if (e.target.classList.contains('service-select') || e.target.matches('[name="barber_id[]"]')) scheduleAvailabilityCheck();
});

const servicesContainer = document.getElementById('services-container');
if (servicesContainer) {
    new MutationObserver(scheduleAvailabilityCheck).observe(servicesContainer, { childList: true });
}

function clearFieldErrors() {
    document.querySelectorAll('.field-error').forEach(el => el.remove());
    document.querySelectorAll('.field-invalid').forEach(el => el.classList.remove('field-invalid', 'border-red-500', 'focus:border-red-500', 'focus:ring-red-500/20'));
}

appointmentForm.addEventListener('submit', function (e) {
    clearFieldErrors();

    if (conflictActive) {
        e.preventDefault();
        scheduleAvailabilityCheck();
        availabilityBanner.scrollIntoView({ block: 'center', behavior: 'smooth' });
        return;
    }

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

    if (dateInput.value !== '' && isClosedDay(dateInput.value)) {
        showError(dateInput, 'El negocio está cerrado los ' + weekdayNames[dayIndexOf(dateInput.value)] + '. Selecciona otro día.');
    } else if (timeInput.value !== '') {
        const r = rangeFor(dateInput.value);
        if (r.open !== '' && r.close !== '') {
            if (timeInput.value < r.open || timeInput.value >= r.close) {
                showError(timeInput, 'La hora de cita debe estar dentro del horario de atención (' + r.open + ' a ' + r.close + ').');
            }
        }
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
