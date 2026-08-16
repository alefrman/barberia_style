<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * InventoryMovement
 *
 * Modelo de la tabla inventory_movements (historial de movimientos de stock).
 */
class InventoryMovement extends Model
{
    protected string $table = 'inventory_movements';

    protected bool $timestamps = false;

    protected array $fillable = [
        'product_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'note',
        'created_by',
    ];
}
