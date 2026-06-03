<?php
require_once '../includes/config.php';
session_start();

// تأكد من أن المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'غير مسجل الدخول']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM telegram_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        $response = [
            'connected' => true,
            'token' => $settings['bot_token'],
            'bot_username' => $settings['bot_username'],
            'bot_name' => $settings['bot_name'],
            'chat_id' => $settings['chat_id'],
            'notifications_enabled' => $settings['notifications_enabled']
        ];
    } else {
        $response = [
            'connected' => false
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
}
?>