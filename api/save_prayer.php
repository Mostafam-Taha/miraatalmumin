<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مسجل الدخول']);
    exit();
}

require_once '../includes/config.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$prayer_name = $data['prayer_name'];
$date = $data['date'];
$status = $data['status'];

// التحقق من أن التاريخ ليس مستقبلياً
$today = date('Y-m-d');
if ($date > $today) {
    echo json_encode(['success' => false, 'message' => 'لا يمكن تسجيل الصلوات لتاريخ مستقبلي']);
    exit();
}

try {
    // التحقق إذا كان هناك سجل سابق
    $stmt = $pdo->prepare("SELECT id FROM prayer_records WHERE user_id = :user_id AND prayer_name = :prayer_name AND date = :date");
    $stmt->execute([
        'user_id' => $user_id,
        'prayer_name' => $prayer_name,
        'date' => $date
    ]);
    $existing_record = $stmt->fetch();
    
    if ($existing_record) {
        // تحديث السجل الموجود
        $stmt = $pdo->prepare("UPDATE prayer_records SET status = :status WHERE id = :id");
        $stmt->execute([
            'status' => $status,
            'id' => $existing_record['id']
        ]);
        $prayer_record_id = $existing_record['id'];
    } else {
        // إضافة سجل جديد
        $stmt = $pdo->prepare("INSERT INTO prayer_records (user_id, prayer_name, date, status) VALUES (:user_id, :prayer_name, :date, :status)");
        $stmt->execute([
            'user_id' => $user_id,
            'prayer_name' => $prayer_name,
            'date' => $date,
            'status' => $status
        ]);
        $prayer_record_id = $pdo->lastInsertId(); // الحصول على معرف السجل الجديد
    }
    
    // حذف النوافل القديمة المرتبطة بهذه الصلاة
    $stmt = $pdo->prepare("DELETE FROM nawafil_records WHERE prayer_record_id = :prayer_record_id");
    $stmt->execute([
        'prayer_record_id' => $prayer_record_id
    ]);
    
    // حفظ النوافل الجديدة مع الربط بمعرف الصلاة
    if (!empty($data['nawafil'])) {
        foreach ($data['nawafil'] as $nawafil) {
            $stmt = $pdo->prepare("INSERT INTO nawafil_records (user_id, prayer_record_id, prayer_name, nawafil_type, rakat_count, date) VALUES (:user_id, :prayer_record_id, :prayer_name, :nawafil_type, :rakat_count, :date)");
            $stmt->execute([
                'user_id' => $user_id,
                'prayer_record_id' => $prayer_record_id,
                'prayer_name' => $prayer_name,
                'nawafil_type' => $nawafil['type'],
                'rakat_count' => $nawafil['rakat'],
                'date' => $date
            ]);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'تم الحفظ بنجاح', 'prayer_record_id' => $prayer_record_id]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}