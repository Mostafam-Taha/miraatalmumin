<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

require_once '../includes/config.php';

// التحقق من الصلاحيات
$is_admin = true; // يمكنك تغيير هذا بناءً على صلاحيات المستخدم

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => 'صلاحيات غير كافية']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المستخدم مطلوب']);
    exit();
}

$user_id = intval($data['user_id']);

// منع حذف المستخدم الحالي
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'لا يمكن حذف حسابك الخاص']);
    exit();
}

try {
    // بداية transaction
    $pdo->beginTransaction();
    
    // حذف المستخدم وجميع سجلاته (سواء من قاعدة البيانات الخاصة بك)
    // يمكنك إضافة جداول أخرى يجب حذف بيانات المستخدم منها
    
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'تم حذف المستخدم بنجاح']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'خطأ في حذف المستخدم: ' . $e->getMessage()]);
}
?>