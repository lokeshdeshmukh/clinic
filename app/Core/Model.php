<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findActiveById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL LIMIT 1";
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $statement = $this->db->prepare($sql);
        $statement->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function updateById(int $id, array $data): bool
    {
        $assignments = [];
        foreach (array_keys($data) as $column) {
            $assignments[] = $column . ' = :' . $column;
        }

        $data['id'] = $id;
        $sql = sprintf('UPDATE %s SET %s WHERE id = :id', $this->table, implode(', ', $assignments));
        $statement = $this->db->prepare($sql);

        return $statement->execute($data);
    }

    public function softDelete(int $id): bool
    {
        return $this->updateById($id, ['deleted_at' => date('Y-m-d H:i:s')]);
    }
}
