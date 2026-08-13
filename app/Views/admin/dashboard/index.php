<?php
/**
 * Dashboard — Panel de Administración
 */
use App\Core\View;

$totalServices = $totalServices ?? 0;
$totalProducts = $totalProducts ?? 0;
$totalExpenses = $totalExpenses ?? 0;
$todayAppointments = $todayAppointments ?? 0;
?>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

    <div class="reveal is-visible bg-darksoft rounded-2xl border border-white/5 p-6 flex items-center gap-4 hover:border-gold/30 transition">
        <span class="w-12 h-12 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-calendar-check text-gold"></i>
        </span>
        <div>
            <p class="font-display text-2xl font-semibold text-white"><?= $todayAppointments ?></p>
            <p class="text-[11px] uppercase tracking-widest text-cream/50">Citas de hoy</p>
        </div>
    </div>

    <div class="reveal is-visible bg-darksoft rounded-2xl border border-white/5 p-6 flex items-center gap-4 hover:border-gold/30 transition" style="--delay:80ms">
        <span class="w-12 h-12 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-scissors text-gold"></i>
        </span>
        <div>
            <p class="font-display text-2xl font-semibold text-white"><?= $totalServices ?></p>
            <p class="text-[11px] uppercase tracking-widest text-cream/50">Servicios</p>
        </div>
    </div>

    <div class="reveal is-visible bg-darksoft rounded-2xl border border-white/5 p-6 flex items-center gap-4 hover:border-gold/30 transition" style="--delay:160ms">
        <span class="w-12 h-12 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-boxes-stacked text-gold"></i>
        </span>
        <div>
            <p class="font-display text-2xl font-semibold text-white"><?= $totalProducts ?></p>
            <p class="text-[11px] uppercase tracking-widest text-cream/50">Productos</p>
        </div>
    </div>

    <div class="reveal is-visible bg-darksoft rounded-2xl border border-white/5 p-6 flex items-center gap-4 hover:border-gold/30 transition" style="--delay:240ms">
        <span class="w-12 h-12 rounded-xl bg-gold/10 border border-gold/30 flex items-center justify-center">
            <i class="fa-solid fa-money-bill-transfer text-gold"></i>
        </span>
        <div>
            <p class="font-display text-2xl font-semibold text-white"><?= $totalExpenses ?></p>
            <p class="text-[11px] uppercase tracking-widest text-cream/50">Gastos</p>
        </div>
    </div>
</div>

<div class="mt-8 bg-darksoft rounded-2xl border border-white/5 p-8">
    <div class="text-center">
        <span class="inline-flex w-14 h-14 items-center justify-center rounded-full border border-gold/30 bg-gold/5">
            <i class="fa-solid fa-user-gear text-gold text-xl"></i>
        </span>
        <h2 class="mt-4 font-display text-2xl font-semibold text-goldlight">Bienvenido, <?= View::e($user->getAttribute('name')) ?></h2>
        <p class="mt-2 text-sm text-cream/60 max-w-lg mx-auto">
            El panel de administración está listo. Desde el menú lateral podrás gestionar
            citas, inventario, servicios, barberos y gastos.
        </p>
        <p class="mt-3 text-[11px] uppercase tracking-widest text-cream/40">
            Rol: <?= View::e($user->roleName() ?? 'Sin rol') ?>
        </p>
    </div>
</div>
