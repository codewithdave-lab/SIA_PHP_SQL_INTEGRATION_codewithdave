# Integration Test Checklist

Record the order ID, cart order status, gateway transaction ID, and gateway status for each test.

- [ ] Cart and gateway load from separate local URLs.
- [ ] Gateway API rejects GET requests with HTTP 405.
- [ ] Gateway API rejects invalid JSON with HTTP 400.
- [ ] Gateway API rejects missing/invalid fields with HTTP 422.
- [ ] Successful payment changes cart order to PAID.
- [ ] Failed payment changes cart order to PAYMENT_FAILED.
- [ ] Pending payment changes cart order to PAYMENT_PENDING.
- [ ] Gateway transaction ID is stored in the cart database.
- [ ] Same idempotency key returns the same transaction, not a second transaction.
- [ ] Invalid gateway URL or timeout never marks the order PAID automatically.
- [ ] Cart can still show order history after a gateway error.
- [ ] Shopping-cart source contains no connection to payment_gateway_db.

Teacher note: change the `outcome` value in checkout.php to FAILED or PENDING for deterministic test cases. To test timeout, temporarily stop Apache or change `$apiUrl` to an unused local port, then restore it.
