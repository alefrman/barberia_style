<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * AppointmentService
 *
 * Detalle de servicios dentro de una cita (historiza precio).
 */
class AppointmentService extends Model
{
    protected string $table = 'appointment_services';

    protected bool $timestamps = false;

    protected array $fillable = [
        'appointment_id',
        'service_id',
        'barber_id',
        'price',
    ];
}
