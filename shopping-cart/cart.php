<?php
require __DIR__ . '/config.php';
$pdo = cart_db();
$cart = $_SESSION['cart'] ?? [];
$items = []; $total = 0.0;
if ($cart) {
    $ids = array_map('intval', array_keys($cart));
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id,name,price,stock FROM products WHERE id IN ($marks)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) {
        $qty = min((int)$cart[$p['id']], (int)$p['stock']);
        $line = $qty * (float)$p['price']; $total += $line;
        $items[] = ['product'=>$p,'qty'=>$qty,'line'=>$line];
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Your Cart</title><style>body{font:16px Arial;max-width:900px;margin:35px auto;padding:0 18px;color:#172033}table{border-collapse:collapse;width:100%}td,th{padding:10px;border-bottom:1px solid #ddd;text-align:left}button,a{background:#1f4e85;color:white;padding:9px 13px;border:0;border-radius:6px;text-decoration:none}</style></head><body>
<h1>Your Shopping Cart</h1><p><a href="index.php">Continue shopping</a></p>
<?php if (!$items): ?><p>Your cart is empty.</p><?php else: ?>
<table><tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Subtotal</th></tr>
<?php foreach ($items as $i): ?><tr><td><?= htmlspecialchars($i['product']['name']) ?></td><td><?= $i['qty'] ?></td><td>₱<?= number_format((float)$i['product']['price'],2) ?></td><td>₱<?= number_format($i['line'],2) ?></td></tr><?php endforeach; ?>
<tr><th colspan="3">Total</th><th>₱<?= number_format($total,2) ?></th></tr></table>
<h2>Checkout</h2><form action="checkout.php" method="post"><label>Your name <input name="customer_name" required maxlength="120"></label><p><label>Simulated payment method <select name="payment_method"><option value="CARD">Card (simulated)</option><option value="EWALLET">E-wallet (simulated)</option><option value="BANK">Bank transfer (simulated)</option></select></label></p><button type="submit">Place order and pay</button></form>
<?php endif; ?></body></html>
