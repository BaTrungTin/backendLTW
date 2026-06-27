<?php

namespace App\Controllers\Client;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\GenerateHelper;
use App\Models\City;
use App\Models\Order;
use App\Models\Tour;

class OrderController
{
    public function createPost(Request $request): void
    {
        try {
            $body = $request->body();
            $body['code'] = 'OD' . GenerateHelper::randomNumber(10);
            $body['subTotal'] = 0;

            foreach ($body['items'] as &$item) {
                $tourInfo = Tour::findOne([
                    'id' => $item['tourId'],
                    'deleted' => false,
                    'status' => 'active',
                ]);
                if ($tourInfo) {
                    $body['subTotal'] += $item['quantityAdult'] * $tourInfo['priceNewAdult']
                        + $item['quantityChildren'] * $tourInfo['priceNewChildren']
                        + $item['quantityBaby'] * $tourInfo['priceNewBaby'];
                    $item['priceNewAdult'] = $tourInfo['priceNewAdult'];
                    $item['priceNewChildren'] = $tourInfo['priceNewChildren'];
                    $item['priceNewBaby'] = $tourInfo['priceNewBaby'];
                    $item['departureDate'] = $tourInfo['departureDate'];
                    $item['avatar'] = $tourInfo['avatar'];
                    $item['name'] = $tourInfo['name'];
                    $item['slug'] = $tourInfo['slug'];

                    Tour::updateOne(['id' => $item['tourId']], [
                        'stockAdult' => $tourInfo['stockAdult'] - $item['quantityAdult'],
                        'stockChildren' => $tourInfo['stockChildren'] - $item['quantityChildren'],
                        'stockBaby' => $tourInfo['stockBaby'] - $item['quantityBaby'],
                    ]);
                }
            }
            unset($item);

            $body['discount'] = 0;
            $body['total'] = $body['subTotal'] - $body['discount'];
            $body['paymentStatus'] = 'unpaid';
            $body['status'] = 'initial';

            Order::insert([
                'code' => $body['code'],
                'fullName' => $body['fullName'],
                'phone' => $body['phone'],
                'note' => $body['note'] ?? '',
                'items' => $body['items'],
                'subTotal' => $body['subTotal'],
                'discount' => $body['discount'],
                'total' => $body['total'],
                'paymentMethod' => $body['paymentMethod'],
                'paymentStatus' => $body['paymentStatus'],
                'status' => $body['status'],
            ]);

            Response::json([
                'code' => 'success',
                'message' => 'Thành công!',
                'orderCode' => $body['code'],
                'phone' => $body['phone'],
            ]);
        } catch (\Throwable) {
            Response::json(['code' => 'error', 'message' => 'Dữ liệu không hợp lệ!']);
        }
    }

    public function success(Request $request): void
    {
        $orderCode = $request->query('orderCode');
        $phone = $request->query('phone');
        if (!$orderCode || !$phone) {
            Response::redirect('/');
            return;
        }
        $orderDetail = Order::findOne(['code' => $orderCode, 'phone' => $phone, 'deleted' => false]);
        if (!$orderDetail) {
            Response::redirect('/');
            return;
        }

        $vars = $GLOBALS['variables'];
        $orderDetail['paymentMethodName'] = $this->label($vars['payment_method_list'], $orderDetail['paymentMethod']);
        $orderDetail['paymentStatusName'] = $this->label($vars['payment_status_list'], $orderDetail['paymentStatus']);
        $orderDetail['statusName'] = $this->label($vars['status_list'], $orderDetail['status']);
        $orderDetail['createdAtFormat'] = date('H:i - d/m/Y', strtotime($orderDetail['createdAt']));

        foreach ($orderDetail['items'] as &$item) {
            $item['departureDateFormat'] = date('d/m/Y', strtotime($item['departureDate']));
            $city = City::findOne(['id' => $item['locationFrom']]);
            $item['cityName'] = $city['name'] ?? '';
        }

        View::render('client/pages/order-success', [
            'pageTitle' => 'Đặt hàng thành công',
            'orderDetail' => $orderDetail,
        ]);
    }


    public function paymentVNPay(Request $request): void
    {
        $orderCode = $request->query('orderCode');
        $phone = $request->query('phone');
        $orderDetail = Order::findOne(['code' => $orderCode, 'phone' => $phone, 'deleted' => false]);
        if (!$orderDetail) {
            Response::redirect('/');
            return;
        }
        $cfg = $GLOBALS['config'];
        if (empty($cfg['vnpay_tmncode']) || empty($cfg['vnpay_secret']) || empty($cfg['vnpay_url'])) {
            Response::redirect('/');
            return;
        }

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $vnp = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $cfg['vnpay_tmncode'],
            'vnp_Locale' => 'vn',
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => "{$orderCode}-{$phone}-" . time(),
            'vnp_OrderInfo' => 'Thanh toan don hang',
            'vnp_OrderType' => 'other',
            'vnp_Amount' => (int) ($orderDetail['total'] * 100),
            'vnp_ReturnUrl' => $cfg['website_domain'] . '/order/payment-vnpay-result',
            'vnp_IpAddr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        ];
        ksort($vnp);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($vnp as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashdata .= urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
            $query .= urlencode((string)$key) . "=" . urlencode((string)$value) . '&';
        }

        $vnp_Url = $cfg['vnpay_url'] . "?" . $query;
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $cfg['vnpay_secret']);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        Response::redirect($vnp_Url);
    }

    public function paymentVNPayResult(Request $request): void
    {
        $cfg = $GLOBALS['config'];
        $vnp = $request->query();
        $secureHash = $vnp['vnp_SecureHash'] ?? '';
        unset($vnp['vnp_SecureHash'], $vnp['vnp_SecureHashType']);
        ksort($vnp);
        $i = 0;
        $hashData = "";
        foreach ($vnp as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode((string)$key) . "=" . urlencode((string)$value);
            } else {
                $hashData .= urlencode((string)$key) . "=" . urlencode((string)$value);
                $i = 1;
            }
        }
        $signed = hash_hmac('sha512', $hashData, $cfg['vnpay_secret']);
        if ($secureHash === $signed && ($vnp['vnp_ResponseCode'] ?? '') === '00')
        {
            [$orderCode, $phone] = explode('-', $vnp['vnp_TxnRef'] ?? '-', 3);

            Order::updateOne(
                ['code' => $orderCode, 'phone' => $phone],
                [
                    'paymentStatus' => 'paid',
                    'status' => 'done'
                ]
            );
            Response::redirect(
                $cfg['website_domain'] .
                "/order/success?orderCode={$orderCode}&phone={$phone}"
            );
            return;
        }

        Response::redirect('/');
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
