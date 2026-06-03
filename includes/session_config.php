<?php
// إعدادات الجلسة الدائمة
session_set_cookie_params([
    'lifetime' => 365 * 24 * 60 * 60, // سنة كاملة
    'path' => '/',
    'domain' => '', // يكتشف تلقائياً
    'secure' => isset($_SERVER['HTTPS']), // https فقط لو موجود
    'httponly' => true, // منع الوصول عبر JavaScript
    'samesite' => 'Lax' // حماية من CSRF
]);

// بدء الجلسة
session_start();

// تجديد معرف الجلسة لمنع هجمات التثبيت
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
}

// تأكيد أن الجلسة باقية لفترة طويلة
if (isset($_SESSION['user_id']) && !isset($_SESSION['session_lifetime_set'])) {
    $_SESSION['session_lifetime_set'] = time();
}
?>