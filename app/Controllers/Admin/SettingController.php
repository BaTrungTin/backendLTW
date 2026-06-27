<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\CategoryHelper;
use App\Helpers\UploadHelper;
use App\Models\AccountAdmin;
use App\Models\Category;
use App\Models\Role;
use App\Models\SettingWebsiteInfo;

class SettingController
{
    public function list(Request $request): void
    {
        View::render('admin/pages/setting-list', ['pageTitle' => 'Cài đặt chung']);
    }

    public function websiteInfo(Request $request): void
    {
        View::render('admin/pages/setting-website-info', [
            'pageTitle' => 'Thông tin website',
            'record' => SettingWebsiteInfo::findOne([]),
            'categoryList' => CategoryHelper::buildCategoryTree(Category::find([]), ''),
        ]);
    }

    public function websiteInfoPatch(Request $request): void
    {
        $body = $_POST;
        if ($logo = UploadHelper::fromRequest('logo')) {
            $body['logo'] = $logo;
        }
        if ($favicon = UploadHelper::fromRequest('favicon')) {
            $body['favicon'] = $favicon;
        }
        if (SettingWebsiteInfo::count([]) > 0) {
            SettingWebsiteInfo::updateOne([], $body);
        } else {
            SettingWebsiteInfo::insert($body);
        }
        Response::json(['code' => 'success', 'message' => 'Cập nhật thành công!']);
    }

    public function accountAdminList(Request $request): void
    {
        $list = AccountAdmin::find(['deleted' => false], ['sort' => ['createdAt' => 'desc']]);
        foreach ($list as &$item) {
            if (!empty($item['role'])) {
                $role = Role::findOne(['id' => $item['role']]);
                $item['roleName'] = $role['name'] ?? '';
            }
        }
        View::render('admin/pages/setting-account-admin-list', [
            'pageTitle' => 'Tài khoản quản trị',
            'accountAdminList' => $list,
        ]);
    }

    public function accountAdminCreate(Request $request): void
    {
        View::render('admin/pages/setting-account-admin-create', [
            'pageTitle' => 'Tạo tài khoản quản trị',
            'roleList' => Role::find(['deleted' => false], ['sort' => ['createdAt' => 'desc']]),
        ]);
    }

    public function accountAdminCreatePost(Request $request): void
    {
        $body = $_POST;
        if (AccountAdmin::findOne(['email' => $body['email']])) {
            Response::json(['code' => 'error', 'message' => 'Email đã tồn tại trong hệ thống!']);
            return;
        }
        AccountAdmin::insert([
            'fullName' => $body['fullName'],
            'email' => $body['email'],
            'phone' => $body['phone'] ?? '',
            'role' => $body['role'] ?? null,
            'status' => $body['status'] ?? 'active',
            'password' => password_hash($body['password'], PASSWORD_BCRYPT),
            'avatar' => UploadHelper::fromRequest('avatar') ?? '',
            'createdBy' => $request->account->id,
            'updatedBy' => $request->account->id,
        ]);
        Response::json(['code' => 'success', 'message' => 'Tạo tài khoản thành công!']);
    }

    public function accountAdminEdit(Request $request): void
    {
        $accountDetail = AccountAdmin::findOne(['id' => $request->params['id'], 'deleted' => false]);
        if (!$accountDetail) {
            Response::redirect('/' . $GLOBALS['pathAdmin'] . '/setting/account-admin/list');
            return;
        }
        View::render('admin/pages/setting-account-admin-edit', [
            'pageTitle' => 'Sửa tài khoản quản trị',
            'accountDetail' => $accountDetail,
            'roleList' => Role::find(['deleted' => false]),
        ]);
    }

    public function accountAdminEditPatch(Request $request): void
    {
        $body = $_POST;
        $data = [
            'fullName' => $body['fullName'],
            'email' => $body['email'],
            'phone' => $body['phone'] ?? '',
            'role' => $body['role'] ?? null,
            'status' => $body['status'] ?? 'active',
            'updatedBy' => $request->account->id,
        ];
        if (!empty($body['password'])) {
            $data['password'] = password_hash($body['password'], PASSWORD_BCRYPT);
        }
        if ($avatar = UploadHelper::fromRequest('avatar')) {
            $data['avatar'] = $avatar;
        }
        AccountAdmin::updateOne(['id' => $request->params['id']], $data);
        Response::json(['code' => 'success', 'message' => 'Cập nhật thành công!']);
    }

    public function roleList(Request $request): void
    {
        View::render('admin/pages/setting-role-list', [
            'pageTitle' => 'Nhóm quyền',
            'roleList' => Role::find(['deleted' => false], ['sort' => ['createdAt' => 'desc']]),
        ]);
    }

    public function roleCreate(Request $request): void
    {
        View::render('admin/pages/setting-role-create', [
            'pageTitle' => 'Tạo nhóm quyền',
            'permissionList' => $GLOBALS['variables']['permission_list'],
        ]);
    }

    public function roleCreatePost(Request $request): void
    {
        $body = $request->body();
        $body['permissions'] = $body['permissions'] ?? [];
        $body['createdBy'] = $request->account->id;
        $body['updatedBy'] = $request->account->id;
        Role::insert($body);
        Response::json(['code' => 'success', 'message' => 'Tạo nhóm quyền thành công!']);
    }

    public function roleEdit(Request $request): void
    {
        $roleDetail = Role::findOne(['id' => $request->params['id'], 'deleted' => false]);
        View::render('admin/pages/setting-role-edit', [
            'pageTitle' => 'Sửa nhóm quyền',
            'roleDetail' => $roleDetail,
            'permissionList' => $GLOBALS['variables']['permission_list'],
        ]);
    }

    public function roleEditPatch(Request $request): void
    {
        $body = $request->body();
        Role::updateOne(['id' => $request->params['id']], $body);
        Response::json(['code' => 'success', 'message' => 'Cập nhật thành công!']);
    }
}
