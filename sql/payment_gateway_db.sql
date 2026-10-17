CREATE DATABASE IF NOT EXISTS payment_gateway_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE payment_gateway_db;

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(80) NOT NULL UNIQUE,
    idempotency_key VARCHAR(100) NOT NULL UNIQUE,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    customer_name VARCHAR(120) NOT NULL,
    payment_method VARCHAR(30) NOT NULL,
    status ENUM('SUCCESS','FAILED','PENDING') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
