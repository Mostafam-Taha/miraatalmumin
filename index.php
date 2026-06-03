<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/config.php';
require_once 'api/hijri_date.php';

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

$selected_date = isset($_GET['date']) ? $_GET['date'] : $today;

$today_timestamp = strtotime($today);
$selected_timestamp = strtotime($selected_date);
$is_future_date = ($selected_timestamp > $today_timestamp);

$user_city = $_SESSION['user_city'] ?? 'Cairo';
$user_country = $_SESSION['user_country'] ?? 'Egypt';
$calculation_method = $_SESSION['calculation_method'] ?? 5;

$prayer_times = [
    'الفجر' => '--:--',
    'الظهر' => '--:--',
    'العصر' => '--:--',
    'المغرب' => '--:--',
    'العشاء' => '--:--'
];

$api_url = "https://api.aladhan.com/v1/timingsByCity/{$selected_date}?city={$user_city}&country={$user_country}&method={$calculation_method}";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200 && $response) {
    $data = json_decode($response, true);
    if (isset($data['data']['timings'])) {
        $timings = $data['data']['timings'];
        $prayer_times = [
            'الفجر'  => $timings['Fajr'],
            'الظهر'  => $timings['Dhuhr'],
            'العصر'  => $timings['Asr'],
            'المغرب' => $timings['Maghrib'],
            'العشاء' => $timings['Isha']
        ];
    }
}

if ($is_future_date) {
    $prayer_records     = [];
    $nawafil_records    = [];
    $additional_prayers = [];
    $fasting_records    = [];
    $optional_nawafil   = [];
} else {
    $stmt = $pdo->prepare("SELECT prayer_name, status FROM prayer_records WHERE user_id = :user_id AND date = :date");
    $stmt->execute(['user_id' => $user_id, 'date' => $selected_date]);
    $prayer_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT prayer_name, nawafil_type, rakat_count FROM nawafil_records WHERE user_id = :user_id AND date = :date");
    $stmt->execute(['user_id' => $user_id, 'date' => $selected_date]);
    $nawafil_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, prayer_name, rakats, notes, is_checked FROM additional_prayers WHERE user_id = :user_id AND date = :date ORDER BY id DESC");
    $stmt->execute(['user_id' => $user_id, 'date' => $selected_date]);
    $additional_prayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT id, fasting_type, notes, is_checked FROM fasting_records WHERE user_id = :user_id AND date = :date");
    $stmt->execute(['user_id' => $user_id, 'date' => $selected_date]);
    $fasting_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // السنن الاختيارية: الضحى وقيام الليل
    $optional_nawafil = [];
    foreach ($nawafil_records as $rec) {
        if (in_array($rec['prayer_name'], ['الضحى', 'قيام الليل'])) {
            $optional_nawafil[$rec['prayer_name']] = $rec['rakat_count'];
        }
    }
}

$hijri_date = convertToHijri($selected_date);

$prayer_status = [];
foreach ($prayer_records as $record) {
    $prayer_status[$record['prayer_name']] = $record['status'];
}

$prayer_nawafil = [];
foreach ($nawafil_records as $record) {
    if (!in_array($record['prayer_name'], ['الضحى', 'قيام الليل'])) {
        $prayer_nawafil[$record['prayer_name']][$record['nawafil_type']] = $record['rakat_count'];
    }
}

$nawafil_info = [
    'الفجر'  => ['قبل' => 2, 'بعد' => 0],
    'الظهر'  => ['قبل' => 4, 'بعد' => 2],
    'العصر'  => ['قبل' => 0, 'بعد' => 0],
    'المغرب' => ['قبل' => 0, 'بعد' => 2],
    'العشاء' => ['قبل' => 0, 'بعد' => 2]
];

$completed_prayers = 0;
foreach ($prayer_status as $status) {
    if ($status === 'prayed_in_mosque' || $status === 'prayed_alone') {
        $completed_prayers++;
    }
}

$completed_additional = 0;
foreach ($additional_prayers as $prayer) {
    if ($prayer['is_checked']) $completed_additional++;
}

$completed_fasting = 0;
foreach ($fasting_records as $fasting) {
    if ($fasting['is_checked']) $completed_fasting++;
}

$completion_percentage = ($completed_prayers / 5) * 100;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>مرآة المؤمن | تتبع الصلوات اليومية</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/prayer.css">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-RSG9M1LGJD"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-RSG9M1LGJD');
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            min-height: 100vh;
            padding-bottom: 80px;
        }
        .modern-header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 20px 20px 30px 20px;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 4px 20px rgba(5,150,105,0.3);
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .welcome-text { font-size: 16px; opacity: 0.9; }
        .welcome-text i { margin-left: 5px; }
        .header-actions { display: flex; gap: 15px; }
        .header-actions a { color: white; font-size: 22px; text-decoration: none; transition: transform 0.2s; }
        .header-actions a:hover { transform: scale(1.1); }
        .stats-container { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 10px; }
        .stats-card { background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 15px; padding: 12px; text-align: center; }
        .stats-card .stats-number { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
        .stats-card .stats-label { font-size: 11px; opacity: 0.9; }
        .progress-card { grid-column: span 3; padding: 15px; }
        .stats-title { font-size: 14px; opacity: 0.9; margin-bottom: 10px; display: flex; justify-content: space-between; }
        .progress-bar-container { background: rgba(255,255,255,0.3); border-radius: 20px; height: 12px; overflow: hidden; margin-bottom: 10px; }
        .progress-bar-fill { background: #fbbf24; height: 100%; border-radius: 20px; transition: width 0.5s ease; width: <?php echo $completion_percentage; ?>%; }
        .stats-numbers { display: flex; justify-content: space-between; font-size: 12px; }
        .date-navigation { background: white; margin: 15px 15px 20px 15px; border-radius: 25px; padding: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .date-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 0 10px; }
        .nav-btn { background: #059669; border: none; width: 45px; height: 45px; border-radius: 25px; font-size: 20px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; color: white; }
        .nav-btn:hover { transform: scale(1.05); }
        .nt-one { background: #059669; color: white; width: auto; padding: 0 25px; font-weight: 600; }
        .date-display { text-align: center; margin-bottom: 15px; padding: 10px; background: #f0fdf4; border-radius: 15px; }
        .gregorian-date { font-size: 18px; font-weight: 600; color: #065f46; }
        .hijri-date { font-size: 14px; color: #059669; margin-top: 5px; }
        .date-scroll { overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; scrollbar-width: thin; padding: 5px 0; }
        .date-scroll::-webkit-scrollbar { height: 3px; }
        .date-scroll::-webkit-scrollbar-track { background: #e5e7eb; border-radius: 10px; }
        .date-scroll::-webkit-scrollbar-thumb { background: #059669; border-radius: 10px; }
        .date-grid { display: inline-flex; gap: 10px; padding: 5px; }
        .date-item { background: #f9fafb; border-radius: 15px; padding: 10px 15px; text-align: center; min-width: 70px; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
        .date-item.selected { background: #059669; color: white; transform: scale(1.05); border-color: #fbbf24; }
        .date-item.today-marker { border-color: #fbbf24; }
        .date-item.future-date { opacity: 0.5; background: #f3f4f6; }
        .date-day { font-size: 12px; font-weight: 500; }
        .date-number { font-size: 18px; font-weight: 700; }
        .date-month { font-size: 10px; }
        .prayer-section { padding: 0 15px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin: 20px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb; }
        .section-title { font-size: 18px; font-weight: 700; color: #1f2937; }
        .add-btn { background: none; border: none; color: #059669; font-size: 24px; cursor: pointer; padding: 5px; transition: transform 0.2s; }
        .add-btn:hover { transform: scale(1.1); }
        .prayer-card { background: white; border-radius: 20px; margin-bottom: 12px; overflow: hidden; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.05); cursor: pointer; }
        .prayer-card:hover { transform: translateX(-5px); box-shadow: 0 8px 25px rgba(5,150,105,0.15); }
        .prayer-card.completed { background: linear-gradient(135deg,#f0fdf4,#ecfdf5); border-right: 4px solid #059669; }
        .prayer-card.missed { background: linear-gradient(135deg,#fef2f2,#fee2e2); border-right: 4px solid #ef4444; }
        .prayer-card.delayed { background: linear-gradient(135deg,#fffbeb,#fef3c7); border-right: 4px solid #f59e0b; }
        .prayer-card.checked { background: linear-gradient(135deg,#f0fdf4,#ecfdf5); border-right: 4px solid #059669; }
        .prayer-content { display: flex; justify-content: space-between; align-items: center; padding: 18px; }
        .prayer-info { flex: 1; }
        .prayer-name { font-size: 18px; font-weight: 700; color: #1f2937; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .prayer-time { font-size: 14px; color: #6b7280; display: flex; align-items: center; gap: 5px; }
        .status-badge { display: inline-block; font-size: 11px; padding: 3px 8px; border-radius: 20px; margin-top: 5px; }
        .status-badge.mosque { background: #d1fae5; color: #065f46; }
        .status-badge.alone { background: #dbeafe; color: #1e40af; }
        .status-badge.not-prayed { background: #fee2e2; color: #991b1b; }
        .status-badge.delayed { background: #fed7aa; color: #92400e; }
        .fasting-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; }
        .fasting-obligatory { background: #d1fae5; color: #065f46; }
        .fasting-sunnah { background: #dbeafe; color: #1e40af; }
        .fasting-voluntary { background: #fef3c7; color: #92400e; }
        .prayer-status-icon { width: 50px; height: 50px; border-radius: 25px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .prayer-status-icon.completed,.prayer-status-icon.checked { background: #059669; color: white; }
        .prayer-status-icon.missed { background: #ef4444; color: white; }
        .prayer-status-icon.delayed { background: #f59e0b; color: white; }
        .prayer-status-icon.pending { background: #e5e7eb; color: #9ca3af; }
        .nawafil-info { padding: 10px 18px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #059669; display: flex; gap: 15px; }
        .extra-details { font-size: 12px; color: #6b7280; margin-top: 5px; }
        .delete-btn { background: none; border: none; color: #ef4444; font-size: 20px; cursor: pointer; padding: 5px; margin-left: 10px; }
        .empty-state { text-align: center; padding: 40px 20px; background: white; border-radius: 20px; color: #9ca3af; }
        .empty-state i { font-size: 48px; margin-bottom: 10px; }

        /* ===== زر السنن الاختيارية (FAB) ===== */
        .optional-fab {
            position: fixed;
            bottom: 150px;
            left: 20px;
            background: linear-gradient(135deg, #d97706, #f59e0b);
            color: white;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            cursor: pointer;
            box-shadow: 0 4px 18px rgba(217,119,6,0.45);
            transition: all 0.3s;
            z-index: 100;
            border: none;
        }
        .optional-fab:hover { transform: scale(1.12) rotate(-10deg); background: linear-gradient(135deg, #b45309, #d97706); }
        .optional-fab-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #059669;
            color: white;
            font-size: 10px;
            font-weight: 700;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .feedback-fab {
            position: fixed;
            bottom: 85px;
            left: 20px;
            background: #059669;
            color: white;
            width: 55px;
            height: 55px;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(5,150,105,0.4);
            transition: all 0.3s;
            z-index: 100;
            border: none;
        }
        .feedback-fab:hover { transform: scale(1.1); background: #047857; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-radius: 25px 25px 0 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
            padding: 10px 20px;
            z-index: 100;
        }
        .nav-foot { max-width: 500px; margin: 0 auto; }
        .list-foot { display: flex; justify-content: space-around; list-style: none; padding: 5px 0; }
        .item-foot a { font-size: 24px; color: #9ca3af; text-decoration: none; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; gap: 3px; }
        .item-foot a.active { color: #059669; transform: translateY(-3px); }
        .item-foot span { font-size: 11px; }

        /* ===== Modals ===== */
        .modal, .feedback-modal, .extra-modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-content, .feedback-content, .extra-modal-content {
            background: white;
            border-radius: 30px;
            max-width: 400px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideUp 0.3s ease;
        }
        @keyframes modalSlideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            border-radius: 30px 30px 0 0;
        }
        .modal-title { font-size: 20px; font-weight: 700; color: #1f2937; }
        .close-modal { background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; }
        .modal-body { padding: 20px; }
        .status-options { display: grid; grid-template-columns: repeat(2,1fr); gap: 12px; margin-bottom: 25px; }
        .status-option { display: flex; align-items: center; gap: 10px; padding: 12px; background: #f9fafb; border-radius: 15px; cursor: pointer; transition: all 0.2s; border: 2px solid transparent; }
        .status-option:hover { background: #f3f4f6; }
        .status-option input:checked + span { color: #059669; font-weight: 600; }
        .status-option input { width: 18px; height: 18px; cursor: pointer; }
        .status-option span { font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; color: #1f2937; margin-bottom: 8px; }
        .form-input,.form-select,.form-textarea { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 15px; font-size: 14px; font-family: inherit; transition: border-color 0.2s; }
        .form-input:focus,.form-select:focus,.form-textarea:focus { outline: none; border-color: #059669; }
        .modal-actions { padding: 20px; border-top: 1px solid #e5e7eb; display: flex; gap: 12px; }
        .btn { flex: 1; padding: 12px; border: none; border-radius: 25px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: inherit; }
        .btn-primary { background: #059669; color: white; }
        .btn-primary:hover { background: #047857; }
        .btn-secondary { background: #f3f4f6; color: #4b5563; }
        .btn-secondary:hover { background: #e5e7eb; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }

        /* ===== Modal السنن الاختيارية ===== */
        .optional-choice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 10px;
        }
        .optional-choice-card {
            background: #fffbeb;
            border: 2.5px solid #fde68a;
            border-radius: 20px;
            padding: 20px 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
        }
        .optional-choice-card:hover { background: #fef3c7; border-color: #f59e0b; transform: translateY(-3px); box-shadow: 0 6px 20px rgba(245,158,11,0.2); }
        .optional-choice-card.done { background: #f0fdf4; border-color: #86efac; }
        .optional-choice-card.done:hover { border-color: #4ade80; }
        .optional-choice-icon { font-size: 38px; margin-bottom: 8px; }
        .optional-choice-name { font-size: 16px; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
        .optional-choice-desc { font-size: 11px; color: #9ca3af; }
        .optional-done-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #16a34a;
            font-weight: 600;
            background: #dcfce7;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 6px;
        }

        /* ===== خيارات الركعات ===== */
        .rakat-grid { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 10px; }
        .rakat-btn {
            padding: 12px 20px;
            border: 2.5px solid #e5e7eb;
            border-radius: 14px;
            background: white;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            color: #374151;
            min-width: 80px;
        }
        .rakat-btn:hover { border-color: #f59e0b; background: #fffbeb; }
        .rakat-btn.selected { background: #059669; border-color: #059669; color: white; }

        /* Loading */
        .loading-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 2000; align-items: center; justify-content: center; flex-direction: column; gap: 15px; }
        .spinner { width: 50px; height: 50px; border: 4px solid #e5e7eb; border-top-color: #059669; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Feedback */
        .feedback-type-selector { display: grid; grid-template-columns: repeat(4,1fr); gap: 10px; margin-bottom: 20px; }
        .feedback-type-btn { display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 12px 5px; background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 15px; cursor: pointer; transition: all 0.2s; font-family: inherit; }
        .feedback-type-btn.selected { background: #059669; border-color: #059669; color: white; }
        .feedback-type-btn i { font-size: 20px; }
        .feedback-type-btn span { font-size: 11px; }
        .feedback-textarea { width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 20px; font-size: 14px; font-family: inherit; resize: vertical; min-height: 120px; }
        .feedback-textarea:focus { outline: none; border-color: #059669; }
        .feedback-actions { display: flex; gap: 12px; margin-top: 20px; }

        @media (max-width:480px) {
            .status-options { grid-template-columns: 1fr; }
            .prayer-name { font-size: 16px; }
            .prayer-status-icon { width: 45px; height: 45px; font-size: 20px; }
            .stats-container { gap: 8px; }
            .stats-card .stats-number { font-size: 20px; }
            .optional-choice-grid { grid-template-columns: 1fr; gap: 10px; }
        }
    </style>
</head>
<body>

<!-- Modern Header -->
<div class="modern-header">
    <div class="header-top">
        <div class="welcome-text">
            <i class="bi bi-moon-stars"></i>
            مرحباً <?php echo htmlspecialchars($_SESSION['user_name']); ?>
        </div>
        <div class="header-actions">
            <a href="settings.php" class="bi bi-gear-fill"></a>
        </div>
    </div>
    <div class="stats-container">
        <div class="stats-card">
            <div class="stats-number"><?php echo $completed_prayers; ?>/5</div>
            <div class="stats-label">🌟 الفروض</div>
        </div>
        <div class="stats-card">
            <div class="stats-number"><?php echo $completed_additional; ?></div>
            <div class="stats-label">📿 صلوات إضافية</div>
        </div>
        <div class="stats-card">
            <div class="stats-number"><?php echo $completed_fasting; ?></div>
            <div class="stats-label">🤲 أيام صوم</div>
        </div>
        <div class="stats-card progress-card">
            <div class="stats-title">
                <span>تقدم الفروض اليوم</span>
                <span><?php echo $completed_prayers; ?>/5</span>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill"></div>
            </div>
            <div class="stats-numbers">
                <span>🌟 <?php echo round($completion_percentage); ?>% مكتمل</span>
                <span>🎯 الهدف: 5 صلوات</span>
            </div>
        </div>
    </div>
</div>

<!-- Date Navigation -->
<div class="date-navigation">
    <div class="date-controls">
        <button class="nav-btn" onclick="changeDate(-1)"><i class="bi bi-chevron-right"></i></button>
        <button class="nav-btn nt-one" onclick="goToToday()"><i class="bi bi-calendar-week"></i> اليوم</button>
        <button class="nav-btn" onclick="changeDate(1)"><i class="bi bi-chevron-left"></i></button>
    </div>
    <div class="date-display">
        <div class="gregorian-date"><?php echo date('d F Y', strtotime($selected_date)); ?></div>
        <div class="hijri-date"><?php echo $hijri_date ?: 'التاريخ الهجري غير متوفر'; ?></div>
    </div>
    <div class="date-scroll">
        <div class="date-grid" id="dateGrid"></div>
    </div>
</div>

<!-- Prayer Cards Section -->
<div class="prayer-section">

    <!-- الفروض الخمسة -->
    <div class="section-header">
        <h3 class="section-title"><i class="bi bi-moon-stars"></i> الصلوات المفروضة</h3>
    </div>

    <?php foreach ($prayer_times as $prayer => $time):
        $status   = $prayer_status[$prayer] ?? null;
        $cardClass = $iconClass = 'pending';
        if ($status === 'prayed_in_mosque' || $status === 'prayed_alone') { $cardClass = $iconClass = 'completed'; }
        elseif ($status === 'not_prayed')  { $cardClass = $iconClass = 'missed'; }
        elseif ($status === 'delayed')     { $cardClass = $iconClass = 'delayed'; }
        $statusText = '';
        if ($status === 'prayed_in_mosque') $statusText = 'في المسجد';
        elseif ($status === 'prayed_alone') $statusText = 'منفرد';
        elseif ($status === 'not_prayed')   $statusText = 'لم تصل بعد';
        elseif ($status === 'delayed')      $statusText = 'متأخرة';
    ?>
    <div class="prayer-card <?php echo $cardClass; ?>" onclick="openPrayerModal('<?php echo $prayer; ?>')">
        <div class="prayer-content">
            <div class="prayer-info">
                <div class="prayer-name"><?php echo $prayer; ?></div>
                <div class="prayer-time"><i class="bi bi-clock"></i> <?php echo $time; ?></div>
                <?php if ($statusText): ?>
                <div class="status-badge <?php echo $status === 'prayed_in_mosque' ? 'mosque' : ($status === 'prayed_alone' ? 'alone' : ($status === 'not_prayed' ? 'not-prayed' : 'delayed')); ?>">
                    <?php echo $statusText; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="prayer-status-icon <?php echo $iconClass; ?>">
                <?php if ($iconClass === 'completed'): ?><i class="bi bi-check-lg"></i>
                <?php elseif ($iconClass === 'missed'): ?><i class="bi bi-x-lg"></i>
                <?php elseif ($iconClass === 'delayed'): ?><i class="bi bi-clock-history"></i>
                <?php else: ?><i class="bi bi-circle"></i><?php endif; ?>
            </div>
        </div>
        <?php if (!empty($prayer_nawafil[$prayer])): ?>
        <div class="nawafil-info">
            <i class="bi bi-flower1"></i>
            <?php
            $t = [];
            foreach ($prayer_nawafil[$prayer] as $type => $rakat) { $t[] = "$rakat ركعة $type"; }
            echo implode(' + ', $t);
            ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <!-- الصلوات الإضافية -->
    <div class="section-header">
        <h3 class="section-title"><i class="bi bi-star"></i> صلوات إضافية (نوافل/قضاء)</h3>
        <?php if (!$is_future_date): ?>
        <button class="add-btn" onclick="openAdditionalPrayerModal()"><i class="bi bi-plus-circle"></i></button>
        <?php endif; ?>
    </div>
    <div id="additionalPrayersList">
        <?php if (empty($additional_prayers)): ?>
        <div class="empty-state">
            <i class="bi bi-journal-bookmark-fill"></i>
            <p>لا توجد صلوات إضافية</p>
            <p style="font-size:12px;">أضف صلاة نافلة أو قضاء</p>
        </div>
        <?php else: ?>
            <?php foreach ($additional_prayers as $prayer): $isChecked = $prayer['is_checked']; ?>
            <div class="prayer-card <?php echo $isChecked ? 'checked' : ''; ?>">
                <div class="prayer-content" onclick="toggleAdditionalPrayerCheck(<?php echo $prayer['id']; ?>, <?php echo $isChecked ? 'false' : 'true'; ?>)">
                    <div class="prayer-info">
                        <div class="prayer-name"><i class="bi bi-flower2"></i> <?php echo htmlspecialchars($prayer['prayer_name']); ?></div>
                        <div class="extra-details"><i class="bi bi-flower1"></i> <?php echo $prayer['rakats']; ?> ركعة
                            <?php if ($prayer['notes']): ?><br><i class="bi bi-chat"></i> <?php echo htmlspecialchars($prayer['notes']); ?><?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;">
                        <div class="prayer-status-icon <?php echo $isChecked ? 'checked' : 'pending'; ?>">
                            <?php if ($isChecked): ?><i class="bi bi-check-lg"></i><?php else: ?><i class="bi bi-circle"></i><?php endif; ?>
                        </div>
                        <button class="delete-btn" onclick="event.stopPropagation(); deleteAdditionalPrayer(<?php echo $prayer['id']; ?>)"><i class="bi bi-trash3"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- أيام الصوم -->
    <div class="section-header">
        <h3 class="section-title"><i class="bi bi-calendar-heart"></i> أيام الصوم</h3>
        <?php if (!$is_future_date): ?>
        <button class="add-btn" onclick="openFastingModal()"><i class="bi bi-plus-circle"></i></button>
        <?php endif; ?>
    </div>
    <div id="fastingList">
        <?php if (empty($fasting_records)): ?>
        <div class="empty-state">
            <i class="bi bi-emoji-frown"></i>
            <p>لا توجد أيام صوم مسجلة</p>
            <p style="font-size:12px;">أضف يوم صوم (فرض، سنة، أو تطوع)</p>
        </div>
        <?php else: ?>
            <?php foreach ($fasting_records as $fasting):
                $isChecked = $fasting['is_checked'];
                $typeText = $fasting['fasting_type'] === 'obligatory' ? 'فرض' : ($fasting['fasting_type'] === 'sunnah' ? 'سنة' : 'تطوع');
                $typeClass = $fasting['fasting_type'] === 'obligatory' ? 'fasting-obligatory' : ($fasting['fasting_type'] === 'sunnah' ? 'fasting-sunnah' : 'fasting-voluntary');
            ?>
            <div class="prayer-card <?php echo $isChecked ? 'checked' : ''; ?>">
                <div class="prayer-content" onclick="toggleFastingCheck(<?php echo $fasting['id']; ?>, <?php echo $isChecked ? 'false' : 'true'; ?>)">
                    <div class="prayer-info">
                        <div class="prayer-name"><span class="fasting-badge <?php echo $typeClass; ?>"><?php echo $typeText; ?></span> يوم صوم</div>
                        <?php if ($fasting['notes']): ?>
                        <div class="extra-details"><i class="bi bi-chat"></i> <?php echo htmlspecialchars($fasting['notes']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;align-items:center;">
                        <div class="prayer-status-icon <?php echo $isChecked ? 'checked' : 'pending'; ?>">
                            <?php if ($isChecked): ?><i class="bi bi-check-lg"></i><?php else: ?><i class="bi bi-circle"></i><?php endif; ?>
                        </div>
                        <button class="delete-btn" onclick="event.stopPropagation(); deleteFasting(<?php echo $fasting['id']; ?>)"><i class="bi bi-trash3"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($is_future_date): ?>
<div class="empty-state" style="margin:20px;">
    <i class="bi bi-calendar-heart"></i>
    <p>لا يمكن تسجيل الصلوات أو الصوم لتاريخ مستقبلي</p>
</div>
<?php endif; ?>

<!-- ===== زر السنن الاختيارية العائم ===== -->
<?php if (!$is_future_date): ?>
<button class="optional-fab" onclick="openOptionalListModal()" title="سنن اختيارية">
    <i class="bi bi-stars"></i>
    <?php $done_count = count(array_filter(['الضحى','قيام الليل'], fn($p) => isset($optional_nawafil[$p]))); ?>
    <?php if ($done_count > 0): ?>
    <span class="optional-fab-badge"><?php echo $done_count; ?></span>
    <?php endif; ?>
</button>
<?php endif; ?>

<!-- Feedback FAB -->
<button class="feedback-fab" onclick="openFeedbackModal()"><i class="bi bi-chat-left-text"></i></button>

<!-- Footer -->
<footer class="footer">
    <nav class="nav-foot">
        <ul class="list-foot">
            <li class="item-foot"><a href="index.php" class="bi bi-house-fill active"></a></li>
            <li class="item-foot"><a href="statistics.php" class="bi bi-graph-up"></a></li>
            <li class="item-foot"><a href="reminder.php" class="bi bi-bell-fill"></a></li>
            <li class="item-foot"><a href="profile.php" class="bi bi-person-fill"></a></li>
        </ul>
    </nav>
</footer>

<!-- ========== Modal الصلوات المفروضة ========== -->
<div class="modal" id="prayerModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalPrayerName">الصلاة</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="status-options">
                <label class="status-option"><input type="radio" name="prayerStatus" value="prayed_in_mosque"><span>🕌 صليت في المسجد</span></label>
                <label class="status-option"><input type="radio" name="prayerStatus" value="prayed_alone"><span>🏠 صليت منفرداً</span></label>
                <label class="status-option"><input type="radio" name="prayerStatus" value="not_prayed"><span>❌ لم أصل بعد</span></label>
                <label class="status-option"><input type="radio" name="prayerStatus" value="delayed"><span>⏰ صليت متأخراً</span></label>
            </div>
            <div class="nawafil-section">
                <h4 class="section-title"><i class="bi bi-flower2"></i> النوافل المستحبة</h4>
                <div class="nawafil-options" id="nawafilOptions"></div>
                <p style="font-size:12px;color:#6b7280;margin-top:10px;"><i class="bi bi-info-circle"></i> اختر النوافل التي صليتها مع هذه الفريضة</p>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeModal()">إلغاء</button>
            <button class="btn btn-primary" onclick="savePrayerRecord()">حفظ</button>
        </div>
    </div>
</div>

<!-- ========== Modal قائمة السنن الاختيارية ========== -->
<div class="modal" id="optionalListModal">
    <div class="modal-content">
        <div class="modal-header" style="background:linear-gradient(135deg,#fffbeb,#fef9c3);border-radius:30px 30px 0 0;">
            <h3 class="modal-title" style="color:#92400e;"><i class="bi bi-stars" style="color:#d97706;margin-left:6px;"></i> السنن الاختيارية</h3>
            <button class="close-modal" onclick="closeOptionalListModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:#9ca3af;margin-bottom:18px;text-align:center;">اختر السنة التي تريد تسجيلها — اضغط عليها لإدخال عدد الركعات</p>
            <div class="optional-choice-grid">
                <!-- صلاة الضحى -->
                <div class="optional-choice-card <?php echo isset($optional_nawafil['الضحى']) ? 'done' : ''; ?>"
                     onclick="openOptionalRakatModal('الضحى')">
                    <div class="optional-choice-icon">🌤️</div>
                    <div class="optional-choice-name">صلاة الضحى</div>
                    <div class="optional-choice-desc">من ركعتين إلى ثماني ركعات</div>
                    <?php if (isset($optional_nawafil['الضحى'])): ?>
                    <div class="optional-done-pill"><i class="bi bi-check-circle-fill"></i> <?php echo $optional_nawafil['الضحى']; ?> ركعات ✓</div>
                    <?php endif; ?>
                </div>
                <!-- قيام الليل -->
                <div class="optional-choice-card <?php echo isset($optional_nawafil['قيام الليل']) ? 'done' : ''; ?>"
                     onclick="openOptionalRakatModal('قيام الليل')">
                    <div class="optional-choice-icon">🌙</div>
                    <div class="optional-choice-name">قيام الليل</div>
                    <div class="optional-choice-desc">ركعتان فأكثر بعد العشاء</div>
                    <?php if (isset($optional_nawafil['قيام الليل'])): ?>
                    <div class="optional-done-pill"><i class="bi bi-check-circle-fill"></i> <?php echo $optional_nawafil['قيام الليل']; ?> ركعات ✓</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========== Modal تسجيل ركعات السنة ========== -->
<div class="modal" id="optionalRakatModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="optionalRakatTitle">عدد الركعات</h3>
            <button class="close-modal" onclick="closeOptionalRakatModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:#6b7280;margin-bottom:16px;" id="optionalRakatDesc"></p>
            <h4 style="font-size:14px;font-weight:700;color:#1f2937;margin-bottom:12px;">كم ركعة صليت؟</h4>
            <div class="rakat-grid" id="rakatGrid"></div>
        </div>
        <div class="modal-actions" style="flex-wrap:wrap;gap:10px;">
            <button class="btn btn-danger" id="deleteOptionalBtn" onclick="deleteOptionalRecord()" style="display:none;flex:0 0 auto;padding:12px 16px;">
                <i class="bi bi-trash"></i> حذف
            </button>
            <button class="btn btn-secondary" onclick="closeOptionalRakatModal()">إلغاء</button>
            <button class="btn btn-primary" onclick="saveOptionalRecord()">حفظ</button>
        </div>
    </div>
</div>

<!-- ========== Modal إضافة صلاة ========== -->
<div class="extra-modal" id="additionalPrayerModal">
    <div class="extra-modal-content">
        <div class="modal-header">
            <h3 class="modal-title">إضافة صلاة</h3>
            <button class="close-modal" onclick="closeAdditionalPrayerModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">اسم الصلاة</label>
                <input type="text" class="form-input" id="additionalPrayerName" placeholder="مثال: قضاء الفجر، سنة الوضوء">
            </div>
            <div class="form-group">
                <label class="form-label">عدد الركعات</label>
                <input type="number" class="form-input" id="additionalPrayerRakats" value="2" min="1" max="20">
            </div>
            <div class="form-group">
                <label class="form-label">ملاحظات (اختياري)</label>
                <textarea class="form-textarea" id="additionalPrayerNotes" rows="3" placeholder="أضف أي ملاحظات..."></textarea>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeAdditionalPrayerModal()">إلغاء</button>
            <button class="btn btn-primary" onclick="saveAdditionalPrayer()">حفظ</button>
        </div>
    </div>
</div>

<!-- ========== Modal الصوم ========== -->
<div class="extra-modal" id="fastingModal">
    <div class="extra-modal-content">
        <div class="modal-header">
            <h3 class="modal-title">تسجيل يوم صوم</h3>
            <button class="close-modal" onclick="closeFastingModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">نوع الصوم</label>
                <select class="form-select" id="fastingType">
                    <option value="obligatory">فرض (مثل رمضان)</option>
                    <option value="sunnah">سنة (مثل الاثنين والخميس)</option>
                    <option value="voluntary">تطوع</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">ملاحظات (اختياري)</label>
                <textarea class="form-textarea" id="fastingNotes" rows="3" placeholder="أضف أي ملاحظات..."></textarea>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeFastingModal()">إلغاء</button>
            <button class="btn btn-primary" onclick="saveFasting()">حفظ</button>
        </div>
    </div>
</div>

<!-- ========== Feedback Modal ========== -->
<div class="feedback-modal" id="feedbackModal">
    <div class="feedback-content">
        <div class="feedback-header">
            <button class="feedback-close" onclick="closeFeedbackModal()">&times;</button>
            <h3 id="feedbackTitle">تقييم واقتراحات</h3>
            <p id="feedbackSubtitle" style="font-size:13px;color:#6b7280;margin-top:5px;">شاركنا رأيك لنساعدك بشكل أفضل</p>
        </div>
        <div id="feedbackForm">
            <div class="feedback-body">
                <div class="feedback-type-selector">
                    <button type="button" class="feedback-type-btn" onclick="selectFeedbackType('suggestion')"><i class="bi bi-lightbulb"></i><span>اقتراح</span></button>
                    <button type="button" class="feedback-type-btn" onclick="selectFeedbackType('complaint')"><i class="bi bi-exclamation-triangle"></i><span>شكوى</span></button>
                    <button type="button" class="feedback-type-btn" onclick="selectFeedbackType('bug')"><i class="bi bi-bug"></i><span>خطأ</span></button>
                    <button type="button" class="feedback-type-btn" onclick="selectFeedbackType('thanks')"><i class="bi bi-heart"></i><span>شكر</span></button>
                </div>
                <textarea class="feedback-textarea" id="feedbackMessage" placeholder="اكتب رسالتك هنا... (اختياري)" maxlength="1000"></textarea>
                <div style="text-align:left;margin-top:5px;font-size:11px;color:#9ca3af;"><span id="charCount">0</span>/1000</div>
                <div class="feedback-actions">
                    <button class="feedback-cancel-btn" onclick="closeFeedbackModal()">إلغاء</button>
                    <button class="feedback-submit-btn" id="submitFeedbackBtn" onclick="submitFeedback()" disabled>إرسال</button>
                </div>
            </div>
        </div>
        <div id="feedbackSuccess" style="display:none;">
            <div class="feedback-success">
                <i class="bi bi-check-circle-fill"></i>
                <h4>تم الإرسال بنجاح!</h4>
                <p style="font-size:13px;color:#6b7280;margin-top:10px;">شكراً لك على مشاركة رأيك.</p>
                <button class="btn btn-primary" style="margin-top:20px;" onclick="closeFeedbackModal()">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div style="color:#059669;">جاري الحفظ...</div>
</div>

<script>
    // ===== المتغيرات العامة =====
    let currentPrayer    = '';
    let selectedDate     = '<?php echo $selected_date; ?>';
    let today            = '<?php echo $today; ?>';
    let selectedNawafil  = [];
    let nawafilInfo      = <?php echo json_encode($nawafil_info); ?>;
    let optionalNawafil  = <?php echo json_encode($optional_nawafil ?? []); ?>;

    // بيانات السنن الاختيارية
    const optionalConfig = {
        'الضحى': {
            desc: 'تُصلى من بعد طلوع الشمس حتى قبيل الزوال — أفضل وقتها عند اشتداد الضحى',
            rakatOptions: [2, 4, 6, 8]
        },
        'قيام الليل': {
            desc: 'تُصلى بعد صلاة العشاء ويُختم بصلاة الوتر — أفضل وقتها الثلث الأخير من الليل',
            rakatOptions: [2, 4, 6, 8, 10, 12]
        }
    };

    let currentOptionalPrayer = '';
    let selectedOptionalRakat  = 0;

    // ===== التاريخ =====
    function generateDateGrid() {
        const grid = document.getElementById('dateGrid');
        if (!grid) return;
        grid.innerHTML = '';
        const days = ['أحد','اثنين','ثلاثاء','أربعاء','خميس','جمعة','سبت'];
        for (let i = -7; i <= 7; i++) {
            const date = new Date(selectedDate);
            date.setDate(date.getDate() + i);
            const dateString = date.toISOString().split('T')[0];
            const dateItem = document.createElement('div');
            dateItem.className = 'date-item';
            if (dateString === selectedDate) dateItem.classList.add('selected');
            if (dateString === today) dateItem.classList.add('today-marker');
            if (date > new Date(today)) dateItem.classList.add('future-date');
            const monthNames = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
            dateItem.innerHTML = `<div class="date-day">${days[date.getDay()]}</div><div class="date-number">${date.getDate()}</div><div class="date-month">${monthNames[date.getMonth()].substring(0,4)}</div>`;
            dateItem.onclick = function() {
                if (date > new Date(today)) { alert('لا يمكن تسجيل الصلوات لتاريخ مستقبلي'); return; }
                window.location.href = `index.php?date=${dateString}`;
            };
            grid.appendChild(dateItem);
        }
        const sel = grid.querySelector('.selected');
        if (sel) sel.scrollIntoView({ behavior: 'smooth', inline: 'center' });
    }
    function changeDate(days) {
        const date = new Date(selectedDate);
        date.setDate(date.getDate() + days);
        const newStr = date.toISOString().split('T')[0];
        if (new Date(newStr) > new Date(today)) { alert('لا يمكن تسجيل الصلوات لتاريخ مستقبلي'); return; }
        window.location.href = `index.php?date=${newStr}`;
    }
    function goToToday() { window.location.href = `index.php?date=${today}`; }

    // ===== الصلوات المفروضة =====
    function openPrayerModal(prayer) {
        if (new Date(selectedDate) > new Date(today)) { alert('لا يمكن تسجيل الصلوات لتاريخ مستقبلي'); return; }
        currentPrayer = prayer;
        document.getElementById('modalPrayerName').textContent = prayer;
        const nawafilDiv = document.getElementById('nawafilOptions');
        nawafilDiv.innerHTML = '';
        selectedNawafil = [];
        const nawafil = nawafilInfo[prayer];
        if (nawafil && nawafil['قبل'] > 0) buildNawafilBtn(nawafilDiv, 'قبل', nawafil['قبل'], '🌅');
        if (nawafil && nawafil['بعد'] > 0) buildNawafilBtn(nawafilDiv, 'بعد', nawafil['بعد'], '🌇');
        fetch(`api/get_prayer_status.php?date=${selectedDate}&prayer=${encodeURIComponent(prayer)}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.record) {
                    const radio = document.querySelector(`input[name="prayerStatus"][value="${data.record.status}"]`);
                    if (radio) radio.checked = true;
                    if (data.record.nawafil) {
                        data.record.nawafil.forEach(n => {
                            const btn = nawafilDiv.querySelector(`[data-type="${n.nawafil_type}"]`);
                            if (btn) { btn.classList.add('selected'); toggleNawafil(n.nawafil_type, n.rakat_count); }
                        });
                    }
                }
            }).catch(() => {});
        document.getElementById('prayerModal').style.display = 'flex';
    }
    function buildNawafilBtn(container, type, rakat, icon) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'nawafil-btn';
        btn.innerHTML = `${icon} ${rakat} ركعات ${type}`;
        btn.dataset.type  = type;
        btn.dataset.rakat = rakat;
        btn.onclick = function(e) {
            e.stopPropagation();
            this.classList.toggle('selected');
            toggleNawafil(this.dataset.type, this.dataset.rakat);
        };
        container.appendChild(btn);
    }
    function toggleNawafil(type, rakat) {
        const idx = selectedNawafil.findIndex(n => n.type === type);
        if (idx > -1) selectedNawafil.splice(idx, 1);
        else selectedNawafil.push({ type, rakat: parseInt(rakat) });
    }
    function closeModal() {
        document.getElementById('prayerModal').style.display = 'none';
        document.querySelectorAll('input[name="prayerStatus"]').forEach(r => r.checked = false);
        document.querySelectorAll('.nawafil-btn').forEach(b => b.classList.remove('selected'));
        selectedNawafil = [];
    }
    function savePrayerRecord() {
        const radio = document.querySelector('input[name="prayerStatus"]:checked');
        if (!radio) { alert('الرجاء اختيار حالة الصلاة'); return; }
        showLoading();
        fetch('api/save_prayer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prayer_name: currentPrayer, date: selectedDate, status: radio.value, nawafil: selectedNawafil })
        }).then(r => r.json()).then(d => { hideLoading(); if (d.success) location.reload(); else alert('خطأ: ' + d.message); })
          .catch(() => { hideLoading(); alert('حدث خطأ أثناء الحفظ'); });
    }

    // ===== السنن الاختيارية =====
    function openOptionalListModal() {
        document.getElementById('optionalListModal').style.display = 'flex';
    }
    function closeOptionalListModal() {
        document.getElementById('optionalListModal').style.display = 'none';
    }

    function openOptionalRakatModal(prayerName) {
        currentOptionalPrayer = prayerName;
        selectedOptionalRakat  = 0;
        const config = optionalConfig[prayerName];

        document.getElementById('optionalRakatTitle').textContent = prayerName;
        document.getElementById('optionalRakatDesc').textContent   = config.desc;

        // بناء أزرار الركعات
        const grid = document.getElementById('rakatGrid');
        grid.innerHTML = '';
        config.rakatOptions.forEach(r => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rakat-btn';
            btn.textContent = r + ' ركعات';
            btn.dataset.rakat = r;
            // تحديد الركعات المحفوظة مسبقاً
            if (optionalNawafil[prayerName] && parseInt(optionalNawafil[prayerName]) === r) {
                btn.classList.add('selected');
                selectedOptionalRakat = r;
            }
            btn.onclick = function() {
                document.querySelectorAll('.rakat-btn').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                selectedOptionalRakat = parseInt(this.dataset.rakat);
            };
            grid.appendChild(btn);
        });

        // إظهار زر الحذف إذا كانت مسجلة مسبقاً
        const delBtn = document.getElementById('deleteOptionalBtn');
        delBtn.style.display = optionalNawafil[prayerName] ? 'inline-flex' : 'none';

        // إغلاق قائمة الاختيار وفتح modal الركعات
        closeOptionalListModal();
        document.getElementById('optionalRakatModal').style.display = 'flex';
    }
    function closeOptionalRakatModal() {
        document.getElementById('optionalRakatModal').style.display = 'none';
        currentOptionalPrayer = '';
        selectedOptionalRakat  = 0;
    }
    function saveOptionalRecord() {
        if (!selectedOptionalRakat) { alert('الرجاء اختيار عدد الركعات'); return; }
        showLoading();
        fetch('api/save_prayer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                prayer_name: currentOptionalPrayer,
                date:        selectedDate,
                status:      'optional',
                nawafil:     [{ type: 'أداء', rakat: selectedOptionalRakat }]
            })
        }).then(r => r.json()).then(d => { hideLoading(); if (d.success) location.reload(); else alert('خطأ: ' + d.message); })
          .catch(() => { hideLoading(); alert('حدث خطأ أثناء الحفظ'); });
    }
    function deleteOptionalRecord() {
        if (!confirm('هل تريد حذف تسجيل ' + currentOptionalPrayer + '؟')) return;
        showLoading();
        fetch('api/save_prayer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prayer_name: currentOptionalPrayer, date: selectedDate, status: 'delete_optional', nawafil: [] })
        }).then(r => r.json()).then(d => { hideLoading(); if (d.success) location.reload(); else alert('خطأ: ' + d.message); })
          .catch(() => { hideLoading(); alert('حدث خطأ أثناء الحذف'); });
    }

    // ===== صلوات إضافية =====
    function openAdditionalPrayerModal() {
        document.getElementById('additionalPrayerName').value  = '';
        document.getElementById('additionalPrayerRakats').value = '2';
        document.getElementById('additionalPrayerNotes').value  = '';
        document.getElementById('additionalPrayerModal').style.display = 'flex';
    }
    function closeAdditionalPrayerModal() { document.getElementById('additionalPrayerModal').style.display = 'none'; }
    function saveAdditionalPrayer() {
        const name = document.getElementById('additionalPrayerName').value.trim();
        if (!name) { alert('الرجاء إدخال اسم الصلاة'); return; }
        showLoading();
        fetch('api/save_additional_prayer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prayer_name: name, date: selectedDate, rakats: document.getElementById('additionalPrayerRakats').value, notes: document.getElementById('additionalPrayerNotes').value })
        }).then(r => r.json()).then(d => { hideLoading(); if (d.success) location.reload(); else alert('خطأ: ' + d.message); })
          .catch(() => { hideLoading(); alert('حدث خطأ'); });
    }
    function toggleAdditionalPrayerCheck(id, isChecked) {
        showLoading();
        fetch('api/toggle_additional_prayer.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, is_checked: isChecked })
        }).then(r => r.json()).then(d => { hideLoading(); if (d.success) location.reload(); else alert('خطأ: ' + d.message); })
          .catch(() => { hideLoading(); alert('حدث خطأ'); });
    }
    function deleteAdditionalPrayer(id) {
        if (!confirm('هل أنت متأكد من حذف هذه الصلاة؟')) return;
        showLoading();
        fetch('api/delete_additional_prayer.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        }).then(r => r.json()).then(d => { hideLoading(); if (d.success) location.reload(); else alert('خطأ: ' + d.message); })
          .catch(() => { hideLoading(); alert('حدث خطأ'); });
    }

    // ===== الصوم =====
    function openFastingModal() {
        document.getElementById('fastingType').value  = 'voluntary';
        document.getElementById('fastingNotes').value = '';
        document.getElementById('fastingModal').style.display = 'flex';
    }
    function closeFastingModal() { document.getElementById('fastingModal').style.display = 'none'; }
    function saveFasting() {
        showLoading();
        fetch('api/save_fasting.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fasting_type: document.getElementById('fastingType').value, date: selectedDate, notes: document.getElementById('fastingNotes').value })
        }).then(r => r.json()).then(d => { hideLoading(); if (d.success) location.reload(); else alert('خطأ: ' + d.message); })
          .catch(() => { hideLoading(); alert('حدث خطأ'); });
    }
    function toggleFastingCheck(id, isChecked) {
        showLoading();
        fetch('api/toggle_fasting.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, is_checked: isChecked })
        }).then(r => r.json()).then(d => { hideLoading(); if (d.success) location.reload(); else alert('خطأ: ' + d.message); })
          .catch(() => { hideLoading(); alert('حدث خطأ'); });
    }
    function deleteFasting(id) {
        if (!confirm('هل أنت متأكد من حذف هذا اليوم؟')) return;
        showLoading();
        fetch('api/delete_fasting.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        }).then(r => r.json()).then(d => { hideLoading(); if (d.success) location.reload(); else alert('خطأ: ' + d.message); })
          .catch(() => { hideLoading(); alert('حدث خطأ'); });
    }

    // ===== Feedback =====
    let selectedFeedbackType = '';
    function openFeedbackModal() { resetFeedbackForm(); document.getElementById('feedbackModal').style.display = 'flex'; }
    function closeFeedbackModal() { document.getElementById('feedbackModal').style.display = 'none'; setTimeout(resetFeedbackForm, 300); }
    function resetFeedbackForm() {
        selectedFeedbackType = '';
        document.querySelectorAll('.feedback-type-btn').forEach(b => b.classList.remove('selected'));
        document.getElementById('feedbackMessage').value  = '';
        document.getElementById('charCount').textContent  = '0';
        document.getElementById('submitFeedbackBtn').disabled = true;
        document.getElementById('feedbackForm').style.display    = 'block';
        document.getElementById('feedbackSuccess').style.display = 'none';
    }
    function selectFeedbackType(type) {
        document.querySelectorAll('.feedback-type-btn').forEach(b => b.classList.remove('selected'));
        const types = ['suggestion','complaint','bug','thanks'];
        const btns  = document.querySelectorAll('.feedback-type-btn');
        if (btns[types.indexOf(type)]) btns[types.indexOf(type)].classList.add('selected');
        selectedFeedbackType = type;
        const titles = {
            suggestion: { title:'اقتراح', subtitle:'شاركنا أفكارك' },
            complaint:  { title:'شكوى',   subtitle:'نعتذر عن أي إزعاج' },
            bug:        { title:'خطأ',     subtitle:'ساعدنا في التحسين' },
            thanks:     { title:'شكر',    subtitle:'نشكرك على دعمك' }
        };
        document.getElementById('feedbackTitle').textContent    = titles[type].title;
        document.getElementById('feedbackSubtitle').textContent = titles[type].subtitle;
        document.getElementById('submitFeedbackBtn').disabled   = false;
    }
    document.getElementById('feedbackMessage')?.addEventListener('input', function() {
        document.getElementById('charCount').textContent = this.value.length;
    });
    async function submitFeedback() {
        if (!selectedFeedbackType) { alert('الرجاء اختيار نوع الرسالة'); return; }
        const submitBtn = document.getElementById('submitFeedbackBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري الإرسال...';
        try {
            const res = await fetch('api/submit_feedback.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: selectedFeedbackType, message: document.getElementById('feedbackMessage').value, user_id: <?php echo $_SESSION['user_id']; ?>, user_name: '<?php echo addslashes($_SESSION['user_name']); ?>', user_email: '<?php echo $_SESSION['user_email']; ?>' })
            });
            const d = await res.json();
            if (d.success) {
                document.getElementById('feedbackForm').style.display    = 'none';
                document.getElementById('feedbackSuccess').style.display = 'block';
                setTimeout(closeFeedbackModal, 3000);
            } else {
                alert('حدث خطأ: ' + (d.message || 'يرجى المحاولة مرة أخرى'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'إرسال';
            }
        } catch (e) {
            alert('حدث خطأ في الاتصال');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'إرسال';
        }
    }

    // ===== مساعدات =====
    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }
    function hideLoading() { document.getElementById('loadingOverlay').style.display = 'none'; }

    // إغلاق بـ ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal(); closeFeedbackModal(); closeAdditionalPrayerModal();
            closeFastingModal(); closeOptionalListModal(); closeOptionalRakatModal();
        }
    });

    // إغلاق بالضغط خارج الـ modal
    ['prayerModal','optionalListModal','optionalRakatModal','additionalPrayerModal','fastingModal','feedbackModal'].forEach(id => {
        document.getElementById(id)?.addEventListener('click', function(e) {
            if (e.target === this) {
                if (id === 'prayerModal')            closeModal();
                else if (id === 'optionalListModal') closeOptionalListModal();
                else if (id === 'optionalRakatModal') closeOptionalRakatModal();
                else if (id === 'additionalPrayerModal') closeAdditionalPrayerModal();
                else if (id === 'fastingModal')      closeFastingModal();
                else if (id === 'feedbackModal')     closeFeedbackModal();
            }
        });
    });

    window.addEventListener('DOMContentLoaded', generateDateGrid);
    setInterval(() => { if (new Date().toISOString().split('T')[0] !== today) location.reload(); }, 3600000);
</script>
</body>
</html>