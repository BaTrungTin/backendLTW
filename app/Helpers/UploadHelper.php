<?php

namespace App\Helpers;

class UploadHelper
{
    public static function save(array $file, string $subdir = ''): string
    {
        $config = $GLOBALS['config'];
        $dir = rtrim($config['upload_dir'] . ($subdir ? '/' . $subdir : ''), '/');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('img_', true) . ($ext ? '.' . $ext : '');
        $path = $dir . '/' . $name;
        move_uploaded_file($file['tmp_name'], $path);
        return rtrim($config['upload_url'], '/') . ($subdir ? '/' . $subdir : '') . '/' . $name;
    }

    public static function fromRequest(string $field): ?string
    {
        if (empty($_FILES[$field]['tmp_name'])) {
            return null;
        }
        return self::save($_FILES[$field]);
    }

    public static function multiple(string $field): array
    {
        $paths = [];
        if (empty($_FILES[$field]['name'][0])) {
            return $paths;
        }
        foreach ($_FILES[$field]['name'] as $i => $name) {
            if (empty($name)) {
                continue;
            }
            $file = [
                'name' => $name,
                'tmp_name' => $_FILES[$field]['tmp_name'][$i],
                'error' => $_FILES[$field]['error'][$i],
            ];
            $paths[] = self::save($file);
        }
        return $paths;
    }
}
