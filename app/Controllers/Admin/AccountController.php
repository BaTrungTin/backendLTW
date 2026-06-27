<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Helpers\GenerateHelper;
use App\Helpers\MailHelper;
use App\Helpers\AuthHelper;
use App\Models\AccountAdmin;
use App\Models\ForgotPassword;

class AccountController
{
    public function login(Request $request): void
    {
        View::render('admin/pages/login', ['pageTitle' => 'Đăng nhập']);
    }

    public function loginPost(Request $request): void
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->input('rememberPassword');
        $account = AccountAdmin::findOne(['email' => $email]);
        if (!$account || !password_verify($password, $account['password'])) {
            Response::json(['code' => 'error', 'message' => $account ? 'Mật khẩu không đúng!' : 'Email không tồn tại trong hệ thống!']);
            return;
        }
        if ($account['status'] !== 'active') {
            Response::json(['code' => 'error', 'message' => 'Tài khoản chưa được kích hoạt!']);
            return;
        }
        AuthHelper::login($account, (bool) $remember);
        Response::json(['code' => 'success', 'message' => 'Đăng nhập thành công!']);
    }

    public function register(Request $request): void
    {
        View::render('admin/pages/register', ['pageTitle' => 'Đăng ký']);
    }

    public function registerPost(Request $request): void
    {
        $body = $request->body();
        if (AccountAdmin::findOne(['email' => $body['email']])) {
            Response::json(['code' => 'error', 'message' => 'Email đã tồn tại trong hệ thống!']);
            return;
        }
        AccountAdmin::insert([
            'fullName' => $body['fullName'],
            'email' => $body['email'],
            'phone' => $body['phone'] ?? '',
            'password' => password_hash($body['password'], PASSWORD_BCRYPT),
            'status' => 'initial',
        ]);
        Response::json(['code' => 'success', 'message' => 'Đăng ký tài khoản thành công!']);
    }

    public function registerInitial(Request $request): void
    {
        View::render('admin/pages/register-initial', ['pageTitle' => 'Tài khoản đã được khởi tạo']);
    }

    public function forgotPassword(Request $request): void
    {
        View::render('admin/pages/forgot-password', ['pageTitle' => 'Quên mật khẩu']);
    }

    public function forgotPasswordPost(Request $request): void
    {
        $email = $request->input('email');
        if (!AccountAdmin::findOne(['email' => $email, 'status' => 'active'])) {
            Response::json(['code' => 'error', 'message' => 'Email không tồn tại trong hệ thống!']);
            return;
        }
        if (ForgotPassword::findOne(['email' => $email])) {
            Response::json(['code' => 'error', 'message' => 'Vui lòng gửi lại yêu cầu sau 5 phút!']);
            return;
        }
        $otp = GenerateHelper::randomNumber(6);
        ForgotPassword::insert([
            'email' => $email,
            'otp' => $otp,
            'expireAt' => date('Y-m-d H:i:s', time() + 300),
        ]);
        MailHelper::sendMail($email, 'Mã OTP lấy lại mật khẩu', "Mã OTP của bạn là: <b>{$otp}</b>");
        Response::json(['code' => 'success', 'message' => 'Đã gửi OTP qua email!']);
    }

    public function otpPassword(Request $request): void
    {
        View::render('admin/pages/otp-password', ['pageTitle' => 'Nhập mã OTP']);
    }

    public function otpPasswordPost(Request $request): void
    {
        $email = $request->input('email');
        $otp = $request->input('otp');
        $account = AccountAdmin::findOne(['email' => $email, 'status' => 'active']);
        if (!$account || !ForgotPassword::findOne(['email' => $email, 'otp' => $otp])) {
            Response::json(['code' => 'error', 'message' => 'Mã OTP không hợp lệ!']);
            return;
        }
        AuthHelper::login($account, false);
        Response::json(['code' => 'success', 'message' => 'Xác thực thành công!']);
    }

    public function resetPassword(Request $request): void
    {
        View::render('admin/pages/reset-password', ['pageTitle' => 'Đổi mật khẩu']);
    }

    public function resetPasswordPost(Request $request): void
    {
        $password = password_hash($request->input('password'), PASSWORD_BCRYPT);
        AccountAdmin::updateOne(['id' => $request->account->id], ['password' => $password]);
        Response::json(['code' => 'success', 'message' => 'Đã đổi mật khẩu thành công!']);
    }

    public function logoutPost(Request $request): void
    {
        AuthHelper::logout();
        Response::json(['code' => 'success', 'message' => 'Đã đăng xuất!']);
    }
}
