<?php
require_once 'config.php';

header('Content-Type: application/json');

$pdo = Config::getInstance()->getConnection();

try {
    $stmt = $pdo->prepare("SELECT id, username, public_key FROM users WHERE public_key IS NOT NULL");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    jsonResponse(true, $users);
} catch (PDOException $e) {
    error_log("Get public keys error: " . $e->getMessage());
    jsonResponse(false, null, 'Failed to get public keys');
}
?>