-- ============================================
--  UKAY UKAY ECOMMERCE — DATABASE SCHEMA
-- ============================================
CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

CREATE TABLE IF NOT EXISTS users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  email       VARCHAR(100) UNIQUE NOT NULL,
  password    VARCHAR(255) NOT NULL,
  contact     VARCHAR(20)  DEFAULT '',
  address     TEXT         DEFAULT '',
  role        ENUM('user','admin') DEFAULT 'user',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(200) NOT NULL,
  description     TEXT,
  base_price      DECIMAL(10,2) NOT NULL,
  image_url       VARCHAR(500) DEFAULT '',
  category        VARCHAR(100) DEFAULT 'Tops',
  condition_label VARCHAR(50)  DEFAULT 'Good',
  seller_id       INT DEFAULT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS product_sizes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  product_id  INT NOT NULL,
  size_label  VARCHAR(20) NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS product_colors (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  product_id  INT NOT NULL,
  color_name  VARCHAR(50) NOT NULL,
  color_hex   VARCHAR(7)  NOT NULL DEFAULT '#888888',
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS product_variants (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  product_id  INT NOT NULL,
  size_id     INT NOT NULL,
  color_id    INT NOT NULL,
  stock       INT DEFAULT 0,
  UNIQUE KEY unique_variant (product_id, size_id, color_id),
  FOREIGN KEY (product_id) REFERENCES products(id)      ON DELETE CASCADE,
  FOREIGN KEY (size_id)    REFERENCES product_sizes(id) ON DELETE CASCADE,
  FOREIGN KEY (color_id)   REFERENCES product_colors(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cart (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  product_id  INT NOT NULL,
  variant_id  INT NOT NULL,
  quantity    INT DEFAULT 1,
  FOREIGN KEY (user_id)    REFERENCES users(id)             ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)          ON DELETE CASCADE,
  FOREIGN KEY (variant_id) REFERENCES product_variants(id)  ON DELETE CASCADE,
  UNIQUE KEY unique_cart_item (user_id, variant_id)
);

CREATE TABLE IF NOT EXISTS orders (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  user_id           INT NOT NULL,
  total_amount      DECIMAL(10,2) NOT NULL,
  status            ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  payment_method    VARCHAR(50) NOT NULL,
  recipient_name    VARCHAR(100) NOT NULL,
  recipient_address TEXT NOT NULL,
  recipient_email   VARCHAR(100) NOT NULL,
  recipient_contact VARCHAR(20) NOT NULL,
  notes             TEXT DEFAULT '',
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  order_id      INT NOT NULL,
  product_id    INT,
  product_name  VARCHAR(200) NOT NULL,
  size_label    VARCHAR(20)  NOT NULL,
  color_name    VARCHAR(50)  NOT NULL,
  color_hex     VARCHAR(7)   NOT NULL,
  quantity      INT NOT NULL,
  price         DECIMAL(10,2) NOT NULL,
  image_url     VARCHAR(500) DEFAULT '',
  FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);
-- Add seller_id to products if not exists (safe to run even if column exists)
ALTER TABLE products ADD COLUMN IF NOT EXISTS seller_id INT DEFAULT NULL;
ALTER TABLE products ADD CONSTRAINT fk_product_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL;