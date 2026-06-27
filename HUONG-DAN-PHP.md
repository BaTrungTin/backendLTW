# Hướng dẫn chạy dự án Tour (PHP + MySQL)

Dự án đã được chuyển backend sang **PHP 8 + MySQL**. Thư mục `public/` (CSS, JS, hình ảnh) và các file view **Pug** được giữ nguyên — PHP render Pug qua Node.js (cần cài Node để hiển thị giao diện).

## Yêu cầu

- PHP 8.1+ (`brew install php`)
- MySQL 8+ (`brew install mysql`)
- Composer (`brew install composer`)
- Node.js (đã có từ dự án cũ — dùng render view Pug)

## Cài đặt nhanh trên Mac

```bash
cd "/Users/thtkiet/UTH/Lập trình web/tour"
chmod +x scripts/setup-mac.sh
./scripts/setup-mac.sh
```

Chỉnh `DB_PASSWORD` trong `.env` nếu MySQL root có mật khẩu.

## Chạy server

```bash
php -S localhost:8000 -t public public/router.php
```

(Nếu cổng 8000 đã bị chiếm, dùng `8888` hoặc cổng khác.)

- Trang khách: http://localhost:8888  
- Admin: http://localhost:8000/admin/account/login  
- Đăng nhập mặc định: `admin@tour.local` / `admin123`

## Cấu trúc mới

| Thư mục | Mô tả |
|---------|--------|
| `app/` | Controllers, Models, Helpers, Middleware (PHP) |
| `config/` | Cấu hình app, database |
| `database/` | `schema.sql`, `seed.sql` |
| `public/` | File tĩnh + `index.php` (entry point) |
| `routes/web.php` | Định nghĩa route |
| `views/` | Template Pug (giữ nguyên) |

## Upload ảnh

Ảnh admin upload lưu tại `public/uploads/` (thay Cloudinary khi chạy local).

## Node.js (chỉ render Pug)

Backend Node/Express đã xóa. Chỉ cần cài `pug`:

```bash
rm -rf node_modules && yarn install
```

## Ghi chú

- API JSON và URL giữ nguyên như bản Express (JS frontend không đổi).
- Thanh toán ZaloPay/VNPay: cấu hình trong `.env`.
