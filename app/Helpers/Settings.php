<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;

/**
 * Settings
 *
 * Acceso a la configuración del sitio (tabla settings) con cache por request.
 * Fuente única para el footer/navbar público y el panel de administración.
 */
class Settings
{
    private static ?array $cache = null;

    /**
     * Todos los settings como mapa setting_key => setting_value.
     */
    public static function all(): array
    {
        if (self::$cache === null) {
            $settings = [];
            foreach (Setting::all() as $setting) {
                $settings[$setting->getAttribute('setting_key')] = $setting->getAttribute('setting_value');
            }
            self::$cache = $settings;
        }

        return self::$cache;
    }

    /**
     * Valor de un setting por clave (con default si no existe).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    /**
     * Valor booleano de un setting (1/true => true).
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default ? '1' : '0');
        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Horario de atención decodificado (JSON) con estructura por día.
     */
    public static function businessHours(): array
    {
        $default = [];
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $default[$day] = ['open' => '', 'close' => ''];
        }

        $raw = self::get('business_hours', '');
        if ($raw === '') {
            return $default;
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            return $default;
        }

        return array_replace($default, $decoded);
    }

    /**
     * Guarda (o crea) un setting e invalida la cache.
     */
    public static function set(string $key, string $value): void
    {
        $row = Setting::whereFirst(['setting_key' => $key]);

        if ($row !== null) {
            Setting::updateWhere(['id' => (int) $row->getAttribute('id')], ['setting_value' => $value]);
        } else {
            Setting::create([
                'setting_key'   => $key,
                'setting_value' => $value,
            ]);
        }

        self::$cache = null;
    }
}
