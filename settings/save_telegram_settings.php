<?php
require_once '../includes/config.php';
session_start();

// تأكد من أن المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'غير مسجل الدخول']);
    exit();
}

// قراءة بيانات JSON من الطلب
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

if (!isset($input['token']) || empty($input['token'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Token مطلوب']);
    exit();
}

$token = trim($input['token']);
$chat_id = isset($input['chat_id']) ? trim($input['chat_id']) : null;
$enable_notifications = isset($input['enable_notifications']) ? (int)$input['enable_notifications'] : 1;

// دالة cURL للاتصال بـ Telegram API
function callTelegramAPI($url, $postData = null) {
    $ch = curl_init();
    
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; TelegramBot/1.0)'
    ];
    
    if ($postData !== null) {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($postData);
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
    }
    
    curl_setopt_array($ch, $options);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return ['success' => false, 'message' => 'cURL Error: ' . $curlError];
    }
    
    if ($httpCode !== 200) {
        return ['success' => false, 'message' => 'HTTP Error: ' . $httpCode];
    }
    
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['ok']) || !$data['ok']) {
        $errorMsg = isset($data['description']) ? $data['description'] : 'Unknown error';
        return ['success' => false, 'message' => $errorMsg];
    }
    
    return ['success' => true, 'data' => $data];
}

// دالة للحصول على معلومات البوت
function getBotInfo($token) {
    $url = "https://api.telegram.org/bot{$token}/getMe";
    $result = callTelegramAPI($url);
    
    if (!$result['success']) {
        return ['success' => false, 'message' => $result['message']];
    }
    
    $bot = $result['data']['result'];
    
    return [
        'success' => true,
        'id' => $bot['id'],
        'username' => $bot['username'],
        'first_name' => $bot['first_name']
    ];
}

// دالة للحصول على Chat ID من آخر تحديث
function getChatIdFromToken($token) {
    $url = "https://api.telegram.org/bot{$token}/getUpdates";
    $result = callTelegramAPI($url);
    
    if (!$result['success'] || empty($result['data']['result'])) {
        return null;
    }
    
    // الحصول على آخر chat_id من التحديثات
    $updates = $result['data']['result'];
    $lastUpdate = end($updates);
    
    if (isset($lastUpdate['message']['chat']['id'])) {
        return $lastUpdate['message']['chat']['id'];
    }
    
    if (isset($lastUpdate['callback_query']['message']['chat']['id'])) {
        return $lastUpdate['callback_query']['message']['chat']['id'];
    }
    
    return null;
}

// دالة لإرسال رسالة عبر Telegram
function sendTelegramMessage($token, $chat_id, $message) {
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $result = callTelegramAPI($url, $data);
    return $result['success'];
}

try {
    // التحقق من صحة الـ Token والحصول على معلومات البوت
    $bot_info = getBotInfo($token);
    
    if (!$bot_info['success']) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Token غير صالح: ' . $bot_info['message']]);
        exit();
    }
    
    // إذا لم يتم إدخال Chat ID، نحاول الحصول عليه تلقائياً
    if (!$chat_id) {
        $chat_id = getChatIdFromToken($token);
    }
    
    // التحقق من وجود جدول telegram_settings
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'telegram_settings'");
        if ($checkTable->rowCount() == 0) {
            // إنشاء الجدول إذا لم يكن موجوداً
            $createTable = "
                CREATE TABLE IF NOT EXISTS `telegram_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `bot_token` varchar(255) NOT NULL,
                    `bot_username` varchar(255) DEFAULT NULL,
                    `bot_name` varchar(255) DEFAULT NULL,
                    `chat_id` varchar(100) DEFAULT NULL,
                    `notifications_enabled` tinyint(1) DEFAULT 1,
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `user_id` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ";
            $pdo->exec($createTable);
        }
    } catch (PDOException $e) {
        // الجدول موجود بالفعل أو حدث خطأ
    }
    
    // التحقق مما إذا كان هناك إعدادات موجودة للمستخدم
    $stmt = $pdo->prepare("SELECT id FROM telegram_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // تحديث الإعدادات الموجودة
        $stmt = $pdo->prepare("
            UPDATE telegram_settings 
            SET bot_token = ?, 
                bot_username = ?, 
                bot_name = ?, 
                chat_id = ?, 
                notifications_enabled = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?
        ");
        $stmt->execute([
            $token,
            $bot_info['username'],
            $bot_info['first_name'],
            $chat_id,
            $enable_notifications,
            $user_id
        ]);
    } else {
        // إدخال إعدادات جديدة
        $stmt = $pdo->prepare("
            INSERT INTO telegram_settings 
            (user_id, bot_token, bot_username, bot_name, chat_id, notifications_enabled) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $token,
            $bot_info['username'],
            $bot_info['first_name'],
            $chat_id,
            $enable_notifications
        ]);
    }
    
    // إرسال رسالة تأكيد إلى البوت إذا كان لدينا Chat ID
    if ($chat_id) {
        sendTelegramMessage($token, $chat_id, "✅ تم ربط البوت بنجاح مع تطبيق مرآة المؤمن!\n\nيمكنك الآن استلام إشعارات الصلاة تلقائياً.");
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ الإعدادات بنجاح',
        'bot_username' => $bot_info['username'],
        'bot_name' => $bot_info['first_name'],
        'chat_id' => $chat_id
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'خطأ عام: ' . $e->getMessage()]);
}
?>