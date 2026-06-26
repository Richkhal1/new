<?php
require_once __DIR__ . '/../includes/config.php';
requireAuth();

$user = getCurrentUser();
$account = getUserAccount($user['id']);
if (!$account) jsonError('No account found', 404);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $stmt = getDB()->prepare("SELECT * FROM transactions WHERE account_id = ? ORDER BY created_at DESC LIMIT 50");
  $stmt->execute([$account['id']]);
  jsonResponse($stmt->fetchAll());
}

if ($method === 'POST') {
  $type = $_POST['type'] ?? '';
  $amount = floatval($_POST['amount'] ?? 0);
  $description = trim($_POST['description'] ?? '');

  if ($amount <= 0) jsonError('Invalid amount');

  $ref = generateReference();

  if ($type === 'transfer') {
    $toAccount = trim($_POST['to_account'] ?? '');
    $toName = trim($_POST['to_name'] ?? '');

    if (!$toAccount) jsonError('Recipient account required');
    if ($amount > $account['balance']) jsonError('Insufficient funds');

    getDB()->beginTransaction();
    try {
      $stmt = getDB()->prepare("INSERT INTO transfers (from_account_id, to_account_number, to_account_name, amount, description, auth_code, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
      $stmt->execute([$account['id'], $toAccount, $toName, $amount, $description, $ref]);

      $stmt = getDB()->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?, 'transfer_out', ?, ?, ?, 'pending')");
      $stmt->execute([$account['id'], $amount, 'Transfer to ' . ($toName ?: $toAccount), $ref]);

      getDB()->commit();
      jsonResponse(['success' => true, 'reference' => $ref, 'message' => 'Transfer submitted for approval']);
    } catch (Exception $e) {
      getDB()->rollBack();
      jsonError('Transfer failed');
    }
    return;
  }

  if ($type === 'deposit') {
    getDB()->beginTransaction();
    try {
      $stmt = getDB()->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
      $stmt->execute([$amount, $account['id']]);

      $stmt = getDB()->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?, 'deposit', ?, ?, ?, 'completed')");
      $stmt->execute([$account['id'], $amount, $description ?: 'Deposit', $ref]);

      getDB()->commit();
      jsonResponse(['success' => true, 'reference' => $ref]);
    } catch (Exception $e) {
      getDB()->rollBack();
      jsonError('Deposit failed');
    }
    return;
  }

  jsonError('Invalid transaction type');
}

jsonError('Method not allowed', 405);
