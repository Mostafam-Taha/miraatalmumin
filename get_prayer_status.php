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
$prayer_type = $_GET['type'] ?? 'obligatory';

try {
    // جلب حالة الصلاة
    $stmt = $pdo->prepare("SELECT id, status FROM prayer_records WHERE user_id = :user_id AND prayer_name = :prayer_name AND date = :date AND type = :type");
    $stmt->execute([
        'user_id' => $user_id,
        'prayer_name' => $prayer_name,
        'date' => $date,
        'type' => $prayer_type
    ]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // جلب النوافل المرتبطة
    $nawafil = [];
    if ($record) {
        $stmt2 = $pdo->prepare("SELECT nawafil_type, rakat_count FROM nawafil_records WHERE prayer_record_id = :prayer_record_id");
        $stmt2->execute(['prayer_record_id' => $record['id']]);
        $nawafil = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'record' => $record ? [
            'status' => $record['status'],
            'nawafil' => $nawafil
        ] : null
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}