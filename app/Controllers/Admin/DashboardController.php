<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\AccountAdmin;
use App\Models\Order;

class DashboardController
{
    public function dashboard(Request $request): void
    {
        $orders = Order::find(['deleted' => false]);
        $totalRevenue = 0;
        foreach ($orders as $order) {
            if ($order['status'] !== 'cancel') {
                $totalRevenue += $order['total'];
            }
        }
        $overview = [
            'totalAdmin' => AccountAdmin::count(['deleted' => false]),
            'totalOrder' => count($orders),
            'totalRevenue' => $totalRevenue,
        ];
        View::render('admin/pages/dashboard', ['pageTitle' => 'Tổng quan', 'overview' => $overview]);
    }

    public function revenueChartPost(Request $request): void
    {
        $body = $request->body();
        $currentMonth = (int) $body['currentMonth'];
        $currentYear = (int) $body['currentYear'];
        $prevMonth = (int) $body['prevMonth'];
        $prevYear = (int) $body['prevYear'];
        $arrayDay = $body['arrayDay'];

        $current = Order::findByDateRange(
            ['deleted' => false],
            'createdAt',
            sprintf('%04d-%02d-01', $currentYear, $currentMonth),
            sprintf('%04d-%02d-01', $currentYear, $currentMonth + 1)
        );
        $prev = Order::findByDateRange(
            ['deleted' => false],
            'createdAt',
            sprintf('%04d-%02d-01', $prevYear, $prevMonth),
            sprintf('%04d-%02d-01', $prevYear, $prevMonth + 1)
        );

        $dataCurrentMonth = [];
        $dataPrevMonth = [];
        foreach ($arrayDay as $day) {
            $revC = 0;
            foreach ($current as $order) {
                if ($order['status'] !== 'cancel' && (int) date('j', strtotime($order['createdAt'])) == (int) $day) {
                    $revC += $order['total'];
                }
            }
            $dataCurrentMonth[] = $revC;
            $revP = 0;
            foreach ($prev as $order) {
                if ($order['status'] !== 'cancel' && (int) date('j', strtotime($order['createdAt'])) == (int) $day) {
                    $revP += $order['total'];
                }
            }
            $dataPrevMonth[] = $revP;
        }

        Response::json([
            'code' => 'success',
            'message' => 'Thành công!',
            'dataCurrentMonth' => $dataCurrentMonth,
            'dataPrevMonth' => $dataPrevMonth,
        ]);
    }
}
