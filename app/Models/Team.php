<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Team
 *
 * Modelo de la tabla team (barberos del equipo).
 */
class Team extends Model
{
    protected string $table = 'team';

    protected array $fillable = [
        'name',
        'position',
        'description',
        'image',
        'is_active',
        'sort_order',
    ];
}
