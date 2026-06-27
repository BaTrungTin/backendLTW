<?php

namespace App\Models;

class Order extends BaseModel
{
    protected static string $table = 'orders';
    protected static array $jsonFields = ['items'];

    protected static function map(array $row): array
    {
        return [
            'id' => $row['id'],
            'code' => $row['code'],
            'fullName' => $row['full_name'],
            'phone' => $row['phone'],
            'note' => $row['note'],
            'items' => is_string($row['items'] ?? null) ? json_decode($row['items'], true) : ($row['items'] ?? []),
            'subTotal' => (float) $row['sub_total'],
            'discount' => (float) $row['discount'],
            'total' => (float) $row['total'],
            'paymentMethod' => $row['payment_method'],
            'paymentStatus' => $row['payment_status'],
            'status' => $row['status'],
            'updatedBy' => $row['updated_by'],
            'deleted' => (bool) $row['deleted'],
            'deletedAt' => $row['deleted_at'],
            'deletedBy' => $row['deleted_by'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }
}
