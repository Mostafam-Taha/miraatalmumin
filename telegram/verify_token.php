<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// تأمين البيانات
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// التحقق من أن الطريقة POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// قراءة البيانات
$input = json_decode(file_get_contents('php://input'), true);
$bot_token = isset($input['bot_token']) ? sanitize_input($input['bot_token']) : '';

if (empty($bot_token)) {
    echo json_encode(['success' => false, 'message' => 'Bot token is required']);
    exit;
}

// التحقق من صحة Token عبر Telegram API
$telegram_api_url = "https://api.telegram.org/bot{$bot_token}/getMe";

// استخدام cURL للاتصال ب API Telegram
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $telegram_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    
    if ($data['ok']) {
        $bot_username = $data['result']['username'];
        $bot_name = $data['result']['first_name'];
        
        // إنشاء رابط Webhook
        $webhook_url = "https://your-domain.com/telegram/webhook.php?token=" . md5($bot_token);
        
        echo json_encode([
            'success' => true,
            'bot_username' => $bot_username,
            'bot_name' => $bot_name,
            'webhook_url' => $webhook_url,
            'message' => 'تم التحقق من البوت بنجاح'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid bot token']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'فشل في الاتصال بـ Telegram API']);
}
?>