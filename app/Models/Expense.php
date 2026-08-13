<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Expense
 *
 * Modelo de la tabla expenses (gastos/salidas de efectivo).
 */
class Expense extends Model
{
    protected string $table = 'expenses';

    protected array $fillable = [
        'category_id',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'notes',
        'created_by',
    ];
}
