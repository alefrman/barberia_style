<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * AppointmentType
 *
 * Modelo de la tabla appointment_types (Ahora / Programada).
 */
class AppointmentType extends Model
{
    protected string $table = 'appointment_types';

    protected bool $timestamps = false;

    protected array $fillable = [
        'name',
        'description',
    ];
}
