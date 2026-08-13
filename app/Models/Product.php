<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Product
 *
 * Modelo de la tabla products (catálogo de productos en venta con stock).
 */
class Product extends Model
{
    protected string $table = 'products';

    protected array $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'cost',
        'stock',
        'min_stock',
        'image',
        'is_active',
        'sort_order',
    ];
}
