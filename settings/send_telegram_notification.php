<?php
require_once 'includes/config.php';
session_start();

function sendPrayerNotification($user_id, $prayer_name, $prayer_time) {
    global $pdo;
    
    try {
        // الحصول على إعدادات Telegram للمستخدم
        $stmt = $pdo->prepare("
            SELECT bot_token, chat_id, notifications_enabled 
            FROM telegram_settings 
            WHERE user_id = ? AND notifications_enabled = 1
        ");
        $stmt->execute([$user_id]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$settings || empty($settings['bot_token']) || empty($settings['chat_id'])) {
            return false;
        }
        
        $message = "🕌 تذكير بوقت الصلاة\n\n";
        $message .= "الصلاة: $prayer_name\n";
        $message .= "الوقت: $prayer_time\n\n";
        $message .= "حان وقت الصلاة، الله أكبر!";
        
        $url = "https://api.telegram.org/bot{$settings['bot_token']}/sendMessage";
        
        $data = [
            'chat_id' => $settings['chat_id'],
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data),
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        return $result !== FALSE;
        
    } catch (PDOException $e) {
        error_log("Telegram notification error: " . $e->getMessage());
        return false;
    }
}

// مثال للاستخدام:
// sendPrayerNotification(1, "صلاة الفجر", "04:30");
?>