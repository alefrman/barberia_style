<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * AppointmentStatus
 *
 * Modelo de la tabla appointment_statuses (Pendiente, Confirmada, Completada, ...).
 */
class AppointmentStatus extends Model
{
    protected string $table = 'appointment_statuses';

    protected bool $timestamps = false;

    protected array $fillable = [
        'name',
        'description',
        'is_active',
    ];
}
