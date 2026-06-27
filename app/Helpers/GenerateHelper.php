<?php

namespace App\Helpers;

class GenerateHelper
{
    public static function randomNumber(int $length): string
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= (string) random_int(0, 9);
        }
        return $result;
    }
}
