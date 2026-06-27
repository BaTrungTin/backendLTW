<?php

namespace App\Controllers\Client;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\TourHelper;
use App\Models\Tour;

class SearchController
{
    public function list(Request $request): void
    {
        try {
            $tourList = TourHelper::enrichTourList(Tour::search($request->query()));
            View::render('client/pages/search', [
                'pageTitle' => 'Kết quả tìm kiếm',
                'tourList' => $tourList,
            ]);
        } catch (\Throwable) {
            Response::redirect('/');
        }
    }
}
