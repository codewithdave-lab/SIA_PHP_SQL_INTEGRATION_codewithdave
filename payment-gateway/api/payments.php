<?php
require dirname(__DIR__) . '/config.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST'); http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Use POST']); exit;
}
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid JSON']); exit;
}
$orderId = filter_var($data['order_id'] ?? null, FILTER_VALIDATE_INT);
$amount = filter_var($data['amount'] ?? null, FILTER_VALIDATE_FLOAT);
$name = trim((string)($data['customer_name'] ?? ''));
$method = (string)($data['payment_method'] ?? '');
$key = trim((string)($data['idempotency_key'] ?? ''));
if (!$orderId || $amount === false || $amount <= 0 || $name === '' || $key === ''
    || !in_array($method, ['CARD','EWALLET','BANK'], true)) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Missing or invalid fields']); exit;
}
$outcome = strtoupper((string)($data['outcome'] ?? 'SUCCESS'));
if (!in_array($outcome, ['SUCCESS','FAILED','PENDING'], true)) $outcome = 'SUCCESS';

try {
    $pdo = gateway_db();
    // Idempotency: repeated request with the same key returns the existing transaction.
    $stmt = $pdo->prepare('SELECT transaction_id, order_id, amount, status FROM transactions WHERE idempotency_key = ?');
    $stmt->execute([$key]);
    $existing = $stmt->fetch();
    if ($existing) {
        echo json_encode([
            'success'=>true, 'transaction_id'=>$existing['transaction_id'],
            'order_id'=>(int)$existing['order_id'], 'amount'=>(float)$existing['amount'],
            'status'=>$existing['status'], 'message'=>'Existing transaction returned (idempotent replay)'
        ]); exit;
    }

    $transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(4)));
    $stmt = $pdo->prepare('INSERT INTO transactions (transaction_id,idempotency_key,order_id,amount,customer_name,payment_method,status) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$transactionId,$key,$orderId,number_format((float)$amount,2,'.',''),$name,$method,$outcome]);
    echo json_encode([
        'success'=>true, 'transaction_id'=>$transactionId,
        'order_id'=>$orderId, 'amount'=>(float)$amount, 'status'=>$outcome,
        'message'=>'Simulated payment processed'
    ]);
} catch (Throwable $e) {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Gateway could not record transaction']);
}
