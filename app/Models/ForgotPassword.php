<?php

namespace App\Models;

class ForgotPassword extends BaseModel
{
    protected static string $table = 'forgot_password';

    protected static function map(array $row): array
    {
        return [
            'id' => $row['id'],
            'email' => $row['email'],
            'otp' => $row['otp'],
            'expireAt' => $row['expire_at'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }
}
