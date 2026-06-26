<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = trim($_POST['phone'] ?? '');
$dateOfBirth = $_POST['date_of_birth'] ?? '';
$addressStreet = trim($_POST['address_street'] ?? '');
$addressCity = trim($_POST['address_city'] ?? '');
$addressState = trim($_POST['address_state'] ?? '');
$addressZip = trim($_POST['address_zip'] ?? '');
$ssn = trim(str_replace('-', '', $_POST['ssn'] ?? ''));
$idType = $_POST['id_type'] ?? '';
$idNumber = trim($_POST['id_number'] ?? '');
$employmentStatus = $_POST['employment_status'] ?? '';
$occupation = trim($_POST['occupation'] ?? '');
$employerName = trim($_POST['employer_name'] ?? '');
$accountPurpose = $_POST['account_purpose'] ?? '';
$securityQuestion = $_POST['security_question'] ?? '';
$securityAnswer = trim($_POST['security_answer'] ?? '');
$agreedTos = $_POST['agreed_tos'] ?? '0';
$agreedElectronic = $_POST['agreed_electronic'] ?? '0';

// Required field validation
$required = ['first_name', 'last_name', 'email', 'password', 'phone', 'date_of_birth',
  'address_street', 'address_city', 'address_state', 'address_zip',
  'ssn', 'id_type', 'id_number', 'employment_status', 'account_purpose',
  'security_question', 'security_answer'];
foreach ($required as $f) {
  if (empty($_POST[$f])) jsonError(ucfirst(str_replace('_', ' ', $f)) . ' is required');
}

if (strlen($password) < 8) jsonError('Password must be at least 8 characters');
if (!preg_match('/^\d{9}$/', $ssn)) jsonError('SSN/ITIN must be exactly 9 digits');
if ($agreedTos !== '1') jsonError('You must agree to the Terms of Service');

// Validate age (at least 18)
$dob = new DateTime($dateOfBirth);
$now = new DateTime();
$age = $now->diff($dob)->y;
if ($age < 18) jsonError('You must be at least 18 years old to open an account');

$db = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) jsonError('Email already registered');

$hash = password_hash($password, PASSWORD_BCRYPT);
$fullAddress = "$addressStreet, $addressCity, $addressState $addressZip";

$db->beginTransaction();
try {
  $stmt = $db->prepare("INSERT INTO users (
    first_name, last_name, date_of_birth, email, password, phone,
    address_street, address_city, address_state, address_zip, address,
    ssn, id_type, id_number, employment_status, employer_name, occupation,
    account_purpose, security_question, security_answer,
    agreed_tos, agreed_electronic, kyc_status, status
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending_kyc')");
  $stmt->execute([
    $firstName, $lastName, $dateOfBirth, $email, $hash, $phone,
    $addressStreet, $addressCity, $addressState, $addressZip, $fullAddress,
    $ssn, $idType, $idNumber, $employmentStatus, $employerName, $occupation,
    $accountPurpose, $securityQuestion, $securityAnswer,
    $agreedTos, $agreedElectronic
  ]);
  $userId = $db->lastInsertId();

  // Create account
  $stmt = $db->prepare("INSERT INTO accounts (user_id, account_number, account_type, balance) VALUES (?, ?, 'Legacy Spending Account', 0.00)");
  $stmt->execute([$userId, generateAccountNumber()]);

  // Handle file uploads
  $uploadDir = __DIR__ . '/../signup/uploads/';
  if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

  $docStmt = $db->prepare("INSERT INTO kyc_documents (user_id, document_type, file_path, original_name, file_size) VALUES (?, ?, ?, ?, ?)");

  $idFront = $_FILES['id_front'] ?? null;
  $idBack = $_FILES['id_back'] ?? null;

  if ($idFront && $idFront['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($idFront['name'], PATHINFO_EXTENSION);
    $filename = 'id_front_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file($idFront['tmp_name'], $uploadDir . $filename);
    $docStmt->execute([$userId, 'id_front', 'signup/uploads/' . $filename, $idFront['name'], $idFront['size']]);
  }

  if ($idBack && $idBack['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($idBack['name'], PATHINFO_EXTENSION);
    $filename = 'id_back_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file($idBack['tmp_name'], $uploadDir . $filename);
    $docStmt->execute([$userId, 'id_back', 'signup/uploads/' . $filename, $idBack['name'], $idBack['size']]);
  }

  $db->commit();
  jsonResponse(['success' => true, 'message' => 'Application submitted successfully']);
} catch (Exception $e) {
  $db->rollBack();
  jsonError('Registration failed. Please try again.');
}
