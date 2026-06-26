<?php
require_once __DIR__ . '/../includes/config.php';
requireAuth();

$user = getCurrentUser();
$account = getUserAccount($user['id']);
if (!$account) jsonError('No account found', 404);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $authType = $_GET['type'] ?? '';
  if ($authType === 'pending') {
    $stmt = getDB()->prepare("SELECT t.*, a.account_number as from_account FROM transfers t JOIN accounts a ON t.from_account_id = a.id WHERE a.user_id = ? AND t.status = 'pending'");
    $stmt->execute([$account['id']]);
    jsonResponse($stmt->fetchAll());
  }

  $stmt = getDB()->prepare("SELECT * FROM transfers WHERE from_account_id = ? ORDER BY created_at DESC LIMIT 50");
  $stmt->execute([$account['id']]);
  jsonResponse($stmt->fetchAll());
}

if ($method === 'POST') {
  $action = $_POST['action'] ?? '';
  $transferId = intval($_POST['transfer_id'] ?? 0);
  $authCode = trim($_POST['auth_code'] ?? '');

  if (!$transferId) jsonError('Transfer ID required');

  $stmt = getDB()->prepare("SELECT t.*, a.user_id FROM transfers t JOIN accounts a ON t.from_account_id = a.id WHERE t.id = ?");
  $stmt->execute([$transferId]);
  $transfer = $stmt->fetch();

  if (!$transfer) jsonError('Transfer not found', 404);
  if ($transfer['user_id'] !== $user['id']) jsonError('Unauthorized', 403);

  if ($action === 'approve') {
    $settings = getDB()->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    $authRequired = ($settings['transfer_auth_required'] ?? 'false') === 'true';

    if ($authRequired) {
      if (!$authCode) jsonError('Authorization code required');
      $expectedType = $settings['auth_type'] ?? 'none';
      $expectedCode = $settings[$expectedType . '_code'] ?? '';
      if (strtoupper($authCode) !== strtoupper($expectedCode)) jsonError('Invalid authorization code');
    }

    getDB()->beginTransaction();
    try {
      $fromAccount = $account;

      if ($transfer['amount'] > $fromAccount['balance']) jsonError('Insufficient funds');

      $stmt = getDB()->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
      $stmt->execute([$transfer['amount'], $fromAccount['id']]);

      $toStmt = getDB()->prepare("SELECT * FROM accounts WHERE account_number = ?");
      $toStmt->execute([$transfer['to_account_number']]);
      $toAccount = $toStmt->fetch();

      if ($toAccount) {
        $stmt = getDB()->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$transfer['amount'], $toAccount['id']]);

        $stmt = getDB()->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?, 'transfer_in', ?, ?, ?, 'completed')");
        $stmt->execute([$toAccount['id'], $transfer['amount'], 'Transfer from ' . $fromAccount['account_number'], $transfer['auth_code']]);
      }

      $stmt = getDB()->prepare("UPDATE transfers SET status = 'completed', processed_at = NOW(), processed_by = ? WHERE id = ?");
      $stmt->execute([$user['id'], $transferId]);

      $stmt = getDB()->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?, 'transfer_out', ?, ?, ?, 'completed')");
      $stmt->execute([$fromAccount['id'], $transfer['amount'], 'Transfer to ' . $transfer['to_account_number'], $transfer['auth_code']]);

      getDB()->commit();
      jsonResponse(['success' => true, 'message' => 'Transfer completed']);
    } catch (Exception $e) {
      getDB()->rollBack();
      jsonError('Transfer failed');
    }
    return;
  }

  if ($action === 'reject') {
    $stmt = getDB()->prepare("UPDATE transfers SET status = 'rejected', processed_at = NOW() WHERE id = ?");
    $stmt->execute([$transferId]);
    jsonResponse(['success' => true, 'message' => 'Transfer rejected']);
  }

  jsonError('Invalid action');
}

jsonError('Method not allowed', 405);
