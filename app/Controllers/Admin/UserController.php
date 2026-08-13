<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Models\Role;
use App\Models\User;

/**
 * UserController
 *
 * Gestión de usuarios del panel (solo Superadmin).
 */
class UserController extends Controller
{
    /**
     * Lista de usuarios.
     */
    public function index(Request $request, array $params): Response
    {
        $users = User::all('created_at', 'DESC');

        // Asocia el nombre del rol a cada usuario
        $roles = [];
        foreach (Role::all() as $role) {
            $roles[(int) $role->getAttribute('id')] = $role->getAttribute('name');
        }

        return $this->view('admin/users/index', [
            'title'   => 'Usuarios',
            'user'    => Auth::user(),
            'active'  => 'users',
            'users'   => $users,
            'roles'   => $roles,
            'currentUserId' => Auth::id(),
        ], 'admin');
    }

    /**
     * Formulario de creación.
     */
    public function create(Request $request, array $params): Response
    {
        return $this->view('admin/users/form', [
            'title'  => 'Nuevo usuario',
            'user'   => Auth::user(),
            'active' => 'users',
            'roles'  => Role::all(),
            'editing' => null,
        ], 'admin');
    }

    /**
     * Guarda un usuario nuevo.
     */
    public function store(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/users');
        }

        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $roleId = (int) $request->input('role_id', 0);
        $password = (string) $request->input('password', '');
        $isActive = $request->input('is_active') ? 1 : 0;

        // Validaciones
        $errors = $this->validateUser($name, $email, $roleId, $password, true);
        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/users/create');
        }

        // Email único
        if (User::whereFirst(['email' => $email]) !== null) {
            Session::flash('error', 'Ya existe un usuario con ese email.');
            return $this->redirect('/admin.php/users/create');
        }

        User::create([
            'role_id'   => $roleId,
            'name'      => $name,
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_BCRYPT),
            'is_active' => $isActive,
        ]);

        Session::flash('success', 'Usuario creado correctamente.');
        return $this->redirect('/admin.php/users');
    }

    /**
     * Formulario de edición.
     */
    public function edit(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $user = User::find($id);

        if ($user === null) {
            Session::flash('error', 'Usuario no encontrado.');
            return $this->redirect('/admin.php/users');
        }

        return $this->view('admin/users/form', [
            'title'   => 'Editar usuario',
            'user'    => Auth::user(),
            'active'  => 'users',
            'roles'   => Role::all(),
            'editing' => $user,
        ], 'admin');
    }

    /**
     * Actualiza un usuario.
     */
    public function update(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/users');
        }

        $id = (int) ($params['id'] ?? 0);
        $user = User::find($id);

        if ($user === null) {
            Session::flash('error', 'Usuario no encontrado.');
            return $this->redirect('/admin.php/users');
        }

        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $roleId = (int) $request->input('role_id', 0);
        $password = (string) $request->input('password', '');
        $isActive = $request->input('is_active') ? 1 : 0;

        $errors = $this->validateUser($name, $email, $roleId, $password, $password !== '');
        if ($errors !== []) {
            Session::flash('error', $errors[0]);
            return $this->redirect('/admin.php/users/edit/' . $id);
        }

        // Email único (excepto el propio)
        $existing = User::whereFirst(['email' => $email]);
        if ($existing !== null && (int) $existing->getAttribute('id') !== $id) {
            Session::flash('error', 'Ya existe otro usuario con ese email.');
            return $this->redirect('/admin.php/users/edit/' . $id);
        }

        // Evitar auto-desactivarse o degradarse
        if ((int) $user->getAttribute('id') === Auth::id() && ($isActive !== 1 || $roleId !== (int) $user->getAttribute('role_id'))) {
            Session::flash('error', 'No puedes desactivar ni cambiar el rol de tu propia cuenta.');
            return $this->redirect('/admin.php/users/edit/' . $id);
        }

        $data = [
            'role_id'   => $roleId,
            'name'      => $name,
            'email'     => $email,
            'is_active' => $isActive,
        ];

        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        User::updateWhere(['id' => $id], $data);

        Session::flash('success', 'Usuario actualizado correctamente.');
        return $this->redirect('/admin.php/users');
    }

    /**
     * Elimina un usuario.
     */
    public function destroy(Request $request, array $params): Response
    {
        if (!$this->validCsrf($request)) {
            return $this->redirect('/admin.php/users');
        }

        $id = (int) ($params['id'] ?? 0);

        if ($id === Auth::id()) {
            Session::flash('error', 'No puedes eliminar tu propia cuenta.');
            return $this->redirect('/admin.php/users');
        }

        $user = User::find($id);
        if ($user === null) {
            Session::flash('error', 'Usuario no encontrado.');
            return $this->redirect('/admin.php/users');
        }

        $user->delete();
        Session::flash('success', 'Usuario eliminado correctamente.');
        return $this->redirect('/admin.php/users');
    }

    /**
     * Valida los campos del usuario.
     *
     * @return array Errores de validación (vacío si todo OK).
     */
    private function validateUser(string $name, string $email, int $roleId, string $password, bool $passwordRequired): array
    {
        $errors = [];

        if ($name === '') {
            $errors[] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($name) > 100) {
            $errors[] = 'El nombre no puede superar 100 caracteres.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingresa un email válido.';
        }

        if (Role::find($roleId) === null) {
            $errors[] = 'Selecciona un rol válido.';
        }

        if ($passwordRequired) {
            if (strlen($password) < 6) {
                $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
            }
        }

        return $errors;
    }

    /**
     * Verifica el token CSRF y muestra flash si falla.
     */
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
