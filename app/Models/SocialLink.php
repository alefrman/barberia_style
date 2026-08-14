<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * SocialLink
 *
 * Modelo de la tabla social_links (redes sociales del sitio).
 */
class SocialLink extends Model
{
    protected string $table = 'social_links';

    protected array $fillable = [
        'platform',
        'url',
        'sort_order',
    ];

    public const PLATFORMS = [
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
        'tiktok'    => 'TikTok',
        'x'         => 'X (Twitter)',
        'youtube'   => 'YouTube',
        'whatsapp'  => 'WhatsApp',
    ];

    public const ICONS = [
        'instagram' => 'fa-instagram',
        'facebook'  => 'fa-facebook-f',
        'tiktok'    => 'fa-tiktok',
        'x'         => 'fa-x-twitter',
        'youtube'   => 'fa-youtube',
        'whatsapp'  => 'fa-whatsapp',
    ];

    public static function labelFor(string $platform): string
    {
        return self::PLATFORMS[$platform] ?? ucfirst($platform);
    }

    public static function iconFor(string $platform): string
    {
        return self::ICONS[$platform] ?? 'fa-link';
    }

    /**
     * Redes configuradas (con URL) ordenadas para mostrar en el sitio.
     */
    public static function active(): array
    {
        $links = static::all('sort_order', 'ASC');
        return array_values(array_filter(
            $links,
            fn($link) => trim((string) $link->getAttribute('url')) !== ''
        ));
    }
}
