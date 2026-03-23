-- ============================================================
--  Tuấn Huy Computer — RBAC & Activity Logging Migration
--  Tương thích MySQL 5.6+ / MariaDB 10+
--  Chạy từng khối một trong phpMyAdmin nếu gặp lỗi cột đã tồn tại
-- ============================================================

-- ------------------------------------------------------------
-- 1. Soft-delete columns — products
--    Bỏ qua lỗi "Duplicate column name" nếu đã chạy trước rồi
-- ------------------------------------------------------------
ALTER TABLE `products`
  ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN `deleted_by` INT NULL DEFAULT NULL;

-- Index cho query nhanh hơn
ALTER TABLE `products`
  ADD INDEX `idx_is_deleted` (`is_deleted`);

-- ------------------------------------------------------------
-- 2. Soft-delete column — orders
-- ------------------------------------------------------------
ALTER TABLE `orders`
  ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0;

-- ------------------------------------------------------------
-- 3. action_logs — audit trail
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `action_logs` (
  `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `user_id`     INT              NULL,
  `user_name`   VARCHAR(120)     NOT NULL DEFAULT '',
  `user_role`   TINYINT(1)       NOT NULL DEFAULT 0,
  `action`      VARCHAR(20)      NOT NULL COMMENT 'CREATE|UPDATE|DELETE|LOGIN|LOGOUT',
  `table_name`  VARCHAR(64)      NOT NULL,
  `target_id`   INT              NULL,
  `old_data`    TEXT             NULL,
  `new_data`    TEXT             NULL,
  `ip_address`  VARCHAR(45)      NOT NULL DEFAULT '',
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user`    (`user_id`),
  INDEX `idx_table`   (`table_name`),
  INDEX `idx_action`  (`action`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Tạo tài khoản nhân sự
--    Mật khẩu mặc định: 123456
--    (Hash bcrypt của "123456")
-- ------------------------------------------------------------
INSERT INTO `users` (fullname, email, phone, password, role, is_active) VALUES
  ('Quản Lý 1', 'manager1@tuanhuy.vn', '0900000002',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1);

-- Staff mẫu (bỏ comment nếu cần)
-- INSERT INTO `users` (fullname, email, phone, password, role, is_active) VALUES
--   ('Staff Test', 'staff@tuanhuy.vn', '0900000003',
--    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1);
