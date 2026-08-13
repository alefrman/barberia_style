<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Models\Gallery;

/**
 * GalleryController
 *
 * Módulo de Galería: portafolio de cortes con imagen, descripción y orden.
 */
class GalleryController extends Controller
{
    /**
     * Listado de fotos.
     */
    public function index(Request $request, array $params): Response
    {
        $rows = Database::fetchAll(
            "SELECT * FROM gallery
             ORDER BY sort_order ASC, title ASC"
        );

        return $this->view('admin/gallery/index', [
            'title'  => 'Galería',
            'user'   => Auth::user(),
            'active' => 'gallery',
            'rows'   => $rows,
        ], 'admin');
    }

    /**
     * Formulario de creación.
     */
    public function create(Request $request, array $params): Response
    {
        return $this->formView(null, 'Nueva foto');
    }

    /**
     * Guarda una foto nueva.
     */
    public function store(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/gallery');
        }

        $data = $this->extractGallery($request);
        $errors = $this->validateGallery($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/gallery/create');
        }

        $image = $this->handleImageUpload($request, null);
        if (is_string($image) && $image === 'UPLOAD_ERROR') {
            Session::flash('error', $this->uploadError ?? 'Error al subir la imagen.');
            return $this->redirect('/admin.php/gallery/create');
        }

        Gallery::create([
            'title'       => $data['title'],
            'description' => $data['description'],
            'image'       => $image ?: null,
            'is_active'   => $data['is_active'],
            'sort_order'  => $data['sort_order'],
        ]);

        Session::flash('success', 'Foto agregada correctamente.');
        return $this->redirect('/admin.php/gallery');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $gallery = Gallery::find($id);

        if ($gallery === null) {
            Session::flash('error', 'Foto no encontrada.');
            return $this->redirect('/admin.php/gallery');
        }

        return $this->formView($gallery, 'Editar foto');
    }

    /**
     * Actualiza una foto.
     */
    public function update(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/gallery');
        }

        $id = (int) ($params['id'] ?? 0);
        $gallery = Gallery::find($id);

        if ($gallery === null) {
            Session::flash('error', 'Foto no encontrada.');
            return $this->redirect('/admin.php/gallery');
        }

        $data = $this->extractGallery($request);
        $errors = $this->validateGallery($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/gallery/edit/' . $id);
        }

        $oldImage = (string) $gallery->getAttribute('image');
        $removeImage = (int) $request->input('remove_image', 0) === 1;
        $image = $this->handleImageUpload($request, $removeImage ? null : $oldImage);

        if (is_string($image) && $image === 'UPLOAD_ERROR') {
            Session::flash('error', $this->uploadError ?? 'Error al subir la imagen.');
            return $this->redirect('/admin.php/gallery/edit/' . $id);
        }

        if ($removeImage && $oldImage !== '') {
            $this->deleteImageFile($oldImage);
        }

        Gallery::updateWhere(['id' => $id], [
            'title'       => $data['title'],
            'description' => $data['description'],
            'image'       => $image ?: ($removeImage ? null : $oldImage),
            'is_active'   => $data['is_active'],
            'sort_order'  => $data['sort_order'],
        ]);

        Session::flash('success', 'Foto actualizada correctamente.');
        return $this->redirect('/admin.php/gallery');
    }

    /**
     * Elimina una foto.
     */
    public function destroy(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/gallery');
        }

        $id = (int) ($params['id'] ?? 0);
        $gallery = Gallery::find($id);

        if ($gallery === null) {
            Session::flash('error', 'Foto no encontrada.');
            return $this->redirect('/admin.php/gallery');
        }

        $image = (string) $gallery->getAttribute('image');

        try {
            $gallery->delete();
        } catch (\PDOException $e) {
            Session::flash('error', 'No se pudo eliminar la foto.');
            return $this->redirect('/admin.php/gallery');
        }

        if ($image !== '') {
            $this->deleteImageFile($image);
        }

        Session::flash('success', 'Foto eliminada correctamente.');
        return $this->redirect('/admin.php/gallery');
    }

    /* ============================================================
     * HELPERS
     * ========================================================== */

    private function formView(?Gallery $gallery, string $title): Response
    {
        return $this->view('admin/gallery/form', [
            'title'   => $title,
            'user'    => Auth::user(),
            'active'  => 'gallery',
            'editing' => $gallery,
        ], 'admin');
    }

    private function extractGallery(Request $request): array
    {
        return [
            'title'       => trim((string) $request->input('title', '')),
            'description' => trim((string) $request->input('description', '')),
            'is_active'   => $request->input('is_active') ? 1 : 0,
            'sort_order'  => (int) $request->input('sort_order', 0),
        ];
    }

    private function validateGallery(array $data): array
    {
        $errors = [];

        if ($data['title'] === '') {
            $errors[] = 'El título es obligatorio.';
        } elseif (mb_strlen($data['title']) > 100) {
            $errors[] = 'El título no puede superar 100 caracteres.';
        }

        if ($data['sort_order'] < 0) {
            $errors[] = 'El orden no puede ser negativo.';
        }

        return $errors;
    }

    private ?string $uploadError = null;

    /**
     * Procesa la subida de una imagen.
     * Retorna la ruta relativa (gallery/<archivo>) o null si no hay archivo.
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

        $dir = UPLOAD_PATH . 'gallery';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            $this->uploadError = 'No se pudo crear el directorio de imágenes.';
            return 'UPLOAD_ERROR';
        }

        $filename = 'gal_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

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

        return 'gallery/' . $filename;
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
