<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * ProductCategory
 *
 * Modelo de la tabla product_categories (categorías de productos/inventario).
 */
class ProductCategory extends Model
{
    protected string $table = 'product_categories';

    protected array $fillable = [
        'name',
        'description',
        'is_active',
    ];
}
