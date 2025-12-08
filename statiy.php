<?php
session_start();
require_once 'includes/config.php';

// التحقق إذا كان المستخدم مسجل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['username'] = $user['name'];
    }
}

// ============ دالة لحساب المداومة من السجلات التاريخية ============
function calculatePrayerStreakFromRecords($user_id, $pdo) {
    try {
        // جلب جميع سجلات الصلوات للمستخدم مرتبة حسب التاريخ
        $stmt = $pdo->prepare("
            SELECT DISTINCT DATE(date) as prayer_date
            FROM prayer_records 
            WHERE user_id = ? 
            AND status IN ('prayed_in_mosque', 'prayed_alone', 'delayed')
            ORDER BY prayer_date ASC
        ");
        $stmt->execute([$user_id]);
        $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($dates)) {
            return [
                'current_streak' => 0,
                'max_streak' => 0,
                'all_prayed_dates' => [],
                'streak_history' => []
            ];
        }
        
        // تحويل التواريخ إلى صيغة Y-m-d
        $all_dates = array_column($dates, 'prayer_date');
        
        // جلب أيام قام فيها بصلاة جميع الصلوات الخمس
        $stmt = $pdo->prepare("
            SELECT DATE(date) as prayer_date, 
                   COUNT(DISTINCT prayer_name) as prayer_count
            FROM prayer_records 
            WHERE user_id = ? 
            AND status IN ('prayed_in_mosque', 'prayed_alone', 'delayed')
            GROUP BY DATE(date)
            HAVING prayer_count = 5
            ORDER BY prayer_date ASC
        ");
        $stmt->execute([$user_id]);
        $complete_days = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($complete_days)) {
            return [
                'current_streak' => 0,
                'max_streak' => 0,
                'all_prayed_dates' => $all_dates,
                'streak_history' => []
            ];
        }
        
        // تحويل أيام الصلاة الكاملة إلى مصفوفة
        $complete_dates = [];
        foreach ($complete_days as $day) {
            $complete_dates[] = $day['prayer_date'];
        }
        
        // حساب المداومة الحالية والأعلى
        $current_streak = 0;
        $max_streak = 0;
        $streak_history = [];
        
        // فرز التواريخ تصاعدياً
        sort($complete_dates);
        
        $temp_streak = 1;
        $last_date = null;
        
        foreach ($complete_dates as $date) {
            $current_date = new DateTime($date);
            
            if ($last_date === null) {
                $temp_streak = 1;
            } else {
                $diff = $last_date->diff($current_date);
                
                // إذا كان الفرق يوم واحد فقط (متتالي)
                if ($diff->days == 1) {
                    $temp_streak++;
                } 
                // إذا كان هناك فجوة أكثر من يوم
                else {
                    // حفظ المداومة السابقة
                    $streak_history[] = [
                        'start' => date('Y-m-d', strtotime($date . " -" . ($temp_streak - 1) . " days")),
                        'end' => $last_date->format('Y-m-d'),
                        'length' => $temp_streak
                    ];
                    
                    $temp_streak = 1;
                }
            }
            
            // تحديث أعلى مداومة
            if ($temp_streak > $max_streak) {
                $max_streak = $temp_streak;
            }
            
            $last_date = $current_date;
        }
        
        // حفظ آخر مداومة
        if ($temp_streak > 0 && $last_date !== null) {
            $streak_history[] = [
                'start' => date('Y-m-d', strtotime($last_date->format('Y-m-d') . " -" . ($temp_streak - 1) . " days")),
                'end' => $last_date->format('Y-m-d'),
                'length' => $temp_streak
            ];
            
            // المداومة الحالية هي آخر مداومة إذا كانت حتى اليوم أو البارحة
            $today = new DateTime();
            $yesterday = clone $today;
            $yesterday->modify('-1 day');
            
            $last_date_str = $last_date->format('Y-m-d');
            $today_str = $today->format('Y-m-d');
            $yesterday_str = $yesterday->format('Y-m-d');
            
            if ($last_date_str === $today_str || $last_date_str === $yesterday_str) {
                $current_streak = $temp_streak;
            } else {
                $current_streak = 0;
            }
        }
        
        // الحصول على بيانات المستخدم الحالية
        $stmt = $pdo->prepare("SELECT current_prayer_streak, max_prayer_streak FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $db_current_streak = $user_data['current_prayer_streak'] ?? 0;
        $db_max_streak = $user_data['max_prayer_streak'] ?? 0;
        
        // تحديث أعلى مداومة في قاعدة البيانات إذا كانت الحسابات أعلى
        if ($max_streak > $db_max_streak) {
            $stmt = $pdo->prepare("UPDATE users SET max_prayer_streak = ? WHERE id = ?");
            $stmt->execute([$max_streak, $user_id]);
        }
        
        // تحديث المداومة الحالية في قاعدة البيانات
        if ($current_streak != $db_current_streak) {
            $stmt = $pdo->prepare("UPDATE users SET current_prayer_streak = ? WHERE id = ?");
            $stmt->execute([$current_streak, $user_id]);
        }
        
        return [
            'current_streak' => $current_streak,
            'max_streak' => $max_streak,
            'all_prayed_dates' => $all_dates,
            'streak_history' => $streak_history,
            'complete_dates' => $complete_dates
        ];
        
    } catch (PDOException $e) {
        error_log("Error calculating prayer streak from records: " . $e->getMessage());
        return [
            'current_streak' => 0,
            'max_streak' => 0,
            'all_prayed_dates' => [],
            'streak_history' => []
        ];
    }
}

// ============ دالة لحساب صلاة اليوم ============
function getTodayPrayerStatus($user_id, $pdo) {
    try {
        $today = date('Y-m-d');
        
        // حساب الصلوات المؤداة اليوم
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT prayer_name) as prayed_count,
                   GROUP_CONCAT(DISTINCT prayer_name) as prayed_names
            FROM prayer_records 
            WHERE user_id = ? 
            AND date = ? 
            AND status IN ('prayed_in_mosque', 'prayed_alone', 'delayed')
        ");
        $stmt->execute([$user_id, $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $prayed_today = $result['prayed_count'] ?? 0;
        $prayed_names = $result['prayed_names'] ?? '';
        
        // حساب الصلوات المتبقية
        $all_prayers = ['الفجر', 'الظهر', 'العصر', 'المغرب', 'العشاء'];
        $prayed_array = $prayed_names ? explode(',', $prayed_names) : [];
        $remaining_prayers = array_diff($all_prayers, $prayed_array);
        
        // التحقق إذا صلى جميع الصلوات اليوم
        $all_prayed_today = ($prayed_today >= 5) ? true : false;
        
        return [
            'prayed_count' => $prayed_today,
            'prayed_names' => $prayed_array,
            'remaining_prayers' => array_values($remaining_prayers),
            'all_prayed' => $all_prayed_today,
            'progress_percentage' => min(100, ($prayed_today / 5) * 100)
        ];
        
    } catch (PDOException $e) {
        error_log("Error getting today prayer status: " . $e->getMessage());
        return [
            'prayed_count' => 0,
            'prayed_names' => [],
            'remaining_prayers' => ['الفجر', 'الظهر', 'العصر', 'المغرب', 'العشاء'],
            'all_prayed' => false,
            'progress_percentage' => 0
        ];
    }
}

// ============ حساب المداومة من السجلات التاريخية ============
$user_id = $_SESSION['user_id'];
$streak_data = calculatePrayerStreakFromRecords($user_id, $pdo);
$today_status = getTodayPrayerStatus($user_id, $pdo);

// ============ الحصول على المداومة الحالية والأعلى ============
$current_streak = $streak_data['current_streak'];
$max_streak = $streak_data['max_streak'];
$prayed_today = $today_status['prayed_count'];
$all_prayed_today = $today_status['all_prayed'];
$progress_percentage = $today_status['progress_percentage'];

// إحصائيات إضافية للمداومة
$total_prayer_days = count($streak_data['all_prayed_dates']);
$total_complete_days = count($streak_data['complete_dates']);
$longest_streak = $max_streak;

// ============ بقية الكود ============
// تحديد الشهر والسنة من الـ URL أو استخدام القيم الحالية
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// أسماء الصلوات بالترتيب
$prayers_list = ['الفجر', 'الظهر', 'العصر', 'المغرب', 'العشاء'];

// استعلام لجلب جميع سجلات الصلوات للشهر المحدد
$stmt = $pdo->prepare("
    SELECT 
        prayer_name,
        DATE(date) as prayer_date,
        status,
        COUNT(*) as count
    FROM prayer_records 
    WHERE user_id = ? 
        AND YEAR(date) = ? 
        AND MONTH(date) = ?
    GROUP BY prayer_name, DATE(date), status
    ORDER BY 
        CASE prayer_name 
            WHEN 'الفجر' THEN 1
            WHEN 'الظهر' THEN 2
            WHEN 'العصر' THEN 3
            WHEN 'المغرب' THEN 4
            WHEN 'العشاء' THEN 5
            ELSE 6
        END,
        prayer_date
");
$stmt->execute([$_SESSION['user_id'], $selected_year, $selected_month]);
$records = $stmt->fetchAll();

// تهيئة مصفوفة البيانات للـ Heatmap
$heatmap_data = [];
$daily_summary = [];

// تحضير البيانات بشكل مناسب للـ Heatmap
foreach ($records as $record) {
    $prayer = $record['prayer_name'];
    $date = $record['prayer_date'];
    $status = $record['status'];
    
    // تخصيص ألوان وقيم لكل حالة
    switch($status) {
        case 'prayed_in_mosque':
            $color = '#10B981'; // أخضر داكن
            $value = 4; // أعلى قيمة
            break;
        case 'prayed_alone':
            $color = '#3B82F6'; // أزرق
            $value = 3;
            break;
        case 'delayed':
            $color = '#F59E0B'; // برتقالي
            $value = 2;
            break;
        case 'not_prayed':
            $color = '#EF4444'; // أحمر
            $value = 1;
            break;
        default:
            $color = '#E5E7EB'; // رمادي (لا توجد بيانات)
            $value = 0;
            break;
    }
    
    // تخزين بيانات الـ Heatmap
    if (!isset($heatmap_data[$prayer])) {
        $heatmap_data[$prayer] = [];
    }
    
    $heatmap_data[$prayer][$date] = [
        'x' => $date,
        'y' => $value,
        'status' => $status,
        'color' => $color
    ];
}

// حساب إحصائيات عامة
$month_stats = [
    'total_prayers' => 0,
    'prayed_in_mosque' => 0,
    'prayed_alone' => 0,
    'delayed' => 0,
    'not_prayed' => 0,
    'no_data' => 0
];

// إحصائيات لكل صلاة
$prayer_stats = [];
foreach ($prayers_list as $prayer) {
    $prayer_stats[$prayer] = [
        'total' => 0,
        'prayed_in_mosque' => 0,
        'prayed_alone' => 0,
        'delayed' => 0,
        'not_prayed' => 0,
        'no_data' => 0
    ];
}

// حساب أيام الشهر
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year);
$total_days = $days_in_month;

// حساب الإحصائيات
foreach ($prayers_list as $prayer) {
    $month_stats['total_prayers'] += $total_days; // كل صلاة × أيام الشهر
    
    // حساب لكل صلاة
    $prayer_data = isset($heatmap_data[$prayer]) ? $heatmap_data[$prayer] : [];
    
    // حساب الحالات لكل صلاة
    for ($day = 1; $day <= $total_days; $day++) {
        $date = sprintf('%04d-%02d-%02d', $selected_year, $selected_month, $day);
        
        if (isset($prayer_data[$date])) {
            $status = $prayer_data[$date]['status'];
            $prayer_stats[$prayer][$status]++;
            $month_stats[$status]++;
        } else {
            $prayer_stats[$prayer]['no_data']++;
            $month_stats['no_data']++;
        }
    }
}

// حساب النسب المئوية
if ($month_stats['total_prayers'] > 0) {
    $total_prayed = $month_stats['prayed_in_mosque'] + $month_stats['prayed_alone'] + $month_stats['delayed'];
    $month_stats['percentage_prayed'] = round(($total_prayed / $month_stats['total_prayers']) * 100, 1);
    $month_stats['percentage_mosque'] = round(($month_stats['prayed_in_mosque'] / $month_stats['total_prayers']) * 100, 1);
} else {
    $month_stats['percentage_prayed'] = 0;
    $month_stats['percentage_mosque'] = 0;
}

// تحضير البيانات للـ Heatmap
$heatmap_series = [];
foreach ($prayers_list as $prayer) {
    $prayer_data = isset($heatmap_data[$prayer]) ? $heatmap_data[$prayer] : [];
    
    $series_data = [];
    for ($day = 1; $day <= $total_days; $day++) {
        $date = sprintf('%04d-%02d-%02d', $selected_year, $selected_month, $day);
        
        if (isset($prayer_data[$date])) {
            $series_data[] = $prayer_data[$date];
        } else {
            $series_data[] = [
                'x' => $date,
                'y' => 0,
                'status' => 'no_data',
                'color' => '#E5E7EB'
            ];
        }
    }
    
    $heatmap_series[] = [
        'name' => $prayer,
        'data' => $series_data
    ];
}

// ============ بيانات الرسم البياني للصلوات ============
// استعلام للحصول على إحصائيات الصلوات للشهر الحالي
$stmt = $pdo->prepare("
    SELECT 
        prayer_name,
        SUM(CASE WHEN status = 'prayed_in_mosque' THEN 1 ELSE 0 END) as mosque_count,
        SUM(CASE WHEN status = 'prayed_alone' THEN 1 ELSE 0 END) as alone_count,
        SUM(CASE WHEN status = 'delayed' THEN 1 ELSE 0 END) as delayed_count,
        SUM(CASE WHEN status = 'not_prayed' THEN 1 ELSE 0 END) as not_prayed_count,
        COUNT(*) as total_count
    FROM prayer_records 
    WHERE user_id = ? 
        AND YEAR(date) = ? 
        AND MONTH(date) = ?
    GROUP BY prayer_name
    ORDER BY 
        CASE prayer_name 
            WHEN 'الفجر' THEN 1
            WHEN 'الظهر' THEN 2
            WHEN 'العصر' THEN 3
            WHEN 'المغرب' THEN 4
            WHEN 'العشاء' THEN 5
            ELSE 6
        END
");
$stmt->execute([$_SESSION['user_id'], date('Y'), date('n')]);
$prayer_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تهيئة بيانات للرسم البياني
$prayer_names = [];
$mosque_percentages = [];
$alone_percentages = [];
$delayed_percentages = [];
$not_prayed_percentages = [];

foreach ($prayer_chart_data as $prayer) {
    $prayer_names[] = $prayer['prayer_name'];
    
    if ($prayer['total_count'] > 0) {
        $days_in_month = date('t');
        $total_possible = min($prayer['total_count'], $days_in_month);
        
        $mosque_percentages[] = round(($prayer['mosque_count'] / $days_in_month) * 100, 1);
        $alone_percentages[] = round(($prayer['alone_count'] / $days_in_month) * 100, 1);
        $delayed_percentages[] = round(($prayer['delayed_count'] / $days_in_month) * 100, 1);
        $not_prayed_percentages[] = round(($prayer['not_prayed_count'] / $days_in_month) * 100, 1);
    } else {
        $mosque_percentages[] = 0;
        $alone_percentages[] = 0;
        $delayed_percentages[] = 0;
        $not_prayed_percentages[] = 0;
    }
}

// إذا لم تكن هناك بيانات، أنشئ بيانات افتراضية
if (empty($prayer_chart_data)) {
    $prayer_names = ['الفجر', 'الظهر', 'العصر', 'المغرب', 'العشاء'];
    $mosque_percentages = array_fill(0, 5, 0);
    $alone_percentages = array_fill(0, 5, 0);
    $delayed_percentages = array_fill(0, 5, 0);
    $not_prayed_percentages = array_fill(0, 5, 0);
}















// التحقق من Pro
$pro_enabled = false;
$pro_column_exists = false;

// أولاً: التحقق من وجود عمود Pro في جدول users
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'Pro'");
    $pro_column_exists = $stmt->rowCount() > 0;
    
    if ($pro_column_exists) {
        // جلب قيمة Pro للمستخدم
        $stmt = $pdo->prepare("SELECT Pro FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $pro_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $pro_enabled = $pro_result && $pro_result['Pro'] == 1;
    }
} catch (PDOException $e) {
    error_log("Error checking Pro column: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="assets/css/statistics.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/statiy.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.css">
    
    <title>الإحصائيات</title>
    
    <style>
        
        /* ============ قسم المداومة ============ */
        .streak-section {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            max-width: 1200px;
            border-radius: 15px;
            padding: 10px;
            margin: 10px auto;
            color: white;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .streak-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1%, transparent 20%);
            opacity: 0.5;
        }
        
        .streak-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .streak-title {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .streak-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .streak-stats {
            display: flex;
            justify-content: space-between;
            text-align: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .streak-item {
            flex: 1;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 15px 5px;
        }
        
        .streak-number {
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 5px;
        }
        
        .streak-label {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .streak-progress {
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            position: relative;
        }
        
        .streak-progress-bar {
            height: 100%;
            background: white;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .progress-text {
            position: absolute;
            top: -25px;
            right: 0;
            font-size: 12px;
            font-weight: 600;
        }
        
        .today-status {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .prayed-count-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .prayed-count {
            font-weight: 700;
            color: #10B981;
            background: white;
            min-width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        
        #streak-message {
            flex: 1;
            font-size: 14px;
        }
        
        .prayer-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .prayer-item {
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            font-size: 12px;
            flex: 1;
            min-width: 60px;
            text-align: center;
        }
        
        .prayer-item.prayed {
            background: rgba(255, 255, 255, 0.3);
            font-weight: 600;
        }
        
        .prayer-item.remaining {
            opacity: 0.7;
        }
        
        .streak-history {
            margin-top: 15px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .history-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        
        .history-content {
            font-size: 12px;
            line-height: 1.6;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .history-content.show {
            max-height: 300px;
        }
        
        .history-item {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .history-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .toggle-history {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* ============ استعلامات الوسائط ============ */
        @media (max-width: 768px) {
            .streak-section {
                margin: 10px;
                padding: 8px;
            }
            
            .streak-stats {
                flex-direction: column;
            }
            
            .streak-item {
                padding: 12px;
            }
            
            .streak-number {
                font-size: 24px;
            }
            
            .card-stat {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .card-item {
                padding: 15px;
            }
            
            #combined-heatmap {
                min-width: 500px;
            }
        }
        
        @media (max-width: 480px) {
            .header-title h2 {
                font-size: 18px;
            }
            
            .switch-item .item-link {
                font-size: 12px;
                padding: 8px 5px;
            }
            
            .streak-title {
                font-size: 16px;
            }
            
            .streak-icon {
                width: 35px;
                height: 35px;
            }
            
            .period-btn {
                font-size: 12px;
                padding: 8px 4px;
            }
            
            .card-stat {
                grid-template-columns: 1fr;
            }
            
            .dis-card i {
                font-size: 20px;
            }
            
            .dis-card span {
                font-size: 20px;
            }
            
            .prayer-item {
                font-size: 11px;
                padding: 5px 8px;
            }
            
            #combined-heatmap {
                min-width: 400px;
            }
        }
        
        @media (max-width: 360px) {
            .streak-number {
                font-size: 22px;
            }
            
            .prayer-item {
                min-width: 55px;
            }
            
            #combined-heatmap {
                min-width: 350px;
            }
        }
        
        /* ============ تحسينات إضافية ============ */
        button:active {
            transform: scale(0.95);
        }
        
        .no-data p {
            margin: 10px 0;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <main>
        <header>
            <div class="header-content">
                <div class="header-icon">
                    <i class="bi bi-arrow-right-circle" onclick="window.location.href='index.php'" style="cursor: pointer;"></i>
                </div>
                <div class="header-title">
                    <h2>الإحصائيات</h2>
                </div>
                <div class="header-icon">
                    <i class="bi bi-person-circle"></i>
                    <i class="bi bi-gear-fill" onclick="window.location.href='settings.php'" style="cursor: pointer;"></i>
                </div>
            </div>
        </header>
    </main>
    
    <section class="bar">
        <div class="switch-bar">
            <div class="switch-item">
                <a href="statistics.php" class="item-link">للوحة التحكم</a>
            </div>
            <div class="switch-item">
                <a href="statistics.php" class="item-link">عبادات</a>
            </div>
            <div class="switch-item">
                <a href="statiy.php" class="item-link active">إحصائيات</a>
            </div>
            <div class="switch-item">
                <a href="groups.php" class="item-link">مجموعة</a>
            </div>
        </div>
    </section>
    
    <!-- قسم المداومة -->
    <section class="streak-section">
        <div class="streak-header">
            <div class="streak-title">
                <div class="streak-icon">
                    <i class="bi bi-fire"></i>
                </div>
                <span>مداومة الصلاة</span>
            </div>
            <div class="streak-badge">
                <i class="bi bi-trophy-fill" style="font-size: 24px; opacity: 0.8;"></i>
            </div>
        </div>
        
        <div class="streak-stats">
            <div class="streak-item">
                <div class="streak-number" id="current-streak">
                    <?php echo $current_streak; ?>
                </div>
                <div class="streak-label">يوم متواصل</div>
                <small style="opacity: 0.7; font-size: 11px;">الحالية</small>
            </div>
            
            <div class="streak-item">
                <div class="streak-number" id="max-streak">
                    <?php echo $max_streak; ?>
                </div>
                <div class="streak-label">يوم متواصل</div>
                <small style="opacity: 0.7; font-size: 11px;">الأعلى</small>
            </div>
            
            <div class="streak-item">
                <div class="streak-number" id="total-days">
                    <?php echo $total_complete_days; ?>
                </div>
                <div class="streak-label">يوم كامل</div>
                <small style="opacity: 0.7; font-size: 11px;">الإجمالي</small>
            </div>
        </div>
        
        <div class="streak-progress">
            <div class="progress-text">صلاة اليوم: <?php echo $prayed_today; ?>/5</div>
            <div class="streak-progress-bar" id="streak-progress-bar" 
                 style="width: <?php echo $progress_percentage; ?>%"></div>
        </div>
        
        <div class="today-status">
            <div class="prayed-count-badge">
                <div class="prayed-count">
                    <?php echo $prayed_today; ?>
                </div>
                <span id="streak-message">
                    <?php
                    if ($all_prayed_today) {
                        if ($current_streak == 1) {
                            echo "ممتاز! بدأت سلسلة المداومة اليوم.";
                        } elseif ($current_streak < 7) {
                            echo "رائع! أنت في اليوم {$current_streak} من المداومة.";
                        } else {
                            echo "مذهل! {$current_streak} يومًا متواصلًا.";
                        }
                    } elseif ($prayed_today > 0) {
                        echo "بدأت اليوم بشكل جيد. أكمل الصلوات الباقية!";
                    } else {
                        echo "ابدأ يومك بالصلاة لتبدأ سلسلة المداومة.";
                    }
                    ?>
                </span>
            </div>
            
            <div class="prayer-list">
                <?php
                $all_prayers = ['الفجر', 'الظهر', 'العصر', 'المغرب', 'العشاء'];
                foreach ($all_prayers as $prayer) {
                    $is_prayed = in_array($prayer, $today_status['prayed_names']);
                    $class = $is_prayed ? 'prayed' : 'remaining';
                    echo '<div class="prayer-item ' . $class . '">' . $prayer . '</div>';
                }
                ?>
            </div>
        </div>
        
        <!-- تاريخ المداومة -->
        <?php if (!empty($streak_data['streak_history'])): ?>
        <div class="streak-history" style="display: none;">
            <div class="history-title" onclick="toggleStreakHistory()">
                <span>تاريخ المداومة</span>
                <button class="toggle-history" id="toggle-history-btn">
                    <i class="bi bi-chevron-down"></i> عرض
                </button>
            </div>
            <div class="history-content" id="history-content">
                <?php
                // عرض آخر 5 مداومات
                $recent_streaks = array_slice($streak_data['streak_history'], -5);
                foreach ($recent_streaks as $streak) {
                    echo '<div class="history-item">';
                    echo '<strong>' . $streak['length'] . ' يوم</strong> ';
                    echo 'من ' . date('d/m/Y', strtotime($streak['start'])) . ' إلى ' . date('d/m/Y', strtotime($streak['end']));
                    echo '</div>';
                }
                
                if (count($streak_data['streak_history']) > 5) {
                    echo '<div style="text-align: center; font-size: 11px; opacity: 0.7; margin-top: 10px;">';
                    echo 'عرض ' . count($recent_streaks) . ' من ' . count($streak_data['streak_history']) . ' مداومة';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
    </section>
    
    <!-- قسم Heatmap -->
        <!-- قسم Heatmap -->
    <section class="bourd" id="bourd">
        <?php if (!$pro_enabled): ?>
        <!-- عرض عند عدم تفعيل Pro -->
        <div class="content-if" style="text-align: center; padding: 30px;">
            <div class="pro-locked">
                <i class="bi bi-lock-fill" style="font-size: 48px; color: #6B7280; margin-bottom: 20px;"></i>
                <h3 style="color: #111827; margin-bottom: 15px;">ميزة متقدمة</h3>
                <p style="color: #6B7280; margin-bottom: 25px; max-width: 300px; margin-left: auto; margin-right: auto;">
                    تعقب الفروض الشهري متاح في النسخة Pro فقط
                </p>
                <button onclick="window.location.href='pro_subscription.php'" 
                        style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); 
                               color: white; 
                               border: none; 
                               padding: 12px 30px; 
                               border-radius: 10px; 
                               font-weight: 600;
                               cursor: pointer;">
                    الترقية إلى Pro
                </button>
            </div>
        </div>
        <?php else: ?>
        <!-- عرض محتوى Heatmap العادي عند تفعيل Pro -->
        <div class="content-if">
            <div class="heatmap-header">
                <p>تعقب الفروض</p>
            </div>
            <div class="month-selector">
                <div class="month-nav">
                    <button onclick="changeMonth(-1)" aria-label="الشهر السابق">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <div class="current-month">
                        <?php 
                        $month_names = [
                            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
                            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
                            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
                        ];
                        echo $month_names[$selected_month] . ' ' . $selected_year;
                        ?>
                    </div>
                    <button onclick="changeMonth(1)" aria-label="الشهر التالي">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </div>
            </div>
            
            <div class="prayer-heatmap-container" id="prayer-heatmap-container">
                <?php if (empty($records)): ?>
                    <div class="no-data">
                        <i class="bi bi-calendar-x"></i>
                        <p>لا توجد بيانات للشهر الحالي</p>
                        <p>ابدأ بتسجيل صلواتك لرؤية الإحصائيات</p>
                    </div>
                <?php else: ?>
                    <div id="combined-heatmap"></div>
                    
                    <div class="legend">
                        <div class="legend-item">
                            <div class="legend-color legend-mosque"></div>
                            <span>صليت في المسجد</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-alone"></div>
                            <span>صليت منفرداً</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-delayed"></div>
                            <span>صليت متأخراً</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-not-prayed"></div>
                            <span>لم أصلِ</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-no-data"></div>
                            <span>لا توجد بيانات</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </section>

        <!-- قسم الإحصائيات -->
    <section class="statistics-if">
        <div class="cn-if">
            <div><p>إحصائيات</p></div>
            <div class="stat-gt">
                <div class="data-stat">
                    <?php if ($pro_enabled): ?>
                    <!-- عرض كل الأزرار إذا Pro مفعل -->
                    <span class="period-btn" data-period="day">يوم</span>
                    <span class="period-btn" data-period="week">أسبوع</span>
                    <span class="period-btn" data-period="month">الشهر</span>
                    <span class="period-btn" data-period="year">سنة</span>
                    <span class="period-btn active" data-period="lifetime">مدى الحياة</span>
                    <?php else: ?>
                    <!-- عرض فقط "مدى الحياة" مع إيقاف تفاعل الباقي -->
                    <span class="period-btn locked" title="متاح في النسخة Pro" style="opacity: 0.5; cursor: not-allowed;">يوم</span>
                    <span class="period-btn locked" title="متاح في النسخة Pro" style="opacity: 0.5; cursor: not-allowed;">أسبوع</span>
                    <span class="period-btn locked" title="متاح في النسخة Pro" style="opacity: 0.5; cursor: not-allowed;">الشهر</span>
                    <span class="period-btn locked" title="متاح في النسخة Pro" style="opacity: 0.5; cursor: not-allowed;">سنة</span>
                    <span class="period-btn active" data-period="lifetime">مدى الحياة</span>
                    <?php endif; ?>
                </div>
                <div class="card-stat">
                    <?php if ($pro_enabled): ?>
                    <!-- عرض كل الإحصائيات إذا Pro مفعل -->
                    <div class="card-item">
                        <div class="dis-card">
                            <i class="fa-solid fa-mosque"></i>
                            <span id="prayed_in_mosque">0%</span>
                        </div>
                        <p>في المسجد</p>
                    </div>
                    <div class="card-item">
                        <div class="dis-card">
                            <i class="fa-solid fa-mosque"></i>
                            <span id="delayed">0%</span>
                        </div>
                        <p>متأخر</p>
                    </div>
                    <div class="card-item">
                        <div class="dis-card">
                            <i class="fa-solid fa-mosque"></i>
                            <span id="prayed_alone">0%</span>
                        </div>
                        <p>منفرد</p>
                    </div>
                    <div class="card-item">
                        <div class="dis-card">
                            <i class="fa-solid fa-mosque"></i>
                            <span id="not_prayed">0%</span>
                        </div>
                        <p>لم تصلي</p>
                    </div>
                    <div class="card-item">
                        <div class="dis-card">
                            <i class="fa-solid fa-mosque"></i>
                            <span id="badges_count">0</span>
                        </div>
                        <p>عدد الشارات</p>
                    </div>
                    <?php else: ?>
                    <!-- عرض إحصائيات "مدى الحياة" فقط إذا Pro غير مفعل -->
                    <div class="card-item">
                        <div class="dis-card">
                            <i class="fa-solid fa-mosque"></i>
                            <span id="prayed_in_mosque_pro">...</span>
                        </div>
                        <p>في المسجد</p>
                    </div>
                    <div class="card-item">
                        <div class="dis-card">
                            <i class="fa-solid fa-mosque"></i>
                            <span id="delayed_pro">...</span>
                        </div>
                        <p>متأخر</p>
                    </div>
                    <div class="card-item">
                        <div class="dis-card">
                            <i class="fa-solid fa-mosque"></i>
                            <span id="prayed_alone_pro">...</span>
                        </div>
                        <p>منفرد</p>
                    </div>
                    <div class="card-item">
                        <div class="dis-card">
                            <i class="fa-solid fa-mosque"></i>
                            <span id="not_prayed_pro">...</span>
                        </div>
                        <p>لم تصلي</p>
                    </div>
                    <div class="card-item" style="grid-column: span 2; text-align: center;">
                        <p style="color: #6B7280; font-size: 14px; margin-top: 20px;">
                            <i class="bi bi-info-circle"></i>
                            إحصائيات مفصلة متاحة في النسخة Pro
                        </p>
                        <button onclick="window.location.href='pro_subscription.php'" 
                                style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); 
                                       color: white; 
                                       border: none; 
                                       padding: 10px 25px; 
                                       border-radius: 8px; 
                                       font-weight: 600;
                                       cursor: pointer;
                                       margin-top: 10px;">
                            الترقية الآن
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- الفوتر -->
    <footer class="footer">
        <nav class="nav-foot">
            <ul class="list-foot">
                <li class="item-foot"><a href="index.php" class="bi bi-house-fill"></a></li>
                <li class="item-foot"><a href="statistics.php" class="bi bi-bar-chart-fill"></a></li>
                <li class="item-foot"><a href="reminder.php" class="bi bi-bell-fill"></a></li>
                <li class="item-foot"><a href="profile.php" class="bi bi-person-fill"></a></li>
            </ul>
        </nav>
    </footer>
    
    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>
    
    <script>
        // بيانات من PHP إلى JavaScript
        const heatmapSeries = <?php echo json_encode($heatmap_series); ?>;
        const prayers = <?php echo json_encode($prayers_list); ?>;
        const selectedYear = <?php echo $selected_year; ?>;
        const selectedMonth = <?php echo $selected_month; ?>;
        const totalDays = <?php echo $total_days; ?>;
        
        // بيانات المداومة
        const currentStreak = <?php echo $current_streak; ?>;
        const maxStreak = <?php echo $max_streak; ?>;
        const prayedToday = <?php echo $prayed_today; ?>;
        const allPrayedToday = <?php echo $all_prayed_today ? 'true' : 'false'; ?>;
        const progressPercentage = <?php echo $progress_percentage; ?>;
        const totalCompleteDays = <?php echo $total_complete_days; ?>;
        const totalPrayerDays = <?php echo $total_prayer_days; ?>;
        
        // ============ وظائف المداومة ============
        function toggleStreakHistory() {
            const historyContent = document.getElementById('history-content');
            const toggleBtn = document.getElementById('toggle-history-btn');
            const icon = toggleBtn.querySelector('i');
            
            if (historyContent.classList.contains('show')) {
                historyContent.classList.remove('show');
                toggleBtn.innerHTML = '<i class="bi bi-chevron-down"></i> عرض';
            } else {
                historyContent.classList.add('show');
                toggleBtn.innerHTML = '<i class="bi bi-chevron-up"></i> إخفاء';
            }
        }
        
        function updateStreakDisplay() {
            // تحديث رسالة المداومة
            const messageElement = document.getElementById('streak-message');
            if (messageElement) {
                let message = '';
                
                if (allPrayedToday) {
                    if (currentStreak === 1) {
                        message = "ممتاز! بدأت سلسلة المداومة اليوم.";
                    } else if (currentStreak < 7) {
                        message = `رائع! أنت في اليوم ${currentStreak} من المداومة.`;
                    } else if (currentStreak < 30) {
                        message = `مذهل! ${currentStreak} يومًا متواصلًا.`;
                    } else {
                        message = `إنجاز خارق! ${currentStreak} يومًا من المداومة.`;
                    }
                } else if (prayedToday > 0) {
                    message = `صليت ${prayedToday} من 5 صلوات اليوم. أكمل الباقي!`;
                } else {
                    message = "ابدأ يومك بالصلاة لتبدأ سلسلة المداومة.";
                }
                
                messageElement.textContent = message;
            }
            
            // تأثيرات للصلوات المكتملة
            const prayedItems = document.querySelectorAll('.prayer-item.prayed');
            prayedItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.transform = 'scale(1.1)';
                    item.style.transition = 'transform 0.3s';
                    
                    setTimeout(() => {
                        item.style.transform = 'scale(1)';
                    }, 300);
                }, index * 100);
            });
        }
        
        // ============ وظائف Heatmap ============
        function initCombinedHeatmap() {
            if (heatmapSeries.length === 0) return;
            
            const options = {
                series: heatmapSeries,
                chart: {
                    type: 'heatmap',
                    height: 'auto',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    heatmap: {
                        shadeIntensity: 0.8,
                        radius: 0,
                        useFillColorAsStroke: false,
                        distributed: true,
                        colorScale: {
                            ranges: [
                                {
                                    from: 4,
                                    to: 4,
                                    color: '#10b981',
                                    name: 'مسجد'
                                },
                                {
                                    from: 3,
                                    to: 3,
                                    color: '#3B82F6',
                                    name: 'منفرد'
                                },
                                {
                                    from: 2,
                                    to: 2,
                                    color: '#F59E0B',
                                    name: 'متأخر'
                                },
                                {
                                    from: 1,
                                    to: 1,
                                    color: '#EF4444',
                                    name: 'لم أصل'
                                },
                                {
                                    from: 0,
                                    to: 0,
                                    color: '#E5E7EB',
                                    name: 'لا توجد بيانات'
                                }
                            ]
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        formatter: function(value) {
                            const date = new Date(value);
                            return date.getDate();
                        },
                        style: {
                            fontSize: window.innerWidth < 768 ? '10px' : '12px'
                        }
                    },
                    tooltip: {
                        enabled: false
                    },
                    tickAmount: Math.min(totalDays, window.innerWidth < 768 ? 15 : totalDays)
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: window.innerWidth < 768 ? '12px' : '14px',
                            fontWeight: 600
                        }
                    }
                },
                grid: {
                    padding: {
                        top: 10,
                        right: 10,
                        bottom: 10,
                        left: 10
                    }
                },
                tooltip: {
                    custom: function({ series, seriesIndex, dataPointIndex, w }) {
                        const seriesName = w.config.series[seriesIndex].name;
                        const data = w.config.series[seriesIndex].data[dataPointIndex];
                        const date = new Date(data.x);
                        const day = date.getDate();
                        const month = date.getMonth() + 1;
                        const year = date.getFullYear();
                        
                        let statusText = '';
                        let color = '#666';
                        
                        switch(data.status) {
                            case 'prayed_in_mosque':
                                statusText = 'صليت في المسجد';
                                color = '#10B981';
                                break;
                            case 'prayed_alone':
                                statusText = 'صليت منفرداً';
                                color = '#3B82F6';
                                break;
                            case 'delayed':
                                statusText = 'صليت متأخراً';
                                color = '#F59E0B';
                                break;
                            case 'not_prayed':
                                statusText = 'لم أصلِ';
                                color = '#EF4444';
                                break;
                            case 'no_data':
                                statusText = 'لا توجد بيانات';
                                color = '#9CA3AF';
                                break;
                            default:
                                statusText = data.status;
                        }
                        
                        return `
                            <div style="padding: 12px; background: white; border: 1px solid #ddd; border-radius: 8px; min-width: 200px;">
                                <div style="font-weight: bold; color: #111; font-size: 16px; margin-bottom: 5px;">${seriesName}</div>
                                <div style="color: #666; margin-bottom: 5px;">${day}/${month}/${year}</div>
                                <div style="color: ${color}; font-weight: 500; font-size: 14px; padding: 4px 8px; background: #f9fafb; border-radius: 4px; display: inline-block;">
                                    ${statusText}
                                </div>
                            </div>
                        `;
                    }
                },
                responsive: [
                    {
                        breakpoint: 768,
                        options: {
                            chart: {
                                height: 450
                            },
                            xaxis: {
                                labels: {
                                    style: {
                                        fontSize: '10px'
                                    }
                                },
                                tickAmount: 15
                            }
                        }
                    }
                ]
            };
            
            const chart = new ApexCharts(document.querySelector("#combined-heatmap"), options);
            chart.render();
        }
        
        // ============ تغيير الشهر ============
        function changeMonth(offset) {
            let newMonth = selectedMonth + offset;
            let newYear = selectedYear;
            
            if (newMonth > 12) {
                newMonth = 1;
                newYear++;
            } else if (newMonth < 1) {
                newMonth = 12;
                newYear--;
            }
            
            window.location.href = `?month=${newMonth}&year=${newYear} #bourd`;
        }
        
        // ============ تحميل الإحصائيات ============
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة المداومة
            updateStreakDisplay();
            
            // تهيئة الرسوم البيانية
            if (heatmapSeries.length > 0) {
                initCombinedHeatmap();
            }
            
            // تحميل إحصائيات الفترة
            loadStatistics('lifetime');
            
            // أحداث أزرار الفترة
            const periodButtons = document.querySelectorAll('.period-btn');
            periodButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const period = this.getAttribute('data-period');
                    
                    periodButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    loadStatistics(period);
                });
            });
            
            function loadStatistics(period) {
                // منع تحميل أي فترات غير "مدى الحياة" إذا Pro غير مفعل
                const proEnabled = <?php echo $pro_enabled ? 'true' : 'false'; ?>;
                
                if (!proEnabled && period !== 'lifetime') {
                    // إظهار رسالة للمستخدم
                    Swal.fire({
                        icon: 'info',
                        title: 'ميزة Pro مطلوبة',
                        text: 'هذه الإحصائيات التفصيلية متاحة فقط في النسخة Pro',
                        confirmButtonText: 'الترقية إلى Pro',
                        cancelButtonText: 'إلغاء',
                        showCancelButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'pro_subscription.php';
                        }
                    });
                    return;
                }
                
                // تحديث واجهة التحميل
                document.querySelectorAll('.card-stat span[id]').forEach(span => {
                    span.textContent = '...';
                });
                
                // جلب البيانات من API
                fetch(`api/statistics_logic.php?period=${period}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (proEnabled) {
                            // تحديث كل البيانات إذا Pro مفعل
                            document.getElementById('prayed_in_mosque').textContent = data.prayed_in_mosque + '%';
                            document.getElementById('delayed').textContent = data.delayed + '%';
                            document.getElementById('prayed_alone').textContent = data.prayed_alone + '%';
                            document.getElementById('not_prayed').textContent = data.not_prayed + '%';
                            document.getElementById('badges_count').textContent = data.badges_count;
                        } else {
                            // تحديث فقط إحصائيات "مدى الحياة" إذا Pro غير مفعل
                            document.getElementById('prayed_in_mosque_pro').textContent = data.prayed_in_mosque + '%';
                            document.getElementById('delayed_pro').textContent = data.delayed + '%';
                            document.getElementById('prayed_alone_pro').textContent = data.prayed_alone + '%';
                            document.getElementById('not_prayed_pro').textContent = data.not_prayed + '%';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // إظهار رسالة خطأ للمستخدم
                        Swal.fire({
                            icon: 'error',
                            title: 'خطأ في تحميل البيانات',
                            text: 'حدث خطأ أثناء تحميل الإحصائيات',
                            confirmButtonText: 'حسناً'
                        });
                    });
            }
            
            // تحديث تلقائي كل 5 دقائق
            setInterval(updateStreakDisplay, 5 * 60 * 1000);
        });
        
        // إعادة رسم الرسوم عند تغيير حجم النافذة
        window.addEventListener('resize', function() {
            if (window.ApexCharts && heatmapSeries.length > 0) {
                document.querySelectorAll('#combined-heatmap .apexcharts-canvas').forEach(canvas => {
                    canvas.remove();
                });
                initCombinedHeatmap();
            }
        });        
    </script>
</body>
</html>