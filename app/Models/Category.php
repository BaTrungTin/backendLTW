<?php

namespace App\Models;

class Category extends BaseModel
{
    protected static string $table = 'categories';

    protected static function map(array $row): array
    {
        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'parent' => $row['parent'] ?? '',
            'position' => (int) $row['position'],
            'status' => $row['status'],
            'avatar' => $row['avatar'],
            'description' => $row['description'],
            'slug' => $row['slug'],
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
