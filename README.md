# SIA System Integration Lab — PHP + MySQL (Teacher Master)

This is the complete working reference implementation. Make a copy of this folder before stripping out student TODOs.

## Requirements
- XAMPP with Apache, PHP 8+, and MySQL/MariaDB
- PHP cURL extension enabled (normally available in XAMPP; verify `extension=curl` in `php.ini`)
- Web browser

## Install
1. Extract `shopping-cart` and `payment-gateway` into `C:\xampp\htdocs\`.
2. Start Apache and MySQL from XAMPP Control Panel.
3. Open phpMyAdmin: `http://localhost/phpmyadmin`.
4. Import `sql/shopping_cart_db.sql`.
5. Import `sql/payment_gateway_db.sql`.
6. Check each app's `config.php` database settings.
7. Visit:
   - Shopping cart: `http://localhost/shopping-cart/`
   - Payment gateway: `http://localhost/payment-gateway/`

## Teacher reference
- Full working integration is in `shopping-cart/checkout.php`.
- Gateway API documentation is in `payment-gateway/README-API.md`.
- Test plan is in `tests/integration-checklist.md`.
- Suggested student TODO points are in `TEACHER-ANSWER-KEY.md`.

## Architecture constraints
- The applications use separate databases.
- The shopping cart calls the payment gateway over HTTP/JSON using PHP cURL.
- The cart must never connect to or query `payment_gateway_db`.
- This is a local simulation, not a real payment service. Do not enter real payment credentials.

## Important demo behavior
The gateway accepts an optional `outcome` value for deterministic classroom testing: `SUCCESS`, `FAILED`, or `PENDING`. This field is only a simulator control. In a production system, the customer must not be allowed to choose their own payment result.


## SQL integrity note
See `SQL-ALIGNMENT-REPORT.md` for the schema-to-code checks and the remaining live-import smoke test requirement.
