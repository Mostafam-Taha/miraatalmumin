<?php
// ... كود تسجيل الصلاة ...

// بعد تسجيل الصلاة بنجاح، تحديث المداومة
if ($success) {
    // استدعاء دالة تحديث المداومة
    require_once '../includes/config.php';
    require_once 'consistency_functions.php';
    
    updatePrayerConsistency($_SESSION['user_id'], $pdo);
}

// ... باقي الكود ...
?>