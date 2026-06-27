<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\View;

class UserController
{
    public function list(Request $request): void
    {
        View::render('admin/pages/user-list', ['pageTitle' => 'Quản lý người dùng']);
    }
}
