<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\CategoryHelper;
use App\Helpers\StrHelper;
use App\Helpers\UploadHelper;
use App\Models\AccountAdmin;
use App\Models\Category;

class CategoryAdminController
{
    private function paginate(Request $request, array $find): array
    {
        if ($request->query('status')) {
            $find['status'] = $request->query('status');
        }
        if ($request->query('createdBy')) {
            $find['createdBy'] = $request->query('createdBy');
        }
        $limit = 4;
        $page = max(1, (int) $request->query('page', 1));
        $skip = ($page - 1) * $limit;
        $keyword = $request->query('keyword');
        if ($keyword) {
            $list = Category::findBySlugLike(StrHelper::slugify($keyword), $find);
            $total = count($list);
            $list = array_slice($list, $skip, $limit);
        } else {
            $total = Category::count($find);
            $list = Category::find($find, ['sort' => ['position' => 'desc'], 'limit' => $limit, 'skip' => $skip]);
        }
        foreach ($list as &$item) {
            if (!empty($item['createdBy'])) {
                $acc = AccountAdmin::findOne(['id' => $item['createdBy']]);
                $item['createdByFullName'] = $acc['fullName'] ?? '';
            }
            $item['createdAtFormat'] = date('H:i - d/m/Y', strtotime($item['createdAt']));
            $item['updatedAtFormat'] = date('H:i - d/m/Y', strtotime($item['updatedAt']));
        }
        return [$list, ['skip' => $skip, 'totalRecord' => $total, 'totalPage' => (int) ceil($total / $limit)]];
    }

    public function list(Request $request): void
    {
        [$categoryList, $pagination] = $this->paginate($request, ['deleted' => false]);
        View::render('admin/pages/category-list', [
            'pageTitle' => 'Quản lý danh mục',
            'categoryList' => $categoryList,
            'accountAdminList' => AccountAdmin::find([]),
            'pagination' => $pagination,
        ]);
    }

    public function create(Request $request): void
    {
        $all = Category::find([]);
        View::render('admin/pages/category-create', [
            'pageTitle' => 'Tạo danh mục',
            'categoryList' => CategoryHelper::buildCategoryTree($all, ''),
        ]);
    }

    public function createPost(Request $request): void
    {
        $body = $request->body();
        if (isset($body['name'])) {
            $body['name'] = trim($body['name']);
        }
        $slug = StrHelper::slugify($body['name']);
        if (Category::findOne(['slug' => $slug])) {
            $slug .= '-' . time();
        }
        $body['slug'] = $slug;
        $body['position'] = (int) ($body['position'] ?? Category::count([]) + 1);
        $body['parent'] = !empty($body['parent']) ? (int) $body['parent'] : null;
        $body['createdBy'] = $request->account->id;
        $body['updatedBy'] = $request->account->id;
        if (isset($body['avatar']) && in_array($body['avatar'], ['null', 'undefined', ''], true)) {
            unset($body['avatar']);
        }
        if ($path = UploadHelper::fromRequest('avatar')) {
            $body['avatar'] = $path;
        }
        Category::insert($body);
        Response::json(['code' => 'success', 'message' => 'Tạo danh mục thành công!']);
    }

    public function edit(Request $request): void
    {
        $categoryDetail = Category::findOne(['id' => $request->params['id'], 'deleted' => false]);
        if (!$categoryDetail) {
            Response::redirect('/' . $GLOBALS['pathAdmin'] . '/category/list');
            return;
        }
        View::render('admin/pages/category-edit', [
            'pageTitle' => 'Chỉnh sửa danh mục',
            'categoryDetail' => $categoryDetail,
            'categoryList' => CategoryHelper::buildCategoryTree(Category::find([]), ''),
        ]);
    }

    public function editPatch(Request $request): void
    {
        $body = $request->body();
        if (!empty($body['name'])) {
            $body['name'] = trim($body['name']);
            $slug = StrHelper::slugify($body['name']);
            $exist = Category::findOne(['slug' => $slug]);
            if ($exist && $exist['id'] != $request->params['id']) {
                $slug .= '-' . time();
            }
            $body['slug'] = $slug;
        }
        if (array_key_exists('parent', $body)) {
            $body['parent'] = !empty($body['parent']) ? (int) $body['parent'] : null;
        }
        $body['updatedBy'] = $request->account->id;
        if (isset($body['avatar']) && in_array($body['avatar'], ['null', 'undefined', ''], true)) {
            unset($body['avatar']);
        }
        if ($path = UploadHelper::fromRequest('avatar')) {
            $body['avatar'] = $path;
        }
        Category::updateOne(['id' => $request->params['id']], $body);
        Response::json(['code' => 'success', 'message' => 'Cập nhật thành công!']);
    }

    public function deletePatch(Request $request): void
    {
        Category::deleteOne(['id' => $request->params['id']]);
        Response::json(['code' => 'success', 'message' => 'Xoá danh mục thành công!']);
    }

    public function changeMultiPatch(Request $request): void
    {
        $value = $request->input('value');
        $ids = $request->input('ids', []);
        match ($value) {
            'active', 'inactive' => Category::updateByIds($ids, ['status' => $value]),
            'delete' => Category::deleteByIds($ids),
            default => null,
        };
        Response::json(['code' => 'success', 'message' => 'Thành công!']);
    }
}
