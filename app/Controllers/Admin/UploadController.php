<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Helpers\UploadHelper;

class UploadController
{
    public function imagePost(Request $request): void
    {
        $path = UploadHelper::fromRequest('file') ?? UploadHelper::fromRequest('image');
        Response::json(['location' => $path ?? '']);
    }
}
