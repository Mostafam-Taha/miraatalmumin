<?php
require_once 'includes/config.php';
session_start();

// تأكد من أن المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'غير مسجل الدخول']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // الحصول على معلومات البوت قبل الحذف لإرسال رسالة وداع
    $stmt = $pdo->prepare("SELECT bot_token, chat_id FROM telegram_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings) {
        // إرسال رسالة وداع
        if ($settings['chat_id']) {
            $message = "👋 تم فصل البوت عن تطبيق تتبع الصلاة.";
            $url = "https://api.telegram.org/bot{$settings['bot_token']}/sendMessage";
            
            $data = [
                'chat_id' => $settings['chat_id'],
                'text' => $message
            ];
            
            $options = [
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/json',
                    'content' => json_encode($data),
                    'timeout' => 5
                ]
            ];
            
            $context = stream_context_create($options);
            @file_get_contents($url, false, $context);
        }
        
        // حذف الإعدادات
        $stmt = $pdo->prepare("DELETE FROM telegram_settings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'تم فصل الاتصال بنجاح']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'لا يوجد اتصال فعال']);
    }
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
}
?>