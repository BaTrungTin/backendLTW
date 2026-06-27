<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\AuthHelper;
use App\Models\Role;

class AdminAuthMiddleware
{
    public static function verify(Request $request): bool
    {
        $pathAdmin = $GLOBALS['pathAdmin'];
        $account = AuthHelper::accountFromSession();
        if (!$account) {
            Response::redirect('/' . $pathAdmin . '/account/login');
            return false;
        }
        if (!empty($account['role'])) {
            $role = Role::findOne(['id' => $account['role']]);
            if ($role) {
                $account['roleName'] = $role['name'];
                $request->permissions = $role['permissions'];
                View::share('permissions', $role['permissions']);
            }
        }
        $request->account = (object) $account;
        View::share('account', (object) $account);
        return true;
    }
}
