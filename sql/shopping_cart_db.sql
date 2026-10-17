CREATE DATABASE IF NOT EXISTS shopping_cart_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shopping_cart_db;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 10
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(120) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('PENDING_PAYMENT','PAID','PAYMENT_FAILED','PAYMENT_PENDING','INTEGRATION_ERROR') NOT NULL DEFAULT 'PENDING_PAYMENT',
    gateway_transaction_id VARCHAR(80) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(120) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

INSERT IGNORE INTO products (id, name, description, price, stock) VALUES
(1, 'USB-C Hub', 'Multiport adapter for laptops', 1250.00, 10),
(2, 'Wireless Mouse', 'Basic wireless mouse', 650.00, 20),
(3, 'Mechanical Keyboard', 'Compact mechanical keyboard', 2200.00, 8);
