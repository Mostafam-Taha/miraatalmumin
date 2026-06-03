<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

require_once '../includes/config.php';

// إعدادات المدينة – يمكن جعلها قابلة للتعديل من ملف الإعدادات أو من جدول users
$city = 'Cairo';
$country = 'Egypt';
$method = 5; // طريقة حساب مواقيت الصلاة (5: Egyptian General Authority)

// محاولة جلب المواقيت من API مع تخزين مؤقت لمدة يوم
$cache_file = sys_get_temp_dir() . "/prayer_times_{$city}_{$country}.json";
$now = time();
$use_cache = false;

if (file_exists($cache_file)) {
    $cache_time = filemtime($cache_file);
    if ($now - $cache_time < 86400) { // أقل من 24 ساعة
        $use_cache = true;
    }
}

if ($use_cache) {
    $data = json_decode(file_get_contents($cache_file), true);
} else {
    $date = date('d-m-Y');
    $url = "http://api.aladhan.com/v1/timingsByCity/{$date}?city={$city}&country={$country}&method={$method}";
    $response = @file_get_contents($url);
    if ($response === false) {
        // فشل الاتصال – استخدم مواقيت افتراضية
        $data = ['code' => 200, 'data' => ['timings' => [
            'Fajr' => '05:00',
            'Dhuhr' => '12:00',
            'Asr' => '15:30',
            'Maghrib' => '18:00',
            'Isha' => '19:30'
        ]]];
    } else {
        $data = json_decode($response, true);
        if (isset($data['data']['timings'])) {
            file_put_contents($cache_file, json_encode($data));
        }
    }
}

// تعريب أسماء الصلوات
$names_map = [
    'Fajr' => 'الفجر',
    'Dhuhr' => 'الظهر',
    'Asr' => 'العصر',
    'Maghrib' => 'المغرب',
    'Isha' => 'العشاء'
];

$timings = [];
if (isset($data['data']['timings'])) {
    foreach ($data['data']['timings'] as $key => $time) {
        if (isset($names_map[$key])) {
            $timings[$names_map[$key]] = substr($time, 0, 5);
        }
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'timings' => $timings]);