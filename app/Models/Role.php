<?php

namespace App\Models;

class Role extends BaseModel
{
    protected static string $table = 'roles';
    protected static array $jsonFields = ['permissions'];

    protected static function map(array $row): array
    {
        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'permissions' => is_string($row['permissions'] ?? null) ? json_decode($row['permissions'], true) : ($row['permissions'] ?? []),
            'createdBy' => $row['created_by'],
            'updatedBy' => $row['updated_by'],
            'deleted' => (bool) $row['deleted'],
            'deletedAt' => $row['deleted_at'],
            'deletedBy' => $row['deleted_by'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }
}
