# Teacher Answer Key — Keep Private

## Main integration code
The complete reference implementation is in `shopping-cart/checkout.php`, in the section after the order is persisted.

Key concepts:
1. Build a JSON payload from the saved order.
2. Send HTTP POST using cURL.
3. Set `Content-Type: application/json`.
4. Set connect and total timeouts.
5. Decode and validate the JSON response and matching order ID.
6. Map `SUCCESS` -> `PAID`, `FAILED` -> `PAYMENT_FAILED`, `PENDING` -> `PAYMENT_PENDING`.
7. Store the gateway transaction ID.
8. If the request times out or the response is invalid, retain a pending state and tell the operator to verify before retrying.
9. Use a stable idempotency key (`cart-order-{orderId}`) so a retry does not create a second gateway transaction.

## Suggested student TODOs
Remove or blank these portions in a student copy:
- Payload construction in `shopping-cart/checkout.php`
- cURL initialization and request options
- JSON decoding and response validation
- Status mapping
- Order update query
- Error/timeout handling

Keep the order creation and the gateway API fully working.

## Expected results
- SUCCESS -> shopping_cart_db.orders.status = PAID
- FAILED -> shopping_cart_db.orders.status = PAYMENT_FAILED
- PENDING -> shopping_cart_db.orders.status = PAYMENT_PENDING
- Timeout/invalid response -> order remains PAYMENT_PENDING; never assume it is paid or definitively failed.
- Same idempotency key -> same gateway transaction ID is returned.

## Discussion points
- The two systems have separate databases.
- HTTP/JSON is the contract between the systems.
- A timeout creates an uncertain outcome, not proof of failure.
- Idempotency protects against duplicate transaction creation.
- In production, use authentication, signed webhooks/status checks, server-side amount verification, CSRF protection, and robust reconciliation. This is a classroom simulation, not production payment software.
