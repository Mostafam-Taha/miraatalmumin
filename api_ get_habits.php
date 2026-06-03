<?php
// api/get_habits.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

require_once '../includes/config.php';

$user_id = $_SESSION['user_id'];
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    // جلب عادات المستخدم المفعّلة مع حالة اليوم
    $stmt = $pdo->prepare("
        SELECT 
            h.id,
            h.name,
            h.icon,
            h.category,
            h.target_type,
            COALESCE(uh.custom_target, h.target_value) AS target_value,
            h.target_unit,
            h.user_id AS owner_id,
            uh.id AS user_habit_id,
            hl.status AS today_status,
            hl.value AS today_value,
            hl.note AS today_note
        FROM user_habits uh
        JOIN habits h ON h.id = uh.habit_id
        LEFT JOIN habit_logs hl ON hl.habit_id = h.id 
            AND hl.user_id = :uid2 
            AND hl.date = :date
        WHERE uh.user_id = :uid 
          AND uh.is_active = 1
        ORDER BY h.sort_order ASC, h.id ASC
    ");
    $stmt->execute([':uid' => $user_id, ':uid2' => $user_id, ':date' => $date]);
    $habits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'habits' => $habits,
        'date' => $date
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات']);
}