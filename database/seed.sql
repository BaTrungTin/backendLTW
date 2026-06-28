USE tour_db;

INSERT INTO setting_website_info (website_name, phone, email, address, logo, favicon)
VALUES ('Tour Travel', '0123456789', 'info@tour.local', 'TP.HCM', '/assets/images/logo.png', '/assets/images/logo.png')
ON DUPLICATE KEY UPDATE website_name = website_name;

INSERT INTO roles (name, description, permissions, deleted)
VALUES ('Super Admin', 'Toàn quyền', JSON_ARRAY('dashboard-view','category-view','category-create','category-edit','category-delete','category-trash','tour-view','tour-create','tour-edit','tour-delete','tour-trash'), 0)
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO accounts_admin (full_name, email, phone, role, status, password, deleted)
SELECT 'Administrator', 'admin@tour.local', '0900000000', r.id, 'active',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0
FROM roles r WHERE r.name = 'Super Admin' LIMIT 1
ON DUPLICATE KEY UPDATE full_name = full_name;

INSERT INTO cities (name) VALUES
('Hà Nội'), ('TP.HCM'), ('Đà Nẵng'), ('Nha Trang'), ('Phú Quốc'),
('Nhật Bản'), ('Hàn Quốc'), ('Thái Lan'), ('Singapore'), ('Pháp'), ('Úc'), ('Mỹ')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, parent, position, status, slug) VALUES
('Tour Trong Nước', NULL, 2, 'active', 'tour-trong-nuoc'),
('Tour Nước Ngoài', NULL, 1, 'active', 'tour-nuoc-ngoai')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, parent, position, status, slug)
SELECT 'Miền Bắc', c.id, 3, 'active', 'mien-bac'
FROM categories c WHERE c.slug = 'tour-trong-nuoc' LIMIT 1
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, parent, position, status, slug)
SELECT 'Miền Trung', c.id, 2, 'active', 'mien-trung'
FROM categories c WHERE c.slug = 'tour-trong-nuoc' LIMIT 1
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, parent, position, status, slug)
SELECT 'Miền Nam', c.id, 1, 'active', 'mien-nam'
FROM categories c WHERE c.slug = 'tour-trong-nuoc' LIMIT 1
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, parent, position, status, slug)
SELECT 'Châu Á', c.id, 2, 'active', 'chau-a'
FROM categories c WHERE c.slug = 'tour-nuoc-ngoai' LIMIT 1
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, parent, position, status, slug)
SELECT 'Châu Âu', c.id, 1, 'active', 'chau-au'
FROM categories c WHERE c.slug = 'tour-nuoc-ngoai' LIMIT 1
ON DUPLICATE KEY UPDATE name = VALUES(name);
