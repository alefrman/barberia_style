<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Models\Team;

/**
 * TeamController
 *
 * Módulo de Barberos: equipo del negocio con foto y descripción.
 */
class TeamController extends Controller
{
    /**
     * Listado de barberos.
     */
    public function index(Request $request, array $params): Response
    {
        return $this->view('admin/team/index', [
            'title'  => 'Barberos',
            'user'   => Auth::user(),
            'active' => 'team',
            'rows'   => Database::fetchAll(
                "SELECT * FROM team ORDER BY sort_order ASC, name ASC"
            ),
        ], 'admin');
    }

    /**
     * Formulario de creación.
     */
    public function create(Request $request, array $params): Response
    {
        return $this->formView(null, 'Nuevo barbero');
    }

    /**
     * Guarda un barbero nuevo.
     */
    public function store(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/team');
        }

        $data = $this->extractTeam($request);
        $errors = $this->validateTeam($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/team/create');
        }

        $image = $this->handleImageUpload($request, null);
        if (is_string($image) && $image === 'UPLOAD_ERROR') {
            Session::flash('error', $this->uploadError ?? 'Error al subir la imagen.');
            return $this->redirect('/admin.php/team/create');
        }

        Team::create([
            'name'        => $data['name'],
            'position'    => $data['position'],
            'description' => $data['description'],
            'image'       => $image ?: null,
            'is_active'   => $data['is_active'],
            'sort_order'  => $data['sort_order'],
        ]);

        Session::flash('success', 'Barbero creado correctamente.');
        return $this->redirect('/admin.php/team');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $team = Team::find($id);

        if ($team === null) {
            Session::flash('error', 'Barbero no encontrado.');
            return $this->redirect('/admin.php/team');
        }

        return $this->formView($team, 'Editar barbero');
    }

    /**
     * Actualiza un barbero.
     */
    public function update(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/team');
        }

        $id = (int) ($params['id'] ?? 0);
        $team = Team::find($id);

        if ($team === null) {
            Session::flash('error', 'Barbero no encontrado.');
            return $this->redirect('/admin.php/team');
        }

        $data = $this->extractTeam($request);
        $errors = $this->validateTeam($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/team/edit/' . $id);
        }

        $oldImage = (string) $team->getAttribute('image');
        $removeImage = (int) $request->input('remove_image', 0) === 1;
        $image = $this->handleImageUpload($request, $removeImage ? null : $oldImage);

        if (is_string($image) && $image === 'UPLOAD_ERROR') {
            Session::flash('error', $this->uploadError ?? 'Error al subir la imagen.');
            return $this->redirect('/admin.php/team/edit/' . $id);
        }

        if ($removeImage && $oldImage !== '') {
            $this->deleteImageFile($oldImage);
        }

        Team::updateWhere(['id' => $id], [
            'name'        => $data['name'],
            'position'    => $data['position'],
            'description' => $data['description'],
            'image'       => $image ?: ($removeImage ? null : $oldImage),
            'is_active'   => $data['is_active'],
            'sort_order'  => $data['sort_order'],
        ]);

        Session::flash('success', 'Barbero actualizado correctamente.');
        return $this->redirect('/admin.php/team');
    }

    /**
     * Activa/desactiva un barbero desde el listado.
     */
    public function toggle(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/team');
        }

        $id = (int) ($params['id'] ?? 0);
        $team = Team::find($id);

        if ($team === null) {
            Session::flash('error', 'Barbero no encontrado.');
            return $this->redirect('/admin.php/team');
        }

        $active = (int) $team->getAttribute('is_active') === 1 ? 0 : 1;
        Team::updateWhere(['id' => $id], ['is_active' => $active]);

        Session::flash('success', $active ? 'Barbero activado.' : 'Barbero desactivado.');
        return $this->redirect('/admin.php/team');
    }

    /**
     * Elimina un barbero.
     */
    public function destroy(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/team');
        }

        $id = (int) ($params['id'] ?? 0);
        $team = Team::find($id);

        if ($team === null) {
            Session::flash('error', 'Barbero no encontrado.');
            return $this->redirect('/admin.php/team');
        }

        $image = (string) $team->getAttribute('image');
        $team->delete();

        if ($image !== '') {
            $this->deleteImageFile($image);
        }

        Session::flash('success', 'Barbero eliminado correctamente.');
        return $this->redirect('/admin.php/team');
    }

    /* ============================================================
     * HELPERS
     * ========================================================== */

    private function formView(?Team $team, string $title): Response
    {
        return $this->view('admin/team/form', [
            'title'   => $title,
            'user'    => Auth::user(),
            'active'  => 'team',
            'editing' => $team,
        ], 'admin');
    }

    private function extractTeam(Request $request): array
    {
        return [
            'name'        => trim((string) $request->input('name', '')),
            'position'    => trim((string) $request->input('position', '')),
            'description' => trim((string) $request->input('description', '')),
            'is_active'   => $request->input('is_active') ? 1 : 0,
            'sort_order'  => (int) $request->input('sort_order', 0),
        ];
    }

    private function validateTeam(array $data): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'El nombre del barbero es obligatorio.';
        } elseif (mb_strlen($data['name']) > 100) {
            $errors[] = 'El nombre no puede superar 100 caracteres.';
        }

        if ($data['position'] === '') {
            $errors[] = 'El cargo del barbero es obligatorio.';
        } elseif (mb_strlen($data['position']) > 100) {
            $errors[] = 'El cargo no puede superar 100 caracteres.';
        }

        return $errors;
    }

    private ?string $uploadError = null;

    /**
     * Procesa la subida de una imagen.
     * Retorna la ruta relativa (team/archivo) o null si no hay archivo.
     * Retorna 'UPLOAD_ERROR' (y setea $this->uploadError) si falla.
     */
    private function handleImageUpload(Request $request, ?string $currentImage): ?string
    {
        if (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $currentImage;
        }

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->uploadError = 'Error al subir el archivo (código ' . $file['error'] . ').';
            return 'UPLOAD_ERROR';
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            $this->uploadError = 'La imagen no puede superar los 2 MB.';
            return 'UPLOAD_ERROR';
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            $this->uploadError = 'El archivo debe ser una imagen válida.';
            return 'UPLOAD_ERROR';
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            $this->uploadError = 'Formato no permitido (usa JPG, PNG, WEBP o GIF).';
            return 'UPLOAD_ERROR';
        }

        $dir = UPLOAD_PATH . 'team';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            $this->uploadError = 'No se pudo crear el directorio de imágenes.';
            return 'UPLOAD_ERROR';
        }

        $filename = 'team_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $destination = $dir . '/' . $filename;
        $moved = move_uploaded_file($file['tmp_name'], $destination);
        if (!$moved) {
            // Fallback para entornos donde move_uploaded_file no aplica
            $moved = @rename($file['tmp_name'], $destination);
        }

        if (!$moved) {
            $this->uploadError = 'No se pudo mover el archivo al directorio de imágenes.';
            return 'UPLOAD_ERROR';
        }

        if ($currentImage !== null && $currentImage !== '' && basename($currentImage) !== $filename) {
            $this->deleteImageFile($currentImage);
        }

        return 'team/' . $filename;
    }

    private function deleteImageFile(string $filename): void
    {
        $clean = str_replace(['\\', '..'], '', $filename);
        $path = UPLOAD_PATH . ltrim($clean, '/');
        if (is_file($path)) {
            @unlink($path);
        }
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
