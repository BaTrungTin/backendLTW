<?php

namespace App\Models;

class AccountAdmin extends BaseModel
{
    protected static string $table = 'accounts_admin';

    protected static function map(array $row): array
    {
        return [
            'id' => $row['id'],
            'fullName' => $row['full_name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'role' => $row['role'],
            'positionCompany' => $row['position_company'],
            'status' => $row['status'],
            'password' => $row['password'],
            'avatar' => $row['avatar'],
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
