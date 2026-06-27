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
        $orderList = Order::find(['deleted' => false], ['sort' => ['createdAt' => 'desc']]);
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
            $item['createdAtFormat'] = date('H:i - d/m/Y', strtotime($item['createdAt']));
        }
        View::render('admin/pages/order-list', ['pageTitle' => 'Quản lý đơn hàng', 'orderList' => $orderList]);
    }

    public function edit(Request $request): void
    {
        $orderDetail = Order::findOne(['id' => $request->params['id'], 'deleted' => false]);
        if (!$orderDetail) {
            Response::redirect('/' . $GLOBALS['pathAdmin'] . '/order/list');
            return;
        }
        foreach ($orderDetail['items'] as &$item) {
            $city = City::findOne(['id' => $item['locationFrom'] ?? 0]);
            $item['cityName'] = $city['name'] ?? '';
        }
        View::render('admin/pages/order-edit', [
            'pageTitle' => 'Chi tiết đơn hàng',
            'orderDetail' => $orderDetail,
            'cityList' => City::find([]),
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
}
