<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * User
 *
 * Modelo de la tabla users (usuarios del panel de administración).
 */
class User extends Model
{
    protected string $table = 'users';

    protected array $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'avatar',
        'is_active',
        'last_login',
    ];

    /**
     * Indica si el usuario pertenece a un rol (por nombre).
     */
    public function isRole(string $roleName): bool
    {
        $role = Role::find((int) $this->getAttribute('role_id'));
        return $role !== null
            && strcasecmp((string) $role->getAttribute('name'), $roleName) === 0;
    }

    /**
     * Nombre del rol del usuario.
     */
    public function roleName(): ?string
    {
        $role = Role::find((int) $this->getAttribute('role_id'));
        return $role?->getAttribute('name');
    }
}
