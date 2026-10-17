# Mock Payment Gateway API

Endpoint: `POST http://localhost/payment-gateway/api/payments.php`
Header: `Content-Type: application/json`

Example request:
```json
{
  "order_id": 1,
  "amount": 1250.00,
  "customer_name": "Student Test",
  "payment_method": "CARD",
  "idempotency_key": "cart-order-1",
  "outcome": "SUCCESS"
}
```

Response:
```json
{
  "success": true,
  "transaction_id": "TXN-A1B2C3D4",
  "order_id": 1,
  "amount": 1250,
  "status": "SUCCESS",
  "message": "Simulated payment processed"
}
```

Statuses: SUCCESS, FAILED, PENDING. HTTP errors: 400 invalid JSON, 405 wrong method, 422 invalid fields, 500 server/database error.

`outcome` is a teaching-only simulator control and must not be trusted in production.
