<?php

namespace App\Models;

class SettingWebsiteInfo extends BaseModel
{
    protected static string $table = 'setting_website_info';

    protected static function map(array $row): array
    {
        return [
            'id' => $row['id'],
            'websiteName' => $row['website_name'],
            'phone' => $row['phone'],
            'email' => $row['email'],
            'address' => $row['address'],
            'logo' => $row['logo'],
            'favicon' => $row['favicon'],
            'categoryIdSection4' => $row['category_id_section4'],
            'categoryIdSection6' => $row['category_id_section6'],
        ];
    }
}
