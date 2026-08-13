<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Service
 *
 * Modelo de la tabla services (catálogo de servicios de barbería).
 */
class Service extends Model
{
    protected string $table = 'services';

    protected array $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'duration',
        'image',
        'is_active',
        'sort_order',
    ];
}
