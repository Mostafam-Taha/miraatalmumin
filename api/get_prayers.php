<?php
// ajax/get_prayers.php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit();
}

$user_id = $_SESSION['user_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

try {
    // استعلام الصلوات الفرض مع التصفية
    $fardQuery = "SELECT * FROM prayer_records WHERE user_id = :user_id";
    
    // تطبيق الفلتر
    switch($filter) {
        case 'missed':
            $fardQuery .= " AND status IN ('not_prayed', 'delayed')";
            break;
        case 'mosque':
            $fardQuery .= " AND status = 'prayed_in_mosque'";
            break;
        // حالة 'all' لا تضيف أي شرط إضافي
    }
    
    $fardQuery .= " ORDER BY date DESC, FIELD(prayer_name, 'الفجر', 'الظهر', 'العصر', 'المغرب', 'العشاء')";
    
    $stmt = $pdo->prepare($fardQuery);
    $stmt->execute([':user_id' => $user_id]);
    $fard_prayers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // استعلام النوافل
    $nawafilQuery = "SELECT * FROM nawafil_records 
                     WHERE user_id = :user_id 
                     ORDER BY date DESC, prayer_name";
    
    $stmt = $pdo->prepare($nawafilQuery);
    $stmt->execute([':user_id' => $user_id]);
    $nawafil = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // إرجاع البيانات كـ JSON
    echo json_encode([
        'success' => true,
        'fard_prayers' => $fard_prayers,
        'nawafil' => $nawafil
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
    ]);
}
?>