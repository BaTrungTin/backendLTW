<?php

use App\Controllers\Admin\AccountController;
use App\Controllers\Admin\CategoryAdminController;
use App\Controllers\Admin\ContactController as AdminContactController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\OrderAdminController;
use App\Controllers\Admin\ProfileController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\TourAdminController;
use App\Controllers\Admin\UploadController;
use App\Controllers\Admin\UserController;
use App\Controllers\Client\CartController;
use App\Controllers\Client\CategoryController;
use App\Controllers\Client\ContactController;
use App\Controllers\Client\HomeController;
use App\Controllers\Client\OrderController;
use App\Controllers\Client\SearchController;
use App\Controllers\Client\TourController;
use App\Core\Router;
use App\Core\View;
use App\Middleware\AdminAuthMiddleware;
use App\Middleware\ClientMiddleware;

$router = new Router();
$pa = '/' . $GLOBALS['pathAdmin'];
$auth = [[AdminAuthMiddleware::class, 'verify']];
$client = [[ClientMiddleware::class, 'bootstrap']];

$router->get('/', [HomeController::class, 'home'], $client);
$router->get('/tour/detail/:slug', [TourController::class, 'detail'], $client);
$router->get('/cart', [CartController::class, 'cart'], $client);
$router->post('/cart/detail', [CartController::class, 'detailPost'], $client);
$router->post('/contact/create', [ContactController::class, 'createPost'], $client);
$router->get('/category/:slug', [CategoryController::class, 'list'], $client);
$router->get('/search', [SearchController::class, 'list'], $client);
$router->post('/order/create', [OrderController::class, 'createPost'], $client);
$router->get('/order/success', [OrderController::class, 'success'], $client);

$router->get('/order/payment-vnpay', [OrderController::class, 'paymentVNPay'], $client);
$router->get('/order/payment-vnpay-result', [OrderController::class, 'paymentVNPayResult'], $client);

$router->get("{$pa}/account/login", [AccountController::class, 'login']);
$router->post("{$pa}/account/login", [AccountController::class, 'loginPost']);
$router->get("{$pa}/account/register", [AccountController::class, 'register']);
$router->post("{$pa}/account/register", [AccountController::class, 'registerPost']);
$router->get("{$pa}/account/register-initial", [AccountController::class, 'registerInitial']);
$router->get("{$pa}/account/forgot-password", [AccountController::class, 'forgotPassword']);
$router->post("{$pa}/account/forgot-password", [AccountController::class, 'forgotPasswordPost']);
$router->get("{$pa}/account/otp-password", [AccountController::class, 'otpPassword']);
$router->post("{$pa}/account/otp-password", [AccountController::class, 'otpPasswordPost']);
$router->get("{$pa}/account/reset-password", [AccountController::class, 'resetPassword'], $auth);
$router->post("{$pa}/account/reset-password", [AccountController::class, 'resetPasswordPost'], $auth);
$router->post("{$pa}/account/logout", [AccountController::class, 'logoutPost'], $auth);

$router->get("{$pa}/dashboard", [DashboardController::class, 'dashboard'], $auth);
$router->post("{$pa}/dashboard/revenue-chart", [DashboardController::class, 'revenueChartPost'], $auth);

$router->get("{$pa}/category/list", [CategoryAdminController::class, 'list'], $auth);
$router->get("{$pa}/category/create", [CategoryAdminController::class, 'create'], $auth);
$router->post("{$pa}/category/create", [CategoryAdminController::class, 'createPost'], $auth);
$router->get("{$pa}/category/edit/:id", [CategoryAdminController::class, 'edit'], $auth);
$router->patch("{$pa}/category/edit/:id", [CategoryAdminController::class, 'editPatch'], $auth);
$router->patch("{$pa}/category/delete/:id", [CategoryAdminController::class, 'deletePatch'], $auth);
$router->patch("{$pa}/category/change-multi", [CategoryAdminController::class, 'changeMultiPatch'], $auth);

$router->get("{$pa}/tour/list", [TourAdminController::class, 'list'], $auth);
$router->get("{$pa}/tour/create", [TourAdminController::class, 'create'], $auth);
$router->post("{$pa}/tour/create", [TourAdminController::class, 'createPost'], $auth);
$router->get("{$pa}/tour/trash", [TourAdminController::class, 'trash'], $auth);
$router->get("{$pa}/tour/edit/:id", [TourAdminController::class, 'edit'], $auth);
$router->patch("{$pa}/tour/edit/:id", [TourAdminController::class, 'editPatch'], $auth);
$router->patch("{$pa}/tour/delete/:id", [TourAdminController::class, 'deletePatch'], $auth);
$router->patch("{$pa}/tour/undo/:id", [TourAdminController::class, 'undoPatch'], $auth);
$router->delete("{$pa}/tour/destroy/:id", [TourAdminController::class, 'destroyDelete'], $auth);
$router->patch("{$pa}/tour/change-multi", [TourAdminController::class, 'changeMultiPatch'], $auth);

$router->get("{$pa}/order/list", [OrderAdminController::class, 'list'], $auth);
$router->get("{$pa}/order/edit/:id", [OrderAdminController::class, 'edit'], $auth);
$router->patch("{$pa}/order/edit/:id", [OrderAdminController::class, 'editPatch'], $auth);

$router->get("{$pa}/user/list", [UserController::class, 'list'], $auth);
$router->get("{$pa}/contact/list", [AdminContactController::class, 'list'], $auth);

$router->get("{$pa}/setting/list", [SettingController::class, 'list'], $auth);
$router->get("{$pa}/setting/website-info", [SettingController::class, 'websiteInfo'], $auth);
$router->patch("{$pa}/setting/website-info", [SettingController::class, 'websiteInfoPatch'], $auth);
$router->get("{$pa}/setting/account-admin/list", [SettingController::class, 'accountAdminList'], $auth);
$router->get("{$pa}/setting/account-admin/create", [SettingController::class, 'accountAdminCreate'], $auth);
$router->post("{$pa}/setting/account-admin/create", [SettingController::class, 'accountAdminCreatePost'], $auth);
$router->get("{$pa}/setting/account-admin/edit/:id", [SettingController::class, 'accountAdminEdit'], $auth);
$router->patch("{$pa}/setting/account-admin/edit/:id", [SettingController::class, 'accountAdminEditPatch'], $auth);
$router->get("{$pa}/setting/role/list", [SettingController::class, 'roleList'], $auth);
$router->get("{$pa}/setting/role/create", [SettingController::class, 'roleCreate'], $auth);
$router->post("{$pa}/setting/role/create", [SettingController::class, 'roleCreatePost'], $auth);
$router->get("{$pa}/setting/role/edit/:id", [SettingController::class, 'roleEdit'], $auth);
$router->patch("{$pa}/setting/role/edit/:id", [SettingController::class, 'roleEditPatch'], $auth);

$router->get("{$pa}/profile/edit", [ProfileController::class, 'edit'], $auth);
$router->patch("{$pa}/profile/edit", [ProfileController::class, 'editPatch'], $auth);
$router->get("{$pa}/profile/change-password", [ProfileController::class, 'changePassword'], $auth);
$router->patch("{$pa}/profile/change-password", [ProfileController::class, 'changePasswordPatch'], $auth);

$router->post("{$pa}/upload/image", [UploadController::class, 'imagePost'], $auth);

$router->get("{$pa}", function ($request) {
    View::render('admin/pages/error-404', ['pageTitle' => '404 Not Found']);
}, $auth);

return $router;
