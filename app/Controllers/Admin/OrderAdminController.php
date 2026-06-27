<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\City;
use App\Models\Order;

class OrderAdminController
{
    public function list(Request $request): void
    {
        $find = ['deleted' => false];

        // Filters
        if ($request->query('status')) {
            $find['status'] = $request->query('status');
        }
        if ($request->query('paymentMethod')) {
            $find['paymentMethod'] = $request->query('paymentMethod');
        }
        if ($request->query('paymentStatus')) {
            $find['paymentStatus'] = $request->query('paymentStatus');
        }
        
        $createdAtFilters = [];
        if ($request->query('startDate')) {
            $createdAtFilters['$gte'] = $request->query('startDate') . ' 00:00:00';
        }
        if ($request->query('endDate')) {
            $createdAtFilters['$lte'] = $request->query('endDate') . ' 23:59:59';
        }
        if (!empty($createdAtFilters)) {
            $find['createdAt'] = $createdAtFilters;
        }

        // Search by keyword is not natively supported by BaseModel, we will skip it for now to avoid query errors, 
        // as the UI can be complex to implement without modifying the ORM.

        $orderList = Order::find($find, ['sort' => ['createdAt' => 'desc']]);
        $vars = $GLOBALS['variables'];
        foreach ($orderList as &$item) {
            $item['paymentMethodName'] = $this->label($vars['payment_method_list'], $item['paymentMethod']);
            $item['paymentStatusName'] = $this->label($vars['payment_status_list'], $item['paymentStatus']);
            $item['statusName'] = $this->label($vars['status_list'], $item['status']);
            $item['statusInfo'] = [
                'label' => $item['statusName'],
                'color' => match ($item['status']) {
                    'done' => 'green',
                    'cancel' => 'red',
                    default => 'yellow'
                }
            ];
            
            $item['createdAtTime'] = date('H:i', strtotime($item['createdAt']));

            $item['createdAtDate'] = date('d/m/Y', strtotime($item['createdAt']));
        }
        View::render('admin/pages/order-list', ['pageTitle' => 'Quản lý đơn hàng', 'orderList' => $orderList]);
    }

    public function edit(Request $request): void
    {
        $vars = $GLOBALS['variables'];
        $orderDetail = Order::findOne(['id' => $request->params['id'], 'deleted' => false]);
        
        if (!$orderDetail) {
            Response::redirect('/' . $GLOBALS['pathAdmin'] . '/order/list');
            return;
        }

        $orderDetail['createdAtFormat'] = date(
            'Y-m-d\TH:i',
            strtotime($orderDetail['createdAt'])
        );

        foreach ($orderDetail['items'] as &$item) {
            $city = City::findOne(['id' => $item['locationFrom'] ?? 0]);
            $item['cityName'] = $city['name'] ?? '';
        }

        

        View::render('admin/pages/order-edit', [
            'pageTitle' => 'Chi tiết đơn hàng',
            'orderDetail' => $orderDetail,
            'cityList' => City::find([]),
            'paymentMethodList' => $vars['payment_method_list'],
            'paymentStatusList' => $vars['payment_status_list'],
            'statusList' => $vars['status_list'],
        ]);
    }

    public function editPatch(Request $request): void
    {
        Order::updateOne(['id' => $request->params['id']], $request->body());
        Response::json(['code' => 'success', 'message' => 'Cập nhật thành công!']);
    }

    private function label(array $list, string $value): string
    {
        foreach ($list as $item) {
            if ($item['value'] === $value) {
                return $item['label'];
            }
        }
        return $value;
    }

    public function deletePatch(Request $request): void
    {
        Order::updateOne(
            ['id' => $request->params['id']],
            [
                'deleted'   => true,
                'deletedAt' => date('Y-m-d H:i:s'),
                'deletedBy' => $request->account->id
            ]
        );

        Response::json([
            'code' => 'success',
            'message' => 'Xóa đơn hàng thành công!'
        ]);
    }
}


