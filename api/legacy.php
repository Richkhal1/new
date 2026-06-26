<?php
require_once __DIR__ . '/../includes/config.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();
$user = getCurrentUser();

$userId = $user['id'];

// Get all user accounts for reference
$accounts = $db->prepare("SELECT * FROM accounts WHERE user_id = ?");
$accounts->execute([$userId]);
$userAccounts = $accounts->fetchAll();

if ($method === 'GET') {
  $action = $_GET['action'] ?? '';

  // ── Beneficiaries ──
  if ($action === 'beneficiaries') {
    $aid = intval($_GET['account_id'] ?? 0);
    if ($aid) {
      $stmt = $db->prepare("SELECT b.*, a.account_number FROM beneficiaries b JOIN accounts a ON b.account_id = a.id WHERE b.user_id = ? AND b.account_id = ? ORDER BY b.created_at DESC");
      $stmt->execute([$userId, $aid]);
    } else {
      $stmt = $db->prepare("SELECT b.*, a.account_number FROM beneficiaries b JOIN accounts a ON b.account_id = a.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
      $stmt->execute([$userId]);
    }
    $bens = $stmt->fetchAll();
    // Calculate total allocation
    $totalPct = 0;
    foreach ($bens as $b) $totalPct += $b['percentage'];
    jsonResponse(['beneficiaries' => $bens, 'total_percentage' => $totalPct]);
  }

  // ── Legacy Plan ──
  if ($action === 'legacy_plan') {
    $stmt = $db->prepare("SELECT * FROM legacy_plans WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $plan = $stmt->fetch();
    if ($plan) {
      // Include beneficiaries summary
      $bens = $db->prepare("SELECT COUNT(*) as count, COALESCE(SUM(percentage),0) as total_pct FROM beneficiaries WHERE user_id = ? AND status='active'");
      $bens->execute([$userId]);
      $plan['beneficiaries_summary'] = $bens->fetch();
      // Include inheritance value (total balance)
      $bal = $db->prepare("SELECT COALESCE(SUM(balance),0) as total FROM accounts WHERE user_id = ? AND status='active'");
      $bal->execute([$userId]);
      $plan['inheritance_value'] = $bal->fetchColumn();
    }
    jsonResponse($plan ?: ['status' => 'no_plan']);
  }

  // ── Loan Accounts ──
  if ($action === 'loan_accounts') {
    $stmt = $db->prepare("SELECT * FROM loan_accounts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $loanAccts = $stmt->fetchAll();
    foreach ($loanAccts as &$la) {
      $p = $db->prepare("SELECT COUNT(*) as total, COUNT(CASE WHEN status='paid' THEN 1 END) as paid, COALESCE(SUM(CASE WHEN status='paid' THEN amount END),0) as total_paid FROM loan_payments WHERE loan_id IN (SELECT id FROM loans WHERE account_id = ?)");
      $p->execute([$la['id']]);
      $la['payments_summary'] = $p->fetch();
    }
    jsonResponse($loanAccts);
  }

  // ── Dashboard Summary (for user overview) ──
  if ($action === 'dashboard') {
    $benCount = $db->prepare("SELECT COUNT(*) as cnt FROM beneficiaries WHERE user_id = ? AND status='active'");
    $benCount->execute([$userId]);
    $plan = $db->prepare("SELECT status FROM legacy_plans WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $plan->execute([$userId]); $p = $plan->fetch();
    $loans = $db->prepare("SELECT COUNT(*) as cnt FROM loans WHERE user_id = ? AND status IN ('pending','active')");
    $loans->execute([$userId]);
    $laCnt = $db->prepare("SELECT COUNT(*) as cnt FROM loan_accounts WHERE user_id = ?");
    $laCnt->execute([$userId]);
    jsonResponse([
      'beneficiaries' => (int)$benCount->fetchColumn(),
      'has_legacy_plan' => $p ? $p['status'] : 'none',
      'active_loans' => (int)$loans->fetchColumn(),
      'loan_accounts' => (int)$laCnt->fetchColumn(),
    ]);
  }

  jsonError('Invalid action');
}

if ($method === 'POST') {
  $action = $_POST['action'] ?? '';

  // ── Add Beneficiary ──
  if ($action === 'add_beneficiary') {
    $accountId = intval($_POST['account_id'] ?? 0);
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $relationship = trim($_POST['relationship'] ?? '');
    $percentage = floatval($_POST['percentage'] ?? 0);
    $age = intval($_POST['age'] ?? 0);

    if (!$accountId || !$fullName) jsonError('Account and beneficiary name required');
    if ($percentage <= 0 || $percentage > 100) jsonError('Percentage must be 1-100');

    // Verify account belongs to user
    $stmt = $db->prepare("SELECT id FROM accounts WHERE id = ? AND user_id = ?");
    $stmt->execute([$accountId, $userId]);
    if (!$stmt->fetch()) jsonError('Account not found', 404);

    // Check total percentage doesn't exceed 100
    $pctStmt = $db->prepare("SELECT COALESCE(SUM(percentage),0) as total FROM beneficiaries WHERE account_id = ? AND status='active'");
    $pctStmt->execute([$accountId]);
    $currentPct = $pctStmt->fetchColumn();
    if ($currentPct + $percentage > 100) jsonError('Total beneficiary allocation would exceed 100%');

    $stmt = $db->prepare("INSERT INTO beneficiaries (user_id, account_id, full_name, email, phone, relationship, percentage, age) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$userId, $accountId, $fullName, $email, $phone, $relationship, $percentage, $age ?: null]);

    $log = function($d) use ($db) {
      $s = $db->prepare("INSERT INTO admin_log (admin_id, description) VALUES (?, ?)");
      $s->execute([$_SESSION['user_id'], $d]);
    };

    jsonResponse(['success' => true, 'message' => "Beneficiary '$fullName' added with $percentage% allocation"]);
  }

  // ── Remove Beneficiary ──
  if ($action === 'remove_beneficiary') {
    $benId = intval($_POST['beneficiary_id'] ?? 0);
    $stmt = $db->prepare("UPDATE beneficiaries SET status = 'inactive' WHERE id = ? AND user_id = ?");
    $stmt->execute([$benId, $userId]);
    jsonResponse(['success' => true, 'message' => 'Beneficiary removed']);
  }

  // ── Save Legacy Plan ──
  if ($action === 'save_legacy_plan') {
    $planName = trim($_POST['plan_name'] ?? 'My Legacy Plan');
    $instructions = trim($_POST['instructions'] ?? '');
    $executorName = trim($_POST['executor_name'] ?? '');
    $executorEmail = trim($_POST['executor_email'] ?? '');
    $executorPhone = trim($_POST['executor_phone'] ?? '');
    $funeralWishes = trim($_POST['funeral_wishes'] ?? '');

    // Check if plan exists
    $stmt = $db->prepare("SELECT id FROM legacy_plans WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $existing = $stmt->fetch();

    if ($existing) {
      $stmt = $db->prepare("UPDATE legacy_plans SET plan_name=?, instructions=?, executor_name=?, executor_email=?, executor_phone=?, funeral_wishes=?, status='active' WHERE id=?");
      $stmt->execute([$planName, $instructions, $executorName, $executorEmail, $executorPhone, $funeralWishes, $existing['id']]);
    } else {
      $stmt = $db->prepare("INSERT INTO legacy_plans (user_id, plan_name, instructions, executor_name, executor_email, executor_phone, funeral_wishes, status) VALUES (?,?,?,?,?,?,?,'active')");
      $stmt->execute([$userId, $planName, $instructions, $executorName, $executorEmail, $executorPhone, $funeralWishes]);
    }

    jsonResponse(['success' => true, 'message' => 'Legacy plan saved']);
  }

  // ── Create Loan Account ──
  if ($action === 'create_loan_account') {
    $loanType = trim($_POST['loan_type'] ?? 'Personal');
    $principal = floatval($_POST['principal'] ?? 0);
    $termMonths = intval($_POST['term_months'] ?? 12);
    $purpose = trim($_POST['purpose'] ?? '');

    if ($principal <= 0) jsonError('Invalid loan amount');
    if ($termMonths < 1 || $termMonths > 60) jsonError('Term must be 1-60 months');
    if (!$purpose) jsonError('Purpose is required');

    // Get interest rate from settings
    $rate = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'default_interest_rate'")->fetchColumn();
    if (!$rate) $rate = 5.00;

    // Generate account number
    $acctNum = 'LOAN' . str_pad(mt_rand(0, 99999999), 10, '0', STR_PAD_LEFT);

    $db->beginTransaction();
    try {
      $stmt = $db->prepare("INSERT INTO loan_accounts (user_id, account_number, loan_type, principal, interest_rate, term_months, balance, purpose, status) VALUES (?,?,?,?,?,?,0,?,'pending')");
      $stmt->execute([$userId, $acctNum, $loanType, $principal, $rate, $termMonths, $purpose]);
      $laId = $db->lastInsertId();

      // Also create a loan record linked to this account
      $stmt = $db->prepare("INSERT INTO loans (user_id, account_id, amount, interest_rate, term_months, purpose, status) VALUES (?,?,?,?,?,?,'pending')");
      $stmt->execute([$userId, $laId, $principal, $rate, $termMonths, $purpose]);

      $db->commit();
      jsonResponse(['success' => true, 'message' => 'Loan account created', 'account_number' => $acctNum]);
    } catch (Exception $e) {
      $db->rollBack();
      jsonError('Failed to create loan account');
    }
  }

  // ── Disburse Loan (admin side via admin.php, but user can request) ──
  if ($action === 'request_disbursement') {
    $laId = intval($_POST['loan_account_id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM loan_accounts WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$laId, $userId]);
    $la = $stmt->fetch();
    if (!$la) jsonError('Loan account not found or already processed');

    // Update loan account status, create notification for admin
    $stmt = $db->prepare("UPDATE loan_accounts SET status = 'active', disbursed_at = NOW(), balance = principal WHERE id = ?");
    $stmt->execute([$laId]);

    // Credit user's main account
    $mainAcct = $db->prepare("SELECT id, account_number FROM accounts WHERE user_id = ? AND status='active' LIMIT 1");
    $mainAcct->execute([$userId]); $main = $mainAcct->fetch();
    if ($main) {
      $db->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([$la['principal'], $main['id']]);
      $ref = generateReference();
      $db->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?,'credit',?,'Loan disbursement - {$la['loan_type']}',?,'completed')")->execute([$main['id'], $la['principal'], $ref]);
    }

    // Update linked loan
    $db->prepare("UPDATE loans SET status = 'active' WHERE account_id = ? AND status='pending'")->execute([$laId]);

    jsonResponse(['success' => true, 'message' => 'Loan disbursed to your account']);
  }

  jsonError('Invalid action');
}

jsonError('Method not allowed', 405);
