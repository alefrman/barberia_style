<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Setting
 *
 * Modelo de la tabla settings (configuración de la barbería).
 */
class Setting extends Model
{
    protected string $table = 'settings';

    protected array $fillable = [
        'setting_key',
        'setting_value',
        'description',
    ];
}
