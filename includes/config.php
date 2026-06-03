<?php
// إعدادات الاتصال
$host = 'sql207.infinityfree.com';
$dbname = 'if0_39304815_miraatalmuminif';
$username = 'if0_39304815';
$password = 'NIHOIGYPkLGsq0';

// ضبط التوقيت المصري في PHP أولاً
date_default_timezone_set('Africa/Cairo');

try {
    // الاتصال مع إعداد التوقيت في DSN
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+02:00'"
    ]);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // تأكيد ضبط التوقيت للجلسة
    $pdo->exec("SET time_zone = '+02:00'");
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// رابط الموقع
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));

// دالة للحصول على الوقت المصري الحالي
function getEgyptTime() {
    return date('Y-m-d H:i:s');
}

// دالة لتحويل أي وقت للتوقيت المصري
function toEgyptTime($datetime) {
    $date = new DateTime($datetime, new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone('Africa/Cairo'));
    return $date->format('Y-m-d H:i:s');
}
?>