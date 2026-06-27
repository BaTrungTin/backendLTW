<?php

namespace App\Controllers\Client;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\CategoryHelper;
use App\Helpers\TourHelper;
use App\Models\Category;
use App\Models\Tour;

class CategoryController
{
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
        $tourList = TourHelper::enrichTourList(Tour::find(
            ['category' => ['$in' => $ids], 'deleted' => false, 'status' => 'active'],
            ['sort' => ['position' => 'desc']]
        ));

        View::render('client/pages/tour-list', [
            'pageTitle' => 'Danh sách tour',
            'categoryDetail' => $categoryDetail,
            'breadcrumb' => $breadcrumb,
            'tourList' => $tourList,
        ]);
    }
}
