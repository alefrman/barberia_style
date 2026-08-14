<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\Settings;
use App\Models\SocialLink;

/**
 * SettingController
 *
 * Módulo de Configuración (solo Superadmin):
 * contenido del sitio, contacto, horarios y redes sociales (footer).
 */
class SettingController extends Controller
{
    /**
     * Pantalla de configuración del sitio.
     */
    public function index(Request $request, array $params): Response
    {
        $keys = [
            'site_name', 'site_tagline', 'site_description',
            'newsletter_title', 'newsletter_text', 'newsletter_enabled',
            'phone', 'whatsapp', 'email', 'address',
        ];

        $values = [];
        foreach ($keys as $key) {
            $values[$key] = (string) Settings::get($key, '');
        }

        return $this->view('admin/settings/index', [
            'title'     => 'Configuración',
            'user'      => Auth::user(),
            'active'    => 'settings',
            'values'    => $values,
            'hours'     => Settings::businessHours(),
            'days'      => [
                'monday'    => 'Lunes',
                'tuesday'   => 'Martes',
                'wednesday' => 'Miércoles',
                'thursday'  => 'Jueves',
                'friday'    => 'Viernes',
                'saturday'  => 'Sábado',
                'sunday'    => 'Domingo',
            ],
            'socials'   => SocialLink::all('sort_order', 'ASC'),
            'platforms' => SocialLink::PLATFORMS,
        ], 'admin');
    }

    /**
     * Guarda el contenido del sitio (marca, lema, descripción y boletín).
     */
    public function updateContent(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/settings');
        }

        $siteName = trim((string) $request->input('site_name', ''));
        $tagline  = trim((string) $request->input('site_tagline', ''));
        $desc     = trim((string) $request->input('site_description', ''));
        $newsTitle = trim((string) $request->input('newsletter_title', ''));
        $newsText  = trim((string) $request->input('newsletter_text', ''));
        $newsEnabled = $request->has('newsletter_enabled') ? '1' : '0';

        if ($siteName === '') {
            Session::flash('error', 'El nombre del sitio es obligatorio.');
            return $this->redirect('/admin.php/settings');
        }
        if (mb_strlen($siteName) > 100 || mb_strlen($tagline) > 100) {
            Session::flash('error', 'El nombre y el lema no pueden superar 100 caracteres.');
            return $this->redirect('/admin.php/settings');
        }

        Settings::set('site_name', $siteName);
        Settings::set('site_tagline', $tagline);
        Settings::set('site_description', $desc);
        Settings::set('newsletter_title', $newsTitle);
        Settings::set('newsletter_text', $newsText);
        Settings::set('newsletter_enabled', $newsEnabled);

        Session::flash('success', 'Contenido del sitio actualizado.');
        return $this->redirect('/admin.php/settings');
    }

    /**
     * Guarda los datos de contacto (WhatsApp, teléfono, correo y dirección).
     */
    public function updateContact(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/settings');
        }

        $phone    = trim((string) $request->input('phone', ''));
        $whatsapp = trim((string) $request->input('whatsapp', ''));
        $email    = trim((string) $request->input('email', ''));
        $address  = trim((string) $request->input('address', ''));

        if ($phone !== '' && !preg_match('/^\+503 \d{4}-\d{4}$/', $phone)) {
            Session::flash('error', 'El teléfono debe tener el formato +503 0000-0000.');
            return $this->redirect('/admin.php/settings');
        }

        if (!preg_match('/^\+503 \d{4}-\d{4}$/', $whatsapp)) {
            Session::flash('error', 'El número de WhatsApp debe tener el formato +503 0000-0000.');
            return $this->redirect('/admin.php/settings');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Ingresa un correo de contacto válido.');
            return $this->redirect('/admin.php/settings');
        }

        Settings::set('phone', $phone);
        Settings::set('whatsapp', $whatsapp);
        Settings::set('email', $email);
        Settings::set('address', $address);

        Session::flash('success', 'Datos de contacto actualizados.');
        return $this->redirect('/admin.php/settings');
    }

    /**
     * Guarda los horarios de atención (días abiertos/cerrados).
     */
    public function updateHours(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/settings');
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            if ($request->has('closed_' . $day)) {
                $hours[$day] = ['open' => '', 'close' => ''];
                continue;
            }

            $open  = trim((string) $request->input('open_' . $day, ''));
            $close = trim((string) $request->input('close_' . $day, ''));

            if ($open === '' && $close === '') {
                $hours[$day] = ['open' => '', 'close' => ''];
                continue;
            }

            if ($open === '' || $close === '') {
                Session::flash('error', 'Cada día atendido necesita hora de apertura y de cierre.');
                return $this->redirect('/admin.php/settings');
            }
            if (!preg_match('/^\d{2}:\d{2}$/', $open) || !preg_match('/^\d{2}:\d{2}$/', $close)) {
                Session::flash('error', 'Las horas deben tener el formato HH:MM.');
                return $this->redirect('/admin.php/settings');
            }
            if ($open >= $close) {
                Session::flash('error', 'La hora de cierre debe ser posterior a la de apertura.');
                return $this->redirect('/admin.php/settings');
            }

            $hours[$day] = ['open' => $open, 'close' => $close];
        }

        Settings::set('business_hours', json_encode($hours, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        Session::flash('success', 'Horarios de atención actualizados.');
        return $this->redirect('/admin.php/settings');
    }

    /**
     * Agrega (o actualiza) el link de una red social.
     */
    public function storeSocial(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/settings');
        }

        $platform = (string) $request->input('platform', '');
        $url = trim((string) $request->input('url', ''));

        if (!isset(SocialLink::PLATFORMS[$platform])) {
            Session::flash('error', 'Selecciona una red social válida.');
            return $this->redirect('/admin.php/settings');
        }
        if ($url === '') {
            Session::flash('error', 'El link de la red social es obligatorio.');
            return $this->redirect('/admin.php/settings');
        }
        if (!preg_match('#^https?://#i', $url)) {
            Session::flash('error', 'El link debe comenzar con http:// o https://');
            return $this->redirect('/admin.php/settings');
        }

        $existing = SocialLink::whereFirst(['platform' => $platform]);
        if ($existing !== null) {
            SocialLink::updateWhere(['id' => (int) $existing->getAttribute('id')], ['url' => $url]);
        } else {
            $nextOrder = (int) (Database::fetchValue(
                'SELECT COALESCE(MAX(sort_order), 0) + 1 FROM social_links'
            ) ?? 1);
            SocialLink::create([
                'platform'   => $platform,
                'url'        => $url,
                'sort_order' => $nextOrder,
            ]);
        }

        Session::flash('success', 'Red social agregada.');
        return $this->redirect('/admin.php/settings');
    }

    /**
     * Elimina una red social.
     */
    public function destroySocial(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/settings');
        }

        $id = (int) ($params['id'] ?? 0);
        $link = SocialLink::find($id);

        if ($link === null) {
            Session::flash('error', 'Red social no encontrada.');
            return $this->redirect('/admin.php/settings');
        }

        $link->delete();
        Session::flash('success', 'Red social eliminada.');
        return $this->redirect('/admin.php/settings');
    }

    private function validCsrf(Request $request): bool
    {
        $token = $request->input('_csrf');
        if (Session::verifyCsrf(is_string($token) ? $token : null)) {
            return true;
        }
        Session::flash('error', 'Token de seguridad inválido.');
        return false;
    }
}
