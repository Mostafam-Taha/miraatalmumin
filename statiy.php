<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'api/statistics_data.php';
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
    
    
    
    <!-- Google tag (gtag.js) -->

<script async src="https://www.googletagmanager.com/gtag/js?id=G-RSG9M1LGJD"></script>

<script>

  window.dataLayer = window.dataLayer || [];

  function gtag(){dataLayer.push(arguments);}

  gtag('js', new Date());

  gtag('config', 'G-RSG9M1LGJD');

</script>
    
    
    
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
    
    <section class="bar-section">
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
    
    <!-- قسم التقرير الأسبوعي والشهري -->
    <section class="weekly-report-section">
        <div class="report-header">
            <div class="report-title">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>تقرير الأداء</span>
            </div>
            <div class="report-period" id="current-period">
                الأسبوع الحالي
            </div>
        </div>
        
        <div class="report-switcher">
            <div class="report-tab active" data-report="weekly">
                <i class="bi bi-calendar-week"></i>
                الأسبوعي
            </div>
            <div class="report-tab" data-report="monthly">
                <i class="bi bi-calendar-month"></i>
                الشهري
            </div>
        </div>
        
        <!-- التقرير الأسبوعي -->
        <div class="report-content active" id="weekly-report">
            <?php if (!empty($weekly_report['days'])): ?>
            <div class="weekly-bars">
                <?php foreach ($weekly_report['days'] as $day): ?>
                <div class="day-bar">
                    <div class="bar-container">
                        <div class="bar" 
                             style="height: <?php echo $day['percentage']; ?>%; background-color: <?php echo $day['color']; ?>"
                             title="<?php echo $day['day'] . ': ' . $day['percentage'] . '%' ?>">
                        </div>
                        <div class="bar-value"><?php echo round($day['percentage']); ?>%</div>
                        <div class="bar-label"><?php echo $day['day']; ?></div>
                    </div>
                    <div class="day-info">
                        <?php echo date('d/m', strtotime($day['date'])); ?>
                        <span><?php echo $day['prayed_count']; ?>/5</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="report-summary">
                <div class="summary-item">
                    <div class="summary-value percentage"><?php echo $weekly_report['average_percentage']; ?>%</div>
                    <div class="summary-label">متوسط الإلتزام</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value"><?php echo $weekly_report['total_prayed']; ?></div>
                    <div class="summary-label">إجمالي الصلوات</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value nawafil"><?php echo $weekly_report['total_nawafil']; ?></div>
                    <div class="summary-label">نوافل هذا الأسبوع</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value"><?php echo round($weekly_report['total_prayed'] / 35 * 100); ?>%</div>
                    <div class="summary-label">نسبة الأداء</div>
                </div>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 40px 20px;">
                <i class="bi bi-calendar-x" style="font-size: 48px; color: #9CA3AF; margin-bottom: 20px;"></i>
                <h3 style="color: #6B7280; margin-bottom: 10px;">لا توجد بيانات للأسبوع الحالي</h3>
                <p style="color: #9CA3AF;">ابدأ بتسجيل صلواتك لرؤية التقرير الأسبوعي</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- التقرير الشهري -->
        <div class="report-content" id="monthly-report">
            <?php if ($monthly_report['total_days'] > 0): ?>
            <div class="monthly-report-grid">
                <div class="monthly-stat">
                    <div class="monthly-value"><?php echo $monthly_report['percentage']; ?>%</div>
                    <div class="monthly-label">نسبة الإلتزام الشهري</div>
                    <div class="monthly-progress">
                        <div class="monthly-progress-bar" style="width: <?php echo $monthly_report['percentage']; ?>%"></div>
                    </div>
                </div>
                
                <div class="monthly-stat">
                    <div class="monthly-value"><?php echo $monthly_report['completed_days']; ?></div>
                    <div class="monthly-label">يوم كامل الصلاة</div>
                    <div class="monthly-progress">
                        <div class="monthly-progress-bar" 
                             style="width: <?php echo round(($monthly_report['completed_days'] / $monthly_report['total_days']) * 100); ?>%"></div>
                    </div>
                </div>
                
                <div class="monthly-stat">
                    <div class="monthly-value" style="color: #047857;"><?php echo $monthly_report['total_nawafil']; ?></div>
                    <div class="monthly-label">عدد النوافل</div>
                    <div class="monthly-progress">
                        <div class="monthly-progress-bar" 
                             style="width: <?php echo min(100, round($monthly_report['total_nawafil'] / 20)); ?>%"></div>
                    </div>
                </div>
            </div>
            
            <div class="monthly-details">
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">إجمالي الصلوات</span>
                        <span class="detail-value"><?php echo $monthly_report['total_prayed']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">أيام الشهر</span>
                        <span class="detail-value"><?php echo $monthly_report['total_days']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">الحد الأقصى</span>
                        <span class="detail-value"><?php echo $monthly_report['max_possible']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">نسبة الإنجاز</span>
                        <span class="detail-value"><?php echo round(($monthly_report['total_prayed'] / $monthly_report['max_possible']) * 100, 1); ?>%</span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 40px 20px;">
                <i class="bi bi-calendar-x" style="font-size: 48px; color: #9CA3AF; margin-bottom: 20px;"></i>
                <h3 style="color: #6B7280; margin-bottom: 10px;">لا توجد بيانات للشهر الحالي</h3>
                <p style="color: #9CA3AF;">ابدأ بتسجيل صلواتك لرؤية التقرير الشهري</p>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
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
        
        // بيانات التقرير الأسبوعي
        const weeklyReport = <?php echo json_encode($weekly_report); ?>;
        const monthlyReport = <?php echo json_encode($monthly_report); ?>;
        
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
        
        // ============ وظائف تقرير الأداء ============
        function initializeReportTabs() {
            const tabs = document.querySelectorAll('.report-tab');
            const contents = document.querySelectorAll('.report-content');
            const periodElement = document.getElementById('current-period');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const reportType = this.getAttribute('data-report');
                    
                    // تحديث التبويبات النشطة
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // تحديث المحتوى
                    contents.forEach(content => content.classList.remove('active'));
                    document.getElementById(`${reportType}-report`).classList.add('active');
                    
                    // تحديث نص الفترة
                    if (reportType === 'weekly') {
                        periodElement.textContent = 'الأسبوع الحالي';
                        animateWeeklyBars();
                    } else {
                        const monthNames = [
                            'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
                        ];
                        const currentDate = new Date();
                        periodElement.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
                    }
                });
            });
            
            // تشغيل الرسوم المتحركة للأعمدة
            if (weeklyReport.days && weeklyReport.days.length > 0) {
                setTimeout(animateWeeklyBars, 500);
            }
        }
        
        function animateWeeklyBars() {
            const bars = document.querySelectorAll('.weekly-bars .bar');
            bars.forEach((bar, index) => {
                // إعادة تعيين الارتفاع للرسوم المتحركة
                const currentHeight = bar.style.height;
                bar.style.height = '0%';
                
                setTimeout(() => {
                    bar.style.transition = 'height 1s ease-in-out';
                    bar.style.height = currentHeight;
                    
                    // إضافة تأثير نبض عند اكتمال الرسوم المتحركة
                    setTimeout(() => {
                        bar.style.transform = 'translateX(-50%) scale(1.1)';
                        setTimeout(() => {
                            bar.style.transform = 'translateX(-50%) scale(1)';
                        }, 200);
                    }, 1000);
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
            
            // تهيئة تقرير الأداء
            initializeReportTabs();
            
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