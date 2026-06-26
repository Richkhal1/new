<?php
require_once __DIR__ . '/../includes/config.php';
requireAuth();

$user = getCurrentUser();
if (!$user) jsonError('User not found', 404);

$account = getUserAccount($user['id']);

jsonResponse([
  'user' => $user,
  'account' => $account,
]);
