<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

require_once '../includes/config.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$prayer_name = isset($_GET['prayer']) ? $_GET['prayer'] : '';

if (empty($prayer_name)) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // جلب حالة الصلاة
    $stmt = $pdo->prepare("SELECT status FROM prayer_records WHERE user_id = :user_id AND date = :date AND prayer_name = :prayer_name");
    $stmt->execute(['user_id' => $user_id, 'date' => $date, 'prayer_name' => $prayer_name]);
    $prayer_record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // جلب النوافل
    $stmt = $pdo->prepare("SELECT nawafil_type, rakat_count FROM nawafil_records WHERE user_id = :user_id AND date = :date AND prayer_name = :prayer_name");
    $stmt->execute(['user_id' => $user_id, 'date' => $date, 'prayer_name' => $prayer_name]);
    $nawafil = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $record = null;
    if ($prayer_record) {
        $record = [
            'status' => $prayer_record['status'],
            'nawafil' => $nawafil
        ];
    }
    
    echo json_encode(['success' => true, 'record' => $record]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
}
?>