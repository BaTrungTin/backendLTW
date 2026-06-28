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
        $logo = $setting['logo'] ?? '';
        $logoFile = BASE_PATH . '/public' . ($logo !== '' && $logo[0] === '/' ? $logo : '/' . ltrim($logo, '/'));
        if ($logo === '' || !is_file($logoFile)) {
            $setting['logo'] = '/assets/images/logo.png';
        }
        $request->settingWebsiteInfo = $setting;
        View::share('settingWebsiteInfo', $setting);

        $categories = Category::find(['deleted' => false, 'status' => 'active']);
        View::share('categoryList', CategoryHelper::buildCategoryTree($categories, ''));

        $cities = City::find([]);
        View::share('cityList', $cities);

        $currentPath = $request->path;
        $navActiveSlug = null;
        if (preg_match('#^/category/([^/]+)#', $currentPath, $matches)) {
            $navActiveSlug = $matches[1];
        }
        View::share('currentPath', $currentPath);
        View::share('navActiveSlug', $navActiveSlug);

        return true;
    }
}
