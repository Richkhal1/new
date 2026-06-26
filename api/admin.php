<?php
require_once __DIR__ . '/../includes/config.php';
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$db = getDB();

$log = function($desc) use ($db) {
  $stmt = $db->prepare("INSERT INTO admin_log (admin_id, description) VALUES (?, ?)");
  $stmt->execute([$_SESSION['user_id'], $desc]);
};

// ─────────── GET endpoints ───────────
if ($method === 'GET') {
  $action = $_GET['action'] ?? '';

  if ($action === 'stats') {
    $users = $db->query("SELECT COUNT(*) as total, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active FROM users")->fetch();
    $accounts = $db->query("SELECT COUNT(*) as total, COALESCE(SUM(balance),0) as total_balance FROM accounts WHERE status='active'")->fetch();
    $pending = $db->query("SELECT COUNT(*) as total, COALESCE(SUM(amount),0) as total_amount FROM transfers WHERE status='pending'")->fetch();
    $pendingLoans = $db->query("SELECT COUNT(*) as total, COALESCE(SUM(amount),0) as total_amount FROM loans WHERE status='pending'")->fetch();
    $activeLoans = $db->query("SELECT COUNT(*) as total, COALESCE(SUM(amount),0) as total_amount FROM loans WHERE status='active'")->fetch();
    $totalTrf = $db->query("SELECT COUNT(*) as total FROM transfers")->fetch();
    $recent = $db->query("SELECT COUNT(*) as cnt FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch();
    $txVolume = $db->query("SELECT COALESCE(SUM(amount),0) as vol FROM transactions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status='completed'")->fetch();
    jsonResponse(compact('users','accounts','pending','pendingLoans','activeLoans','totalTrf','recent','txVolume'));
  }

  if ($action === 'chart_registrations') {
    $days = intval($_GET['days'] ?? 14);
    $stmt = $db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY DATE(created_at) ORDER BY date");
    $stmt->execute([$days]);
    $rows = $stmt->fetchAll();
    $data = [];
    for ($i = $days - 1; $i >= 0; $i--) {
      $d = date('Y-m-d', strtotime("-$i days"));
      $found = 0;
      foreach ($rows as $r) { if ($r['date'] === $d) { $found = (int)$r['count']; break; } }
      $data[] = ['date' => $d, 'count' => $found];
    }
    jsonResponse($data);
  }

  if ($action === 'chart_transactions') {
    $days = intval($_GET['days'] ?? 14);
    $stmt = $db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count, COALESCE(SUM(amount),0) as volume FROM transactions WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY DATE(created_at) ORDER BY date");
    $stmt->execute([$days]);
    $rows = $stmt->fetchAll();
    $data = [];
    for ($i = $days - 1; $i >= 0; $i--) {
      $d = date('Y-m-d', strtotime("-$i days"));
      $found = ['count' => 0, 'volume' => 0];
      foreach ($rows as $r) { if ($r['date'] === $d) { $found = ['count' => (int)$r['count'], 'volume' => (float)$r['volume']]; break; } }
      $data[] = ['date' => $d, 'count' => $found['count'], 'volume' => $found['volume']];
    }
    jsonResponse($data);
  }

  if ($action === 'chart_loans') {
    $stmt = $db->query("SELECT status, COUNT(*) as count, COALESCE(SUM(amount),0) as total FROM loans GROUP BY status");
    jsonResponse($stmt->fetchAll());
  }

  if ($action === 'recent_activity') {
    $limit = intval($_GET['limit'] ?? 10);
    $recent = $db->query("SELECT u.first_name, u.last_name, 'registration' as type, u.created_at as date FROM users u ORDER BY u.created_at DESC LIMIT $limit")->fetchAll();
    $transfers = $db->query("SELECT t.*, u.first_name, u.last_name FROM transfers t JOIN accounts a ON t.from_account_id = a.id JOIN users u ON a.user_id = u.id ORDER BY t.created_at DESC LIMIT $limit")->fetchAll();
    $loans = $db->query("SELECT l.*, u.first_name, u.last_name FROM loans l JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT $limit")->fetchAll();
    jsonResponse(compact('recent','transfers','loans'));
  }

  if ($action === 'users') {
    $search = trim($_GET['search'] ?? '');
    if ($search) {
      $like = "%$search%";
      $stmt = $db->prepare("SELECT u.*, a.account_number, a.balance, a.account_type, a.status as acct_status FROM users u LEFT JOIN accounts a ON u.id = a.user_id WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR a.account_number LIKE ? ORDER BY u.created_at DESC");
      $stmt->execute([$like, $like, $like, $like]);
    } else {
      $stmt = $db->query("SELECT u.*, a.account_number, a.balance, a.account_type, a.status as acct_status FROM users u LEFT JOIN accounts a ON u.id = a.user_id ORDER BY u.created_at DESC");
    }
    $users = $stmt->fetchAll();
    // Merge accounts for users with multiple accounts, include loan counts
    $result = [];
    foreach ($users as $u) {
      if (!isset($result[$u['id']])) {
        $loanCount = $db->prepare("SELECT COUNT(*) as cnt FROM loans WHERE user_id = ?");
        $loanCount->execute([$u['id']]);
        $result[$u['id']] = $u;
        $result[$u['id']]['accounts'] = [];
        $result[$u['id']]['loans'] = $loanCount->fetch();
      }
      if ($u['account_number']) $result[$u['id']]['accounts'][] = ['account_number' => $u['account_number'], 'balance' => $u['balance'], 'account_type' => $u['account_type'], 'status' => $u['acct_status']];
    }
    jsonResponse(array_values($result));
  }

  if ($action === 'user_detail') {
    $uid = intval($_GET['user_id'] ?? 0);
    if (!$uid) jsonError('User ID required');
    $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, role, status, created_at, updated_at FROM users WHERE id = ?");
    $stmt->execute([$uid]); $user = $stmt->fetch();
    if (!$user) jsonError('User not found', 404);

    $user['accounts'] = $db->prepare("SELECT * FROM accounts WHERE user_id = ?"); $user['accounts']->execute([$uid]); $user['accounts'] = $user['accounts']->fetchAll();
    $user['transactions'] = $db->prepare("SELECT t.*, a.account_number FROM transactions t JOIN accounts a ON t.account_id = a.id WHERE a.user_id = ? ORDER BY t.created_at DESC LIMIT 50"); $user['transactions']->execute([$uid]); $user['transactions'] = $user['transactions']->fetchAll();
    $user['loans'] = $db->prepare("SELECT * FROM loans WHERE user_id = ? ORDER BY created_at DESC"); $user['loans']->execute([$uid]); $user['loans'] = $user['loans']->fetchAll();
    $user['notes'] = $db->prepare("SELECT n.*, u.first_name, u.last_name FROM admin_notes n JOIN users u ON n.created_by = u.id WHERE n.user_id = ? ORDER BY n.created_at DESC"); $user['notes']->execute([$uid]); $user['notes'] = $user['notes']->fetchAll();
    jsonResponse($user);
  }

  if ($action === 'transfers') {
    $s = $_GET['status'] ?? 'pending';
    if (!in_array($s, ['pending','approved','rejected','completed'])) $s = 'pending';
    $stmt = $db->prepare("SELECT t.*, a.account_number as from_account, u.first_name, u.last_name FROM transfers t JOIN accounts a ON t.from_account_id = a.id JOIN users u ON a.user_id = u.id WHERE t.status = ? ORDER BY t.created_at DESC");
    $stmt->execute([$s]); jsonResponse($stmt->fetchAll());
  }
  if ($action === 'all_transfers') {
    $stmt = $db->query("SELECT t.*, a.account_number as from_account, u.first_name, u.last_name FROM transfers t JOIN accounts a ON t.from_account_id = a.id JOIN users u ON a.user_id = u.id ORDER BY t.created_at DESC LIMIT 200");
    jsonResponse($stmt->fetchAll());
  }

  if ($action === 'transactions') {
    $acct = trim($_GET['account'] ?? ''); $type = trim($_GET['type'] ?? ''); $s = trim($_GET['status'] ?? ''); $from = trim($_GET['from'] ?? ''); $to = trim($_GET['to'] ?? '');
    $sql = "SELECT t.*, a.account_number, u.first_name, u.last_name FROM transactions t JOIN accounts a ON t.account_id = a.id JOIN users u ON a.user_id = u.id WHERE 1=1"; $p = [];
    if ($acct) { $sql .= " AND a.account_number LIKE ?"; $p[] = "%$acct%"; }
    if ($type) { $sql .= " AND t.type = ?"; $p[] = $type; }
    if ($s) { $sql .= " AND t.status = ?"; $p[] = $s; }
    if ($from) { $sql .= " AND t.created_at >= ?"; $p[] = $from; }
    if ($to) { $sql .= " AND t.created_at <= ?"; $p[] = $to . ' 23:59:59'; }
    $sql .= " ORDER BY t.created_at DESC LIMIT 200";
    $stmt = $db->prepare($sql); $stmt->execute($p); jsonResponse($stmt->fetchAll());
  }

  if ($action === 'loans') {
    $s = $_GET['status'] ?? '';
    $sql = "SELECT l.*, u.first_name, u.last_name, u.email FROM loans l JOIN users u ON l.user_id = u.id";
    $p = [];
    if ($s && $s !== 'all') { $sql .= " WHERE l.status = ?"; $p[] = $s; }
    $sql .= " ORDER BY l.created_at DESC";
    $stmt = $db->prepare($sql); $stmt->execute($p); $loans = $stmt->fetchAll();
    foreach ($loans as &$l) {
      $stmt2 = $db->prepare("SELECT COUNT(*) as paid, COALESCE(SUM(amount),0) as total_paid FROM loan_payments WHERE loan_id = ? AND status = 'paid'");
      $stmt2->execute([$l['id']]); $l['payments_summary'] = $stmt2->fetch();
    }
    jsonResponse($loans);
  }

  if ($action === 'loan_detail') {
    $lid = intval($_GET['loan_id'] ?? 0);
    $stmt = $db->prepare("SELECT l.*, u.first_name, u.last_name, u.email, u.phone FROM loans l JOIN users u ON l.user_id = u.id WHERE l.id = ?");
    $stmt->execute([$lid]); $loan = $stmt->fetch();
    if (!$loan) jsonError('Loan not found', 404);
    $p = $db->prepare("SELECT * FROM loan_payments WHERE loan_id = ? ORDER BY due_date");
    $p->execute([$lid]); $loan['payments'] = $p->fetchAll();
    jsonResponse($loan);
  }

  if ($action === 'announcements') {
    $stmt = $db->query("SELECT a.*, u.first_name, u.last_name FROM announcements a JOIN users u ON a.created_by = u.id ORDER BY a.created_at DESC");
    jsonResponse($stmt->fetchAll());
  }

  if ($action === 'settings') {
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    $s = []; foreach ($stmt->fetchAll() as $r) $s[$r['setting_key']] = $r['setting_value'];
    jsonResponse($s);
  }

  if ($action === 'activity') {
    $stmt = $db->query("SELECT al.*, u.first_name, u.last_name FROM admin_log al JOIN users u ON al.admin_id = u.id ORDER BY al.created_at DESC LIMIT 200");
    jsonResponse($stmt->fetchAll());
  }

  jsonError('Invalid action');
}

// ─────────── POST endpoints ───────────
if ($method === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'create_user') {
    $first = trim($_POST['first_name'] ?? ''); $last = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? ''); $deposit = floatval($_POST['initial_deposit'] ?? 0);
    if (!$first || !$last || !$email || !$password) jsonError('Required fields missing');
    if (strlen($password) < 8) jsonError('Password must be 8+ characters');
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $db->beginTransaction();
    try {
      $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, password, phone, role, status) VALUES (?,?,?,?,?,'user','active')");
      $stmt->execute([$first,$last,$email,$hash,$phone]); $uid = $db->lastInsertId();
      $acctNum = generateAccountNumber();
      $stmt = $db->prepare("INSERT INTO accounts (user_id, account_number, account_type, balance) VALUES (?,?,'Legacy Spending Account',?)");
      $stmt->execute([$uid,$acctNum,max(0,$deposit)]);
      if ($deposit > 0) {
        $ref = generateReference();
        $db->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?, 'deposit', ?, 'Initial deposit', ?, 'completed')")->execute([$db->lastInsertId(),$deposit,$ref]);
      }
      $db->commit();
      $log("Created user $first $last ($email) account $acctNum" . ($deposit>0?" with \$$deposit deposit":""));
      jsonResponse(['success'=>true,'message'=>'User created','account_number'=>$acctNum]);
    } catch (Exception $e) { $db->rollBack(); jsonError($e->getCode()==23000?'Email already exists':'Database error'); }
  }

  if ($action === 'fund' || $action === 'credit') {
    $uid = intval($_POST['user_id'] ?? 0); $amount = floatval($_POST['amount'] ?? 0);
    $desc = trim($_POST['description'] ?? 'Admin credit');
    if ($amount <= 0) jsonError('Invalid amount');
    $stmt = $db->prepare("SELECT id, account_number FROM accounts WHERE user_id = ? AND status='active'");
    $stmt->execute([$uid]); $acct = $stmt->fetch();
    if (!$acct) jsonError('No active account found');
    $db->beginTransaction();
    try {
      $db->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([$amount,$acct['id']]);
      $ref = generateReference();
      $db->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?,'credit',?,?,?,'completed')")->execute([$acct['id'],$amount,$desc,$ref]);
      $db->commit();
      $log("Credited \$$amount to {$acct['account_number']} ($desc)");
      jsonResponse(['success'=>true,'message'=>"Account credited with \$$amount",'reference'=>$ref]);
    } catch (Exception $e) { $db->rollBack(); jsonError('Credit failed'); }
  }

  if ($action === 'debit') {
    $uid = intval($_POST['user_id'] ?? 0); $amount = floatval($_POST['amount'] ?? 0);
    $desc = trim($_POST['description'] ?? 'Admin debit');
    if ($amount <= 0) jsonError('Invalid amount');
    $stmt = $db->prepare("SELECT id, balance, account_number FROM accounts WHERE user_id = ? AND status='active'");
    $stmt->execute([$uid]); $acct = $stmt->fetch();
    if (!$acct) jsonError('No active account found');
    if ($amount > $acct['balance']) jsonError('Insufficient balance');
    $db->beginTransaction();
    try {
      $db->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?")->execute([$amount,$acct['id']]);
      $ref = generateReference();
      $db->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?,'debit',?,?,?,'completed')")->execute([$acct['id'],$amount,$desc,$ref]);
      $db->commit();
      $log("Debited \$$amount from {$acct['account_number']} ($desc)");
      jsonResponse(['success'=>true,'message'=>"Account debited with \$$amount",'reference'=>$ref]);
    } catch (Exception $e) { $db->rollBack(); jsonError('Debit failed'); }
  }

  if ($action === 'update_user_status') {
    $uid = intval($_POST['user_id'] ?? 0); $ns = $_POST['status'] ?? '';
    if (!in_array($ns, ['active','suspended','pending'])) jsonError('Invalid status');
    $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$ns, $uid]);
    if ($stmt->rowCount()) { $log("Changed user #$uid status to $ns"); jsonResponse(['success'=>true,'message'=>"User status changed to $ns"]); }
    jsonError('User not found');
  }

  if ($action === 'delete_user') {
    $uid = intval($_POST['user_id'] ?? 0);
    if ($uid == $_SESSION['user_id']) jsonError('Cannot delete yourself');
    $stmt = $db->prepare("UPDATE users SET status='suspended', email=CONCAT('closed_',id,'_',email) WHERE id=? AND role='user'");
    $stmt->execute([$uid]);
    if ($stmt->rowCount()) {
      $db->prepare("UPDATE accounts SET status='closed' WHERE user_id=?")->execute([$uid]);
      $log("Closed user account #$uid");
      jsonResponse(['success'=>true,'message'=>'User account closed']);
    }
    jsonError('User not found or is admin');
  }

  if ($action === 'approve_transfer') {
    $tid = intval($_POST['transfer_id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM transfers WHERE id = ? AND status = 'pending'"); $stmt->execute([$tid]); $t = $stmt->fetch();
    if (!$t) jsonError('Transfer not found or already processed');
    $db->beginTransaction();
    try {
      $fa = $db->prepare("SELECT id, balance, account_number FROM accounts WHERE id = ?"); $fa->execute([$t['from_account_id']]); $from = $fa->fetch();
      if ($t['amount'] > $from['balance']) jsonError('Insufficient funds');
      $db->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?")->execute([$t['amount'], $from['id']]);
      $ta = $db->prepare("SELECT id, user_id FROM accounts WHERE account_number = ?"); $ta->execute([$t['to_account_number']]); $to = $ta->fetch();
      if ($to) {
        $db->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([$t['amount'], $to['id']]);
        $ref = generateReference();
        $db->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?,'transfer_in',?,?,?,'completed')")->execute([$to['id'],$t['amount'],"Transfer from {$from['account_number']}",$ref]);
      }
      $db->prepare("UPDATE transfers SET status='approved', processed_at=NOW(), processed_by=? WHERE id=?")->execute([$_SESSION['user_id'],$tid]);
      $ref = generateReference();
      $db->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?,'transfer_out',?,?,?,'completed')")->execute([$from['id'],$t['amount'],"Transfer to {$t['to_account_number']}",$ref]);
      $db->commit();
      $log("Approved transfer #$tid of \${$t['amount']}");
      jsonResponse(['success'=>true,'message'=>'Transfer approved']);
    } catch (Exception $e) { $db->rollBack(); jsonError('Approval failed: '.$e->getMessage()); }
  }

  if ($action === 'reject_transfer') {
    $tid = intval($_POST['transfer_id'] ?? 0); $reason = trim($_POST['reason'] ?? 'Rejected by admin');
    $stmt = $db->prepare("UPDATE transfers SET status='rejected', processed_at=NOW(), processed_by=? WHERE id=? AND status='pending'");
    $stmt->execute([$_SESSION['user_id'], $tid]);
    if ($stmt->rowCount()) { $log("Rejected transfer #$tid ($reason)"); jsonResponse(['success'=>true,'message'=>'Transfer rejected']); }
    jsonError('Transfer not found');
  }

  // ── Loan actions ──
  if ($action === 'approve_loan') {
    $lid = intval($_POST['loan_id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM loans WHERE id = ? AND status = 'pending'"); $stmt->execute([$lid]); $loan = $stmt->fetch();
    if (!$loan) jsonError('Loan not found or already processed');
    $db->beginTransaction();
    try {
      // Calculate monthly payment (simple amortization)
      $r = ($loan['interest_rate'] / 100) / 12;
      $n = $loan['term_months'];
      $pv = $loan['amount'];
      $mpmt = $r > 0 ? $pv * ($r * pow(1+$r, $n)) / (pow(1+$r, $n) - 1) : $pv / $n;
      $totalRepay = $mpmt * $n;

      $db->prepare("UPDATE loans SET status='active', monthly_payment=?, total_repayment=?, approved_by=?, approved_at=NOW() WHERE id=?")
         ->execute([round($mpmt,2), round($totalRepay,2), $_SESSION['user_id'], $lid]);

      // Generate payment schedule
      $dueDate = new DateTime();
      $payStmt = $db->prepare("INSERT INTO loan_payments (loan_id, amount, due_date, status) VALUES (?, ?, ?, 'pending')");
      for ($i = 1; $i <= $n; $i++) {
        $dueDate->modify('+1 month');
        $payStmt->execute([$lid, round($mpmt,2), $dueDate->format('Y-m-d')]);
      }

      // Credit the user's account
      $acct = $db->prepare("SELECT id, account_number FROM accounts WHERE user_id = ? AND status='active'"); $acct->execute([$loan['user_id']]); $acct = $acct->fetch();
      if ($acct) {
        $db->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([$loan['amount'], $acct['id']]);
        $ref = generateReference();
        $db->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?,'credit',?,'Loan disbursement',?,'completed')")->execute([$acct['id'],$loan['amount'],$ref]);
      }
      $db->commit();
      $log("Approved loan #$lid of \${$loan['amount']} for user #{$loan['user_id']}");
      jsonResponse(['success'=>true,'message'=>'Loan approved','monthly_payment'=>round($mpmt,2),'total_repayment'=>round($totalRepay,2)]);
    } catch (Exception $e) { $db->rollBack(); jsonError('Loan approval failed: '.$e->getMessage()); }
  }

  if ($action === 'reject_loan') {
    $lid = intval($_POST['loan_id'] ?? 0);
    $note = trim($_POST['note'] ?? 'Not approved');
    $stmt = $db->prepare("UPDATE loans SET status='rejected', admin_note=? WHERE id=? AND status='pending'");
    $stmt->execute([$note, $lid]);
    if ($stmt->rowCount()) { $log("Rejected loan #$lid ($note)"); jsonResponse(['success'=>true,'message'=>'Loan rejected']); }
    jsonError('Loan not found');
  }

  if ($action === 'record_loan_payment') {
    $pid = intval($_POST['payment_id'] ?? 0);
    $stmt = $db->prepare("SELECT lp.*, l.user_id, l.id as loan_id FROM loan_payments lp JOIN loans l ON lp.loan_id = l.id WHERE lp.id = ? AND lp.status = 'pending'");
    $stmt->execute([$pid]); $p = $stmt->fetch();
    if (!$p) jsonError('Payment not found or already paid');
    $db->beginTransaction();
    try {
      $acct = $db->prepare("SELECT id, balance FROM accounts WHERE user_id = ? AND status='active'"); $acct->execute([$p['user_id']]); $acct = $acct->fetch();
      if (!$acct || $acct['balance'] < $p['amount']) jsonError('Insufficient balance for payment');
      $db->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?")->execute([$p['amount'], $acct['id']]);
      $ref = generateReference();
      $db->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?,'debit',?,'Loan payment',?,'completed')")->execute([$acct['id'],$p['amount'],$ref]);
      $db->prepare("UPDATE loan_payments SET status='paid', paid_date=NOW(), transaction_id=(SELECT MAX(id) FROM transactions WHERE reference=?) WHERE id=?")->execute([$ref, $pid]);
      $db->prepare("UPDATE loans SET paid_so_far = paid_so_far + ? WHERE id = ?")->execute([$p['amount'], $p['loan_id']]);
      // Check if loan is fully paid
      $loan = $db->prepare("SELECT total_repayment, paid_so_far FROM loans WHERE id = ?"); $loan->execute([$p['loan_id']]); $loan = $loan->fetch();
      if ($loan && $loan['paid_so_far'] >= $loan['total_repayment']) {
        $db->prepare("UPDATE loans SET status='paid' WHERE id = ?")->execute([$p['loan_id']]);
      }
      $db->commit();
      $log("Recorded loan payment of \${$p['amount']} for loan #{$p['loan_id']}");
      jsonResponse(['success'=>true,'message'=>'Payment recorded']);
    } catch (Exception $e) { $db->rollBack(); jsonError('Payment failed: '.$e->getMessage()); }
  }

  // ── Announcements ──
  if ($action === 'create_announcement') {
    $title = trim($_POST['title'] ?? ''); $message = trim($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'normal';
    if (!$title || !$message) jsonError('Title and message required');
    $stmt = $db->prepare("INSERT INTO announcements (title, message, priority, created_by) VALUES (?,?,?,?)");
    $stmt->execute([$title, $message, $priority, $_SESSION['user_id']]);
    $log("Created announcement: $title");
    jsonResponse(['success'=>true,'message'=>'Announcement created']);
  }

  if ($action === 'toggle_announcement') {
    $aid = intval($_POST['announcement_id'] ?? 0);
    $stmt = $db->prepare("UPDATE announcements SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$aid]);
    if ($stmt->rowCount()) jsonResponse(['success'=>true,'message'=>'Announcement toggled']);
    jsonError('Announcement not found');
  }

  if ($action === 'delete_announcement') {
    $aid = intval($_POST['announcement_id'] ?? 0);
    $db->prepare("DELETE FROM announcement_views WHERE announcement_id = ?")->execute([$aid]);
    $db->prepare("DELETE FROM announcements WHERE id = ?")->execute([$aid]);
    jsonResponse(['success'=>true,'message'=>'Announcement deleted']);
  }

  // ── Admin Notes ──
  if ($action === 'add_note') {
    $uid = intval($_POST['user_id'] ?? 0); $note = trim($_POST['note'] ?? '');
    if (!$uid || !$note) jsonError('User ID and note required');
    $stmt = $db->prepare("INSERT INTO admin_notes (user_id, note, created_by) VALUES (?,?,?)");
    $stmt->execute([$uid, $note, $_SESSION['user_id']]);
    jsonResponse(['success'=>true,'message'=>'Note added']);
  }

  // ── Settings ──
  if ($action === 'update_settings') {
    $settings = $_POST['settings'] ?? [];
    if (!is_array($settings)) jsonError('Invalid settings');
    foreach ($settings as $k => $v) {
      $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$k, $v]);
    }
    $log("Updated bank settings");
    jsonResponse(['success'=>true,'message'=>'Settings updated']);
  }

  if ($action === 'bulk_fund') {
    $amount = floatval($_POST['amount'] ?? 0);
    $userIds = $_POST['user_ids'] ?? [];
    $desc = trim($_POST['description'] ?? 'Bulk credit');
    if ($amount <= 0 || !is_array($userIds) || !count($userIds)) jsonError('Invalid request');
    $count = 0;
    foreach ($userIds as $uid) {
      $uid = intval($uid);
      $acct = $db->prepare("SELECT id FROM accounts WHERE user_id = ? AND status='active'"); $acct->execute([$uid]); $acct = $acct->fetch();
      if ($acct) {
        $db->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?")->execute([$amount, $acct['id']]);
        $db->prepare("INSERT INTO transactions (account_id, type, amount, description, reference, status) VALUES (?,'credit',?,?,?,'completed')")->execute([$acct['id'],$amount,$desc,generateReference()]);
        $count++;
      }
    }
    $log("Bulk credited \$$amount to $count users");
    jsonResponse(['success'=>true,'message'=>"Credited $count users with \$$amount"]);
  }

  jsonError('Invalid action');
}

jsonError('Method not allowed', 405);
