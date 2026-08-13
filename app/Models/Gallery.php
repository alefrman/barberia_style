<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Gallery
 *
 * Modelo de la tabla gallery (portafolio de cortes realizados).
 */
class Gallery extends Model
{
    protected string $table = 'gallery';

    protected array $fillable = [
        'title',
        'image',
        'description',
        'is_active',
        'sort_order',
    ];
}
