<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مسجل الدخول']);
    exit();
}

require_once '../includes/config.php';

$user_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? date('Y-m-d');
$prayer_name = $_GET['prayer'] ?? '';

if (empty($prayer_name)) {
    echo json_encode(['success' => false, 'message' => 'اسم الصلاة مطلوب']);
    exit();
}

try {
    // جلب سجل الصلاة
    $stmt = $pdo->prepare("SELECT status FROM prayer_records WHERE user_id = :user_id AND prayer_name = :prayer_name AND date = :date");
    $stmt->execute([
        'user_id' => $user_id,
        'prayer_name' => $prayer_name,
        'date' => $date
    ]);
    $prayer_record = $stmt->fetch();
    
    // جلب النوافل
    $stmt = $pdo->prepare("SELECT nawafil_type, rakat_count FROM nawafil_records WHERE user_id = :user_id AND prayer_name = :prayer_name AND date = :date");
    $stmt->execute([
        'user_id' => $user_id,
        'prayer_name' => $prayer_name,
        'date' => $date
    ]);
    $nawafil_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'record' => [
            'status' => $prayer_record['status'] ?? null,
            'nawafil' => $nawafil_records
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
}
?>