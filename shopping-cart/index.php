<?php
require __DIR__ . '/config.php';
$pdo = cart_db();
$products = $pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, min(10, (int)($_POST['quantity'] ?? 1)));
    $stmt = $pdo->prepare('SELECT id, name, price, stock FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if ($product && $qty <= (int)$product['stock']) {
        $_SESSION['cart'][$id] = min((int)$product['stock'], ($_SESSION['cart'][$id] ?? 0) + $qty);
    }
    header('Location: index.php'); exit;
}
$cartCount = array_sum($_SESSION['cart'] ?? []);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Shopping Cart</title>
<style>body{font:16px Arial;max-width:900px;margin:35px auto;padding:0 18px;color:#172033} .card{border:1px solid #d6dbe3;border-radius:10px;padding:16px;margin:12px 0}button,a.button{background:#1f4e85;color:white;border:0;padding:9px 13px;border-radius:6px;text-decoration:none;cursor:pointer}small{color:#566}</style></head><body>
<h1>Online Shopping System</h1><p>Demo store. <a href="cart.php">View cart (<?= (int)$cartCount ?>)</a> | <a href="orders.php">Order history</a></p>
<?php foreach ($products as $p): ?><div class="card"><h3><?= htmlspecialchars($p['name']) ?></h3><p><?= htmlspecialchars($p['description']) ?></p><p><strong>₱<?= number_format((float)$p['price'],2) ?></strong> · Stock: <?= (int)$p['stock'] ?></p>
<form method="post"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>"><label>Quantity <input type="number" name="quantity" min="1" max="10" value="1"></label> <button>Add to cart</button></form></div><?php endforeach; ?>
<p><small>System A owns product and order data. It communicates with the independent payment service through an HTTP API.</small></p></body></html>
