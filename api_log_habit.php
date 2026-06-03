<?php
// api/log_habit.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

require_once '../includes/config.php';

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

$habit_id  = isset($input['habit_id'])  ? (int)$input['habit_id']  : 0;
$date      = isset($input['date'])      ? $input['date']            : date('Y-m-d');
$status    = isset($input['status'])    ? $input['status']          : 'done';
$value     = isset($input['value'])     ? (int)$input['value']      : 1;
$note      = isset($input['note'])      ? trim($input['note'])      : null;

// التحقق من البيانات
if (!$habit_id) {
    echo json_encode(['success' => false, 'message' => 'معرّف العادة مطلوب']);
    exit();
}

if (!in_array($status, ['done', 'skipped', 'partial'])) {
    echo json_encode(['success' => false, 'message' => 'حالة غير صحيحة']);
    exit();
}

// لا تسمح بتسجيل تاريخ مستقبلي
if ($date > date('Y-m-d')) {
    echo json_encode(['success' => false, 'message' => 'لا يمكن التسجيل لتاريخ مستقبلي']);
    exit();
}

try {
    // تأكد أن العادة تنتمي للمستخدم
    $check = $pdo->prepare("
        SELECT uh.id FROM user_habits uh
        WHERE uh.user_id = :uid AND uh.habit_id = :hid AND uh.is_active = 1
    ");
    $check->execute([':uid' => $user_id, ':hid' => $habit_id]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'العادة غير موجودة']);
        exit();
    }

    // INSERT أو UPDATE
    $stmt = $pdo->prepare("
        INSERT INTO habit_logs (user_id, habit_id, date, status, value, note)
        VALUES (:uid, :hid, :date, :status, :value, :note)
        ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            value  = VALUES(value),
            note   = VALUES(note),
            logged_at = current_timestamp()
    ");
    $stmt->execute([
        ':uid'    => $user_id,
        ':hid'    => $habit_id,
        ':date'   => $date,
        ':status' => $status,
        ':value'  => $value,
        ':note'   => $note
    ]);

    echo json_encode(['success' => true, 'message' => 'تم الحفظ بنجاح']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات']);
}
