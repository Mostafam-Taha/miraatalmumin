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

// تحديد الشهر والسنة من الـ URL أو استخدام القيم الحالية
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// أسماء الصلوات بالترتيب
$prayers = ['الفجر', 'الظهر', 'العصر', 'المغرب', 'العشاء'];

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
foreach ($prayers as $prayer) {
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
foreach ($prayers as $prayer) {
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
foreach ($prayers as $prayer) {
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
?>


<?php
// قسم بيانات النوافل لكل صلاة
$user_id = $_SESSION['user_id'] ?? 0;

// تعريف الصلوات والنوافل المطلوبة
$prayers = [
    'Fajr' => [
        'name' => 'الفجر',
        'before' => 2,  // ركعتين قبل
        'after' => 0    // لا نوافل بعد
    ],
    'Dhuhr' => [
        'name' => 'الظهر',
        'before' => 4,  // 4 ركعات قبل
        'after' => 2    // ركعتين بعد
    ],
    'Asr' => [
        'name' => 'العصر',
        'before' => 0,  // لا نوافل قبل
        'after' => 0    // لا نوافل بعد
    ],
    'Maghrib' => [
        'name' => 'المغرب',
        'before' => 0,  // لا نوافل قبل
        'after' => 2    // ركعتين بعد
    ],
    'Isha' => [
        'name' => 'العشاء',
        'before' => 0,  // لا نوافل قبل
        'after' => 2    // ركعتين بعد
    ]
];

// احصل على تاريخ اليوم
$today = date('Y-m-d');

// استعلام لجلب النوافل المسجلة لهذا اليوم
$nawafil_sql = "SELECT prayer_name, nawafil_type, SUM(rakat_count) as total_rakat 
                FROM nawafil_records 
                WHERE user_id = :user_id 
                AND date = :today 
                GROUP BY prayer_name, nawafil_type";
$nawafil_stmt = $pdo->prepare($nawafil_sql);
$nawafil_stmt->execute([':user_id' => $user_id, ':today' => $today]);
$today_nawafil = $nawafil_stmt->fetchAll(PDO::FETCH_ASSOC);

// تنظيم البيانات في مصفوفة
$nawafil_data = [];
foreach ($today_nawafil as $row) {
    $nawafil_data[$row['prayer_name']][$row['nawafil_type']] = $row['total_rakat'];
}

// احسب النسب المئوية لكل صلاة
$prayer_percentages = [];
foreach ($prayers as $prayer_key => $prayer_info) {
    $total_required = $prayer_info['before'] + $prayer_info['after'];
    $total_performed = 0;
    
    // احسب عدد الركعات المؤداة
    $before_count = $nawafil_data[$prayer_key]['before'] ?? 0;
    $after_count = $nawafil_data[$prayer_key]['after'] ?? 0;
    
    $total_performed = $before_count + $after_count;
    
    // احسب النسبة المئوية
    if ($total_required > 0) {
        $percentage = min(100, round(($total_performed / $total_required) * 100));
    } else {
        $percentage = 0;
    }
    
    $prayer_percentages[$prayer_key] = [
        'arabic_name' => $prayer_info['name'],
        'before_required' => $prayer_info['before'],
        'after_required' => $prayer_info['after'],
        'before_performed' => $before_count,
        'after_performed' => $after_count,
        'total_performed' => $total_performed,
        'percentage' => $percentage
    ];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/statistics.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/statiy.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    
    <!-- ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.css">
    
    <title>الإحصائيات</title>
    
    <style>

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
                    <h2>الاحصائيات</h2>
                </div>
                <div class="header-icon">
                    <i class="bi bi-person-circle"></i>
                    <i class="bi bi-gear-fill"></i>
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
                <a href="statiy.php" class="item-link active">احصائيات</a>
            </div>
            <div class="switch-item">
                <a href="groups.php" class="item-link">مجموعة</a>
            </div>
        </div>
    </section>
    
    <!-- عرض إحصائيات الشهر -->
    <section class="bourd">
        <div class="content-if">
            <div class="heatmap-header">
                <p>تعقب الفروض</p>
            </div>
            <div class="month-selector">
                <div class="month-nav">
                    <button onclick="changeMonth(-1)">
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
                    <button onclick="changeMonth(1)">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </div>
            </div>
            
            <!-- Heatmap موحد للصلوات -->
            <div class="prayer-heatmap-container">
                <?php if (empty($records)): ?>
                    <div class="no-data">
                        <i class="bi bi-calendar-x" style="font-size: 48px; margin-bottom: 15px; color: #9CA3AF;"></i>
                        <p>لا توجد بيانات للشهر الحالي</p>
                        <p style="font-size: 14px; margin-top: 10px;">ابدأ بتسجيل صلواتك لرؤية الإحصائيات</p>
                    </div>
                <?php else: ?>
                    <div id="combined-heatmap"></div>
                    
                    <!-- مفتاح الألوان -->
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
    </section>
    



<!--  -->
<!--  -->
<!-- قسم النوافل -->
<div class="nawafil-section">
    <div class="sei-abady">
        <h4>نوافل اليوم</h4>
        <a href="#" onclick="showAllNawafil()">إظهار الكل</a>
    </div>
    
    <div class="nawafil-container">
        <?php foreach ($prayer_percentages as $prayer_key => $data): ?>
            <div class="nawafil-item">
                <div class="nawafil-header">
                    <h5><?php echo $data['arabic_name']; ?></h5>
                    <span class="percentage"><?php echo $data['percentage']; ?>%</span>
                </div>
                
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?php echo $data['percentage']; ?>%; 
                        background-color: <?php 
                            if ($data['percentage'] == 100) echo '#28a745';
                            elseif ($data['percentage'] >= 50) echo '#ffc107';
                            else echo '#dc3545';
                        ?>;">
                    </div>
                </div>
                
                <div class="nawafil-details">
                    <div class="detail-item">
                        <span class="label">قبل الصلاة:</span>
                        <span class="value">
                            <?php echo $data['before_performed']; ?>/<?php echo $data['before_required']; ?> ركعات
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="label">بعد الصلاة:</span>
                        <span class="value">
                            <?php echo $data['after_performed']; ?>/<?php echo $data['after_required']; ?> ركعات
                        </span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="label">المجموع:</span>
                        <span class="value">
                            <?php echo $data['total_performed']; ?>/<?php echo ($data['before_required'] + $data['after_required']); ?> ركعات
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!--  -->
<!--  -->

    

    <section class="statistics-if">
        <div class="cn-if">
            <div><p>إحصائيات</p></div>
            <div class="stat-gt">
                <div class="data-stat">
                    <span class="period-btn" data-period="day">يوم</span>
                    <span class="period-btn" data-period="week">أسبوع</span>
                    <span class="period-btn" data-period="month">الشهر</span>
                    <span class="period-btn" data-period="year">سنة</span>
                    <span class="period-btn active" data-period="lifetime">مدى الحياة</span>
                </div>
                <div class="card-stat">
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
                </div>
            </div>
        </div>
    </section>

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
        // بيانات الصلوات من PHP إلى JavaScript
        const heatmapSeries = <?php echo json_encode($heatmap_series); ?>;
        const prayers = <?php echo json_encode($prayers); ?>;
        const selectedYear = <?php echo $selected_year; ?>;
        const selectedMonth = <?php echo $selected_month; ?>;
        const totalDays = <?php echo $total_days; ?>;
        
        // تهيئة الـ Heatmap
        function initCombinedHeatmap() {
            const options = {
                series: heatmapSeries,
                chart: {
                    type: 'heatmap',
                    height: 400,
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
                                    color: '#10B981',
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
                            fontSize: '12px'
                        }
                    },
                    tooltip: {
                        enabled: false
                    },
                    tickAmount: totalDays
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '14px',
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
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 500
                        },
                        xaxis: {
                            labels: {
                                style: {
                                    fontSize: '10px'
                                }
                            },
                            tickAmount: Math.min(totalDays, 15)
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        }
                    }
                }]
            };
            
            const chart = new ApexCharts(document.querySelector("#combined-heatmap"), options);
            chart.render();
        }
        
        // تغيير الشهر
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
            
            window.location.href = `?month=${newMonth}&year=${newYear}`;
        }
        
        // تهيئة الرسوم البيانية عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            if (heatmapSeries.length > 0) {
                initCombinedHeatmap();
            }
        });

        // 
        // 
        // 
        document.addEventListener('DOMContentLoaded', function() {
            const periodButtons = document.querySelectorAll('.period-btn');
            
            // تحميل البيانات عند فتح الصفحة
            loadStatistics('lifetime');
            
            // إضافة أحداث النقر على أزرار الفترة
            periodButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const period = this.getAttribute('data-period');
                    
                    // تحديث الحالة النشطة
                    periodButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // تحميل البيانات للفترة المحددة
                    loadStatistics(period);
                });
            });
            
            function loadStatistics(period) {
                // إظهار رسالة تحميل (اختياري)
                document.querySelectorAll('.card-stat span[id]').forEach(span => {
                    span.textContent = '...';
                });
                
                // طلب البيانات من الخادم
                fetch(`api/statistics_logic.php?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        // تحديث البيانات في الواجهة
                        document.getElementById('prayed_in_mosque').textContent = data.prayed_in_mosque + '%';
                        document.getElementById('delayed').textContent = data.delayed + '%';
                        document.getElementById('prayed_alone').textContent = data.prayed_alone + '%';
                        document.getElementById('not_prayed').textContent = data.not_prayed + '%';
                        document.getElementById('badges_count').textContent = data.badges_count;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ في تحميل البيانات');
                    });
            }
        });
    </script>
</body>
</html>