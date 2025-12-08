<?php
session_start();
require_once '../includes/config.php';

// دالة للتحقق من صحة المدخلات
function validateFeedback($type, $message) {
    $allowedTypes = ['suggestion', 'complaint', 'bug', 'thanks'];
    
    if (!in_array($type, $allowedTypes)) {
        return 'نوع الرسالة غير صالح';
    }
    
    if (strlen($message) > 1000) {
        return 'الرسالة طويلة جداً (الحد الأقصى 1000 حرف)';
    }
    
    return null;
}

// التحقق من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صحيحة']);
    exit;
}

// قراءة البيانات المرسلة
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// التحقق من وجود البيانات المطلوبة
if (!isset($data['type']) || !isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit;
}

// التحقق من صحة البيانات
$type = trim($data['type']);
$message = isset($data['message']) ? trim($data['message']) : '';
$user_id = intval($data['user_id']);
$user_name = isset($data['user_name']) ? trim($data['user_name']) : 'مستخدم';
$user_email = isset($data['user_email']) ? trim($data['user_email']) : '';

// التحقق من صحة التغذية الراجعة
$validationError = validateFeedback($type, $message);
if ($validationError) {
    echo json_encode(['success' => false, 'message' => $validationError]);
    exit;
}

try {
    // إنشاء جدول feedbacks إذا لم يكن موجوداً
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS feedbacks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_name VARCHAR(255),
        user_email VARCHAR(255),
        feedback_type ENUM('suggestion', 'complaint', 'bug', 'thanks') NOT NULL,
        message TEXT,
        status ENUM('new', 'read', 'in_progress', 'resolved') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_type (feedback_type),
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    
    $pdo->exec($createTableSQL);
    
    // إدخال التغذية الراجعة في قاعدة البيانات
    $stmt = $pdo->prepare("
        INSERT INTO feedbacks (user_id, user_name, user_email, feedback_type, message, status) 
        VALUES (?, ?, ?, ?, ?, 'new')
    ");
    
    $stmt->execute([
        $user_id,
        $user_name,
        $user_email,
        $type,
        $message
    ]);
    
    $feedback_id = $pdo->lastInsertId();
    
    // هنا يمكنك إضافة إرسال إشعار إلى الإدارة أو بريد إلكتروني
    sendFeedbackNotification($type, $user_name, $message, $feedback_id);
    
    echo json_encode([
        'success' => true, 
        'message' => 'تم إرسال ملاحظتك بنجاح',
        'feedback_id' => $feedback_id
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in submit_feedback.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'حدث خطأ في الخادم']);
} catch (Exception $e) {
    error_log("General error in submit_feedback.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'حدث خطأ غير متوقع']);
}

// دالة لإرسال إشعار (يمكنك تكاملها مع Telegram أو Email)
function sendFeedbackNotification($type, $user_name, $message, $feedback_id) {
    $type_names = [
        'suggestion' => 'اقتراح',
        'complaint' => 'شكوى',
        'bug' => 'خطأ',
        'thanks' => 'شكر'
    ];
    
    $notification = "📝 *تغذية راجعة جديدة*\n\n";
    $notification .= "👤 *المستخدم:* $user_name\n";
    $notification .= "📋 *النوع:* {$type_names[$type]}\n";
    $notification .= "🆔 *الرقم:* #$feedback_id\n";
    
    if (!empty($message)) {
        $truncated_message = strlen($message) > 200 ? substr($message, 0, 200) . '...' : $message;
        $notification .= "💬 *الرسالة:* $truncated_message\n";
    }
    
    $notification .= "\n" . date('Y-m-d H:i:s');
    
    // هنا يمكنك إرسال الإشعار إلى Telegram Bot أو Email
    // sendTelegramNotification($notification);
    
    error_log("Feedback notification: " . str_replace('*', '', $notification));
}
?>