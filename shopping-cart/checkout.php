<?php
require __DIR__ . '/config.php';

function finish(string $title, string $message): never
{
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>' . htmlspecialchars($title) . '</title><body style="font:16px Arial;max-width:800px;margin:40px auto">';
    echo '<h1>' . htmlspecialchars($title) . '</h1><p>' . htmlspecialchars($message) . '</p><p><a href="index.php">Store</a> | <a href="cart.php">Cart</a> | <a href="orders.php">Orders</a></p></body></html>';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$customer = trim((string) ($_POST['customer_name'] ?? ''));
$method = (string) ($_POST['payment_method'] ?? 'CARD');
if ($customer === '')
    finish('Missing information', 'Enter a customer name.');
if (!in_array($method, ['CARD', 'EWALLET', 'BANK'], true))
    $method = 'CARD';

$cart = $_SESSION['cart'] ?? [];
if (!$cart)
    finish('Empty cart', 'Add an item before checkout.');

$pdo = cart_db();
$ids = array_map('intval', array_keys($cart));
$marks = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id,name,price,stock FROM products WHERE id IN ($marks)");
$stmt->execute($ids);
$products = $stmt->fetchAll();
$total = 0.0;
$validItems = [];
foreach ($products as $p) {
    $qty = min((int) $cart[$p['id']], (int) $p['stock']);
    if ($qty > 0) {
        $total += $qty * (float) $p['price'];
        $validItems[] = ['product' => $p, 'qty' => $qty];
    }
}
if (!$validItems || $total <= 0)
    finish('Checkout error', 'No valid items were found.');

/* Persist order before calling the independent payment service. */
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name,total_amount,status) VALUES (?,?,'PENDING_PAYMENT')");
    $stmt->execute([$customer, number_format($total, 2, '.', '')]);
    $orderId = (int) $pdo->lastInsertId();
    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id,product_id,product_name,quantity,unit_price) VALUES (?,?,?,?,?)');
    foreach ($validItems as $i) {
        $itemStmt->execute([$orderId, $i['product']['id'], $i['product']['name'], $i['qty'], $i['product']['price']]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    finish('Order error', 'Could not save the order.');
}

/*
 * STUDENT TASK: Integrate the shopping cart with the
 * independent payment gateway.
 *
 * Requirements:
 * 1. Prepare the payment request payload.
 * 2. Send an HTTP POST request with JSON using PHP cURL.
 * 3. Handle the gateway's HTTP response.
 * 4. Validate the response and match the order ID.
 * 5. Handle SUCCESS, FAILED, and PENDING outcomes.
 * 6. Handle timeouts and invalid responses safely.
 * 7. Save the gateway transaction ID and update the
 *    order status in the shopping cart database.
 * 8. Prevent duplicate payment requests using an
 *    idempotency key.
 *
 * Read payment-gateway/README-API.md for the API contract.
 */

// ---- Step 8: Construct the payment request ----
$apiUrl = 'http://localhost/SIA_PHP_SQL_Integration/payment-gateway/api/payments.php';

// Stable idempotency key: same order = same key, different order = different key
$idempotencyKey = 'cart-order-' . $orderId;

$payload = [
    'order_id' => $orderId,
    'amount' => round($total, 2),
    'customer_name' => $customer,
    'payment_method' => $method,
    'idempotency_key' => $idempotencyKey,
];

$jsonBody = json_encode($payload);
if ($jsonBody === false) {
    finish('Integration error', 'Could not build the payment request.');
}

$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
];

// ---- Step 9: Send the request to the gateway with cURL ----
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $jsonBody,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_TIMEOUT => 10,
]);
$rawResponse = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// ---- Step 10: Decode and validate the response ----
$communicationOk = false;
$result = null;
$errorNote = '';

if ($rawResponse === false) {
    $errorNote = 'Gateway unreachable or timed out: ' . $curlError;
} elseif ($httpCode >= 500) {
    $errorNote = "Gateway server error (HTTP $httpCode)";
} elseif ($httpCode < 200 || $httpCode >= 300) {
    $errorNote = "Gateway rejected the request (HTTP $httpCode)";
} else {
    $decoded = json_decode($rawResponse, true);

    if (!is_array($decoded)) {
        $errorNote = 'Invalid JSON from gateway';
    } elseif (
        ($decoded['success'] ?? false) !== true
        || !isset($decoded['order_id'], $decoded['status'], $decoded['transaction_id'], $decoded['amount'])
        || !is_string($decoded['status'])
        || !is_string($decoded['transaction_id'])
        || $decoded['transaction_id'] === ''
        || strlen($decoded['transaction_id']) > 80
    ) {
        $errorNote = 'Gateway response is missing required fields';
    } elseif ((int) $decoded['order_id'] !== $orderId) {
        $errorNote = 'Gateway response order ID does not match this order';
    } elseif (abs((float) $decoded['amount'] - round($total, 2)) > 0.001) {
        $errorNote = 'Gateway response amount does not match this order';
    } else {
        $communicationOk = true;
        $result = $decoded;
    }
}

// ---- Step 11: Decide the order status from the outcome ----
$newStatus = 'PAYMENT_PENDING';
$txnId = null;
$title = 'Payment status unconfirmed';
$message = "Order #$orderId was saved, but the payment status could not be confirmed. "
    . 'Check the order status in Orders before attempting payment again.';

if ($communicationOk) {
    $txnId = $result['transaction_id'];

    switch (strtoupper($result['status'])) {
        case 'SUCCESS':
            $newStatus = 'PAID';
            $title = 'Payment successful';
            $message = "Order #$orderId has been paid. Transaction ID: $txnId.";
            break;
        case 'FAILED':
            $newStatus = 'PAYMENT_FAILED';
            $title = 'Payment failed';
            $message = "The payment for order #$orderId failed. Transaction ID: $txnId.";
            break;
        case 'PENDING':
            $newStatus = 'PAYMENT_PENDING';
            $title = 'Payment pending';
            $message = "The payment for order #$orderId is awaiting confirmation. Transaction ID: $txnId.";
            break;
        default:
            $newStatus = 'INTEGRATION_ERROR';
            $txnId = null;
            $title = 'Payment status unconfirmed';
            $message = "Order #$orderId was saved, but the gateway returned an unrecognized status.";
    }
} elseif ($rawResponse !== false && $httpCode >= 200 && $httpCode < 500) {
    $newStatus = 'INTEGRATION_ERROR';
}

// ---- Step 13: Update the order in shopping_cart_db ----
try {
    $stmt = $pdo->prepare('UPDATE orders SET status = ?, gateway_transaction_id = ? WHERE id = ?');
    $stmt->execute([$newStatus, $txnId, $orderId]);
} catch (Throwable $e) {
    finish(
        'Order update error',
        "Order #$orderId was created, but its payment status could not be saved. Check Orders before retrying."
    );
}

// ---- Reduce stock only when payment is confirmed PAID ----
if ($newStatus === 'PAID') {
    try {
        $pdo->beginTransaction();
        $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        foreach ($validItems as $i) {
            $stockStmt->execute([$i['qty'], $i['product']['id'], $i['qty']]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        // Payment is already confirmed, so the order status is left unchanged.
    }
}

if ($newStatus !== 'PAYMENT_FAILED') {
    unset($_SESSION['cart']);
}

finish($title, $message);