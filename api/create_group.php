<?php
session_start();
require_once '../includes/config.php';

// التحقق من وجود رمز الدعوة
if (!isset($_GET['code'])) {
    die('رابط الدعوة غير صالح');
}

if (!isset($_SESSION['user_id'])) {
    // تخزين الرابط والعودة إليه بعد تسجيل الدخول
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../login.php');
    exit;
}

try {
    // البحث عن المجموعة باستخدام رمز الدعوة
    $stmt = $pdo->prepare("SELECT id, name FROM `groups` WHERE invite_code = ?");
    $stmt->execute([$_GET['code']]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        die('رابط الدعوة غير صالح أو منتهي الصلاحية');
    }
    
    // التحقق من عدم انضمام المستخدم مسبقاً
    $stmt = $pdo->prepare("
        SELECT id FROM `group_members` 
        WHERE group_id = ? AND user_id = ?
    ");
    $stmt->execute([$group['id'], $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {
        echo 'أنت بالفعل عضو في هذه المجموعة!';
    } else {
        // إضافة المستخدم إلى المجموعة
        $stmt = $pdo->prepare("
            INSERT INTO `group_members` (group_id, user_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$group['id'], $_SESSION['user_id']]);
        
        echo 'تم انضمامك إلى المجموعة "' . htmlspecialchars($group['name']) . '" بنجاح!';
    }
    
    // إعادة التوجيه إلى صفحة المجموعة بعد 3 ثواني
    header('Refresh: 3; URL=groups.php');
    
} catch (PDOException $e) {
    die('حدث خطأ: ' . $e->getMessage());
}
?>