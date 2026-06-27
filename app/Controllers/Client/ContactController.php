<?php

namespace App\Controllers\Client;

use App\Core\Request;
use App\Core\Response;
use App\Models\Contact;

class ContactController
{
    public function createPost(Request $request): void
    {
        $email = $request->input('email');
        $exist = Contact::findOne(['email' => $email, 'deleted' => false]);
        if ($exist) {
            Response::json(['code' => 'error', 'message' => 'Email đã được đăng ký!']);
            return;
        }
        Contact::insert(['email' => $email, 'deleted' => false]);
        Response::json(['code' => 'success', 'message' => 'Đăng ký nhận tin thành công!']);
    }
}
