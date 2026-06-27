<?php

namespace App\Controllers\Client;

use App\Core\Request;
use App\Core\View;
use App\Helpers\CategoryHelper;
use App\Helpers\TourHelper;
use App\Models\Category;
use App\Models\Tour;

class HomeController
{
    public function home(Request $request): void
    {
        $tourListSection2 = TourHelper::enrichTourList(Tour::find(
            ['deleted' => false, 'status' => 'active'],
            ['sort' => ['position' => 'desc'], 'limit' => 6]
        ));

        $setting = $request->settingWebsiteInfo;
        $categoryIdSection4 = $setting['categoryIdSection4'] ?? null;
        $categorySection4 = $categoryIdSection4
            ? Category::findOne(['id' => $categoryIdSection4, 'deleted' => false, 'status' => 'active'])
            : null;
        $child4 = $categoryIdSection4 ? CategoryHelper::getCategoryChild($categoryIdSection4) : [];
        $ids4 = array_filter(array_merge([$categoryIdSection4], array_column($child4, 'id')));
        $tourListSection4 = empty($ids4) ? [] : TourHelper::enrichTourList(Tour::find(
            ['category' => ['$in' => $ids4], 'deleted' => false, 'status' => 'active'],
            ['sort' => ['position' => 'desc'], 'limit' => 8]
        ));

        $categoryIdSection6 = $setting['categoryIdSection6'] ?? null;
        $categorySection6 = $categoryIdSection6
            ? Category::findOne(['id' => $categoryIdSection6, 'deleted' => false, 'status' => 'active'])
            : null;
        $child6 = $categoryIdSection6 ? CategoryHelper::getCategoryChild($categoryIdSection6) : [];
        $ids6 = array_filter(array_merge([$categoryIdSection6], array_column($child6, 'id')));
        $tourListSection6 = empty($ids6) ? [] : TourHelper::enrichTourList(Tour::find(
            ['category' => ['$in' => $ids6], 'deleted' => false, 'status' => 'active'],
            ['sort' => ['position' => 'desc'], 'limit' => 8]
        ));

        View::render('client/pages/home', [
            'pageTitle' => 'Trang chủ',
            'tourListSection2' => $tourListSection2,
            'tourListSection4' => $tourListSection4,
            'categorySection4' => $categorySection4,
            'tourListSection6' => $tourListSection6,
            'categorySection6' => $categorySection6,
        ]);
    }
}
