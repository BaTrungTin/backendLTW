#!/bin/bash
set -e
cd "$(dirname "$0")/.."

echo "==> Cài Composer dependencies..."
composer install --no-interaction

echo "==> Tạo file .env nếu chưa có..."
if [ ! -f .env ]; then
  cp .env.example .env
  echo "Đã tạo .env — hãy chỉnh DB_PASSWORD nếu MySQL của bạn có mật khẩu."
fi

echo "==> Khởi động MySQL (Homebrew)..."
brew services start mysql 2>/dev/null || true

echo "==> Import database..."
mysql -u root < database/schema.sql 2>/dev/null || mysql -u root -p < database/schema.sql

ADMIN_HASH=$(php -r "echo password_hash('admin123', PASSWORD_BCRYPT);")
mysql -u root tour_db -e "UPDATE accounts_admin SET password='$ADMIN_HASH' WHERE email='admin@tour.local';" 2>/dev/null \
  || mysql -u root -p tour_db -e "UPDATE accounts_admin SET password='$ADMIN_HASH' WHERE email='admin@tour.local';"

mysql -u root tour_db < database/seed.sql 2>/dev/null || mysql -u root -p tour_db < database/seed.sql

mkdir -p public/uploads
chmod -R 775 public/uploads 2>/dev/null || true

echo ""
echo "=== Hoàn tất ==="
echo "Chạy server:  php -S localhost:8888 -t public public/router.php"
echo "Trang chủ:    http://localhost:8888"
echo "Admin:        http://localhost:8888/admin/account/login"
echo "Tài khoản:    admin@tour.local / admin123"
