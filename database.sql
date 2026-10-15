-- database.sql — Coffee Shop App

CREATE DATABASE IF NOT EXISTS db_coffee CHARACTER SET utf8mb4;
USE db_coffee;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,          
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer'
);

-- Products managed by the admin (price in Rupiah)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL DEFAULT '',
    price INT UNSIGNED NOT NULL,
    image VARCHAR(100) DEFAULT NULL           
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    customer_name VARCHAR(100) NOT NULL,
    subtotal INT UNSIGNED NOT NULL,
    discount INT UNSIGNED NOT NULL DEFAULT 0,
    total INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);


CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(100) NOT NULL,
    unit_price INT UNSIGNED NOT NULL,
    qty INT UNSIGNED NOT NULL,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

INSERT INTO products (name, description, price, image) VALUES
('Espresso', 'single shot.', 15000,'bb01655b276d4f69'),
('Americano', 'Espresso + hot water.', 20000,'e20e1980ed6fc724'),
('Ice Americano', 'Espresso + water + ice.', 20000,'e9cfb91853d0f269'),
('Water', 'water', 5000,'328e1ab2bdb6eefa');

