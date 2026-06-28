<?php

namespace App\Controllers\Client;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\CategoryHelper;
use App\Models\Category;
use App\Models\City;
use App\Models\Tour;

class TourController
{
    public function detail(Request $request): void
    {
        $tourDetail = Tour::findOne([
            'slug' => $request->params['slug'],
            'deleted' => false,
            'status' => 'active',
        ]);
        if (!$tourDetail) {
            Response::redirect('/');
            return;
        }

        if (!empty($tourDetail['category'])) {
            $category = Category::findOne(['id' => $tourDetail['category'], 'deleted' => false]);
            if ($category) {
                View::share('navActiveSlug', $category['slug']);
            }
        }

        $breadcrumb = [];
        if (!empty($tourDetail['category'])) {
            $breadcrumb = CategoryHelper::getCategoryParent($tourDetail['category']);
        }
        $breadcrumb[] = [
            'id' => $tourDetail['id'],
            'name' => $tourDetail['name'],
            'avatar' => $tourDetail['avatar'],
            'slug' => $tourDetail['slug'],
        ];

        if (!empty($tourDetail['departureDate'])) {
            $tourDetail['departureDateFormat'] = date('d/m/Y', strtotime($tourDetail['departureDate']));
        }

        $tourDetail['cityList'] = [];
        if (!empty($tourDetail['locations'])) {
            $cityList = [];
            foreach ($tourDetail['locations'] as $cityId) {
                $city = City::findOne(['id' => $cityId]);
                if ($city) {
                    $cityList[] = $city;
                }
            }
            usort($cityList, fn ($a, $b) => strcmp($a['name'], $b['name']));
            $tourDetail['cityList'] = $cityList;
        }

        View::render('client/pages/tour-detail', [
            'pageTitle' => $tourDetail['name'],
            'breadcrumb' => $breadcrumb,
            'tourDetail' => $tourDetail,
        ]);
    }
}
