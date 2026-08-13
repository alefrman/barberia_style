<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Money
 *
 * Formato de dinero de la aplicación (USD).
 */
class Money
{
    /**
     * Formatea un monto como dólar estadounidense: $1,234.56
     */
    public static function format(float $amount): string
    {
        return '$' . number_format($amount, 2, '.', ',');
    }
}
