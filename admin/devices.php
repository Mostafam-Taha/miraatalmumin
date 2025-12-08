<?php
session_start();
// يمكنك إضافة نظام مصادقة للإدمن هنا

require_once '../includes/config.php';

try {
    // إحصائيات عامة
    $total_users = $pdo->query("SELECT COUNT(*) as count FROM users")->fetchColumn();
    $users_with_device = $pdo->query("SELECT COUNT(*) as count FROM users WHERE device_type IS NOT NULL")->fetchColumn();
    
    // إحصائيات الأجهزة
    $device_stats = $pdo->query("
        SELECT 
            device_type,
            COUNT(*) as count,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM users WHERE device_type IS NOT NULL), 2) as percentage
        FROM users 
        WHERE device_type IS NOT NULL 
        GROUP BY device_type 
        ORDER BY count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // إحصائيات أنظمة التشغيل
    $os_stats = [];
    $user_agents = $pdo->query("SELECT user_agent FROM users WHERE user_agent IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($user_agents as $ua) {
        if (!empty($ua)) {
            $os = detectOS($ua);
            if (!isset($os_stats[$os])) {
                $os_stats[$os] = 0;
            }
            $os_stats[$os]++;
        }
    }
    
    arsort($os_stats);
    
    // آخر المستخدمين المسجلين
    $recent_users = $pdo->query("
        SELECT id, name, email, device_type, last_device_login, last_login 
        FROM users 
        WHERE last_login IS NOT NULL 
        ORDER BY last_login DESC 
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}

// دالة مساعدة من ملف save_user.php
function detectOS($user_agent) {
    $user_agent = strtolower($user_agent);
    
    $os_list = [
        'Android' => '/android/',
        'iOS' => '/iphone|ipad|ipod/',
        'Windows' => '/windows nt|windows phone/',
        'Mac OS' => '/macintosh|mac os x/',
        'Linux' => '/linux/',
        'Chrome OS' => '/cros/'
    ];
    
    foreach ($os_list as $os => $pattern) {
        if (preg_match($pattern, $user_agent)) {
            return $os;
        }
    }
    
    return 'Unknown';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إحصائيات الأجهزة - مرآة المؤمن</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 25px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .header h1 {
            color: #059669;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: #059669;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
            color: #059669;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 14px;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .chart-title {
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-container {
            height: 300px;
            position: relative;
        }
        
        .devices-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            padding: 15px;
            background: #f8fafc;
            text-align: right;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .device-icon {
            font-size: 20px;
            margin-left: 10px;
        }
        
        .device-android { color: #3ddc84; }
        .device-iphone { color: #a2aaad; }
        .device-ipad { color: #5ac8fa; }
        .device-windows { color: #0078d7; }
        .device-mac { color: #999999; }
        .device-linux { color: #fbbf24; }
        .device-desktop { color: #8b5cf6; }
        .device-mobile { color: #10b981; }
        
        .os-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .os-android { background: #d1fae5; color: #065f46; }
        .os-ios { background: #dbeafe; color: #1e40af; }
        .os-windows { background: #e0e7ff; color: #3730a3; }
        .os-mac { background: #f3f4f6; color: #374151; }
        .os-linux { background: #fef3c7; color: #92400e; }
        
        .time-ago {
            color: #6b7280;
            font-size: 13px;
        }
        
        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .chart-card {
                padding: 15px;
            }
            
            .chart-container {
                height: 250px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-laptop"></i>
                إحصائيات الأجهزة
            </h1>
            <p>تحليل أجهزة المستخدمين وأنظمة التشغيل</p>
        </div>
        
        <div class="stats-overview">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">إجمالي المستخدمين</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="stat-number"><?php echo $users_with_device; ?></div>
                <div class="stat-label">مستخدمين بأجهزة محددة</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-number"><?php echo $total_users > 0 ? round(($users_with_device / $total_users) * 100, 2) : 0; ?>%</div>
                <div class="stat-label">نسبة التعرف على الأجهزة</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number"><?php echo count($device_stats); ?></div>
                <div class="stat-label">أنواع الأجهزة المختلفة</div>
            </div>
        </div>
        
        <div class="charts-container">
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-pie-chart"></i>
                    توزيع الأجهزة
                </h3>
                <div class="chart-container">
                    <canvas id="devicesChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-chart-bar"></i>
                    توزيع أنظمة التشغيل
                </h3>
                <div class="chart-container">
                    <canvas id="osChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="devices-table">
            <div class="table-header">
                <h3>آخر المستخدمين المسجلين</h3>
                <div>عرض <?php echo count($recent_users); ?> مستخدم</div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>الجهاز</th>
                        <th>نظام التشغيل</th>
                        <th>آخر دخول</th>
                        <th>التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                            <div style="font-size: 12px; color: #6b7280;">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($user['device_type']): ?>
                                <div style="display: flex; align-items: center;">
                                    <?php 
                                    $device_icons = [
                                        'android' => 'android',
                                        'iphone' => 'apple',
                                        'ipad' => 'apple',
                                        'windows' => 'windows',
                                        'mac' => 'apple',
                                        'linux' => 'linux',
                                        'desktop' => 'desktop',
                                        'mobile' => 'mobile-alt'
                                    ];
                                    
                                    $icon = $device_icons[$user['device_type']] ?? 'question-circle';
                                    $color_class = 'device-' . $user['device_type'];
                                    ?>
                                    <i class="fab fa-<?php echo $icon; ?> device-icon <?php echo $color_class; ?>"></i>
                                    <span style="text-transform: capitalize;">
                                        <?php echo $user['device_type']; ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <span style="color: #9ca3af;">غير معروف</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $os = detectOS($user['user_agent'] ?? '');
                            $os_classes = [
                                'Android' => 'os-android',
                                'iOS' => 'os-ios',
                                'Windows' => 'os-windows',
                                'Mac OS' => 'os-mac',
                                'Linux' => 'os-linux'
                            ];
                            ?>
                            <span class="os-badge <?php echo $os_classes[$os] ?? ''; ?>">
                                <?php echo $os; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['last_login']): ?>
                                <div><?php echo date('Y-m-d', strtotime($user['last_login'])); ?></div>
                                <div class="time-ago">
                                    <?php 
                                    $now = new DateTime();
                                    $login_time = new DateTime($user['last_login']);
                                    $interval = $now->diff($login_time);
                                    
                                    if ($interval->days > 0) {
                                        echo "منذ " . $interval->days . " يوم";
                                    } elseif ($interval->h > 0) {
                                        echo "منذ " . $interval->h . " ساعة";
                                    } else {
                                        echo "منذ " . $interval->i . " دقيقة";
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['last_device_login']): ?>
                                <div style="font-size: 12px; color: #6b7280;">
                                    <?php echo htmlspecialchars($user['last_device_login']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // تحضير بيانات الأجهزة
        const deviceData = {
            labels: [<?php echo implode(',', array_map(function($item) { return "'" . $item['device_type'] . "'"; }, $device_stats)); ?>],
            datasets: [{
                data: [<?php echo implode(',', array_map(function($item) { return $item['count']; }, $device_stats)); ?>],
                backgroundColor: [
                    '#059669', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6',
                    '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        };
        
        // تحضير بيانات أنظمة التشغيل
        const osLabels = [<?php echo implode(',', array_map(function($key) { return "'" . $key . "'"; }, array_keys($os_stats))); ?>];
        const osData = [<?php echo implode(',', array_values($os_stats)); ?>];
        
        const osColors = {
            'Android': '#3ddc84',
            'iOS': '#a2aaad',
            'Windows': '#0078d7',
            'Mac OS': '#999999',
            'Linux': '#fbbf24',
            'Chrome OS': '#ea4335',
            'Unknown': '#9ca3af'
        };
        
        const osChartColors = osLabels.map(label => osColors[label] || '#9ca3af');
        
        // إنشاء مخطط الأجهزة
        const devicesCtx = document.getElementById('devicesChart').getContext('2d');
        const devicesChart = new Chart(devicesCtx, {
            type: 'doughnut',
            data: deviceData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        rtl: true,
                        labels: {
                            font: {
                                family: 'Tajawal, Cairo, sans-serif'
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const percentage = context.dataset.data[context.dataIndex] / 
                                    context.dataset.data.reduce((a, b) => a + b, 0) * 100;
                                return `${label}: ${value} مستخدم (${percentage.toFixed(1)}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // إنشاء مخطط أنظمة التشغيل
        const osCtx = document.getElementById('osChart').getContext('2d');
        const osChart = new Chart(osCtx, {
            type: 'bar',
            data: {
                labels: osLabels,
                datasets: [{
                    label: 'عدد المستخدمين',
                    data: osData,
                    backgroundColor: osChartColors,
                    borderColor: osChartColors.map(color => color.replace('0.8', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Tajawal, Cairo, sans-serif'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        rtl: true,
                        callbacks: {
                            label: function(context) {
                                return `المستخدمين: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>