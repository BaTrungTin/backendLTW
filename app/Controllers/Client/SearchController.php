<?php

namespace App\Controllers\Client;

use App\Core\Request;
use App\Core\View;
use App\Helpers\TourHelper;
use App\Models\City;
use App\Models\Tour;

class SearchController
{
    public function list(Request $request): void
    {
        $query = $this->normalizeQuery($request->query());
        $tourList = TourHelper::enrichTourList(Tour::search($query));
        View::render('client/pages/search', [
            'pageTitle' => 'Kết quả tìm kiếm',
            'tourList' => $tourList,
        ]);
    }

    private function normalizeQuery(array $query): array
    {
        if (!empty($query['locationTo']) && !is_numeric($query['locationTo'])) {
            $city = City::findByName((string) $query['locationTo']);
            $query['locationTo'] = $city ? $city['id'] : -1;
        }

        return $query;
    }
}
