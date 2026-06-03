<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

class PrayerNotifications {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function sendDailyPrayerNotifications() {
        $users = $this->getUsersWithNotifications();
        
        foreach ($users as $user) {
            $prayer_times = $this->getUserPrayerTimes($user['user_id']);
            
            if ($prayer_times) {
                $this->sendUserPrayerSchedule($user, $prayer_times);
            }
        }
    }
    
    public function sendPrayerReminder($prayer_name, $prayer_time, $advance_minutes = 5) {
        $users = $this->getUsersWithNotifications();
        
        $reminder_time = date('H:i', strtotime("-$advance_minutes minutes", strtotime($prayer_time)));
        
        foreach ($users as $user) {
            $this->sendMessage(
                $user['chat_id'],
                "⏰ تذكير: صلاة {$prayer_name} بعد {$advance_minutes} دقائق\nالوقت: {$prayer_time}"
            );
        }
    }
    
    private function getUsersWithNotifications() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT ts.user_id, ts.chat_id, ts.bot_token, us.city
                FROM telegram_settings ts
                LEFT JOIN user_settings us ON ts.user_id = us.user_id
                WHERE ts.notifications_enabled = 1
                AND ts.chat_id IS NOT NULL
                AND ts.bot_token IS NOT NULL
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting users: " . $e->getMessage());
            return [];
        }
    }
    
    private function getUserPrayerTimes($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT prayer_times 
                FROM user_settings 
                WHERE user_id = ? 
                AND DATE(updated_at) = DATE(NOW())
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['prayer_times']) {
                return json_decode($result['prayer_times'], true);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error getting prayer times: " . $e->getMessage());
            return null;
        }
    }
    
    private function sendUserPrayerSchedule($user, $prayer_times) {
        $city = $user['city'] ? " في " . $user['city'] : "";
        $date = date('Y-m-d');
        
        $message = "🕌 مواقيت الصلاة لليوم{$city}\n";
        $message .= "📅 " . $this->formatArabicDate($date) . "\n\n";
        
        $prayers = [
            'fajr' => 'الفجر',
            'dhuhr' => 'الظهر',
            'asr' => 'العصر',
            'maghrib' => 'المغرب',
            'isha' => 'العشاء'
        ];
        
        foreach ($prayers as $key => $name) {
            if (isset($prayer_times[$key])) {
                $message .= "• {$name}: {$prayer_times[$key]}\n";
            }
        }
        
        $message .= "\n📝 استخدم /record لتسجيل الصلوات";
        
        $this->sendMessage($user['chat_id'], $message);
    }
    
    private function sendMessage($chat_id, $text) {
        // في التطبيق الحقيقي، هنا ستقوم بإرسال الرسالة عبر Telegram API
        // هذا مثال مبسط
        error_log("Notification to {$chat_id}: " . substr($text, 0, 100));
        
        // للاستخدام الفعلي:
        // $this->callTelegramApi('sendMessage', [
        //     'chat_id' => $chat_id,
        //     'text' => $text
        // ]);
    }
    
    private function callTelegramApi($method, $data) {
        $url = TELEGRAM_API_URL . $this->bot_token . '/' . $method;
        
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data),
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        
        try {
            $response = file_get_contents($url, false, $context);
            return json_decode($response, true);
        } catch (Exception $e) {
            error_log("Telegram API error: " . $e->getMessage());
            return false;
        }
    }
    
    private function formatArabicDate($date) {
        $months = [
            'January' => 'يناير',
            'February' => 'فبراير',
            'March' => 'مارس',
            'April' => 'أبريل',
            'May' => 'مايو',
            'June' => 'يونيو',
            'July' => 'يوليو',
            'August' => 'أغسطس',
            'September' => 'سبتمبر',
            'October' => 'أكتوبر',
            'November' => 'نوفمبر',
            'December' => 'ديسمبر'
        ];
        
        $english_date = date('d F Y', strtotime($date));
        return str_replace(array_keys($months), array_values($months), $english_date);
    }
}

// الاستخدام
// $notifications = new PrayerNotifications($pdo);
// $notifications->sendDailyPrayerNotifications();
?>