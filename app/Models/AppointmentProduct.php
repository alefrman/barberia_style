<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * AppointmentProduct
 *
 * Detalle de productos vendidos dentro de una cita (historiza precio).
 */
class AppointmentProduct extends Model
{
    protected string $table = 'appointment_products';

    protected bool $timestamps = false;

    protected array $fillable = [
        'appointment_id',
        'product_id',
        'quantity',
        'price',
    ];
}
