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

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المستخدم مطلوب']);
    exit();
}

$user_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في جلب البيانات: ' . $e->getMessage()]);
}
?>