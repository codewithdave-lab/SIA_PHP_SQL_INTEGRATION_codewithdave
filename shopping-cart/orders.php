<?php
require __DIR__ . '/config.php';
$pdo = cart_db();
$orders = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 50')->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Order History</title><style>body{font:16px Arial;max-width:1000px;margin:35px auto;padding:0 18px}table{border-collapse:collapse;width:100%}td,th{padding:9px;border-bottom:1px solid #ddd;text-align:left}</style></head><body><h1>Shopping Cart Order History</h1><p><a href="index.php">Store</a></p>
<table><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Gateway transaction</th><th>Created</th></tr>
<?php foreach ($orders as $o): ?><tr><td>#<?= (int)$o['id'] ?></td><td><?= htmlspecialchars($o['customer_name']) ?></td><td>₱<?= number_format((float)$o['total_amount'],2) ?></td><td><?= htmlspecialchars($o['status']) ?></td><td><?= htmlspecialchars($o['gateway_transaction_id'] ?? '-') ?></td><td><?= htmlspecialchars($o['created_at']) ?></td></tr><?php endforeach; ?>
</table></body></html>
