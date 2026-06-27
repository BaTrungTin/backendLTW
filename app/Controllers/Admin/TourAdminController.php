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
use App\Models\City;
use App\Models\Tour;

class TourAdminController
{
    private function enrichList(array $list): array
    {
        foreach ($list as &$item) {
            foreach (['createdBy', 'updatedBy', 'deletedBy'] as $field) {
                if (!empty($item[$field])) {
                    $acc = AccountAdmin::findOne(['id' => $item[$field]]);
                    $item[$field . 'FullName'] = $acc['fullName'] ?? '';
                }
            }
            $item['createdAtFormat'] = date('H:i - d/m/Y', strtotime($item['createdAt']));
            $item['updatedAtFormat'] = date('H:i - d/m/Y', strtotime($item['updatedAt']));
            if (!empty($item['deletedAt'])) {
                $item['deletedAtFormat'] = date('H:i - d/m/Y', strtotime($item['deletedAt']));
            }
        }
        return $list;
    }

    public function list(Request $request): void
    {
        $find = ['deleted' => false];
        if ($request->query('status')) {
            $find['status'] = $request->query('status');
        }
        $limit = 4;
        $page = max(1, (int) $request->query('page', 1));
        $skip = ($page - 1) * $limit;
        $total = Tour::count($find);
        $tourList = $this->enrichList(Tour::find($find, ['sort' => ['position' => 'desc'], 'limit' => $limit, 'skip' => $skip]));
        View::render('admin/pages/tour-list', [
            'pageTitle' => 'Quản lý tour',
            'tourList' => $tourList,
            'accountAdminList' => AccountAdmin::find([]),
            'pagination' => ['skip' => $skip, 'totalRecord' => $total, 'totalPage' => (int) ceil($total / $limit)],
        ]);
    }

    public function create(Request $request): void
    {
        View::render('admin/pages/tour-create', [
            'pageTitle' => 'Tạo tour',
            'categoryList' => CategoryHelper::buildCategoryTree(Category::find([]), ''),
            'cityList' => City::find([]),
        ]);
    }

    public function trash(Request $request): void
    {
        $tourList = $this->enrichList(Tour::find(['deleted' => true], ['sort' => ['deletedAt' => 'desc']]));
        View::render('admin/pages/tour-trash', ['pageTitle' => 'Thùng rác tour', 'tourList' => $tourList]);
    }

    public function createPost(Request $request): void
    {
        if (!in_array('tour-create', $request->permissions ?? [], true)) {
            Response::json(['code' => 'error', 'message' => 'Không có quyền!']);
            return;
        }
        $body = $_POST;
        $data = $this->parseTourBody($body);
        $slug = StrHelper::slugify($data['name']);
        if (Tour::findOne(['slug' => $slug])) {
            $slug .= '-' . time();
        }
        $data['slug'] = $slug;
        $data['createdBy'] = $request->account->id;
        $data['updatedBy'] = $request->account->id;
        if ($path = UploadHelper::fromRequest('avatar')) {
            $data['avatar'] = $path;
        }
        $images = UploadHelper::multiple('images');
        if ($images) {
            $data['images'] = $images;
        }
        Tour::insert($data);
        Response::json(['code' => 'success', 'message' => 'Tạo tour thành công!']);
    }

    public function edit(Request $request): void
    {
        $tourDetail = Tour::findOne(['id' => $request->params['id'], 'deleted' => false]);
        if (!$tourDetail) {
            Response::redirect('/' . $GLOBALS['pathAdmin'] . '/tour/list');
            return;
        }
        if (!empty($tourDetail['departureDate'])) {
            $tourDetail['departureDateFormat'] = date('Y-m-d', strtotime($tourDetail['departureDate']));
        }
        View::render('admin/pages/tour-edit', [
            'pageTitle' => 'Chỉnh sửa tour',
            'tourDetail' => $tourDetail,
            'categoryList' => CategoryHelper::buildCategoryTree(Category::find([]), ''),
            'cityList' => City::find([]),
        ]);
    }

    public function editPatch(Request $request): void
    {
        if (!in_array('tour-edit', $request->permissions ?? [], true)) {
            Response::json(['code' => 'error', 'message' => 'Không có quyền!']);
            return;
        }
        $body = $_POST;
        $data = $this->parseTourBody($body);
        if (!empty($data['name'])) {
            $slug = StrHelper::slugify($data['name']);
            $exist = Tour::findOne(['slug' => $slug]);
            if ($exist && $exist['id'] != $request->params['id']) {
                $slug .= '-' . time();
            }
            $data['slug'] = $slug;
        }
        $data['updatedBy'] = $request->account->id;
        if ($path = UploadHelper::fromRequest('avatar')) {
            $data['avatar'] = $path;
        }
        $images = UploadHelper::multiple('images');
        if ($images) {
            $data['images'] = $images;
        }
        Tour::updateOne(['id' => $request->params['id'], 'deleted' => false], $data);
        Response::json(['code' => 'success', 'message' => 'Cập nhật thành công!']);
    }

    public function deletePatch(Request $request): void
    {
        if (!in_array('tour-delete', $request->permissions ?? [], true)) {
            Response::json(['code' => 'error', 'message' => 'Không có quyền!']);
            return;
        }
        Tour::updateOne(['id' => $request->params['id']], [
            'deleted' => true,
            'deletedAt' => date('Y-m-d H:i:s'),
            'deletedBy' => $request->account->id,
        ]);
        Response::json(['code' => 'success', 'message' => 'Xoá tour thành công!']);
    }

    public function undoPatch(Request $request): void
    {
        Tour::updateOne(['id' => $request->params['id']], ['deleted' => false]);
        Response::json(['code' => 'success', 'message' => 'Đã khôi phục!']);
    }

    public function destroyDelete(Request $request): void
    {
        Tour::deleteOne(['id' => $request->params['id']]);
        Response::json(['code' => 'success', 'message' => 'Đã xoá vĩnh viễn!']);
    }

    public function changeMultiPatch(Request $request): void
    {
        $value = $request->input('value');
        $ids = $request->input('ids', []);
        match ($value) {
            'active', 'inactive' => Tour::updateByIds($ids, ['status' => $value]),
            'delete' => Tour::updateByIds($ids, ['deleted' => true, 'deletedAt' => date('Y-m-d H:i:s'), 'deletedBy' => $request->account->id]),
            'undo' => Tour::updateByIds($ids, ['deleted' => false]),
            'destroy' => Tour::deleteByIds($ids),
            default => null,
        };
        Response::json(['code' => 'success', 'message' => 'Thành công!']);
    }

    private function parseTourBody(array $body): array
    {
        return [
            'name' => $body['name'] ?? '',
            'category' => $body['category'] ?? null,
            'position' => (int) ($body['position'] ?? Tour::count([]) + 1),
            'status' => $body['status'] ?? 'active',
            'priceAdult' => (int) ($body['priceAdult'] ?? 0),
            'priceChildren' => (int) ($body['priceChildren'] ?? 0),
            'priceBaby' => (int) ($body['priceBaby'] ?? 0),
            'priceNewAdult' => (int) ($body['priceNewAdult'] ?? $body['priceAdult'] ?? 0),
            'priceNewChildren' => (int) ($body['priceNewChildren'] ?? $body['priceChildren'] ?? 0),
            'priceNewBaby' => (int) ($body['priceNewBaby'] ?? $body['priceBaby'] ?? 0),
            'stockAdult' => (int) ($body['stockAdult'] ?? 0),
            'stockChildren' => (int) ($body['stockChildren'] ?? 0),
            'stockBaby' => (int) ($body['stockBaby'] ?? 0),
            'locations' => !empty($body['locations']) ? json_decode($body['locations'], true) : [],
            'time' => $body['time'] ?? '',
            'vehicle' => $body['vehicle'] ?? '',
            'departureDate' => !empty($body['departureDate']) ? $body['departureDate'] : null,
            'information' => $body['information'] ?? '',
            'schedules' => !empty($body['schedules']) ? json_decode($body['schedules'], true) : [],
        ];
    }
}
