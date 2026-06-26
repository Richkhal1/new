<?php
require_once __DIR__ . '/../includes/config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();
$user = getCurrentUser();
$account = getUserAccount($user['id']);

if ($method === 'GET') {
  $action = $_GET['action'] ?? 'list';

  if ($action === 'list') {
    $stmt = $db->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $loans = $stmt->fetchAll();
    foreach ($loans as &$l) {
      $p = $db->prepare("SELECT COUNT(*) as total, COUNT(CASE WHEN status='paid' THEN 1 END) as paid, COALESCE(SUM(CASE WHEN status='paid' THEN amount END),0) as total_paid FROM loan_payments WHERE loan_id = ?");
      $p->execute([$l['id']]);
      $l['payments'] = $p->fetch();
      $np = $db->prepare("SELECT * FROM loan_payments WHERE loan_id = ? AND status = 'pending' ORDER BY due_date ASC LIMIT 1");
      $np->execute([$l['id']]);
      $l['next_payment'] = $np->fetch();
    }
    jsonResponse($loans);
  }

  if ($action === 'detail') {
    $lid = intval($_GET['loan_id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM loans WHERE id = ? AND user_id = ?");
    $stmt->execute([$lid, $user['id']]);
    $loan = $stmt->fetch();
    if (!$loan) jsonError('Loan not found', 404);
    $p = $db->prepare("SELECT * FROM loan_payments WHERE loan_id = ? ORDER BY due_date");
    $p->execute([$lid]);
    $loan['payments'] = $p->fetchAll();
    jsonResponse($loan);
  }

  jsonError('Invalid action');
}

if ($method === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'apply') {
    $amount = floatval($_POST['amount'] ?? 0);
    $purpose = trim($_POST['purpose'] ?? '');
    $termMonths = intval($_POST['term_months'] ?? 12);

    if ($amount <= 0) jsonError('Invalid loan amount');
    if (!$purpose) jsonError('Purpose is required');
    if ($termMonths < 1 || $termMonths > 60) jsonError('Term must be 1-60 months');

    // Check for existing pending loan
    $stmt = $db->prepare("SELECT id FROM loans WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$user['id']]);
    if ($stmt->fetch()) jsonError('You already have a pending loan application');

    // Get default interest rate
    $rate = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'default_interest_rate'")->fetchColumn();
    if (!$rate) $rate = 5.00;

    $stmt = $db->prepare("INSERT INTO loans (user_id, amount, interest_rate, term_months, purpose, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$user['id'], $amount, $rate, $termMonths, $purpose]);

    // Create notification for admin
    $stmt = $db->prepare("INSERT INTO notifications (type, title, message, link, is_global) VALUES ('info', 'New Loan Application', ?, '/bank/admin/', 1)");
    $msg = $user['first_name'] . ' ' . $user['last_name'] . ' applied for a $' . number_format($amount, 2) . ' loan.';
    $stmt->execute([$msg]);

    jsonResponse(['success' => true, 'message' => 'Loan application submitted for review']);
  }

  jsonError('Invalid action');
}

jsonError('Method not allowed', 405);
