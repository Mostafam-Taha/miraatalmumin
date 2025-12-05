<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'يجب تسجيل الدخول']));
}

$user_id = $_SESSION['user_id'];
$period = $_GET['period'] ?? 'day';

// تحديد الفترة الزمنية
$dateCondition = '';
switch ($period) {
    case 'week':
        $dateCondition = "AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'month':
        $dateCondition = "AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        break;
    case 'year':
        $dateCondition = "AND date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
        break;
    case 'lifetime':
        $dateCondition = "";
        break;
    default: // day
        $dateCondition = "AND date = CURDATE()";
}

// استعلام للحصول على إحصائيات الصلاة
$query = "
    SELECT 
        status,
        COUNT(*) as count,
        (COUNT(*) / (SELECT COUNT(*) FROM prayer_records WHERE user_id = :user_id $dateCondition) * 100) as percentage
    FROM prayer_records 
    WHERE user_id = :user_id 
    $dateCondition
    GROUP BY status
";

$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تهيئة المصفوفة بالإحصائيات
$stats = [
    'prayed_in_mosque' => ['count' => 0, 'percentage' => 0],
    'delayed' => ['count' => 0, 'percentage' => 0],
    'prayed_alone' => ['count' => 0, 'percentage' => 0],
    'not_prayed' => ['count' => 0, 'percentage' => 0]
];

// تعبئة البيانات من النتائج
foreach ($results as $row) {
    $status = $row['status'];
    if (isset($stats[$status])) {
        $stats[$status]['count'] = (int)$row['count'];
        $stats[$status]['percentage'] = round($row['percentage'] ?? 0, 1);
    }
}

// إضافة العدد الإجمالي للصلوات لتجنب القسمة على صفر
$totalPrayers = array_sum(array_column($stats, 'count'));

// إذا لم تكن هناك صلوات، تكون جميع النسب 0%
if ($totalPrayers == 0) {
    foreach ($stats as $key => $value) {
        $stats[$key]['percentage'] = 0;
    }
}

// إرجاع البيانات كـ JSON
header('Content-Type: application/json');
echo json_encode([
    'prayed_in_mosque' => $stats['prayed_in_mosque']['percentage'],
    'delayed' => $stats['delayed']['percentage'],
    'prayed_alone' => $stats['prayed_alone']['percentage'],
    'not_prayed' => $stats['not_prayed']['percentage'],
    'badges_count' => 0 // عدد الشارات ثابت كما طلبت
]);
?>