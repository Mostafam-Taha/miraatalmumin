<?php
// api/manage_habits.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

require_once '../includes/config.php';

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? $input['action'] : '';

try {
    switch ($action) {

        // ===== إضافة عادة موجودة من القوالب =====
        case 'add_preset':
            $habit_id = (int)($input['habit_id'] ?? 0);
            $custom_target = isset($input['custom_target']) ? (int)$input['custom_target'] : null;

            if (!$habit_id) {
                echo json_encode(['success' => false, 'message' => 'معرّف العادة مطلوب']); exit();
            }

            // تحقق أن العادة موجودة في habits
            $check = $pdo->prepare("SELECT id FROM habits WHERE id = :id");
            $check->execute([':id' => $habit_id]);
            if (!$check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'العادة غير موجودة']); exit();
            }

            $stmt = $pdo->prepare("
                INSERT INTO user_habits (user_id, habit_id, custom_target, is_active)
                VALUES (:uid, :hid, :ct, 1)
                ON DUPLICATE KEY UPDATE is_active = 1, custom_target = VALUES(custom_target)
            ");
            $stmt->execute([':uid' => $user_id, ':hid' => $habit_id, ':ct' => $custom_target]);
            echo json_encode(['success' => true, 'message' => 'تمت إضافة العادة']);
            break;

        // ===== إنشاء عادة مخصصة جديدة =====
        case 'create_custom':
            $name         = trim($input['name'] ?? '');
            $icon         = trim($input['icon'] ?? '⭐');
            $category     = $input['category'] ?? 'أخرى';
            $target_type  = $input['target_type'] ?? 'boolean';
            $target_value = (int)($input['target_value'] ?? 1);
            $target_unit  = trim($input['target_unit'] ?? '');

            if (empty($name)) {
                echo json_encode(['success' => false, 'message' => 'اسم العادة مطلوب']); exit();
            }
            if (!in_array($category, ['صلاة','قرآن','ذكر','صيام','صدقة','أخرى'])) {
                $category = 'أخرى';
            }
            if (!in_array($target_type, ['boolean','count'])) {
                $target_type = 'boolean';
            }
            if ($target_value < 1) $target_value = 1;

            // إنشاء العادة في جدول habits مع user_id للمستخدم الحالي
            $stmt = $pdo->prepare("
                INSERT INTO habits (user_id, name, icon, category, target_type, target_value, target_unit, is_active, sort_order)
                VALUES (:uid, :name, :icon, :cat, :ttype, :tval, :tunit, 1, 99)
            ");
            $stmt->execute([
                ':uid'   => $user_id,
                ':name'  => $name,
                ':icon'  => $icon,
                ':cat'   => $category,
                ':ttype' => $target_type,
                ':tval'  => $target_value,
                ':tunit' => $target_unit ?: null
            ]);
            $new_habit_id = $pdo->lastInsertId();

            // ربطها بالمستخدم مباشرة
            $stmt2 = $pdo->prepare("
                INSERT INTO user_habits (user_id, habit_id, is_active)
                VALUES (:uid, :hid, 1)
            ");
            $stmt2->execute([':uid' => $user_id, ':hid' => $new_habit_id]);

            echo json_encode(['success' => true, 'habit_id' => $new_habit_id, 'message' => 'تمت إضافة العادة المخصصة']);
            break;

        // ===== حذف (تعطيل) عادة =====
        case 'remove':
            $habit_id = (int)($input['habit_id'] ?? 0);
            if (!$habit_id) {
                echo json_encode(['success' => false, 'message' => 'معرّف العادة مطلوب']); exit();
            }

            $stmt = $pdo->prepare("
                UPDATE user_habits SET is_active = 0
                WHERE user_id = :uid AND habit_id = :hid
            ");
            $stmt->execute([':uid' => $user_id, ':hid' => $habit_id]);
            echo json_encode(['success' => true, 'message' => 'تم حذف العادة']);
            break;

        // ===== جلب القوالب المتاحة مع حالة الاشتراك =====
        case 'get_presets':
            $stmt = $pdo->prepare("
                SELECT h.*, 
                       IF(uh.id IS NOT NULL AND uh.is_active = 1, 1, 0) AS is_subscribed
                FROM habits h
                LEFT JOIN user_habits uh ON uh.habit_id = h.id AND uh.user_id = :uid
                WHERE h.user_id = 0
                ORDER BY h.sort_order ASC
            ");
            $stmt->execute([':uid' => $user_id]);
            $presets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'presets' => $presets]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
}