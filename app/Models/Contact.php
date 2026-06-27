<?php

namespace App\Models;

class Contact extends BaseModel
{
    protected static string $table = 'contacts';

    protected static function map(array $row): array
    {
        return [
            'id' => $row['id'],
            'email' => $row['email'],
            'deleted' => (bool) $row['deleted'],
            'deletedBy' => $row['deleted_by'],
            'deletedAt' => $row['deleted_at'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }
}
