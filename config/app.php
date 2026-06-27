<?php

return [
    'path_admin' => 'admin',
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'CHUOINGAUNHIEN',
    'website_domain' => rtrim($_ENV['WEBSITE_DOMAIN'] ?? 'http://localhost:8000', '/'),
    'gmail_user' => $_ENV['GMAIL_USER'] ?? '',
    'gmail_pass' => $_ENV['GMAIL_PASS'] ?? '',
    'zalopay_appid' => $_ENV['ZALOPAY_APPID'] ?? '2554',
    'zalopay_key1' => $_ENV['ZALOPAY_KEY1'] ?? 'sdngKKJmqEMzvh5QQcdD2A9XBSKUNaYn',
    'zalopay_key2' => $_ENV['ZALOPAY_KEY2'] ?? 'trMrHtvjo6myautxDUiAcYsVtaeQ8nhf',
    'zalopay_domain' => $_ENV['ZALOPAY_DOMAIN'] ?? 'https://sb-openapi.zalopay.vn',
    'vnpay_tmncode' => $_ENV['VNPAY_TMNCODE'] ?? '',
    'vnpay_secret' => $_ENV['VNPAY_SECRET'] ?? '',
    'vnpay_url' => $_ENV['VNPAY_URL'] ?? '',
    'upload_dir' => dirname(__DIR__) . '/public/uploads',
    'upload_url' => '/uploads',
];
