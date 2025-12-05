<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح بالوصول']);
    exit();
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['setting']) || !isset($input['value'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit();
}

$setting = $input['setting'];
$value = $input['value'];

try {
    // التحقق من وجود سجل للمستخدم
    $stmt = $pdo->prepare("SELECT id FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // تحديث السجل الموجود
        $stmt = $pdo->prepare("UPDATE user_settings SET $setting = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->execute([$value, $user_id]);
    } else {
        // إنشاء سجل جديد
        $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, $setting) VALUES (?, ?)");
        $stmt->execute([$user_id, $value]);
    }
    
    echo json_encode(['success' => true, 'message' => 'تم حفظ الإعدادات بنجاح']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
}
?>