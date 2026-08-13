<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Appointment
 *
 * Modelo de la tabla appointments (citas/turnos).
 */
class Appointment extends Model
{
    protected string $table = 'appointments';

    protected array $fillable = [
        'type_id',
        'status_id',
        'client_name',
        'client_phone',
        'client_email',
        'appointment_date',
        'appointment_time',
        'notes',
        'subtotal',
        'total',
        'created_by',
    ];
}
