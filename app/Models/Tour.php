<?php

namespace App\Models;

class Tour extends BaseModel
{
    protected static string $table = 'tours';
    protected static array $jsonFields = ['images', 'locations', 'schedules'];

    protected static function map(array $row): array
    {
        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'category' => $row['category'],
            'position' => (int) $row['position'],
            'status' => $row['status'],
            'avatar' => $row['avatar'],
            'images' => is_string($row['images'] ?? null) ? json_decode($row['images'], true) : ($row['images'] ?? []),
            'priceAdult' => (float) $row['price_adult'],
            'priceChildren' => (float) $row['price_children'],
            'priceBaby' => (float) $row['price_baby'],
            'priceNewAdult' => (float) $row['price_new_adult'],
            'priceNewChildren' => (float) $row['price_new_children'],
            'priceNewBaby' => (float) $row['price_new_baby'],
            'stockAdult' => (int) $row['stock_adult'],
            'stockChildren' => (int) $row['stock_children'],
            'stockBaby' => (int) $row['stock_baby'],
            'locations' => is_string($row['locations'] ?? null) ? json_decode($row['locations'], true) : ($row['locations'] ?? []),
            'time' => $row['time'],
            'vehicle' => $row['vehicle'],
            'departureDate' => $row['departure_date'],
            'information' => $row['information'],
            'schedules' => is_string($row['schedules'] ?? null) ? json_decode($row['schedules'], true) : ($row['schedules'] ?? []),
            'createdBy' => $row['created_by'],
            'updatedBy' => $row['updated_by'],
            'slug' => $row['slug'],
            'deleted' => (bool) $row['deleted'],
            'deletedBy' => $row['deleted_by'],
            'deletedAt' => $row['deleted_at'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }

    public static function search(array $query): array
    {
        $sql = 'SELECT * FROM tours WHERE deleted = 0 AND status = ?';
        $params = ['active'];

        if (!empty($query['categoryIds']) && is_array($query['categoryIds'])) {
            $placeholders = implode(',', array_fill(0, count($query['categoryIds']), '?'));
            $sql .= ' AND category IN (' . $placeholders . ')';
            foreach ($query['categoryIds'] as $v) {
                $params[] = $v;
            }
        }

        if (!empty($query['locationFrom'])) {
            $sql .= ' AND JSON_CONTAINS(locations, ?, "$")';
            $params[] = json_encode((string) $query['locationFrom']);
        }
        if (!empty($query['locationTo'])) {
            $keyword = \App\Helpers\StrHelper::slugify($query['locationTo']);
            $sql .= ' AND slug LIKE ?';
            $params[] = '%' . $keyword . '%';
        }
        if (!empty($query['departureDate'])) {
            $sql .= ' AND departure_date = ?';
            $params[] = date('Y-m-d', strtotime($query['departureDate']));
        }
        if (!empty($query['stockAdult'])) {
            $sql .= ' AND stock_adult >= ?';
            $params[] = (int) $query['stockAdult'];
        }
        if (!empty($query['stockChildren'])) {
            $sql .= ' AND stock_children >= ?';
            $params[] = (int) $query['stockChildren'];
        }
        if (!empty($query['stockBaby'])) {
            $sql .= ' AND stock_baby >= ?';
            $params[] = (int) $query['stockBaby'];
        }
        if (!empty($query['price'])) {
            [$min, $max] = array_map('intval', explode('-', $query['price']));
            $sql .= ' AND price_new_adult >= ? AND price_new_adult <= ?';
            $params[] = $min;
            $params[] = $max;
        }

        $sql .= ' ORDER BY position DESC';
        $stmt = \App\Core\Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return array_map([static::class, 'decodeRow'], $stmt->fetchAll());
    }
}
