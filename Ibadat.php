<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/config.php';

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>العبادات | مرآة المؤمن</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            background: #f8f9fa;
            color: #1a1a2e;
            min-height: 100vh;
            padding-bottom: 80px;
        }

        /* HEADER */
        .header { background: #fff; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 100; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; }
        .nav-list { list-style: none; display: flex; gap: 16px; align-items: center; }
        .nav-list a { font-size: 20px; color: #1a1a2e; text-decoration: none; }

        /* HERO */
        .page-hero {
            background: linear-gradient(135deg, #0d5c3a 0%, #1a7a4e 50%, #0d5c3a 100%);
            padding: 24px 16px 36px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .page-hero::before {
            content: ''; position: absolute; top: -30px; right: -30px;
            width: 120px; height: 120px; border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .page-hero::after {
            content: ''; position: absolute; bottom: -20px; left: -20px;
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .page-hero h1 { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 4px; }
        .page-hero p { font-size: 13px; color: rgba(255,255,255,0.75); }
        .hero-date {
            margin-top: 12px; display: inline-block;
            background: rgba(255,255,255,0.15);
            border-radius: 20px; padding: 5px 16px;
            font-size: 13px; color: rgba(255,255,255,0.9);
        }

        /* PROGRESS SUMMARY */
        .progress-summary {
            background: #fff; margin: -16px 16px 0;
            border-radius: 16px; padding: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            position: relative; z-index: 10;
        }
        .progress-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .progress-label { font-size: 13px; color: #666; }
        .progress-count { font-size: 13px; font-weight: 700; color: #0d5c3a; }
        .progress-bar-bg { background: #f0f0f0; border-radius: 10px; height: 8px; overflow: hidden; }
        .progress-bar-fill {
            height: 100%; background: linear-gradient(90deg, #0d5c3a, #2ecc71);
            border-radius: 10px; transition: width 0.8s cubic-bezier(0.4,0,0.2,1);
        }
        .progress-stats { display: flex; justify-content: space-around; margin-top: 14px; padding-top: 14px; border-top: 1px solid #f0f0f0; }
        .stat-item { text-align: center; }
        .stat-number { font-size: 20px; font-weight: 800; color: #0d5c3a; display: block; }
        .stat-label { font-size: 11px; color: #888; }

        /* TABS */
        .tabs-container { padding: 16px 16px 8px; }
        .tabs { display: flex; background: #fff; border-radius: 12px; padding: 4px; gap: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .tab-btn {
            flex: 1; padding: 10px 6px; border: none; background: transparent;
            border-radius: 10px; font-family: 'Cairo', sans-serif;
            font-size: 12px; font-weight: 600; color: #888; cursor: pointer; transition: all 0.2s;
        }
        .tab-btn.active { background: #0d5c3a; color: #fff; box-shadow: 0 2px 8px rgba(13,92,58,0.3); }

        /* SECTION HEADER */
        .section-header { padding: 12px 16px 6px; display: flex; align-items: center; gap: 8px; }
        .section-header h3 { font-size: 15px; font-weight: 700; color: #1a1a2e; }
        .section-icon {
            width: 28px; height: 28px; background: #e8f5ef; border-radius: 8px;
            display: flex; align-items: center; justify-content: center; font-size: 14px; color: #0d5c3a;
        }

        /* DHIKR CARD */
        .dhikr-list { padding: 0 16px; display: flex; flex-direction: column; gap: 10px; }
        .dhikr-card {
            background: #fff; border-radius: 16px; padding: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex; flex-direction: column; gap: 10px;
            border: 2px solid transparent; transition: all 0.3s; position: relative; overflow: hidden;
        }
        .dhikr-card.completed { border-color: #0d5c3a; background: linear-gradient(135deg, #f0faf5 0%, #fff 100%); }
        .dhikr-card.completed::before {
            content: '✓'; position: absolute; top: 10px; left: 12px;
            width: 22px; height: 22px; background: #0d5c3a; color: #fff;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800;
        }
        .dhikr-arabic { font-size: 15px; font-weight: 700; color: #1a1a2e; line-height: 1.6; margin-bottom: 4px; }
        .dhikr-meaning { font-size: 12px; color: #888; line-height: 1.4; }
        .dhikr-virtue { font-size: 11px; color: #0d5c3a; background: #e8f5ef; padding: 4px 8px; border-radius: 8px; margin-top: 6px; display: inline-block; }

        /* COUNTER */
        .counter-section { display: flex; align-items: center; justify-content: space-between; background: #f8f9fa; border-radius: 12px; padding: 10px 14px; }
        .counter-display { display: flex; align-items: center; gap: 10px; }
        .counter-minus, .counter-plus {
            width: 36px; height: 36px; border-radius: 50%; border: none; cursor: pointer;
            font-size: 18px; font-weight: 700; display: flex; align-items: center; justify-content: center;
            transition: all 0.15s; font-family: 'Cairo', sans-serif;
        }
        .counter-minus { background: #fff; color: #888; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .counter-minus:active { transform: scale(0.92); }
        .counter-plus { background: #0d5c3a; color: #fff; box-shadow: 0 4px 12px rgba(13,92,58,0.35); }
        .counter-plus:active { transform: scale(0.92); background: #0a4a2e; }
        .counter-numbers { text-align: center; }
        .counter-current { font-size: 22px; font-weight: 800; color: #0d5c3a; display: block; line-height: 1; transition: transform 0.1s; }
        @keyframes bump { 0%,100%{transform:scale(1)} 50%{transform:scale(1.35)} }
        .counter-current.bump { animation: bump 0.2s ease; }
        .counter-target { font-size: 11px; color: #aaa; }
        .dots-track { display: flex; gap: 4px; flex-wrap: wrap; max-width: 110px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: #e0e0e0; transition: background 0.2s; }
        .dot.filled { background: #0d5c3a; }

        /* TASBIH */
        .tasbih-big-card {
            background: linear-gradient(135deg, #0d5c3a, #1a7a4e);
            border-radius: 20px; padding: 24px; margin: 0 16px;
            text-align: center; color: #fff;
            box-shadow: 0 8px 24px rgba(13,92,58,0.35);
        }
        .tasbih-label { font-size: 13px; opacity: 0.8; margin-bottom: 8px; }
        .tasbih-counter-big { font-size: 64px; font-weight: 900; letter-spacing: -2px; line-height: 1; margin-bottom: 4px; }
        .tasbih-target-label { font-size: 12px; opacity: 0.7; }
        .tasbih-circle-btn {
            width: 80px; height: 80px; border-radius: 50%;
            background: rgba(255,255,255,0.2); border: 3px solid rgba(255,255,255,0.5);
            color: #fff; font-size: 32px; cursor: pointer;
            margin: 20px auto 0; display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
        }
        .tasbih-circle-btn:active { transform: scale(0.88); background: rgba(255,255,255,0.35); }
        .tasbih-controls { display: flex; justify-content: center; gap: 12px; margin-top: 16px; }
        .tasbih-ctrl-btn {
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);
            color: #fff; padding: 8px 18px; border-radius: 20px;
            font-family: 'Cairo', sans-serif; font-size: 13px; cursor: pointer;
        }
        .tasbih-ctrl-btn:active { background: rgba(255,255,255,0.3); }
        .tasbih-tabs { display: flex; gap: 8px; margin: 0 16px 16px; }
        .tasbih-type-btn {
            flex: 1; padding: 10px; border: 2px solid #e0e0e0; background: #fff;
            border-radius: 12px; font-family: 'Cairo', sans-serif;
            font-size: 13px; font-weight: 700; color: #666; cursor: pointer; transition: all 0.2s;
        }
        .tasbih-type-btn.active { border-color: #0d5c3a; color: #0d5c3a; background: #e8f5ef; }
        .tasbih-ring-track { position: relative; width: 100px; height: 100px; margin: 16px auto 0; }
        .tasbih-ring-track svg { transform: rotate(-90deg); }
        .ring-bg { fill: none; stroke: rgba(255,255,255,0.2); stroke-width: 6; }
        .ring-fill { fill: none; stroke: rgba(255,255,255,0.9); stroke-width: 6; stroke-linecap: round; transition: stroke-dashoffset 0.3s; }
        .tasbih-stats-box { margin: 16px 16px 0; background: #fff; border-radius: 14px; padding: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .tasbih-stats-box .ttl { font-size: 13px; font-weight: 700; color: #333; margin-bottom: 10px; }
        .tasbih-stats-row { display: flex; justify-content: space-around; text-align: center; }
        .tasbih-stats-row span.num { font-size: 20px; font-weight: 800; color: #0d5c3a; display: block; }
        .tasbih-stats-row span.lbl { font-size: 11px; color: #888; }
        .tasbih-stats-row .total-num { color: #f59e0b; }

        /* DUA */
        .dua-card { background: #fff; border-radius: 16px; padding: 18px 16px; margin: 0 16px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .dua-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .dua-icon { width: 36px; height: 36px; background: #fff8e6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .dua-title { font-size: 15px; font-weight: 700; color: #1a1a2e; }
        .dua-time { font-size: 11px; color: #f59e0b; background: #fff8e6; padding: 2px 8px; border-radius: 10px; margin-right: auto; }
        .dua-arabic-text { font-size: 16px; line-height: 2; color: #1a1a2e; text-align: right; direction: rtl; border-right: 3px solid #0d5c3a; padding-right: 12px; margin-bottom: 10px; }
        .dua-translation { font-size: 12px; color: #888; line-height: 1.6; }
        .dua-check-btn {
            width: 100%; padding: 10px; border: 2px solid #0d5c3a; background: transparent;
            color: #0d5c3a; border-radius: 12px; font-family: 'Cairo', sans-serif;
            font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.2s; margin-top: 12px;
        }
        .dua-check-btn.done { background: #0d5c3a; color: #fff; }

        /* COMPLETE BANNER */
        .complete-banner {
            background: linear-gradient(135deg, #0d5c3a, #2ecc71);
            color: #fff; text-align: center; padding: 16px;
            margin: 0 16px 10px; border-radius: 16px; display: none;
        }
        .complete-banner.show { display: block; }
        .complete-banner h4 { font-size: 16px; font-weight: 800; }
        .complete-banner p { font-size: 12px; opacity: 0.85; margin-top: 4px; }

        /* TAB CONTENT */
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* FOOTER */
        .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #eee; z-index: 100; }
        .nav-foot { padding: 8px 0; }
        .list-foot { list-style: none; display: flex; justify-content: space-around; align-items: center; }
        .item-foot a { font-size: 22px; color: #aaa; text-decoration: none; padding: 8px; display: block; }
        .item-foot a.active { color: #0d5c3a; }
    </style>
</head>
<body>

<header class="header">
    <nav class="navbar">
        <ul class="nav-list">
            <li><a href="setting.php" class="bi bi-gear-fill"></a></li>
            <li><a href="statistics.php" class="bi bi-list"></a></li>
        </ul>
        <ul class="nav-list">
            <li><span style="font-size:16px;">مرحباً <?php echo htmlspecialchars($_SESSION['user_name']); ?></span></li>
        </ul>
    </nav>
</header>

<!-- Hero -->
<div class="page-hero">
    <h1>🌿 العبادات</h1>
    <p>صل على النبي وأكثر من ذكر الله</p>
    <span class="hero-date" id="heroDate"></span>
</div>

<!-- Progress Summary -->
<div class="progress-summary">
    <div class="progress-row">
        <span class="progress-label">تقدم اليوم</span>
        <span class="progress-count" id="progressText">0 / 0</span>
    </div>
    <div class="progress-bar-bg">
        <div class="progress-bar-fill" id="progressBar" style="width:0%"></div>
    </div>
    <div class="progress-stats">
        <div class="stat-item">
            <span class="stat-number" id="statMorning">0</span>
            <span class="stat-label">أذكار الصباح</span>
        </div>
        <div class="stat-item">
            <span class="stat-number" id="statEvening">0</span>
            <span class="stat-label">أذكار المساء</span>
        </div>
        <div class="stat-item">
            <span class="stat-number" id="statTasbih">0</span>
            <span class="stat-label">تسبيح</span>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="tabs-container">
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('morning',this)">🌅 الصباح</button>
        <button class="tab-btn" onclick="switchTab('evening',this)">🌙 المساء</button>
        <button class="tab-btn" onclick="switchTab('tasbih',this)">📿 التسبيح</button>
        <button class="tab-btn" onclick="switchTab('dua',this)">🤲 الأدعية</button>
    </div>
</div>

<!-- MORNING -->
<div id="tab-morning" class="tab-content active">
    <div class="section-header">
        <div class="section-icon"><i class="bi bi-sun"></i></div>
        <h3>أذكار الصباح</h3>
    </div>
    <div class="complete-banner" id="morningBanner">
        <h4>🎉 أحسنت! أكملت أذكار الصباح</h4>
        <p>جزاك الله خيراً ورزقك يوماً مباركاً</p>
    </div>
    <div class="dhikr-list" id="morningList"></div>
</div>

<!-- EVENING -->
<div id="tab-evening" class="tab-content">
    <div class="section-header">
        <div class="section-icon"><i class="bi bi-moon-stars"></i></div>
        <h3>أذكار المساء</h3>
    </div>
    <div class="complete-banner" id="eveningBanner">
        <h4>🌙 ممتاز! أكملت أذكار المساء</h4>
        <p>ليلة مباركة بإذن الله</p>
    </div>
    <div class="dhikr-list" id="eveningList"></div>
</div>

<!-- TASBIH -->
<div id="tab-tasbih" class="tab-content">
    <div class="section-header">
        <div class="section-icon"><i class="bi bi-circle-fill"></i></div>
        <h3>مسبحة رقمية</h3>
    </div>
    <div class="tasbih-tabs">
        <button class="tasbih-type-btn active" onclick="setTasbih('سبحان الله',33,this)">سبحان الله</button>
        <button class="tasbih-type-btn" onclick="setTasbih('الحمد لله',33,this)">الحمد لله</button>
        <button class="tasbih-type-btn" onclick="setTasbih('الله أكبر',34,this)">الله أكبر</button>
    </div>
    <div class="tasbih-big-card">
        <div class="tasbih-label" id="tasbihLabel">سبحان الله</div>
        <div class="tasbih-counter-big" id="tasbihCount">0</div>
        <div class="tasbih-target-label" id="tasbihTargetLabel">من 33</div>
        <div class="tasbih-ring-track">
            <svg viewBox="0 0 100 100" width="100" height="100">
                <circle class="ring-bg" cx="50" cy="50" r="42"/>
                <circle class="ring-fill" id="tasbihRing" cx="50" cy="50" r="42"
                    stroke-dasharray="264" stroke-dashoffset="264"/>
            </svg>
        </div>
        <button class="tasbih-circle-btn" onclick="incrementTasbih()">+</button>
        <div class="tasbih-controls">
            <button class="tasbih-ctrl-btn" onclick="resetTasbih()"><i class="bi bi-arrow-counterclockwise"></i> تصفير</button>
            <button class="tasbih-ctrl-btn" onclick="decrementTasbih()"><i class="bi bi-dash"></i> طرح</button>
        </div>
    </div>
    <div class="tasbih-stats-box">
        <div class="ttl">📊 إحصائيات اليوم</div>
        <div class="tasbih-stats-row">
            <div><span class="num" id="todaySobhan">0</span><span class="lbl">سبحان الله</span></div>
            <div><span class="num" id="todayHamd">0</span><span class="lbl">الحمد لله</span></div>
            <div><span class="num" id="todayAkbar">0</span><span class="lbl">الله أكبر</span></div>
            <div><span class="num total-num" id="todayTotal">0</span><span class="lbl">المجموع</span></div>
        </div>
    </div>
</div>

<!-- DUA -->
<div id="tab-dua" class="tab-content">
    <div class="section-header">
        <div class="section-icon"><i class="bi bi-hand-index-thumb"></i></div>
        <h3>أدعية يومية</h3>
    </div>
    <div id="duaList"></div>
</div>

<footer class="footer">
    <nav class="nav-foot">
        <ul class="list-foot">
            <li class="item-foot"><a href="index.php" class="bi bi-house-fill"></a></li>
            <li class="item-foot"><a href="statistics.php" class="bi bi-bar-chart-fill"></a></li>
            <li class="item-foot"><a href="ibadat.php" class="bi bi-heart-fill active"></a></li>
            <li class="item-foot"><a href="profile.php" class="bi bi-person-fill"></a></li>
        </ul>
    </nav>
</footer>

<script>
// ===== DATA =====
const morningAdhkar = [
    { id:'m1', arabic:'أَصْبَحْنَا وَأَصْبَحَ الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ', meaning:'أصبحنا وأصبح الملك والحمد لله', virtue:'💎 من أذكار الصباح المستحبة', target:1 },
    { id:'m2', arabic:'اللَّهُمَّ بِكَ أَصْبَحْنَا، وَبِكَ أَمْسَيْنَا، وَبِكَ نَحْيَا، وَبِكَ نَمُوتُ، وَإِلَيْكَ النُّشُورُ', meaning:'اللهم بك نبدأ يومنا ونختمه', virtue:'✨ دعاء النبي ﷺ في الصباح', target:1 },
    { id:'m3', arabic:'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ', meaning:'تنزيه الله وحمده', virtue:'🌱 من قالها مئة مرة غُفرت ذنوبه', target:100 },
    { id:'m4', arabic:'أَعُوذُ بِاللَّهِ مِنَ الشَّيْطَانِ الرَّجِيمِ', meaning:'الاستعاذة بالله من الشيطان', virtue:'🛡️ حصن للمؤمن في يومه', target:3 },
    { id:'m5', arabic:'اللَّهُمَّ أَنْتَ رَبِّي لَا إِلَهَ إِلَّا أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ', meaning:'سيد الاستغفار', virtue:'👑 من قاله موقناً دخل الجنة', target:1 },
    { id:'m6', arabic:'اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ', meaning:'الصلاة على النبي ﷺ', virtue:'💚 من صلى عليّ مرة صلى الله عليه عشراً', target:10 }
];
const eveningAdhkar = [
    { id:'e1', arabic:'أَمْسَيْنَا وَأَمْسَى الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لَا إِلَهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ', meaning:'أمسينا وأمسى الملك والحمد لله', virtue:'💎 ذكر المساء الأساسي', target:1 },
    { id:'e2', arabic:'اللَّهُمَّ بِكَ أَمْسَيْنَا، وَبِكَ أَصْبَحْنَا، وَبِكَ نَحْيَا، وَبِكَ نَمُوتُ، وَإِلَيْكَ الْمَصِيرُ', meaning:'دعاء المساء', virtue:'✨ دعاء النبي ﷺ في المساء', target:1 },
    { id:'e3', arabic:'أَعُوذُ بِكَلِمَاتِ اللَّهِ التَّامَّاتِ مِنْ شَرِّ مَا خَلَقَ', meaning:'الاستعاذة من شر الخلق', virtue:'🛡️ من قالها لم يضره شيء', target:3 },
    { id:'e4', arabic:'بِسْمِ اللَّهِ الَّذِي لَا يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الْأَرْضِ وَلَا فِي السَّمَاءِ', meaning:'باسم الله الذي لا يضر معه شيء', virtue:'🔒 حماية من كل أذى', target:3 },
    { id:'e5', arabic:'اللَّهُمَّ عَافِنِي فِي بَدَنِي، اللَّهُمَّ عَافِنِي فِي سَمْعِي، اللَّهُمَّ عَافِنِي فِي بَصَرِي', meaning:'طلب العافية في البدن والسمع والبصر', virtue:'💚 دعاء العافية الشاملة', target:3 },
    { id:'e6', arabic:'اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ', meaning:'الصلاة على النبي ﷺ', virtue:'💚 من صلى عليّ مرة صلى الله عليه عشراً', target:10 }
];
const duaData = [
    { id:'d1', title:'دعاء دخول المنزل', icon:'🏠', time:'عند الدخول', arabic:'اللَّهُمَّ إِنِّي أَسْأَلُكَ خَيْرَ الْمَوْلِجِ وَخَيْرَ الْمَخْرَجِ، بِسْمِ اللَّهِ وَلَجْنَا، وَبِسْمِ اللَّهِ خَرَجْنَا، وَعَلَى اللَّهِ رَبِّنَا تَوَكَّلْنَا', translation:'اللهم أسألك خير المدخل وخير المخرج' },
    { id:'d2', title:'دعاء قبل النوم', icon:'🌙', time:'قبل النوم', arabic:'اللَّهُمَّ بِاسْمِكَ أَمُوتُ وَأَحْيَا', translation:'اللهم باسمك أموت وأحيا' },
    { id:'d3', title:'دعاء الكرب والهم', icon:'💭', time:'عند الهم', arabic:'لَا إِلَهَ إِلَّا اللَّهُ الْعَظِيمُ الْحَلِيمُ، لَا إِلَهَ إِلَّا اللَّهُ رَبُّ الْعَرْشِ الْعَظِيمِ، لَا إِلَهَ إِلَّا اللَّهُ رَبُّ السَّمَاوَاتِ وَرَبُّ الْأَرْضِ', translation:'دعاء فرج الكرب والهم والحزن' },
    { id:'d4', title:'دعاء طلب الرزق', icon:'💰', time:'في الصباح', arabic:'اللَّهُمَّ إِنِّي أَسْأَلُكَ عِلْمًا نَافِعًا، وَرِزْقًا طَيِّبًا، وَعَمَلًا مُتَقَبَّلًا', translation:'اللهم أسألك علماً نافعاً ورزقاً طيباً وعملاً مقبولاً' },
    { id:'d5', title:'دعاء الاستخارة', icon:'🤲', time:'عند الاستخارة', arabic:'اللَّهُمَّ إِنِّي أَسْتَخِيرُكَ بِعِلْمِكَ، وَأَسْتَقْدِرُكَ بِقُدْرَتِكَ، وَأَسْأَلُكَ مِنْ فَضْلِكَ الْعَظِيمِ', translation:'اللهم إني أستخيرك بعلمك وأستقدرك بقدرتك' }
];

// ===== STATE =====
const storageKey = 'ibadat_' + new Date().toISOString().split('T')[0];
let state = JSON.parse(localStorage.getItem(storageKey) || 'null') || { morning:{}, evening:{}, dua:{}, tasbih:{sobhan:0,hamd:0,akbar:0} };
let curTasbihKey = 'sobhan', curTasbihTarget = 33, curTasbihLabel = 'سبحان الله';
let curTasbihCount = state.tasbih.sobhan;

function saveState() { localStorage.setItem(storageKey, JSON.stringify(state)); }

// ===== TABS =====
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

// ===== DATE =====
function setHeroDate() {
    const days=['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
    const months=['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    const n=new Date();
    document.getElementById('heroDate').textContent=`${days[n.getDay()]} ${n.getDate()} ${months[n.getMonth()]} ${n.getFullYear()}`;
}

// ===== DHIKR =====
function renderDhikrCard(dhikr, listId, group) {
    const current = state[group][dhikr.id] || 0;
    const isDone = current >= dhikr.target;
    const card = document.createElement('div');
    card.className = 'dhikr-card' + (isDone ? ' completed' : '');
    card.id = 'card-' + dhikr.id;
    let dotsHtml = '';
    if (dhikr.target <= 20) {
        for (let i=0;i<dhikr.target;i++) dotsHtml+=`<div class="dot${i<current?' filled':''}"></div>`;
    }
    card.innerHTML = `
        <div>
            <div class="dhikr-arabic">${dhikr.arabic}</div>
            <div class="dhikr-meaning">${dhikr.meaning}</div>
            <div class="dhikr-virtue">${dhikr.virtue}</div>
        </div>
        <div class="counter-section">
            <div class="counter-display">
                <button class="counter-minus" onclick="adjustDhikr('${dhikr.id}','${group}',-1)">−</button>
                <div class="counter-numbers">
                    <span class="counter-current" id="cnt-${dhikr.id}">${current}</span>
                    <span class="counter-target">/ ${dhikr.target}</span>
                </div>
                <button class="counter-plus" onclick="adjustDhikr('${dhikr.id}','${group}',1)">+</button>
            </div>
            ${dotsHtml ? `<div class="dots-track" id="dots-${dhikr.id}">${dotsHtml}</div>` : ''}
        </div>`;
    document.getElementById(listId).appendChild(card);
}

function renderAllDhikr() {
    document.getElementById('morningList').innerHTML='';
    document.getElementById('eveningList').innerHTML='';
    morningAdhkar.forEach(d=>renderDhikrCard(d,'morningList','morning'));
    eveningAdhkar.forEach(d=>renderDhikrCard(d,'eveningList','evening'));
}

function adjustDhikr(id, group, delta) {
    const all=[...morningAdhkar,...eveningAdhkar];
    const dhikr=all.find(d=>d.id===id); if(!dhikr) return;
    let cur=state[group][id]||0;
    cur=Math.max(0,Math.min(dhikr.target,cur+delta));
    state[group][id]=cur; saveState();
    const cnt=document.getElementById('cnt-'+id);
    if(cnt){ cnt.textContent=cur; cnt.classList.remove('bump'); void cnt.offsetWidth; cnt.classList.add('bump'); }
    const dots=document.getElementById('dots-'+id);
    if(dots) dots.querySelectorAll('.dot').forEach((d,i)=>d.classList.toggle('filled',i<cur));
    const card=document.getElementById('card-'+id);
    const done=cur>=dhikr.target;
    if(done && !card.classList.contains('completed')) card.classList.add('completed');
    else if(!done) card.classList.remove('completed');
    updateProgress(); checkGroupComplete(group);
}

// ===== PROGRESS =====
function updateProgress() {
    let total=morningAdhkar.length+eveningAdhkar.length, done=0, md=0, ed=0;
    morningAdhkar.forEach(d=>{ if((state.morning[d.id]||0)>=d.target){done++;md++;} });
    eveningAdhkar.forEach(d=>{ if((state.evening[d.id]||0)>=d.target){done++;ed++;} });
    const tt=state.tasbih.sobhan+state.tasbih.hamd+state.tasbih.akbar;
    document.getElementById('progressText').textContent=`${done} / ${total}`;
    document.getElementById('progressBar').style.width=(done/total*100)+'%';
    document.getElementById('statMorning').textContent=md+'/'+morningAdhkar.length;
    document.getElementById('statEvening').textContent=ed+'/'+eveningAdhkar.length;
    document.getElementById('statTasbih').textContent=tt;
    document.getElementById('todaySobhan').textContent=state.tasbih.sobhan;
    document.getElementById('todayHamd').textContent=state.tasbih.hamd;
    document.getElementById('todayAkbar').textContent=state.tasbih.akbar;
    document.getElementById('todayTotal').textContent=tt;
}

function checkGroupComplete(group) {
    const list=group==='morning'?morningAdhkar:eveningAdhkar;
    const allDone=list.every(d=>(state[group][d.id]||0)>=d.target);
    document.getElementById(group+'Banner').classList.toggle('show',allDone);
}

// ===== TASBIH =====
function setTasbih(label,target,btn) {
    curTasbihLabel=label; curTasbihTarget=target;
    curTasbihKey=label==='سبحان الله'?'sobhan':(label==='الحمد لله'?'hamd':'akbar');
    curTasbihCount=state.tasbih[curTasbihKey];
    document.querySelectorAll('.tasbih-type-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tasbihLabel').textContent=label;
    document.getElementById('tasbihTargetLabel').textContent='من '+target;
    document.getElementById('tasbihCount').textContent=curTasbihCount;
    updateTasbihRing();
}
function incrementTasbih() {
    curTasbihCount++; state.tasbih[curTasbihKey]=curTasbihCount; saveState();
    document.getElementById('tasbihCount').textContent=curTasbihCount;
    updateTasbihRing(); updateProgress();
    if(curTasbihCount===curTasbihTarget){ const el=document.getElementById('tasbihCount'); el.style.color='#f59e0b'; setTimeout(()=>el.style.color='',600); }
}
function decrementTasbih() {
    if(curTasbihCount<=0) return;
    curTasbihCount--; state.tasbih[curTasbihKey]=curTasbihCount; saveState();
    document.getElementById('tasbihCount').textContent=curTasbihCount;
    updateTasbihRing(); updateProgress();
}
function resetTasbih() {
    curTasbihCount=0; state.tasbih[curTasbihKey]=0; saveState();
    document.getElementById('tasbihCount').textContent=0;
    updateTasbihRing(); updateProgress();
}
function updateTasbihRing() {
    const p=Math.min(curTasbihCount/curTasbihTarget,1);
    document.getElementById('tasbihRing').style.strokeDashoffset=264-(p*264);
}

// ===== DUA =====
function renderDuaCards() {
    const c=document.getElementById('duaList'); c.innerHTML='';
    duaData.forEach(dua=>{
        const done=!!state.dua[dua.id];
        const card=document.createElement('div');
        card.className='dua-card';
        card.innerHTML=`
            <div class="dua-header">
                <div class="dua-icon">${dua.icon}</div>
                <div class="dua-title">${dua.title}</div>
                <span class="dua-time">${dua.time}</span>
            </div>
            <div class="dua-arabic-text">${dua.arabic}</div>
            <div class="dua-translation">${dua.translation}</div>
            <button class="dua-check-btn${done?' done':''}" id="dbtn-${dua.id}" onclick="toggleDua('${dua.id}',this)">
                ${done?'✓ تمت القراءة':'قرأت هذا الدعاء'}
            </button>`;
        c.appendChild(card);
    });
}
function toggleDua(id,btn) {
    state.dua[id]=!state.dua[id]; saveState();
    btn.classList.toggle('done',!!state.dua[id]);
    btn.textContent=state.dua[id]?'✓ تمت القراءة':'قرأت هذا الدعاء';
}

// ===== INIT =====
window.addEventListener('DOMContentLoaded',()=>{
    setHeroDate();
    renderAllDhikr();
    renderDuaCards();
    updateProgress();
    updateTasbihRing();
    checkGroupComplete('morning');
    checkGroupComplete('evening');
});
</script>
</body>
</html>