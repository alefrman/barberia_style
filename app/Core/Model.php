<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use InvalidArgumentException;

/**
 * Model
 *
 * Modelo base (Active Record ligero).
 * Provee operaciones CRUD genéricas contra la base de datos
 * usando obligatoriamente sentencias preparadas (PDO).
 *
 * Los modelos concretos definen:
 *   - protected string $table;
 *   - protected array $fillable;
 *
 * Uso:
 *   $user = User::find(5);
 *   $users = User::all();
 *   User::create(['name' => '...', ...]);
 */
abstract class Model
{
    /** Nombre de la tabla en la base de datos. */
    protected string $table;

    /** Columna llave primaria. */
    protected string $primaryKey = 'id';

    /** Columnas permitidas para asignación masiva (whitelist). */
    protected array $fillable = [];

    /** Define si la tabla usa created_at / updated_at automáticos. */
    protected bool $timestamps = true;

    /** Atributos del registro cargado. */
    protected array $attributes = [];

    /** Indicador de si el registro existe en BD. */
    protected bool $exists = false;

    /* ============================================================
     * CONSTRUCTOR Y ACCESO A DATOS
     * ========================================================== */

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Asigna atributos solo si están en $fillable.
     */
    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable, true)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, mixed $value): void
    {
        if (in_array($key, $this->fillable, true)) {
            $this->attributes[$key] = $value;
        }
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    /* ============================================================
     * CONSULTAS (RETORNAN COLECCIONES / INSTANCIAS)
     * ========================================================== */

    /**
     * Obtiene todos los registros de la tabla.
     */
    public static function all(?string $orderBy = null, string $direction = 'ASC'): array
    {
        $model = new static();
        $orderSql = $orderBy
            ? ' ORDER BY ' . preg_replace('/[^a-zA-Z0-9_,\s]/', '', $orderBy) . ' ' . $direction
            : '';

        $rows = Database::fetchAll("SELECT * FROM {$model->table}{$orderSql}");
        return $model->hydrate($rows);
    }

    /**
     * Busca un registro por su llave primaria.
     */
    public static function find(int $id): ?static
    {
        $model = new static();
        $row = Database::fetch(
            "SELECT * FROM {$model->table} WHERE {$model->primaryKey} = :id LIMIT 1",
            ['id' => $id]
        );

        return $row ? $model->newFromRow($row) : null;
    }

    /**
     * Busca registros que cumplan una condición.
     *
     * @param array $where ['columna' => valor, ...]
     */
    public static function where(array $where): array
    {
        [$sql, $params] = self::buildWhere($where);
        $model = new static();

        $rows = Database::fetchAll(
            "SELECT * FROM {$model->table} WHERE {$sql}",
            $params
        );

        return $model->hydrate($rows);
    }

    /**
     * Busca el primer registro que cumpla la condición o null.
     */
    public static function whereFirst(array $where): ?static
    {
        [$sql, $params] = self::buildWhere($where);
        $model = new static();

        $row = Database::fetch(
            "SELECT * FROM {$model->table} WHERE {$sql} LIMIT 1",
            $params
        );

        return $row ? $model->newFromRow($row) : null;
    }

    /**
     * Cuenta registros que cumplan la condición.
     */
    public static function count(array $where = []): int
    {
        $model = new static();

        if ($where === []) {
            return (int) Database::fetchValue("SELECT COUNT(*) FROM {$model->table}");
        }

        [$sql, $params] = self::buildWhere($where);
        return (int) Database::fetchValue(
            "SELECT COUNT(*) FROM {$model->table} WHERE {$sql}",
            $params
        );
    }

    /* ============================================================
     * ESCRITURA (INSERT / UPDATE / DELETE)
     * ========================================================== */

    /**
     * Crea un registro y retorna la instancia con su ID.
     */
    public static function create(array $attributes): static
    {
        $model = new static();
        $model->fill($attributes);

        if ($model->timestamps) {
            $model->attributes['created_at'] = date('Y-m-d H:i:s');
            $model->attributes['updated_at'] = date('Y-m-d H:i:s');
        }

        $data = $model->attributes;

        if ($data === []) {
            throw new InvalidArgumentException("No hay atributos fillable para insertar en {$model->table}.");
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $model->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        Database::execute($sql, $data);
        $model->attributes[$model->primaryKey] = Database::lastInsertId();
        $model->exists = true;

        return $model;
    }

    /**
     * Actualiza el registro cargado en memoria.
     */
    public function save(): bool
    {
        $data = $this->attributes;

        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $id = $data[$this->primaryKey] ?? null;
        unset($data[$this->primaryKey]);

        if ($id === null || $data === []) {
            return false;
        }

        $set = implode(', ', array_map(fn($col) => "{$col} = :{$col}", array_keys($data)));
        $data['__pk'] = $id;

        $affected = Database::execute(
            "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :__pk",
            $data
        );

        $this->exists = true;
        return true;
    }

    /**
     * Actualiza registros que cumplan una condición.
     */
    public static function updateWhere(array $where, array $attributes): int
    {
        if ($attributes === []) {
            return 0;
        }

        $model = new static();
        $attributes['updated_at'] = date('Y-m-d H:i:s');

        $set = implode(', ', array_map(fn($col) => "{$col} = :set_{$col}", array_keys($attributes)));
        [$whereSql, $params] = self::buildWhere($where);

        $bind = [];
        foreach ($attributes as $k => $v) {
            $bind["set_{$k}"] = $v;
        }
        foreach ($params as $k => $v) {
            $bind[$k] = $v;
        }

        return Database::execute(
            "UPDATE {$model->table} SET {$set} WHERE {$whereSql}",
            $bind
        );
    }

    /**
     * Elimina el registro cargado en memoria.
     */
    public function delete(): bool
    {
        $id = $this->attributes[$this->primaryKey] ?? null;
        if ($id === null) {
            return false;
        }

        Database::execute(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id",
            ['id' => $id]
        );

        $this->exists = false;
        return true;
    }

    /**
     * Elimina registros que cumplan una condición.
     */
    public static function deleteWhere(array $where): int
    {
        $model = new static();
        [$sql, $params] = self::buildWhere($where);

        return Database::execute("DELETE FROM {$model->table} WHERE {$sql}", $params);
    }

    /* ============================================================
     * HELPERS INTERNOS
     * ========================================================== */

    /**
     * Construye cláusula WHERE con placeholders :w_columna.
     */
    private static function buildWhere(array $where): array
    {
        if ($where === []) {
            return ['1 = 1', []];
        }

        $parts = [];
        $params = [];
        foreach ($where as $column => $value) {
            $key = 'w_' . str_replace('.', '_', $column);
            $parts[] = "{$column} = :{$key}";
            $params[$key] = $value;
        }

        return [implode(' AND ', $parts), $params];
    }

    /**
     * Convierte filas de BD en instancias del modelo.
     */
    private function hydrate(array $rows): array
    {
        return array_map(fn(array $row) => $this->newFromRow($row), $rows);
    }

    /**
     * Crea una instancia desde una fila de la BD.
     */
    private function newFromRow(array $row): static
    {
        $instance = new static();
        $instance->attributes = $row;
        $instance->exists = true;
        return $instance;
    }
}
