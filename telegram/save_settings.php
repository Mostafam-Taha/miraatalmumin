<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// اتصال بقاعدة البيانات (تعديل حسب إعداداتك)
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "your_database";

// إنشاء اتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// تعيين الترميز
$conn->set_charset("utf8mb4");

// قراءة البيانات
$input = json_decode(file_get_contents('php://input'), true);

// تأمين البيانات
$bot_token = $conn->real_escape_string($input['bot_token'] ?? '');
$bot_username = $conn->real_escape_string($input['bot_username'] ?? '');
$chat_id = $conn->real_escape_string($input['chat_id'] ?? '');
$webhook_url = $conn->real_escape_string($input['webhook_url'] ?? '');
$enable_notifications = intval($input['enable_notifications'] ?? 0);
$is_active = intval($input['is_active'] ?? 0);
$user_id = 1; // يجب تعديل هذا ليتناسب مع نظام المصادقة

// تشفير Token قبل الحفظ (مهم للخصوصية)
$encrypted_token = password_hash($bot_token, PASSWORD_DEFAULT);

// إنشاء أو تحديث السجل في قاعدة البيانات
$sql = "INSERT INTO telegram_settings (user_id, bot_token, bot_username, chat_id, webhook_url, enable_notifications, is_active, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
        bot_token = VALUES(bot_token),
        bot_username = VALUES(bot_username),
        chat_id = VALUES(chat_id),
        webhook_url = VALUES(webhook_url),
        enable_notifications = VALUES(enable_notifications),
        is_active = VALUES(is_active),
        updated_at = NOW()";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issssii", $user_id, $encrypted_token, $bot_username, $chat_id, $webhook_url, $enable_notifications, $is_active);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'تم حفظ الإعدادات بنجاح']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>