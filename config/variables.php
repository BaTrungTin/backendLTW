<?php

return [
    'permission_list' => [
        ['label' => 'Xem trang Tổng quan', 'value' => 'dashboard-view'],
        ['label' => 'Xem danh mục', 'value' => 'category-view'],
        ['label' => 'Tạo danh mục', 'value' => 'category-create'],
        ['label' => 'Sửa danh mục', 'value' => 'category-edit'],
        ['label' => 'Xóa danh mục', 'value' => 'category-delete'],
        ['label' => 'Thùng rác danh mục', 'value' => 'category-trash'],
        ['label' => 'Xem tour', 'value' => 'tour-view'],
        ['label' => 'Tạo tour', 'value' => 'tour-create'],
        ['label' => 'Sửa tour', 'value' => 'tour-edit'],
        ['label' => 'Xóa tour', 'value' => 'tour-delete'],
        ['label' => 'Thùng rác tour', 'value' => 'tour-trash'],
    ],
    'payment_method_list' => [
        ['label' => 'Tiền mặt', 'value' => 'money'],
        ['label' => 'Chuyển khoản', 'value' => 'bank'],
        ['label' => 'Ví momo', 'value' => 'momo'],
        ['label' => 'VNPay', 'value' => 'vnpay'],
    ],
    'payment_status_list' => [
        ['label' => 'Chưa thanh toán', 'value' => 'unpaid'],
        ['label' => 'Đã thanh toán', 'value' => 'paid'],
    ],
    'status_list' => [
        ['label' => 'Khởi tạo', 'value' => 'initial', 'color' => 'orange'],
        ['label' => 'Hoàn thành', 'value' => 'done', 'color' => 'green'],
        ['label' => 'Đã huỷ', 'value' => 'cancel', 'color' => 'red'],
    ],
];
