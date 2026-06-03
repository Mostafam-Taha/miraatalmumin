<?php
session_start();
require_once 'includes/config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم مع المزيد من المعلومات
$userStmt = $pdo->prepare("
    SELECT u.*, 
           us.prayers_per_day, us.skip_day,
           (SELECT COUNT(*) FROM prayer_records WHERE user_id = u.id AND status != 'not_prayed' AND date < CURDATE()) as completed_prayers
    FROM users u
    LEFT JOIN user_settings us ON u.id = us.user_id
    WHERE u.id = ?
");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch();

if (!$user) {
    header('Location: logout.php');
    exit();
}

// جلب الصلوات الفائتة مع مزيد من التفاصيل
$missedPrayersStmt = $pdo->prepare("
    SELECT pr.*, 
           CASE 
               WHEN DATEDIFF(CURDATE(), pr.date) > 30 THEN 'قديمة'
               WHEN DATEDIFF(CURDATE(), pr.date) > 7 THEN 'متوسطة'
               ELSE 'حديثة'
           END as age_category
    FROM prayer_records pr
    WHERE pr.user_id = ? 
    AND pr.status = 'not_prayed' 
    AND pr.date < CURDATE()
    ORDER BY pr.date ASC
");
$missedPrayersStmt->execute([$user_id]);
$missedPrayers = $missedPrayersStmt->fetchAll();

// جلب الإنجازات والإحصائيات
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_missed,
        SUM(CASE WHEN DATEDIFF(CURDATE(), date) <= 3 THEN 1 ELSE 0 END) as recent_missed,
        SUM(CASE WHEN DATEDIFF(CURDATE(), date) > 30 THEN 1 ELSE 0 END) as old_missed,
        MIN(date) as oldest_missed,
        MAX(date) as newest_missed
    FROM prayer_records 
    WHERE user_id = ? 
    AND status = 'not_prayed' 
    AND date < CURDATE()
");
$statsStmt->execute([$user_id]);
$stats = $statsStmt->fetch();

// جلب الصلوات المكتملة اليوم
$todayCompletedStmt = $pdo->prepare("
    SELECT COUNT(*) as today_completed 
    FROM prayer_records 
    WHERE user_id = ? 
    AND status != 'not_prayed' 
    AND DATE(created_at) = CURDATE()
");
$todayCompletedStmt->execute([$user_id]);
$todayCompleted = $todayCompletedStmt->fetch()['today_completed'];

// معالجة الإعدادات
$settings = [
    'prayers_per_day' => $user['prayers_per_day'] ?? 5,
    'skip_day' => $user['skip_day'] ?? null
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_settings'])) {
        $prayers_per_day = isset($_POST['prayers_per_day']) ? (int)$_POST['prayers_per_day'] : 5;
        $skip_day = isset($_POST['skip_day']) ? $_POST['skip_day'] : null;
        
        // التحقق من القيم
        $prayers_per_day = max(1, min(20, $prayers_per_day));
        
        // تحديث الإعدادات
        $updateStmt = $pdo->prepare("
            UPDATE user_settings 
            SET prayers_per_day = ?, skip_day = ?, updated_at = NOW()
            WHERE user_id = ?
        ");
        $updateStmt->execute([$prayers_per_day, $skip_day, $user_id]);
        
        $_SESSION['makeup_settings'] = [
            'prayers_per_day' => $prayers_per_day,
            'skip_day' => $skip_day
        ];
        
        $success_message = "تم حفظ الإعدادات بنجاح! سيتم إعادة حساب جدول التكميل.";
        header("Refresh:2");
    }
    
    // تحديث حالة الصلاة
    if (isset($_POST['update_prayer'])) {
        $prayer_id = $_POST['prayer_id'];
        $status = $_POST['status'];
        
        // تسجيل في سجل الإنجازات
        $logStmt = $pdo->prepare("
            INSERT INTO prayer_achievements (user_id, prayer_id, action, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $logStmt->execute([$user_id, $prayer_id, 'makeup_completed']);
        
        $updatePrayer = $pdo->prepare("
            UPDATE prayer_records 
            SET status = ?, created_at = NOW() 
            WHERE id = ? AND user_id = ?
        ");
        $updatePrayer->execute([$status, $prayer_id, $user_id]);
        
        // زيادة تتابع الصلاة إذا تم إكمال جميع صلوات اليوم
        $checkDayStmt = $pdo->prepare("
            SELECT COUNT(*) as remaining 
            FROM prayer_records 
            WHERE user_id = ? 
            AND status = 'not_prayed'
            AND date < CURDATE()
        ");
        $checkDayStmt->execute([$user_id]);
        $remaining = $checkDayStmt->fetch()['remaining'];
        
        if ($remaining == 0) {
            // تحديث تتابع المستخدم
            $streakUpdate = $pdo->prepare("
                UPDATE users 
                SET current_prayer_streak = current_prayer_streak + 1,
                    max_prayer_streak = GREATEST(max_prayer_streak, current_prayer_streak + 1),
                    last_prayer_date = CURDATE()
                WHERE id = ?
            ");
            $streakUpdate->execute([$user_id]);
        }
        
        header("Location: makeup_prayers.php?updated=1&day=" . ($_GET['day'] ?? 1));
        exit();
    }
    
    // التحديث الجماعي
    if (isset($_POST['bulk_update'])) {
        $day_prayers = $_POST['prayer_ids'] ?? [];
        $status = $_POST['bulk_status'];
        
        if (!empty($day_prayers)) {
            $placeholders = str_repeat('?,', count($day_prayers) - 1) . '?';
            $bulkStmt = $pdo->prepare("
                UPDATE prayer_records 
                SET status = ?, created_at = NOW()
                WHERE id IN ($placeholders) AND user_id = ?
            ");
            $params = array_merge([$status], $day_prayers, [$user_id]);
            $bulkStmt->execute($params);
            
            header("Location: makeup_prayers.php?bulk=1&day=" . ($_GET['day'] ?? 1));
            exit();
        }
    }
}

// حساب جدول التكميل
function calculateSchedule($total, $per_day, $skip_day) {
    if ($total <= 0 || $per_day <= 0) return [];
    
    $schedule = [];
    $days_needed = ceil($total / $per_day);
    $current_date = date('Y-m-d');
    
    $arabic_days = [
        'Sunday' => 'الأحد', 'Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء',
        'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 
        'Friday' => 'الجمعة', 'Saturday' => 'السبت'
    ];
    
    $completed_days = isset($_SESSION['completed_days']) ? $_SESSION['completed_days'] : [];
    
    for ($i = 1; $i <= $days_needed; $i++) {
        $day_offset = $i - 1;
        
        // تخطي الأيام المكتملة
        while (isset($completed_days[$day_offset])) {
            $day_offset++;
        }
        
        $schedule_date = date('Y-m-d', strtotime($current_date . " +" . $day_offset . " days"));
        $day_name = date('l', strtotime($schedule_date));
        
        // تخطي اليوم المستثنى
        if ($skip_day && $day_name == $skip_day) {
            $day_offset++;
            $schedule_date = date('Y-m-d', strtotime($current_date . " +" . $day_offset . " days"));
            $day_name = date('l', strtotime($schedule_date));
            $days_needed++;
        }
        
        $start_index = ($i - 1) * $per_day;
        $end_index = min($start_index + $per_day, $total);
        $prayers_count = $end_index - $start_index;
        
        $schedule[] = [
            'day_number' => $i,
            'date' => $schedule_date,
            'arabic_date' => date('d/m/Y', strtotime($schedule_date)),
            'day_name' => $arabic_days[$day_name],
            'prayers_count' => $prayers_count,
            'start_index' => $start_index,
            'end_index' => $end_index,
            'is_today' => ($schedule_date == date('Y-m-d')),
            'is_completed' => in_array($i, $completed_days)
        ];
    }
    
    return $schedule;
}

$total_missed = $stats['total_missed'] ?? 0;
$schedule = calculateSchedule($total_missed, $settings['prayers_per_day'], $settings['skip_day']);

$current_day = isset($_GET['day']) ? (int)$_GET['day'] : 1;
if ($current_day < 1) $current_day = 1;
if (!empty($schedule) && $current_day > count($schedule)) {
    $current_day = count($schedule);
}

$current_prayers = [];
$current_schedule = [];
if (!empty($schedule) && isset($schedule[$current_day - 1])) {
    $current_schedule = $schedule[$current_day - 1];
    $current_prayers = array_slice($missedPrayers, 
        $current_schedule['start_index'], 
        $current_schedule['prayers_count']
    );
}

// حساب التقدم
$progress_percent = 0;
if ($total_missed > 0) {
    $completed_today = $todayCompleted;
    $total_for_today = $current_schedule['prayers_count'] ?? 0;
    if ($total_for_today > 0) {
        $progress_percent = min(100, ($completed_today / $total_for_today) * 100);
    }
}

// جلب التحفيزات اليومية
$motivations = [
    "قال تعالى: \"وَأَقِيمُوا الصَّلَاةَ وَآتُوا الزَّكَاةَ وَارْكَعُوا مَعَ الرَّاكِعِينَ\" (البقرة: 43)",
    "عن أبي هريرة رضي الله عنه قال: قال رسول الله ﷺ: \"أول ما يحاسب به العبد يوم القيامة الصلاة\"",
    "كل صلاة تقضيها ترفع درجتك عند الله",
    "التوبة تجب ما قبلها، والإخلاص في القضاء يمحو الإثم",
    "الصلاة عماد الدين، فمن أقامها أقام الدين"
];
$daily_motivation = $motivations[array_rand($motivations)];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رحلة تكميل الصلوات - <?php echo htmlspecialchars($user['name']); ?></title>
    <style>
        :root {
            --primary-color: #047857;
            --secondary-color: #059669;
            --accent-color: #111827;
            --gold-color: #f59e0b;
            --silver-color: #9ca3af;
            --bronze-color: #92400e;
            --light-bg: #f8f9fa;
            --text-dark: #333;
            --text-light: #666;
            --white: #ffffff;
            --border-radius: 12px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Arial', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            color: var(--text-dark);
            min-height: 100vh;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* الهيدر المحسن */
        .header {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--primary-color) 100%);
            color: var(--white);
            padding: 30px 0;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        /* بطاقة التحفيز */
        .motivation-card {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border: 2px solid var(--gold-color);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            text-align: center;
            animation: fadeIn 1s ease;
        }
        
        .motivation-icon {
            font-size: 3rem;
            color: var(--gold-color);
            margin-bottom: 15px;
        }
        
        .motivation-text {
            font-size: 1.2rem;
            color: #92400e;
            font-style: italic;
            line-height: 1.8;
        }
        
        /* لوحة الإحصائيات المحسنة */
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: all 0.3s ease;
            border-top: 5px solid var(--secondary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card.warning {
            border-top-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        }
        
        .stat-card.success {
            border-top-color: var(--primary-color);
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }
        
        /* شارة الإنجاز */
        .badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 0.9rem;
            margin: 5px;
        }
        
        .badge-gold {
            background: linear-gradient(45deg, #fbbf24, #f59e0b);
            color: white;
        }
        
        .badge-silver {
            background: linear-gradient(45deg, #d1d5db, #9ca3af);
            color: white;
        }
        
        .badge-bronze {
            background: linear-gradient(45deg, #92400e, #78350f);
            color: white;
        }
        
        /* شريط التقدم المحسن */
        .progress-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            position: relative;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .progress-bar {
            height: 25px;
            background: #e5e7eb;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
            border-radius: 15px;
            transition: width 1s ease;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        /* بطاقة اليوم */
        .day-card {
            background: linear-gradient(135deg, var(--white) 0%, #f8fafc 100%);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .day-card.active {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        
        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        /* قائمة الصلوات المحسنة */
        .prayer-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }
        
        .prayer-item {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .prayer-item:hover {
            border-color: var(--secondary-color);
            transform: translateY(-3px);
        }
        
        .prayer-age {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .age-new {
            background: #d1fae5;
            color: var(--primary-color);
        }
        
        .age-medium {
            background: #fef3c7;
            color: #92400e;
        }
        
        .age-old {
            background: #fee2e2;
            color: #dc2626;
        }
        
        /* الأزرار المحسنة */
        .btn {
            padding: 14px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            box-shadow: 0 4px 6px rgba(5, 150, 105, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(5, 150, 105, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        /* رسائل التنبيه */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            z-index: 1000;
            animation: slideInRight 0.5s ease, fadeOut 0.5s ease 4.5s forwards;
            max-width: 400px;
        }
        
        .notification-success {
            background: var(--white);
            border-right: 5px solid var(--primary-color);
        }
        
        /* الرسوم المتحركة */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* التلميحات */
        .tooltip {
            position: relative;
            display: inline-block;
            cursor: help;
        }
        
        .tooltip .tooltip-text {
            visibility: hidden;
            width: 250px;
            background-color: var(--accent-color);
            color: white;
            text-align: center;
            border-radius: var(--border-radius);
            padding: 10px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.9rem;
        }
        
        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
        
        /* تذييل الصفحة */
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        /* التكيف مع الجوال */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .prayer-list {
                grid-template-columns: 1fr;
            }
            
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .day-header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- إشعارات النجاح -->
        <?php if (isset($_GET['updated'])): ?>
            <div class="notification notification-success">
                <i class="fas fa-check-circle" style="color: var(--primary-color); font-size: 1.5rem; margin-left: 10px;"></i>
                <div>
                    <strong>تهانينا!</strong><br>
                    تم تحديث حالة الصلاة بنجاح. استمر في التقدم! 🎯
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['bulk'])): ?>
            <div class="notification notification-success">
                <i class="fas fa-bolt" style="color: var(--gold-color); font-size: 1.5rem; margin-left: 10px;"></i>
                <div>
                    <strong>إنجاز رائع!</strong><br>
                    تم تحديث جميع الصلوات بنجاح. أحسنت العمل الجماعي! ⚡
                </div>
            </div>
        <?php endif; ?>
        
        <!-- الهيدر -->
        <div class="header">
            <a href="profile.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; position: absolute; right: 20px; top: 20px;">
                <i class="fas fa-arrow-right"></i> رجوع
            </a>
            <div class="header-content">
                <h1><i class="fas fa-mountain"></i> رحلة تكميل الصلوات</h1>
                <p style="opacity: 0.9; font-size: 1.1rem; margin-top: 10px;">
                    كل خطوة تقربك من الله، وكل صلاة تبني مستقبلك الأخروي
                </p>
            </div>
        </div>
        
        <!-- التحفيز اليومي -->
        <div class="motivation-card">
            <div class="motivation-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="motivation-text">
                "<?php echo $daily_motivation; ?>"
            </div>
            <div style="margin-top: 15px; color: #92400e; font-size: 0.9rem;">
                <i class="fas fa-lightbulb"></i> تذكير: كل صلاة تقضيها ترفع من درجاتك وتزيد حسناتك
            </div>
        </div>
        
        <!-- لوحة الإحصائيات -->
        <div class="dashboard">
            <div class="stat-card">
                <i class="fas fa-clock" style="font-size: 2.5rem; color: var(--accent-color);"></i>
                <div class="stat-number" style="font-size: 2.5rem; color: var(--primary-color); margin: 10px 0;">
                    <?php echo $total_missed; ?>
                </div>
                <div class="stat-label">إجمالي الصلوات الفائتة</div>
                <?php if ($stats['oldest_missed']): ?>
                    <div style="margin-top: 10px; font-size: 0.9rem; color: var(--text-light);">
                        <i class="far fa-calendar"></i> أقدمها: <?php echo date('d/m/Y', strtotime($stats['oldest_missed'])); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="stat-card success">
                <i class="fas fa-check-circle" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                <div class="stat-number" style="font-size: 2.5rem; color: var(--primary-color); margin: 10px 0;">
                    <?php echo $todayCompleted; ?>
                </div>
                <div class="stat-label">صلاة مكتملة اليوم</div>
                <div style="margin-top: 10px;">
                    <span class="badge badge-gold">
                        <i class="fas fa-fire"></i> تتابع: <?php echo $user['current_prayer_streak']; ?> يوم
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-calendar-alt" style="font-size: 2.5rem; color: var(--accent-color);"></i>
                <div class="stat-number" style="font-size: 2.5rem; color: var(--primary-color); margin: 10px 0;">
                    <?php echo count($schedule); ?>
                </div>
                <div class="stat-label">يوم لإنهاء التكميل</div>
                <?php if ($settings['skip_day']): ?>
                    <div style="margin-top: 10px; font-size: 0.9rem; color: var(--text-light);">
                        <i class="fas fa-umbrella-beach"></i> إجازة: <?php echo $settings['skip_day'] == 'Friday' ? 'الجمعة' : 'السبت'; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="stat-card warning">
                <i class="fas fa-bolt" style="font-size: 2.5rem; color: #f59e0b;"></i>
                <div class="stat-number" style="font-size: 2.5rem; color: #f59e0b; margin: 10px 0;">
                    <?php echo $settings['prayers_per_day']; ?>
                </div>
                <div class="stat-label">صلاة يوميًا</div>
                <div style="margin-top: 10px;">
                    <button onclick="document.getElementById('settings-form').scrollIntoView({behavior: 'smooth'})" 
                            class="btn" style="padding: 8px 15px; font-size: 0.9rem;">
                        <i class="fas fa-edit"></i> تعديل
                    </button>
                </div>
            </div>
        </div>
        
        <!-- شارات الإنجاز -->
        <div style="background: var(--white); border-radius: var(--border-radius); padding: 20px; margin-bottom: 30px; box-shadow: var(--shadow); text-align: center;">
            <h3 style="color: var(--primary-color); margin-bottom: 15px;">
                <i class="fas fa-trophy"></i> إنجازاتك
            </h3>
            <div>
                <span class="badge badge-gold">
                    <i class="fas fa-crown"></i> التتابع الذهبي
                </span>
                <span class="badge badge-silver">
                    <i class="fas fa-medal"></i> الصلاة في الوقت
                </span>
                <span class="badge badge-bronze">
                    <i class="fas fa-star"></i> المثابرة
                </span>
                <?php if ($todayCompleted >= 3): ?>
                    <span class="badge" style="background: linear-gradient(45deg, #8b5cf6, #7c3aed); color: white;">
                        <i class="fas fa-bolt"></i> اليوم النشط
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- تتبع التقدم -->
        <div class="progress-container">
            <div class="progress-header">
                <h3 style="color: var(--primary-color);">
                    <i class="fas fa-route"></i> رحلة التكميل
                </h3>
                <div style="display: flex; gap: 10px;">
                    <span class="tooltip">
                        <i class="fas fa-info-circle" style="color: var(--text-light);"></i>
                        <span class="tooltip-text">
                            خطة التكميل تحسب بناءً على عدد الصلوات اليومية التي حددتها
                        </span>
                    </span>
                </div>
            </div>
            
            <?php if ($total_missed > 0 && !empty($schedule)): ?>
                <?php
                $total_days = count($schedule);
                $completed_days = 0;
                foreach ($schedule as $day) {
                    if ($day['is_completed']) $completed_days++;
                }
                $overall_progress = ($completed_days / $total_days) * 100;
                ?>
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $overall_progress; ?>%"></div>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin: 15px 0;">
                    <div>
                        <span style="font-weight: bold; color: var(--accent-color);"><?php echo $completed_days; ?></span>
                        <span style="color: var(--text-light);">يوم مكتمل</span>
                    </div>
                    <div>
                        <span style="font-weight: bold; color: var(--accent-color);"><?php echo $total_days - $completed_days; ?></span>
                        <span style="color: var(--text-light);">يوم متبقٍ</span>
                    </div>
                    <div>
                        <span style="font-weight: bold; color: var(--primary-color);"><?php echo round($overall_progress); ?>%</span>
                        <span style="color: var(--text-light);">إكمال</span>
                    </div>
                </div>
                
                <!-- جدول الأيام -->
                <div class="schedule-list">
                    <?php foreach ($schedule as $day): ?>
                        <a href="?day=<?php echo $day['day_number']; ?>" 
                           style="display: block; text-decoration: none; color: inherit;">
                            <div class="day-card <?php echo $current_day == $day['day_number'] ? 'active' : ''; ?> 
                                 <?php echo $day['is_today'] ? 'tooltip' : ''; ?>"
                                 <?php if ($day['is_today']): ?>
                                 title="هذا هو يومك الحالي!"
                                 <?php endif; ?>>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--accent-color);">
                                            اليوم <?php echo $day['day_number']; ?>
                                        </div>
                                        <div style="color: var(--text-light); font-size: 0.9rem;">
                                            <?php echo $day['arabic_date']; ?>
                                        </div>
                                    </div>
                                    <div style="text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">
                                            <?php echo $day['prayers_count']; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-light);">صلاة</div>
                                    </div>
                                </div>
                                <div style="margin-top: 10px; display: flex; justify-content: space-between;">
                                    <span style="background: #e5e7eb; padding: 3px 10px; border-radius: 15px; font-size: 0.8rem;">
                                        <?php echo $day['day_name']; ?>
                                    </span>
                                    <?php if ($day['is_completed']): ?>
                                        <span style="color: var(--primary-color);">
                                            <i class="fas fa-check-circle"></i> مكتمل
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($total_missed > 0): ?>
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-calendar-plus" style="font-size: 4rem; color: var(--primary-color);"></i>
                    <h3 style="margin: 20px 0; color: var(--accent-color);">ابدأ رحلتك الآن</h3>
                    <p style="color: var(--text-light); margin-bottom: 20px;">
                        قم بضبط الإعدادات أدناه لبدء خطة تكميل الصلوات
                    </p>
                    <a href="#settings-form" class="btn btn-primary">
                        <i class="fas fa-cog"></i> إعدادات التكميل
                    </a>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-trophy" style="font-size: 4rem; color: var(--gold-color);"></i>
                    <h3 style="margin: 20px 0; color: var(--primary-color);">تهانينا! 🎉</h3>
                    <p style="color: var(--text-light); font-size: 1.1rem;">
                        لقد أتممت جميع الصلوات. استمر في المحافظة على هذا التميز!
                    </p>
                    <div style="margin-top: 20px;">
                        <span class="badge badge-gold" style="font-size: 1.1rem; padding: 10px 20px;">
                            <i class="fas fa-crown"></i> إنجاز استثنائي
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- قسم الإعدادات -->
        <div id="settings-form" class="day-card" style="animation: fadeIn 1s ease;">
            <h3 style="color: var(--primary-color); margin-bottom: 25px;">
                <i class="fas fa-sliders-h"></i> تخصيص خطتك
            </h3>
            
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <div>
                        <label style="display: block; margin-bottom: 10px; color: var(--accent-color); font-weight: 600;">
                            <i class="fas fa-running"></i> سرعة التكميل
                        </label>
                        <input type="range" 
                               name="prayers_per_day" 
                               min="1" 
                               max="20" 
                               value="<?php echo $settings['prayers_per_day']; ?>"
                               style="width: 100%;"
                               oninput="updateSliderValue(this.value)">
                        <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                            <span style="color: var(--text-light); font-size: 0.9rem;">بطيء</span>
                            <span id="sliderValue" style="font-weight: bold; color: var(--primary-color);">
                                <?php echo $settings['prayers_per_day']; ?> صلاة/يوم
                            </span>
                            <span style="color: var(--text-light); font-size: 0.9rem;">سريع</span>
                        </div>
                        <div style="margin-top: 10px; font-size: 0.9rem; color: var(--text-light);">
                            <i class="fas fa-info-circle"></i> عدد الصلوات التي ستقضيها يومياً
                        </div>
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 10px; color: var(--accent-color); font-weight: 600;">
                            <i class="fas fa-umbrella-beach"></i> يوم الراحة
                        </label>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <label style="display: flex; align-items: center; padding: 10px; background: <?php echo empty($settings['skip_day']) ? 'var(--light-bg)' : '#f0f9ff'; ?>; border-radius: var(--border-radius); cursor: pointer;">
                                <input type="radio" name="skip_day" value="" 
                                       <?php echo empty($settings['skip_day']) ? 'checked' : ''; ?>
                                       style="margin-left: 8px;">
                                <span>لا يوجد راحة</span>
                            </label>
                            <label style="display: flex; align-items: center; padding: 10px; background: <?php echo $settings['skip_day'] == 'Friday' ? '#f0f9ff' : 'var(--light-bg)'; ?>; border-radius: var(--border-radius); cursor: pointer; border: <?php echo $settings['skip_day'] == 'Friday' ? '2px solid var(--primary-color)' : 'none'; ?>">
                                <input type="radio" name="skip_day" value="Friday" 
                                       <?php echo $settings['skip_day'] == 'Friday' ? 'checked' : ''; ?>
                                       style="margin-left: 8px;">
                                <span>الجمعة</span>
                            </label>
                            <label style="display: flex; align-items: center; padding: 10px; background: <?php echo $settings['skip_day'] == 'Saturday' ? '#f0f9ff' : 'var(--light-bg)'; ?>; border-radius: var(--border-radius); cursor: pointer; border: <?php echo $settings['skip_day'] == 'Saturday' ? '2px solid var(--primary-color)' : 'none'; ?>">
                                <input type="radio" name="skip_day" value="Saturday" 
                                       <?php echo $settings['skip_day'] == 'Saturday' ? 'checked' : ''; ?>
                                       style="margin-left: 8px;">
                                <span>السبت</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="save_settings" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-rocket"></i> ابدأ الرحلة
                </button>
            </form>
        </div>
        
        <!-- عرض الصلوات ليوم محدد -->
        <?php if ($total_missed > 0 && !empty($schedule) && !empty($current_prayers)): ?>
            <div class="day-card active" style="animation: fadeIn 1s ease;">
                <div class="day-header">
                    <div>
                        <h2 style="color: var(--accent-color);">
                            <i class="fas fa-calendar-day"></i> اليوم <?php echo $current_day; ?>
                        </h2>
                        <p style="color: var(--text-light); margin-top: 5px;">
                            <?php echo $current_schedule['day_name']; ?> - <?php echo $current_schedule['arabic_date']; ?>
                        </p>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--primary-color);">
                            <?php echo count($current_prayers); ?>
                        </div>
                        <div style="color: var(--text-light);">صلاة لهذا اليوم</div>
                    </div>
                </div>
                
                <!-- شريط تقدم اليوم -->
                <div style="margin: 20px 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--accent-color); font-weight: 600;">
                            تقدم اليوم: <?php echo $todayCompleted; ?> من <?php echo count($current_prayers); ?>
                        </span>
                        <span style="color: var(--primary-color); font-weight: bold;">
                            <?php echo round($progress_percent); ?>%
                        </span>
                    </div>
                    <div style="height: 10px; background: #e5e7eb; border-radius: 5px; overflow: hidden;">
                        <div style="height: 100%; background: linear-gradient(90deg, var(--secondary-color), var(--primary-color)); width: <?php echo $progress_percent; ?>%; border-radius: 5px;"></div>
                    </div>
                </div>
                
                <!-- تحديث جماعي -->
                <?php if (count($current_prayers) > 1): ?>
                    <form method="POST" style="margin-bottom: 20px; background: #f0f9ff; padding: 20px; border-radius: var(--border-radius);">
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 5px; color: var(--accent-color); font-weight: 600;">
                                    <i class="fas fa-bolt"></i> تحديث جميع صلوات اليوم:
                                </label>
                                <select name="bulk_status" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: var(--border-radius);">
                                    <option value="">اختر حالة للجميع</option>
                                    <option value="prayed_in_mosque">صليت في المسجد</option>
                                    <option value="prayed_alone">صليت منفردًا</option>
                                    <option value="delayed">مؤجلة</option>
                                </select>
                            </div>
                            <div>
                                <?php foreach ($current_prayers as $prayer): ?>
                                    <input type="hidden" name="prayer_ids[]" value="<?php echo $prayer['id']; ?>">
                                <?php endforeach; ?>
                                <button type="submit" name="bulk_update" class="btn btn-success">
                                    <i class="fas fa-check-double"></i> تطبيق للكل
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
                
                <!-- قائمة الصلوات -->
                <div class="prayer-list">
                    <?php foreach ($current_prayers as $index => $prayer): ?>
                        <div class="prayer-item">
                            <div class="prayer-age <?php echo 'age-' . ($prayer['age_category'] == 'قديمة' ? 'old' : ($prayer['age_category'] == 'متوسطة' ? 'medium' : 'new')); ?>">
                                <?php echo $prayer['age_category']; ?>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                <div>
                                    <div class="prayer-name" style="font-size: 1.4rem;">
                                        <?php 
                                        $prayer_names = [
                                            'Fajr' => 'الفجر',
                                            'Dhuhr' => 'الظهر',
                                            'Asr' => 'العصر',
                                            'Maghrib' => 'المغرب',
                                            'Isha' => 'العشاء'
                                        ];
                                        echo $prayer_names[$prayer['prayer_name']] ?? $prayer['prayer_name'];
                                        ?>
                                    </div>
                                    <div class="prayer-date" style="margin-top: 5px;">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('d/m/Y', strtotime($prayer['date'])); ?>
                                        <span style="margin-right: 10px; color: var(--text-light);">
                                            (قبل <?php echo floor((strtotime('today') - strtotime($prayer['date'])) / (60*60*24)); ?> يوم)
                                        </span>
                                    </div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 0.9rem; color: var(--text-light);">ترتيب</div>
                                    <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);">
                                        <?php echo $index + 1; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="POST" class="action-form">
                                <input type="hidden" name="prayer_id" value="<?php echo $prayer['id']; ?>">
                                <select name="status" class="select-status" onchange="this.form.submit()" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: var(--border-radius); font-size: 1rem;">
                                    <option value="">حدد حالة الصلاة</option>
                                    <option value="prayed_in_mosque">
                                        <i class="fas fa-mosque"></i> صليت في المسجد
                                    </option>
                                    <option value="prayed_alone">
                                        <i class="fas fa-user"></i> صليت منفردًا
                                    </option>
                                    <option value="delayed">
                                        <i class="fas fa-clock"></i> أريد تأجيلها
                                    </option>
                                </select>
                                <input type="hidden" name="update_prayer" value="1">
                            </form>
                            
                            <?php if ($index == 0): ?>
                                <div style="margin-top: 15px; padding: 10px; background: #f0f9ff; border-radius: var(--border-radius); font-size: 0.9rem; color: var(--primary-color);">
                                    <i class="fas fa-lightbulb"></i> نصيحة: ابدأ بهذه الصلاة الأولى لتحفيز نفسك على الإكمال
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- ملاحظات وتحفيز -->
                <div style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: var(--border-radius);">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <i class="fas fa-bullhorn" style="font-size: 2rem; color: var(--primary-color);"></i>
                        <div>
                            <h4 style="color: var(--accent-color); margin-bottom: 5px;">تحفيز اليوم!</h4>
                            <p style="color: var(--text-light);">
                                إكمال هذا اليوم سيزيد تتابعك إلى <?php echo $user['current_prayer_streak'] + 1; ?> يوم!
                                <?php if (count($current_prayers) <= 3): ?>
                                    يمكنك إنهائه بسهولة خلال ساعة واحدة!
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- التنقل بين الأيام -->
        <?php if (!empty($schedule)): ?>
            <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                <?php if ($current_day > 1): ?>
                    <a href="?day=<?php echo $current_day - 1; ?>" class="btn" style="background: var(--light-bg); color: var(--accent-color);">
                        <i class="fas fa-arrow-right"></i> اليوم السابق
                    </a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
                
                <div style="text-align: center;">
                    <div style="font-size: 1.2rem; font-weight: bold; color: var(--accent-color);">
                        اليوم <?php echo $current_day; ?> من <?php echo count($schedule); ?>
                    </div>
                    <div style="font-size: 0.9rem; color: var(--text-light); margin-top: 5px;">
                        <?php 
                        $remaining_days = count($schedule) - $current_day;
                        if ($remaining_days > 0) {
                            echo "متبقي " . $remaining_days . " يوم";
                        } else {
                            echo "آخر يوم في الرحلة!";
                        }
                        ?>
                    </div>
                </div>
                
                <?php if ($current_day < count($schedule)): ?>
                    <a href="?day=<?php echo $current_day + 1; ?>" class="btn btn-primary">
                        اليوم التالي <i class="fas fa-arrow-left"></i>
                    </a>
                <?php else: ?>
                    <button class="btn btn-success" onclick="alert('تهانينا! لقد أكملت الرحلة 🎉')">
                        <i class="fas fa-flag-checkered"></i> إنهاء الرحلة
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- تذييل الصفحة -->
        <div class="footer">
            <p>رحلة تكميل الصلوات - كل صلاة تقربك من الجنة</p>
            <p style="font-size: 0.8rem; margin-top: 5px; color: var(--text-light);">
                <i class="far fa-copyright"></i> <?php echo date('Y'); ?> Miraat Al_Mumin. جميع الحقوق محفوظة.
            </p>
        </div>
    </div>
    
    <script>
        // تحديث قيمة السلايدر
        function updateSliderValue(value) {
            document.getElementById('sliderValue').textContent = value + ' صلاة/يوم';
        }
        
        // رسالة تحفيزية عند التركيز
        window.addEventListener('focus', function() {
            const motivations = [
                "أحسنت! عدت للعمل 💪",
                "استمر في التقدم، أنت على الطريق الصحيح 🎯",
                "كل صلاة تقضيها ترفع درجتك عند الله 📈"
            ];
            const randomMotivation = motivations[Math.floor(Math.random() * motivations.length)];
            
            // يمكن إضافة إشعار هنا إذا أردت
            console.log(randomMotivation);
        });
        
        // مؤقت تلقائي
        let prayerCounter = <?php echo $todayCompleted; ?>;
        function simulateProgress() {
            if (prayerCounter < <?php echo count($current_prayers); ?>) {
                prayerCounter++;
                updateProgressBar();
            }
        }
        
        // تحديث شريط التقدم
        function updateProgressBar() {
            const total = <?php echo count($current_prayers); ?>;
            const percent = (prayerCounter / total) * 100;
            document.querySelector('.progress-fill').style.width = percent + '%';
        }
        
        // تحريك العناصر عند التمرير
        const observerOptions = {
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeIn 0.8s ease forwards';
                }
            });
        }, observerOptions);
        
        // مراقبة جميع العناصر التي نريد تحريكها
        document.querySelectorAll('.stat-card, .day-card, .prayer-item').forEach(el => {
            observer.observe(el);
        });
        
        // تأثير عند النقر على الصلوات
        document.querySelectorAll('.prayer-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (!e.target.matches('select, option, button, input')) {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                }
            });
        });
    </script>
</body>
</html>