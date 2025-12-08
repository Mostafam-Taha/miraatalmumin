<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// تضمين ملف التهيئة
require_once __DIR__ . '/../includes/config.php';

// التحقق من أن المستخدم مسجل الدخول
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'غير مصرح بالوصول. يرجى تسجيل الدخول.',
        'redirect' => '../login.php'
    ]);
    exit;
}

// استخدام معرف المستخدم من الجلسة
$userId = $_SESSION['user_id'] ?? 0;

try {
    // التحقق من وجود الجدول
    $checkTable = $pdo->query("SHOW TABLES LIKE 'telegram_settings'");
    
    if ($checkTable->rowCount() == 0) {
        echo json_encode([
            'success' => true, 
            'settings' => [
                'token' => '',
                'botName' => '',
                'chatId' => '',
                'enableNotifications' => true,
                'enableBackups' => false,
                'enabled' => false
            ]
        ]);
        exit;
    }
    
    // استعلام لجلب إعدادات المستخدم
    $query = "SELECT * FROM telegram_settings WHERE user_id = :user_id LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $userId]);
    
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode([
            'success' => true,
            'settings' => [
                'token' => $row['token'] ?? '',
                'botName' => $row['bot_name'] ?? '',
                'chatId' => $row['chat_id'] ?? '',
                'enableNotifications' => (bool)($row['enable_notifications'] ?? true),
                'enableBackups' => (bool)($row['enable_backups'] ?? false),
                'enabled' => !empty($row['token'])
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'settings' => [
                'token' => '',
                'botName' => '',
                'chatId' => '',
                'enableNotifications' => true,
                'enableBackups' => false,
                'enabled' => false
            ]
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage(),
        'settings' => []
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'خطأ: ' . $e->getMessage(),
        'settings' => []
    ]);
}
?>