<?php
session_start();
require_once '../includes/config.php';

// التحقق من الصلاحيات (يمكنك إضافة نظام المصادقة الخاص بك)
// يمكنك تعطيل هذا مؤقتاً للاختبار
// if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
//     echo json_encode(['success' => false, 'message' => 'غير مصرح']);
//     exit;
// }

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف غير محدد']);
    exit;
}

$feedback_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("
        SELECT f.*, u.email as user_email, u.name as user_name, u.Pro as user_pro 
        FROM feedbacks f 
        LEFT JOIN users u ON f.user_id = u.id 
        WHERE f.id = ?
    ");
    
    $stmt->execute([$feedback_id]);
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($feedback) {
        // تنظيف البيانات قبل الإرسال
        $feedback['message'] = htmlspecialchars_decode($feedback['message']);
        echo json_encode(['success' => true, 'feedback' => $feedback]);
    } else {
        echo json_encode(['success' => false, 'message' => 'الملاحظة غير موجودة']);
    }
} catch (PDOException $e) {
    error_log("Database error in get_feedback.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات']);
}
?>