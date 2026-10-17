# SQL Dump Integrity and Code Alignment Report

## Result
The two SQL dumps have been statically checked against the PHP source in this package.

## Checks performed
- Database names align with each application's `config.php`.
- Shopping cart tables and referenced columns align with shopping-cart queries.
- Payment transaction table and referenced columns align with gateway API/dashboard queries.
- Order status enum contains every status the shopping-cart integration writes.
- The gateway schema includes the unique `idempotency_key` required by the API.
- The shopping cart sends the fields required by the gateway API.
- Product seed data is repeat-safe: fixed IDs plus `INSERT IGNORE` prevent duplicate sample products when the dump is imported again.

## Important limitation
This is a static schema/source alignment check, not a live import test against MySQL/MariaDB. No MySQL server/driver is available in this validation environment. Before class, import both SQL files in phpMyAdmin and run the smoke tests in `tests/integration-checklist.md` on a machine with XAMPP or equivalent.

## Import order
1. `sql/shopping_cart_db.sql`
2. `sql/payment_gateway_db.sql`

Both dumps create their own database and use `CREATE TABLE IF NOT EXISTS`.
