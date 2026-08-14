<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Text
 *
 * Helpers de transformación de texto para las vistas.
 */
class Text
{
    /**
     * Convierte texto plano simple a HTML seguro:
     * - escapa el HTML (anti XSS)
     * - *texto* => <em class="text-gold-grad italic">texto</em> (resaltado dorado)
     * - saltos de línea => <br>
     */
    public static function markup(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $gold = preg_replace('/\*([^*]+)\*/', '<em class="text-gold-grad italic">$1</em>', $escaped);
        if ($gold === null) {
            $gold = $escaped;
        }

        return nl2br($gold);
    }
}
