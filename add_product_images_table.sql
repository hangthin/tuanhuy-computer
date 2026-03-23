-- Chạy file này 1 lần trong phpMyAdmin hoặc MySQL để tạo bảng product_images
-- Nếu đã có bảng rồi thì bỏ qua, không bị lỗi

CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `product_id` int(11)      NOT NULL,
  `image`      varchar(255) NOT NULL,
  `sort_order` int(11)      NOT NULL DEFAULT 0,
  `created_at` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
