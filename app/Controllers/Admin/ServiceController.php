<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Models\Service;
use App\Models\ServiceCategory;

/**
 * ServiceController
 *
 * Módulo de Servicios: catálogo con categorías, imagen y precios.
 */
class ServiceController extends Controller
{
    /**
     * Listado de servicios.
     */
    public function index(Request $request, array $params): Response
    {
        $rows = Database::fetchAll(
            "SELECT s.*, c.name AS category_name
             FROM services s
             LEFT JOIN service_categories c ON c.id = s.category_id
             ORDER BY s.sort_order ASC, s.name ASC"
        );

        return $this->view('admin/services/index', [
            'title'  => 'Servicios',
            'user'   => Auth::user(),
            'active' => 'services',
            'rows'   => $rows,
        ], 'admin');
    }

    /**
     * Formulario de creación.
     */
    public function create(Request $request, array $params): Response
    {
        return $this->formView(null, 'Nuevo servicio');
    }

    /**
     * Guarda un servicio nuevo.
     */
    public function store(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/services');
        }

        $data = $this->extractService($request);
        $errors = $this->validateService($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/services/create');
        }

        $image = $this->handleImageUpload($request, null);
        if (is_string($image) && $image === 'UPLOAD_ERROR') {
            Session::flash('error', $this->uploadError ?? 'Error al subir la imagen.');
            return $this->redirect('/admin.php/services/create');
        }

        Service::create([
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'description' => $data['description'],
            'price'       => $data['price'],
            'duration'    => $data['duration'],
            'image'       => $image ?: null,
            'is_active'   => $data['is_active'],
            'sort_order'  => $data['sort_order'],
        ]);

        Session::flash('success', 'Servicio creado correctamente.');
        return $this->redirect('/admin.php/services');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $service = Service::find($id);

        if ($service === null) {
            Session::flash('error', 'Servicio no encontrado.');
            return $this->redirect('/admin.php/services');
        }

        return $this->formView($service, 'Editar servicio');
    }

    /**
     * Actualiza un servicio.
     */
    public function update(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/services');
        }

        $id = (int) ($params['id'] ?? 0);
        $service = Service::find($id);

        if ($service === null) {
            Session::flash('error', 'Servicio no encontrado.');
            return $this->redirect('/admin.php/services');
        }

        $data = $this->extractService($request);
        $errors = $this->validateService($data);

        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/services/edit/' . $id);
        }

        $oldImage = (string) $service->getAttribute('image');
        $removeImage = (int) $request->input('remove_image', 0) === 1;
        $image = $this->handleImageUpload($request, $removeImage ? null : $oldImage);

        if (is_string($image) && $image === 'UPLOAD_ERROR') {
            Session::flash('error', $this->uploadError ?? 'Error al subir la imagen.');
            return $this->redirect('/admin.php/services/edit/' . $id);
        }

        if ($removeImage && $oldImage !== '') {
            $this->deleteImageFile($oldImage);
        }

        Service::updateWhere(['id' => $id], [
            'category_id' => $data['category_id'],
            'name'        => $data['name'],
            'description' => $data['description'],
            'price'       => $data['price'],
            'duration'    => $data['duration'],
            'image'       => $image ?: ($removeImage ? null : $oldImage),
            'is_active'   => $data['is_active'],
            'sort_order'  => $data['sort_order'],
        ]);

        Session::flash('success', 'Servicio actualizado correctamente.');
        return $this->redirect('/admin.php/services');
    }

    /**
     * Elimina un servicio.
     */
    public function destroy(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/services');
        }

        $id = (int) ($params['id'] ?? 0);
        $service = Service::find($id);

        if ($service === null) {
            Session::flash('error', 'Servicio no encontrado.');
            return $this->redirect('/admin.php/services');
        }

        $image = (string) $service->getAttribute('image');

        try {
            $service->delete();
        } catch (\PDOException $e) {
            Session::flash('error', 'No se puede eliminar: el servicio está asociado a una o más citas.');
            return $this->redirect('/admin.php/services');
        }

        if ($image !== '') {
            $this->deleteImageFile($image);
        }

        Session::flash('success', 'Servicio eliminado correctamente.');
        return $this->redirect('/admin.php/services');
    }

    /* ============================================================
     * HELPERS
     * ========================================================== */

    private function formView(?Service $service, string $title): Response
    {
        $categories = ServiceCategory::all('name', 'ASC');

        return $this->view('admin/services/form', [
            'title'      => $title,
            'user'       => Auth::user(),
            'active'     => 'services',
            'editing'    => $service,
            'categories' => $categories,
        ], 'admin');
    }

    private function extractService(Request $request): array
    {
        $categoryId = (int) $request->input('category_id', 0);
        $newCategory = trim((string) $request->input('new_category', ''));

        if ($newCategory !== '') {
            $existing = ServiceCategory::whereFirst(['name' => $newCategory]);
            if ($existing !== null) {
                $categoryId = (int) $existing->getAttribute('id');
            } else {
                $created = ServiceCategory::create(['name' => $newCategory]);
                $categoryId = (int) $created->getAttribute('id');
            }
        }

        return [
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'name'        => trim((string) $request->input('name', '')),
            'description' => trim((string) $request->input('description', '')),
            'price'       => round((float) $request->input('price', 0), 2),
            'duration'    => (int) $request->input('duration', 30),
            'is_active'   => $request->input('is_active') ? 1 : 0,
            'sort_order'  => (int) $request->input('sort_order', 0),
        ];
    }

    private function validateService(array $data): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'El nombre del servicio es obligatorio.';
        } elseif (mb_strlen($data['name']) > 100) {
            $errors[] = 'El nombre no puede superar 100 caracteres.';
        }

        if ($data['price'] < 0) {
            $errors[] = 'El precio no puede ser negativo.';
        }

        if ($data['category_id'] > 0 && ServiceCategory::find($data['category_id']) === null) {
            $errors[] = 'Selecciona una categoría válida.';
        }

        return $errors;
    }

    private ?string $uploadError = null;

    /**
     * Procesa la subida de una imagen.
     * Retorna el nombre del archivo relativo o null si no hay archivo.
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

        $dir = UPLOAD_PATH . 'services';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            $this->uploadError = 'No se pudo crear el directorio de imágenes.';
            return 'UPLOAD_ERROR';
        }

        $filename = 'svc_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

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

        return 'services/' . $filename;
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
