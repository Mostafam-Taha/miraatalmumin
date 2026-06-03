<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$bot_token = isset($input['bot_token']) ? sanitize_input($input['bot_token']) : '';

if (empty($bot_token)) {
    echo json_encode(['success' => false, 'message' => 'Bot token is required']);
    exit;
}

// الحصول على آخر التحديثات
$telegram_api_url = "https://api.telegram.org/bot{$bot_token}/getUpdates?offset=-1";

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
    
    if ($data['ok'] && count($data['result']) > 0) {
        $last_update = end($data['result']);
        $chat_id = '';
        
        // استخراج Chat ID من الرسالة
        if (isset($last_update['message'])) {
            $chat_id = $last_update['message']['chat']['id'];
        } elseif (isset($last_update['callback_query'])) {
            $chat_id = $last_update['callback_query']['message']['chat']['id'];
        } elseif (isset($last_update['channel_post'])) {
            $chat_id = $last_update['channel_post']['chat']['id'];
        }
        
        if ($chat_id) {
            echo json_encode([
                'success' => true,
                'chat_id' => $chat_id,
                'message' => 'تم الحصول على Chat ID بنجاح'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'لم يتم العثور على Chat ID في التحديثات']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'أرسل رسالة للبوت أولاً ثم حاول مرة أخرى']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'فشل في الاتصال بـ Telegram API']);
}
?>