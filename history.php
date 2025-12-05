<?php
session_start();
require_once 'includes/config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// تحديد SQL بناء على الفلتر
switch ($filter) {
    case 'missed':
        $prayer_sql = "WHERE user_id = :user_id AND status = 'not_prayed'";
        break;
    case 'mosque':
        $prayer_sql = "WHERE user_id = :user_id AND status = 'prayed_in_mosque'";
        break;
    case 'all':
    default:
        $prayer_sql = "WHERE user_id = :user_id";
        break;
}

// استعلام الصلوات
$stmt = $pdo->prepare("
    SELECT * FROM prayer_records 
    $prayer_sql 
    ORDER BY date DESC, prayer_name ASC
");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$prayers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// استعلام النوافل
$nawafil_stmt = $pdo->prepare("
    SELECT * FROM nawafil_records 
    WHERE user_id = :user_id 
    ORDER BY date DESC, prayer_name ASC
");
$nawafil_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$nawafil_stmt->execute();
$nawafil = $nawafil_stmt->fetchAll(PDO::FETCH_ASSOC);

// دالة لتحديد لون الصلاة حسب الحالة
function getStatusColor($status) {
    switch ($status) {
        case 'prayed_in_mosque':
            return '#28a745'; // أخضر
        case 'prayed_alone':
            return '#17a2b8'; // أزرق
        case 'not_prayed':
            return '#dc3545'; // أحمر
        case 'delayed':
            return '#ffc107'; // أصفر
        default:
            return '#6c757d'; // رمادي
    }
}

// دالة لتحديد نص الحالة بالعربية
function getStatusText($status) {
    switch ($status) {
        case 'prayed_in_mosque':
            return 'صلاة في المسجد';
        case 'prayed_alone':
            return 'صلاة منفردة';
        case 'not_prayed':
            return 'صلاة فائتة';
        case 'delayed':
            return 'صلاة متأخرة';
        default:
            return $status;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجل الصلوات والنوافل</title>
    <link rel="stylesheet" href="assets/css/history.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>سجل الصلوات والنوافل</h1>
            <div class="filter-buttons">
                <a href="?filter=all" class="btn <?php echo $filter == 'all' ? 'active' : ''; ?>">الكل</a>
                <a href="?filter=missed" class="btn <?php echo $filter == 'missed' ? 'active' : ''; ?>">صلوات فائتة</a>
                <a href="?filter=mosque" class="btn <?php echo $filter == 'mosque' ? 'active' : ''; ?>">صلوات في المسجد</a>
            </div>
        </header>

        <main>
            <!-- قسم الصلوات -->
            <section class="prayers-section">
                <h2>سجل الصلوات</h2>
                <?php if (empty($prayers)): ?>
                    <div class="no-data">لا توجد صلوات في هذا القسم</div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>الصلاة</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>وقت التسجيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prayers as $prayer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prayer['prayer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($prayer['date']); ?></td>
                                    <td>
                                        <span class="status-badge" style="background-color: <?php echo getStatusColor($prayer['status']); ?>">
                                            <?php echo getStatusText($prayer['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($prayer['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- قسم النوافل -->
            <section class="nawafil-section">
                <h2>سجل النوافل</h2>
                <?php if (empty($nawafil)): ?>
                    <div class="no-data">لا توجد نوافل مسجلة</div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>الصلاة</th>
                                    <th>نوع النافلة</th>
                                    <th>عدد الركعات</th>
                                    <th>التاريخ</th>
                                    <th>وقت التسجيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($nawafil as $n): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($n['prayer_name']); ?></td>
                                    <td>
                                        <?php 
                                        $type_text = ($n['nawafil_type'] == 'before') ? 'قبلية' : 'بعدية';
                                        echo $type_text;
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($n['rakat_count']); ?></td>
                                    <td><?php echo htmlspecialchars($n['date']); ?></td>
                                    <td><?php echo htmlspecialchars($n['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <footer>
            <a href="dashboard.php" class="btn back-btn">العودة للرئيسية</a>
        </footer>
    </div>

    <script src="assets/js/history.js"></script>
</body>
</html>