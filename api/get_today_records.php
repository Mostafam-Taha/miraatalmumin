<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مسجل الدخول']);
    exit();
}

require_once '../includes/config.php';

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    $stmt = $pdo->prepare("SELECT prayer_name FROM prayer_records WHERE user_id = :user_id AND date = :date");
    $stmt->execute(['user_id' => $user_id, 'date' => $today]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'records' => $records]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>