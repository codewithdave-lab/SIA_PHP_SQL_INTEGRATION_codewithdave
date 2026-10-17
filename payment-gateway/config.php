<?php
declare(strict_types=1);
const DB_HOST = '127.0.0.1';
const DB_NAME = 'payment_gateway_db';
const DB_USER = 'root';
const DB_PASS = '';

function gateway_db(): PDO {
    return new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
}
