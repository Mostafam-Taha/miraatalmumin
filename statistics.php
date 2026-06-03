<?php
session_start();
require_once 'includes/config.php'; // ملف الاتصال بقاعدة البيانات

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
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/statistics.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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
                    <h2>الاحصائيات</h2>
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
                <a href="statistics.php" class="item-link active">للوحة التحكم</a>
            </div>
            <div class="switch-item">
                <a href="worship.php" class="item-link">عبادات</a>
            </div>
            <div class="switch-item">
                <a href="statiy.php" class="item-link">احصائيات</a>
            </div>
            <div class="switch-item">
                <a href="groups.php" class="item-link">مجموعة</a>
            </div>
        </div>
    </section>
    <!--  -->
    <!--  -->
    <!--  -->
    <section class="bourd">
        <div class="content-if">
            <div class="welcome-back-user">
                <h1>السلام عليكم <?php echo htmlspecialchars($_SESSION['username'] ?? 'مستخدم'); ?>!</h1>
                <hr>
                <div class="al-ebadat" id="al-ebadat">
                    <div class="sei-abady">
                        <h4>أهداف اليوم</h4>
                        <a href="#">إظهار الكل</a>
                    </div>
                    <div class="view-cat">
                        <div class="cat-item">
                            <div class="cat-item-icon">
                                <i class="bi bi-calendar2-check"></i>
                            </div>
                            <div class="cat-item-text">
                                <h3>قراءة قرآن الكريم</h3>
                                <p>قرأءة 10 صفحات</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  -->
                <!--  -->
                <div class="Short-groubs">
                    <div class="sei-abady">
                        <h4>مجموعاتي</h4>
                        <a href="groups.php">إظهار الكل</a>
                    </div>
                    <?php
                    $user_id = $_SESSION['user_id'] ?? null;

                    if ($user_id) {
                        // استعلام لجلب آخر 3 مجموعات للمستخدم مرتبة من الأحدث إلى الأقدم
                        $stmt = $pdo->prepare("
                            SELECT g.id, g.group_name, g.emoji, g.description 
                            FROM `groups` g
                            INNER JOIN `group_members` gm ON g.id = gm.group_id
                            WHERE gm.user_id = :user_id
                            ORDER BY gm.joined_at DESC 
                            LIMIT 3
                        ");
                        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $groups = [];
                    }
                    ?>

                    <div class="cat-groubs-list">
                        <?php if (count($groups) > 0): ?>
                            <?php foreach ($groups as $group): ?>
                                <div class="cat-groubs" 
                                    onclick="window.location.href='groups/group_details.php?id=<?php echo $group['id']; ?>'"
                                    style="cursor: pointer;">
                                    
                                    <h2 style="margin: 0;">
                                        <?php echo htmlspecialchars($group['emoji'] . ' ' . $group['group_name']); ?>
                                    </h2>
                                    
                                    <?php if (!empty($group['description'])): ?>
                                        <p><?php echo htmlspecialchars($group['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- عرض أيقونة ونص عندما لا توجد مجموعات -->
                            <div class="no-groups-message" style="text-align: center; padding: 20px 10px; color: #666;">
                                <div style="font-size: 35px; margin-bottom: 10px;">
                                    👥 <!-- أو استخدم أي أيقونة أخرى -->
                                </div>
                                <h3 style="margin: 0 0 5px 0; color: #444; font-size: 15.5px;">لا توجد مجموعات</h3>
                                <p style="margin: 0; font-size: 12px; line-height: 1.6;">
                                    لم تنضم إلى أي مجموعات بعد<br>
                                    <a href="groups.php" style="color: #007bff; text-decoration: none;">
                                        أنشئ مجموعتك الأولى
                                    </a> 
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!--  -->
                <!--  -->
                <?php

                $user_id = $_SESSION['user_id'] ?? 0; // تأكد من تعيين هذا بشكل صحيح في نظامك

                if ($user_id == 0) {
                    die("يرجى تسجيل الدخول أولاً");
                }

                $total_sql = "SELECT COUNT(*) as total FROM prayer_records WHERE user_id = :user_id";
                $total_stmt = $pdo->prepare($total_sql);
                $total_stmt->execute([':user_id' => $user_id]);
                $total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
                $total_prayers = $total_result['total'];

                if ($total_prayers == 0) {
                    $mosque_percentage = 0;
                    $delayed_percentage = 0;
                    $alone_percentage = 0;
                    $not_prayed_percentage = 0;
                } else {

                    $mosque_sql = "SELECT COUNT(*) as count FROM prayer_records 
                                WHERE user_id = :user_id AND status = 'prayed_in_mosque'";
                    $mosque_stmt = $pdo->prepare($mosque_sql);
                    $mosque_stmt->execute([':user_id' => $user_id]);
                    $mosque_result = $mosque_stmt->fetch(PDO::FETCH_ASSOC);
                    $mosque_percentage = round(($mosque_result['count'] / $total_prayers) * 100);
                    
                    $delayed_sql = "SELECT COUNT(*) as count FROM prayer_records 
                                    WHERE user_id = :user_id AND status = 'delayed'";
                    $delayed_stmt = $pdo->prepare($delayed_sql);
                    $delayed_stmt->execute([':user_id' => $user_id]);
                    $delayed_result = $delayed_stmt->fetch(PDO::FETCH_ASSOC);
                    $delayed_percentage = round(($delayed_result['count'] / $total_prayers) * 100);
                    
                    $alone_sql = "SELECT COUNT(*) as count FROM prayer_records 
                                WHERE user_id = :user_id AND status = 'prayed_alone'";
                    $alone_stmt = $pdo->prepare($alone_sql);
                    $alone_stmt->execute([':user_id' => $user_id]);
                    $alone_result = $alone_stmt->fetch(PDO::FETCH_ASSOC);
                    $alone_percentage = round(($alone_result['count'] / $total_prayers) * 100);
                    
                    $not_prayed_sql = "SELECT COUNT(*) as count FROM prayer_records 
                                    WHERE user_id = :user_id AND status = 'not_prayed'";
                    $not_prayed_stmt = $pdo->prepare($not_prayed_sql);
                    $not_prayed_stmt->execute([':user_id' => $user_id]);
                    $not_prayed_result = $not_prayed_stmt->fetch(PDO::FETCH_ASSOC);
                    $not_prayed_percentage = round(($not_prayed_result['count'] / $total_prayers) * 100);
                }
                ?>

                <div class="sei-abady">
                    <h4>التقدم</h4>
                </div>
                <div class="tqadm">
                    <div class="item-one">
                        <a href="#al-ebadat">
                            <div class="icon">
                                <i class="bi bi-person-walking"></i>
                            </div>
                            <div class="done-item-one">
                                <p>الصلوات فى المسجد</p>
                                <span><?php echo $mosque_percentage; ?>%</span>
                            </div>
                        </a>
                    </div>
                    <div class="item-one">
                        <a href="#">
                            <div class="icon">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="done-item-one">
                                <p>الصلوات المتأخر</p>
                                <span><?php echo $delayed_percentage; ?>%</span>
                            </div>
                        </a>
                    </div>
                    <div class="item-one">
                        <a href="#al-ebadat">
                            <div class="icon">
                                <i class="bi bi-person-standing"></i>
                            </div>
                            <div class="done-item-one">
                                <p>الصلاة منفردًا</p>
                                <span><?php echo $alone_percentage; ?>%</span>
                            </div>
                        </a>
                    </div>
                    <div class="item-one">
                        <a href="prayer_plans.php">
                            <div class="icon">
                                <i class="bi bi-x-lg"></i>
                            </div>
                            <div class="done-item-one">
                                <p>الصلوات اللتي لم تصليها بعد</p>
                                <span><?php echo $not_prayed_percentage; ?>%</span>
                            </div>
                        </a>
                    </div>
                </div>
                <!--  -->
                <!--  -->
                <div class="history">
                    <div class="sei-abady">
                        <h4>نشاط</h4>
                        <!-- <a href="history.php">إظهار الكل</a> -->
                    </div>
                    <?php
                    $user_id = $_SESSION['user_id'] ?? 0;

                    if ($user_id == 0) {
                        die("يرجى تسجيل الدخول أولاً");
                    }

                    // استعلام لجلب آخر صلاة تمت صلاتها للمستخدم
                    $sql = "SELECT prayer_name, status, date, created_at 
                            FROM prayer_records 
                            WHERE user_id = :user_id 
                            AND status IN ('prayed_in_mosque', 'prayed_alone', 'delayed')
                            ORDER BY created_at DESC 
                            LIMIT 1";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':user_id' => $user_id]);
                    $last_prayer = $stmt->fetch(PDO::FETCH_ASSOC);

                    // إذا لم توجد صلاة
                    if (!$last_prayer) {
                        echo '<div class="hi-card">
                                <div class="item-card">
                                    <i class="bi bi-emoji-frown" style="color: #6c757d;"></i>
                                    <div class="hi-ti">
                                        <p>لا توجد صلاة مسجلة بعد</p>
                                        <span>ابدأ بتسجيل صلواتك الأولى</span>
                                    </div>
                                </div>
                            </div>';
                    } else {
                        // تحديد الرمز والنص واللون حسب حالة الصلاة
                        $icon = '';
                        $status_text = '';
                        $color = '';
                        
                        switch ($last_prayer['status']) {
                            case 'prayed_in_mosque':
                                $icon = 'bi-building';
                                $status_text = 'في المسجد';
                                $color = '#28a745'; // أخضر
                                break;
                            case 'prayed_alone':
                                $icon = 'bi-person-standing';
                                $status_text = 'منفرد';
                                $color = '#17a2b8'; // أزرق
                                break;
                            case 'delayed':
                                $icon = 'bi-clock-history';
                                $status_text = 'متأخر';
                                $color = '#ffc107'; // أصفر/برتقالي
                                break;
                            default:
                                $icon = 'bi-check-lg';
                                $status_text = 'تمت';
                                $color = '#6c757d'; // رمادي
                        }
                        
                        // تنسيق التاريخ
                        $date_formatted = date('Y-m-d', strtotime($last_prayer['date']));
                        
                        echo '<div class="hi-card">
                                <div class="item-card">
                                    <i class="bi ' . $icon . '" style="color: ' . $color . ';"></i>
                                    <div class="hi-ti">
                                        <p>صلاة ' . htmlspecialchars($last_prayer['prayer_name']) . '</p>
                                        <span>' . $status_text . ' - تاريخ: ' . $date_formatted . '</span>
                                    </div>
                                </div>
                            </div>';
                    }
                    ?>
                </div>
                <!--  -->
                <!--  -->
                <div class="skills">
                    <div class="sei-abady">
                        <h4>مهارات</h4>
                        <a href="#">إظهار الكل</a>
                    </div>
                    <div class="totle-skills">
                        <div class="bi bi-rocket-takeoff"></div>
                        <div class="bi bi-rocket-takeoff"></div>
                        <div class="bi bi-rocket-takeoff"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
        <nav class="nav-foot">
            <ul class="list-foot">
                <li class="item-foot"><a href="index.php" class="bi bi-house-fill"></a></li>
                <li class="item-foot"><a href="statistics.php" class="bi bi-bar-chart-fill active"></a></li>
                <li class="item-foot"><a href="reminder.php" class="bi bi-bell-fill"></a></li>
                <li class="item-foot"><a href="profile.php" class="bi bi-person-fill"></a></li>
            </ul>
        </nav>
    </footer>
</body>
</html>