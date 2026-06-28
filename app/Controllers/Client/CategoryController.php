<?php

namespace App\Controllers\Client;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\CategoryHelper;
use App\Helpers\TourHelper;
use App\Models\Category;
use App\Models\City;
use App\Models\Tour;

class CategoryController
{
    // IDs of domestic Vietnamese cities (city IDs 1–13)
    private const DOMESTIC_CITY_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];
    // Airport cities for international departure (Hà Nội, TP.HCM, Đà Nẵng)
    private const AIRPORT_CITY_IDS = [1, 2, 3];

    private function getRootSlug(array $categoryDetail, array &$allCategories): string
    {
        if (empty($categoryDetail['parent'])) {
            return $categoryDetail['slug'];
        }
        foreach ($allCategories as $cat) {
            if ($cat['id'] == $categoryDetail['parent'] && empty($cat['parent'])) {
                return $cat['slug'];
            }
        }
        return $categoryDetail['slug'];
    }

    public function list(Request $request): void
    {
        $categoryDetail = Category::findOne([
            'slug' => $request->params['slug'],
            'deleted' => false,
            'status' => 'active',
        ]);
        if (!$categoryDetail) {
            Response::redirect('/');
            return;
        }

        $breadcrumb = [];
        if (!empty($categoryDetail['parent'])) {
            $breadcrumb = CategoryHelper::getCategoryParent($categoryDetail['parent']);
        }
        $breadcrumb[] = [
            'id' => $categoryDetail['id'],
            'name' => $categoryDetail['name'],
            'avatar' => $categoryDetail['avatar'],
            'slug' => $categoryDetail['slug'],
        ];

        $child = CategoryHelper::getCategoryChild($categoryDetail['id']);
        $ids = array_merge([$categoryDetail['id']], array_column($child, 'id'));
        $query = $request->query();
        $query['categoryIds'] = $ids;
        $tourList = TourHelper::enrichTourList(Tour::search($query));

        // Determine which city lists to show in filter
        $allCategories = Category::find(['deleted' => false, 'status' => 'active']);
        $rootSlug = $this->getRootSlug($categoryDetail, $allCategories);
        $allCities = City::find([]);

        if ($rootSlug === 'tour-nuoc-ngoai') {
            // International: departure only from 3 airport cities; destinations are international
            $cityListFrom = array_values(array_filter($allCities, fn($c) => in_array((int)$c['id'], self::AIRPORT_CITY_IDS)));
            $cityListTo   = array_values(array_filter($allCities, fn($c) => !in_array((int)$c['id'], self::DOMESTIC_CITY_IDS)));
        } else {
            // Domestic: both from and to are domestic cities
            $cityListFrom = array_values(array_filter($allCities, fn($c) => in_array((int)$c['id'], self::DOMESTIC_CITY_IDS)));
            $cityListTo   = $cityListFrom;
        }

        View::render('client/pages/tour-list', [
            'pageTitle'      => 'Danh sách tour',
            'categoryDetail' => $categoryDetail,
            'breadcrumb'     => $breadcrumb,
            'tourList'       => $tourList,
            'cityListFrom'   => $cityListFrom,
            'cityListTo'     => $cityListTo,
        ]);
    }
}
