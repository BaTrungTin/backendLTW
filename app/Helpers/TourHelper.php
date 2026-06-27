<?php

namespace App\Helpers;

class TourHelper
{
    public static function enrichTourList(array $tourList): array
    {
        foreach ($tourList as &$item) {
            if (!empty($item['priceAdult'])) {
                $item['discount'] = (int) floor((($item['priceAdult'] - $item['priceNewAdult']) / $item['priceAdult']) * 100);
            }
            if (!empty($item['departureDate'])) {
                $item['departureDateFormat'] = date('d/m/Y', strtotime($item['departureDate']));
            }
        }
        return $tourList;
    }
}
