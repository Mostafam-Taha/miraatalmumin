<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['public_key'])) {
    jsonResponse(false, null, 'Missing public key');
}

$pdo = Config::getInstance()->getConnection();
$auth = new Auth($pdo);
$userId = $auth->validateSession();

if (!$userId) {
    jsonResponse(false, null, 'Not authenticated');
}

try {
    $stmt = $pdo->prepare("UPDATE users SET public_key = ? WHERE id = ?");
    $stmt->execute([$data['public_key'], $userId]);
    jsonResponse(true);
} catch (PDOException $e) {
    jsonResponse(false, null, 'Failed to save public key');
}