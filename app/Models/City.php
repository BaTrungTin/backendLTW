<?php

namespace App\Models;

class City extends BaseModel
{
    protected static string $table = 'cities';

    protected static function map(array $row): array
    {
        return ['id' => $row['id'], 'name' => $row['name']];
    }
}
