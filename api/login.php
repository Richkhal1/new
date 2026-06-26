<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) jsonError('Email and password required');

$stmt = getDB()->prepare("SELECT id, password, role, status FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
  jsonError('Invalid email or password', 401);
}

if ($user['status'] === 'suspended') jsonError('Account suspended', 403);
if ($user['status'] === 'pending' || $user['status'] === 'pending_kyc') jsonError('Your account is pending identity verification. Please check your email for instructions or contact support.', 403);

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['role'] = $user['role'];

$redirect = $user['role'] === 'admin' ? '/bank/admin/' : '/bank/user/';

jsonResponse([
  'success' => true,
  'role' => $user['role'],
  'redirect' => $redirect,
]);
