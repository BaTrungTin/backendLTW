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

    public function paymentZaloPay(Request $request): void
    {
        $orderCode = $request->query('orderCode');
        $phone = $request->query('phone');
        $orderDetail = Order::findOne(['code' => $orderCode, 'phone' => $phone, 'deleted' => false]);
        if (!$orderDetail) {
            Response::redirect('/');
            return;
        }

        $cfg = $GLOBALS['config'];
        $embed = json_encode(['redirecturl' => $cfg['website_domain'] . "/order/success?orderCode={$orderCode}&phone={$phone}"]);
        $transId = random_int(100000, 999999);
        $appTransId = date('ymd') . '_' . $transId;
        $order = [
            'app_id' => $cfg['zalopay_appid'],
            'app_trans_id' => $appTransId,
            'app_user' => "{$orderCode}-{$phone}",
            'app_time' => round(microtime(true) * 1000),
            'item' => '[{}]',
            'embed_data' => $embed,
            'amount' => (int) $orderDetail['total'],
            'description' => "Thanh toán đơn hàng {$orderCode}",
            'bank_code' => '',
            'callback_url' => $cfg['website_domain'] . '/order/payment-zalopay-result',
        ];
        $data = $order['app_id'] . '|' . $order['app_trans_id'] . '|' . $order['app_user'] . '|' . $order['amount'] . '|' . $order['app_time'] . '|' . $order['embed_data'] . '|' . $order['item'];
        $order['mac'] = hash_hmac('sha256', $data, $cfg['zalopay_key1']);

        $url = $cfg['zalopay_domain'] . '/v2/create?' . http_build_query($order);
        $response = json_decode(file_get_contents($url), true);
        if (($response['return_code'] ?? 0) == 1) {
            Response::redirect($response['order_url']);
        }
        Response::redirect('/');
    }

    public function paymentZaloPayResultPost(Request $request): void
    {
        $cfg = $GLOBALS['config'];
        $dataStr = $request->input('data');
        $reqMac = $request->input('mac');
        $mac = hash_hmac('sha256', $dataStr, $cfg['zalopay_key2']);
        $result = ['return_code' => -1, 'return_message' => 'mac not equal'];
        if ($reqMac === $mac) {
            $dataJson = json_decode($dataStr, true);
            [$orderCode, $phone] = explode('-', $dataJson['app_user'] ?? '-', 2);
            Order::updateOne(['code' => $orderCode, 'phone' => $phone], ['paymentStatus' => 'paid']);
            $result = ['return_code' => 1, 'return_message' => 'success'];
        }
        Response::json($result);
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
            'vnp_IpAddr' => '127.0.0.1',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        ];
        ksort($vnp);
        $hashData = http_build_query($vnp, '', '&', PHP_QUERY_RFC3986);
        $vnp['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $cfg['vnpay_secret']);

        Response::redirect(
            $cfg['vnpay_url'] . '?' . http_build_query($vnp)
        );;
    }

    public function paymentVNPayResult(Request $request): void
    {
        $cfg = $GLOBALS['config'];
        $vnp = $request->query();
        $secureHash = $vnp['vnp_SecureHash'] ?? '';
        unset($vnp['vnp_SecureHash'], $vnp['vnp_SecureHashType']);
        ksort($vnp);
        $signed = hash_hmac('sha512', http_build_query($vnp, '', '&', PHP_QUERY_RFC3986), $cfg['vnpay_secret']);
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
