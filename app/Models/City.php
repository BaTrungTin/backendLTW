<?php

namespace App\Models;

class City extends BaseModel
{
    protected static string $table = 'cities';

    protected static function map(array $row): array
    {
        return ['id' => $row['id'], 'name' => $row['name']];
    }

    public static function findByName(string $name): ?array
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }
        $stmt = \App\Core\Database::pdo()->prepare('SELECT * FROM cities WHERE LOWER(name) = LOWER(?) LIMIT 1');
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        return $row ? static::decodeRow($row) : null;
    }
}
