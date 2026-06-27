<?php

namespace App\Controllers\Client;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\City;
use App\Models\Tour;

class CartController
{
    public function cart(Request $request): void
    {
        View::render('client/pages/cart', ['pageTitle' => 'Giỏ hàng']);
    }

    public function detailPost(Request $request): void
    {
        try {
            $cart = $request->body();
            $cartDetail = [];
            foreach ($cart as $item) {
                $tourInfo = Tour::findOne([
                    'id' => $item['tourId'],
                    'status' => 'active',
                    'deleted' => false,
                ]);
                if ($tourInfo) {
                    $cityInfo = City::findOne(['id' => $item['locationFrom']]);
                    $cartDetail[] = array_merge($item, [
                        'avatar' => $tourInfo['avatar'],
                        'name' => $tourInfo['name'],
                        'slug' => $tourInfo['slug'],
                        'departureDate' => date('d/m/Y', strtotime($tourInfo['departureDate'])),
                        'cityName' => $cityInfo['name'] ?? '',
                        'priceNewAdult' => $tourInfo['priceNewAdult'],
                        'priceNewChildren' => $tourInfo['priceNewChildren'],
                        'priceNewBaby' => $tourInfo['priceNewBaby'],
                        'stockAdult' => $tourInfo['stockAdult'],
                        'stockChildren' => $tourInfo['stockChildren'],
                        'stockBaby' => $tourInfo['stockBaby'],
                    ]);
                }
            }
            Response::json(['code' => 'success', 'message' => 'Thành công!', 'cart' => $cartDetail]);
        } catch (\Throwable) {
            Response::json(['code' => 'error', 'message' => 'Dữ liệu không hợp lệ!']);
        }
    }
}
