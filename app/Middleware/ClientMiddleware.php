<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\View;
use App\Helpers\CategoryHelper;
use App\Models\Category;
use App\Models\City;
use App\Models\SettingWebsiteInfo;

class ClientMiddleware
{
    public static function bootstrap(Request $request): bool
    {
        $setting = SettingWebsiteInfo::findOne([]);
        $request->settingWebsiteInfo = $setting;
        View::share('settingWebsiteInfo', $setting);

        $categories = Category::find(['deleted' => false, 'status' => 'active']);
        View::share('categoryList', CategoryHelper::buildCategoryTree($categories, ''));

        $cities = City::find([]);
        View::share('cityList', $cities);

        return true;
    }
}
