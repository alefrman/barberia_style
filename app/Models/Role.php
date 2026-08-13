<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Role
 *
 * Modelo de la tabla roles (catálogo de roles del sistema).
 */
class Role extends Model
{
    protected string $table = 'roles';

    protected array $fillable = [
        'name',
        'description',
        'is_active',
    ];
}
