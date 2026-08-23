<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Invoice
 *
 * Modelo de la tabla invoices (encabezado de venta / punto de venta).
 */
class Invoice extends Model
{
    protected string $table = 'invoices';

    protected array $fillable = [
        'invoice_number',
        'client_name',
        'client_phone',
        'payment_method',
        'subtotal',
        'total',
        'notes',
        'created_by',
    ];
}
