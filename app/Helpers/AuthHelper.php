<?php

namespace App\Helpers;

use App\Models\AccountAdmin;

class AuthHelper
{
    public static function login(array $account, bool $remember): void
    {
        $_SESSION['admin_id'] = $account['id'];
        $_SESSION['admin_email'] = $account['email'];
        if ($remember) {
            setcookie('admin_remember', (string) $account['id'], [
                'expires' => time() + 604800,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        }
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_id'], $_SESSION['admin_email']);
        setcookie('admin_remember', '', ['expires' => time() - 3600, 'path' => '/']);
        setcookie('token', '', ['expires' => time() - 3600, 'path' => '/']);
    }

    public static function accountFromSession(): ?array
    {
        if (!empty($_SESSION['admin_id'])) {
            return AccountAdmin::findOne([
                'id' => $_SESSION['admin_id'],
                'email' => $_SESSION['admin_email'] ?? '',
            ]);
        }
        if (!empty($_COOKIE['admin_remember'])) {
            return AccountAdmin::findOne(['id' => $_COOKIE['admin_remember']]);
        }
        return null;
    }
}
