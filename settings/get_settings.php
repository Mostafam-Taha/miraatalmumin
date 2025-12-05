<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['block_all_groups' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT block_all_groups FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        echo json_encode($settings);
    } else {
        echo json_encode(['block_all_groups' => 0]);
    }
} catch (PDOException $e) {
    echo json_encode(['block_all_groups' => 0]);
}
?>