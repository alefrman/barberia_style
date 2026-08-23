<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * InvoiceProduct
 *
 * Modelo de la tabla invoice_products (detalle de productos en una factura).
 */
class InvoiceProduct extends Model
{
    protected string $table = 'invoice_products';

    protected bool $timestamps = false;

    protected array $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'price',
    ];
}
