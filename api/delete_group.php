<?php
session_start();
require_once '../includes/config.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// التحقق من وجود معرف المجموعة
if (!isset($_POST['group_id'])) {
    $_SESSION['error_message'] = "معرف المجموعة مطلوب";
    header('Location: ../groups.php');
    exit();
}

$group_id = intval($_POST['group_id']);

// التحقق من صلاحيات المدير
try {
    $stmt = $pdo->prepare("SELECT g.*, gm.role FROM groups g 
                          JOIN group_members gm ON g.id = gm.group_id 
                          WHERE g.id = ? AND gm.user_id = ? AND gm.role = 'admin'");
    $stmt->execute([$group_id, $user_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        $_SESSION['error_message'] = "ليس لديك صلاحية حذف هذه المجموعة";
        header('Location: ../groups.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "حدث خطأ أثناء التحقق من الصلاحيات";
    header('Location: ../groups.php');
    exit();
}

// حذف المجموعة
try {
    // حذف جميع أعضاء المجموعة أولاً (سيتم حذفهم تلقائيًا بسبب CASCADE)
    $stmt = $pdo->prepare("DELETE FROM groups WHERE id = ?");
    $stmt->execute([$group_id]);
    
    $_SESSION['success_message'] = "تم حذف المجموعة بنجاح!";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "حدث خطأ أثناء حذف المجموعة: " . $e->getMessage();
}

header('Location: ../groups.php');
exit();
?>