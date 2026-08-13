<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * ServiceCategory
 *
 * Modelo de la tabla service_categories (categorías de servicios).
 */
class ServiceCategory extends Model
{
    protected string $table = 'service_categories';

    protected array $fillable = [
        'name',
        'description',
        'is_active',
    ];
}
