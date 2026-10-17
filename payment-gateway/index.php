<?php
require __DIR__ . '/config.php';
$pdo = gateway_db();
$transactions = $pdo->query('SELECT * FROM transactions ORDER BY id DESC LIMIT 50')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Mock Payment Gateway</title><style>body{font:16px Arial;max-width:1100px;margin:35px auto;padding:0 18px;color:#172033}table{border-collapse:collapse;width:100%}td,th{padding:9px;border-bottom:1px solid #ddd;text-align:left}code{background:#eee;padding:3px}</style></head><body>
<h1>Mock Payment Gateway</h1><p>Independent System B. It owns the payment transaction database.</p>
<p>API endpoint: <code>POST /payment-gateway/api/payments.php</code></p><p>Optional test outcomes: SUCCESS, FAILED, PENDING.</p>
<table><tr><th>Transaction</th><th>Idempotency key</th><th>Order</th><th>Amount</th><th>Status</th><th>Created</th></tr>
<?php foreach ($transactions as $t): ?><tr><td><?= htmlspecialchars($t['transaction_id']) ?></td><td><?= htmlspecialchars($t['idempotency_key']) ?></td><td><?= (int)$t['order_id'] ?></td><td>₱<?= number_format((float)$t['amount'],2) ?></td><td><?= htmlspecialchars($t['status']) ?></td><td><?= htmlspecialchars($t['created_at']) ?></td></tr><?php endforeach; ?></table>
</body></html>
