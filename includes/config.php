<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

define('DB_HOST', 'localhost');
define('DB_USER', 'legacy_user');
define('DB_PASS', 'legacy_pass_2025');
define('DB_NAME', 'legacy_bank');

function getDB() {
  static $pdo = null;
  if ($pdo === null) {
    try {
      $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]);
    } catch (PDOException $e) {
      http_response_code(500);
      die(json_encode(['error' => 'Database connection failed']));
    }
  }
  return $pdo;
}

function jsonResponse($data, $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

function jsonError($msg, $code = 400) {
  jsonResponse(['error' => $msg], $code);
}

function isLoggedIn() {
  return isset($_SESSION['user_id']);
}

function requireAuth() {
  if (!isLoggedIn()) {
    jsonError('Unauthorized', 401);
  }
}

function requireAdmin() {
  requireAuth();
  if ($_SESSION['role'] !== 'admin') {
    jsonError('Forbidden', 403);
  }
}

function getCurrentUser() {
  if (!isLoggedIn()) return null;
  $stmt = getDB()->prepare("SELECT id, first_name, last_name, email, phone, role, status, created_at FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  return $stmt->fetch();
}

function getUserAccount($userId) {
  $stmt = getDB()->prepare("SELECT * FROM accounts WHERE user_id = ?");
  $stmt->execute([$userId]);
  return $stmt->fetch();
}

function generateAccountNumber() {
  return '10' . str_pad(mt_rand(0, 999999999), 10, '0', STR_PAD_LEFT);
}

function generateReference() {
  return 'TXN' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}
