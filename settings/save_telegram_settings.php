<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
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

// الحصول على البيانات المرسلة
$data = json_decode(file_get_contents('php://input'), true);

// التحقق من البيانات
if (empty($data['token'])) {
    echo json_encode(['success' => false, 'message' => 'Token مطلوب']);
    exit;
}

try {
    // استخدام معرف المستخدم من الجلسة
    $userId = $_SESSION['user_id'] ?? 0;
    $username = $_SESSION['username'] ?? 'مستخدم';
    
    // تنظيف البيانات
    $token = trim($data['token']);
    $botName = trim($data['bot_name'] ?? '');
    $chatId = trim($data['chat_id'] ?? '');
    $enableNotifications = isset($data['enable_notifications']) ? ($data['enable_notifications'] ? 1 : 0) : 1;
    $enableBackups = isset($data['enable_backups']) ? ($data['enable_backups'] ? 1 : 0) : 0;

    // التحقق من صحة Token مع Telegram API
    if (!empty($token)) {
        $botInfoResponse = @file_get_contents("https://api.telegram.org/bot{$token}/getMe");
        if ($botInfoResponse === FALSE) {
            echo json_encode([
                'success' => false, 
                'message' => 'Token غير صالح أو لا يمكن الاتصال بخوادم Telegram'
            ]);
            exit;
        }
        
        $botInfo = json_decode($botInfoResponse, true);
        if (!$botInfo['ok']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Token غير صالح: ' . ($botInfo['description'] ?? 'خطأ غير معروف')
            ]);
            exit;
        }
        
        // إذا لم يكن هناك اسم بوت، نأخذه من API
        if (empty($botName) && isset($botInfo['result']['username'])) {
            $botName = $botInfo['result']['username'];
        }
    }

    // التحقق من وجود الجدول وإنشاءه إذا لم يكن موجودًا
    $checkTable = $pdo->query("SHOW TABLES LIKE 'telegram_settings'");
    if ($checkTable->rowCount() == 0) {
        // إنشاء الجدول مع مفتاح خارجي للمستخدمين
        $createTableSQL = "
        CREATE TABLE telegram_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            bot_name VARCHAR(100),
            chat_id VARCHAR(50),
            enable_notifications BOOLEAN DEFAULT TRUE,
            enable_backups BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($createTableSQL);
    }

    // حفظ أو تحديث البيانات
    $query = "
        INSERT INTO telegram_settings 
        (user_id, token, bot_name, chat_id, enable_notifications, enable_backups, updated_at) 
        VALUES (:user_id, :token, :bot_name, :chat_id, :enable_notifications, :enable_backups, NOW())
        ON DUPLICATE KEY UPDATE 
        token = VALUES(token),
        bot_name = VALUES(bot_name),
        chat_id = VALUES(chat_id),
        enable_notifications = VALUES(enable_notifications),
        enable_backups = VALUES(enable_backups),
        updated_at = NOW()
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $userId,
        ':token' => $token,
        ':bot_name' => $botName,
        ':chat_id' => $chatId,
        ':enable_notifications' => $enableNotifications,
        ':enable_backups' => $enableBackups
    ]);

    // تسجيل النشاط
    $activityQuery = "INSERT INTO user_activities (user_id, activity_type, description) 
                     VALUES (:user_id, 'telegram_settings', 'تم تحديث إعدادات Telegram')";
    $activityStmt = $pdo->prepare($activityQuery);
    $activityStmt->execute([':user_id' => $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ إعدادات Telegram بنجاح',
        'settings' => [
            'token' => $token,
            'botName' => $botName,
            'chatId' => $chatId,
            'enableNotifications' => (bool)$enableNotifications,
            'enableBackups' => (bool)$enableBackups,
            'enabled' => true,
            'username' => $username
        ]
    ]);

} catch (PDOException $e) {
    error_log("Database error in save_telegram_settings.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error in save_telegram_settings.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'خطأ: ' . $e->getMessage()
    ]);
}
?>