<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * ExpenseCategory
 *
 * Modelo de la tabla expense_categories (categorías de gastos).
 */
class ExpenseCategory extends Model
{
    protected string $table = 'expense_categories';

    protected array $fillable = [
        'name',
        'description',
        'is_active',
    ];
}
