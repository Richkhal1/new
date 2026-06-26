<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = trim($_POST['phone'] ?? '');

if (!$firstName || !$lastName || !$email || !$password) jsonError('Required fields missing');
if (strlen($password) < 8) jsonError('Password must be at least 8 characters');

$stmt = getDB()->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) jsonError('Email already registered');

$hash = password_hash($password, PASSWORD_BCRYPT);

getDB()->beginTransaction();
try {
  $stmt = getDB()->prepare("INSERT INTO users (first_name, last_name, email, password, phone, role, status) VALUES (?, ?, ?, ?, ?, 'user', 'active')");
  $stmt->execute([$firstName, $lastName, $email, $hash, $phone]);
  $userId = getDB()->lastInsertId();

  $stmt = getDB()->prepare("INSERT INTO accounts (user_id, account_number, account_type, balance) VALUES (?, ?, 'Legacy Spending Account', 0.00)");
  $stmt->execute([$userId, generateAccountNumber()]);

  getDB()->commit();
  jsonResponse(['success' => true, 'message' => 'Account created successfully']);
} catch (Exception $e) {
  getDB()->rollBack();
  jsonError('Registration failed. Please try again.');
}
