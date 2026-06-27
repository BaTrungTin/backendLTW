<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\UploadHelper;
use App\Models\AccountAdmin;

class ProfileController
{
    public function edit(Request $request): void
    {
        View::render('admin/pages/profile-edit', ['pageTitle' => 'Hồ sơ cá nhân']);
    }

    public function editPatch(Request $request): void
    {
        $body = $_POST;
        $exist = AccountAdmin::findOne(['email' => $body['email']]);
        if ($exist && (int) $exist['id'] !== (int) $request->account->id) {
            Response::json(['code' => 'error', 'message' => 'Email đã tồn tại!']);
            return;
        }
        $data = [
            'fullName' => $body['fullName'],
            'email' => $body['email'],
            'phone' => $body['phone'] ?? '',
        ];
        if ($avatar = UploadHelper::fromRequest('avatar')) {
            $data['avatar'] = $avatar;
        }
        AccountAdmin::updateOne(['id' => $request->account->id], $data);
        Response::json(['code' => 'success', 'message' => 'Cập nhật thành công!']);
    }

    public function changePassword(Request $request): void
    {
        View::render('admin/pages/profile-change-password', ['pageTitle' => 'Đổi mật khẩu']);
    }

    public function changePasswordPatch(Request $request): void
    {
        AccountAdmin::updateOne(
            ['id' => $request->account->id],
            ['password' => password_hash($request->input('password'), PASSWORD_BCRYPT)]
        );
        Response::json(['code' => 'success', 'message' => 'Đổi mật khẩu thành công!']);
    }
}
