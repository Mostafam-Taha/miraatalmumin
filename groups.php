<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// دالة لتحديث المداومة الشخصية (يتم استدعاؤها من صفحة تسجيل الصلوات)
function updateUserPrayerStreak($user_id, $pdo) {
    try {
        // جلب آخر تاريخ مداومة
        $stmt = $pdo->prepare("SELECT last_prayer_date, current_prayer_streak, current_prayer_streak FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $today = date('Y-m-d');
        $current_streak = 0;
        
        if ($user) {
            if ($user['last_prayer_date'] === $today) {
                // تم التسجيل اليوم بالفعل، لا تغيير
                return;
            }
            
            // حساب إذا كان اليوم متتابع مع الأمس
            if ($user['last_prayer_date']) {
                $last_date = new DateTime($user['last_prayer_date']);
                $today_date = new DateTime($today);
                $interval = $last_date->diff($today_date);
                
                if ($interval->days == 1) {
                    // يوم متتابع
                    $current_streak = $user['current_prayer_streak'] + 1;
                } else if ($interval->days == 0) {
                    // نفس اليوم
                    $current_streak = $user['current_prayer_streak'];
                } else {
                    // انقطاع في المداومة
                    $current_streak = 1;
                }
            } else {
                // أول مرة يسجل صلاة
                $current_streak = 1;
            }
            
            // تحديث أقصى مداومة إذا لزم
            $max_streak = max($user['current_prayer_streak'], $current_streak);
            
            // تحديث بيانات المستخدم
            $stmt = $pdo->prepare("UPDATE users SET 
                                  current_prayer_streak = ?, 
                                  current_prayer_streak = ?, 
                                  last_prayer_date = ? 
                                  WHERE id = ?");
            $stmt->execute([$current_streak, $max_streak, $today, $user_id]);
            
            return $current_streak;
        }
        
        return 0;
    } catch (PDOException $e) {
        error_log("Error updating prayer streak: " . $e->getMessage());
        return 0;
    }
}

// دالة للحصول على المداومة الشخصية من جدول المستخدمين
function getUserPrayerConsistency($user_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT current_prayer_streak FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ? $user['current_prayer_streak'] : 0;
    } catch (PDOException $e) {
        error_log("Error getting user consistency: " . $e->getMessage());
        return 0;
    }
}

// دالة لحساب المداومة الشخصية (النسخة القديمة للتوافق)
function calculatePrayerConsistency($user_id, $pdo) {
    // يمكن استخدام الدالة الجديدة مباشرة
    return getUserPrayerConsistency($user_id, $pdo);
}

// دالة لحساب مجموع المداومة للمجموعة (محدثة)
function calculateGroupConsistency($group_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM group_members WHERE group_id = ?");
        $stmt->execute([$group_id]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($members)) {
            return 0;
        }
        
        $total_consistency = 0;
        $member_count = 0;
        
        foreach ($members as $member) {
            // استخدام المداومة المخزنة في جدول المستخدمين
            $consistency = getUserPrayerConsistency($member['user_id'], $pdo);
            $total_consistency += $consistency;
            $member_count++;
        }
        
        // حساب المتوسط (اختياري)
        // return $member_count > 0 ? round($total_consistency / $member_count) : 0;
        
        // أو إرجاع المجموع الكلي
        return $total_consistency;
    } catch (PDOException $e) {
        error_log("Error calculating group consistency: " . $e->getMessage());
        return 0;
    }
}

// دالة للحصول على اسم المجموعة (مع التحقق من الإخفاء)
function getGroupDisplayName($group) {
    if (isset($group['hide_group_name']) && $group['hide_group_name'] == 1) {
        return "مجموعة مخفية";
    }
    return htmlspecialchars($group['group_name']);
}

// دالة للحصول على رمز/صورة المجموعة (مع التحقق من الإخفاء)
function getGroupDisplayImage($group) {
    // إذا كان إخفاء الصورة مفعلاً
    if (isset($group['hide_group_image']) && $group['hide_group_image'] == 1) {
        return [
            'type' => 'emoji',
            'content' => '🙈', // رمز تعبيري للصورة المخفية
            'emoji' => '🙈'
        ];
    }
    
    // إذا كانت هناك صورة للمجموعة
    if (!empty($group['group_image'])) {
        return [
            'type' => 'image',
            'content' => $group['group_image'],
            'emoji' => htmlspecialchars($group['emoji'], ENT_QUOTES, 'UTF-8')
        ];
    }
    
    // إذا لم تكن هناك صورة، عرض الرمز التعبيري
    return [
        'type' => 'emoji',
        'content' => htmlspecialchars($group['emoji'], ENT_QUOTES, 'UTF-8'),
        'emoji' => htmlspecialchars($group['emoji'], ENT_QUOTES, 'UTF-8')
    ];
}

// دالة لعرض صورة المجموعة في HTML
function displayGroupImage($group) {
    $image_data = getGroupDisplayImage($group);
    
    if ($image_data['type'] === 'image') {
        // عرض الصورة الفعلية
        return '<img src="uploads/groups/' . htmlspecialchars($image_data['content']) . '" alt="' . htmlspecialchars($group['group_name']) . '" class="group-image">';
    } else {
        // عرض الرمز التعبيري
        return '<span class="emoji-text">' . $image_data['content'] . '</span>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $group_name = trim($_POST['group_name']);
    $emoji = trim($_POST['emoji']);
    $group_type = $_POST['group_type'];
    $description = trim($_POST['description']);
    
    $join_code = uniqid('group_', true);
    $join_link = SITE_URL . "/groups/group_preview.php?code=" . $join_code;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO groups (group_name, emoji, group_type, description, join_code, join_link, created_by, created_at, user_id) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->execute([$group_name, $emoji, $group_type, $description, $join_code, $join_link, $user_id, $user_id]);
        
        $group_id = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role, joined_at) 
                              VALUES (?, ?, 'admin', NOW())");
        $stmt->execute([$group_id, $user_id]);
        
        $_SESSION['success_message'] = "تم إنشاء المجموعة بنجاح!";
        header('Location: groups.php');
        exit();
    } catch (PDOException $e) {
        $error_message = "حدث خطأ أثناء إنشاء المجموعة: " . $e->getMessage();
    }
}

// جلب مجموعات المستخدم مع جميع الحقول
$user_groups = [];
try {
    $stmt = $pdo->prepare("SELECT g.*, gm.role 
                          FROM groups g 
                          JOIN group_members gm ON g.id = gm.group_id 
                          WHERE gm.user_id = ? 
                          ORDER BY g.created_at DESC");
    $stmt->execute([$user_id]);
    $user_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "حدث خطأ أثناء تحميل المجموعات: " . $e->getMessage();
}

// جلب جميع المجموعات العامة مع حساب المداومة
$public_groups = [];
$total_public_groups = 0;
try {
    // جلب جميع المجموعات العامة لحساب العدد الكلي
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_count FROM groups WHERE group_type = 'public'");
    $stmt->execute();
    $total_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_public_groups = $total_result['total_count'];
    
    // جلب أول 10 مجموعات عامة فقط مع جميع الحقول
    $stmt = $pdo->prepare("SELECT g.*, 
                          (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.id) as member_count 
                          FROM groups g 
                          WHERE g.group_type = 'public' 
                          ORDER BY g.created_at DESC 
                          LIMIT 10");
    $stmt->execute();
    $public_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // حساب المداومة لكل مجموعة عامة
    foreach ($public_groups as &$group) {
        $group['consistency_score'] = calculateGroupConsistency($group['id'], $pdo);
    }
    unset($group);
    
    // ترتيب المجموعات حسب المداومة (من الأعلى إلى الأقل)
    usort($public_groups, function($a, $b) {
        return $b['consistency_score'] - $a['consistency_score'];
    });
    
} catch (PDOException $e) {
    error_log("Error loading public groups: " . $e->getMessage());
}

// جلب إحصائيات المستخدم الحالي
$user_stats = [
    'current_streak' => 0,
    'max_streak' => 0
];
try {
    $stmt = $pdo->prepare("SELECT current_prayer_streak, current_prayer_streak FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error getting user stats: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مجموعاتي - مرآة المؤمن</title>
    <link rel="stylesheet" href="assets/css/groups.css">
    <link rel="stylesheet" href="assets/css/statistics.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        
        
        
        <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RSG9M1LGJD"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-RSG9M1LGJD');
        </script>
        
        
        
        /* إضافة بعض الأنماط للصور المخفية */
        .hidden-group {
            opacity: 0.8;
        }
        .hidden-emoji {
            filter: grayscale(0.5);
            opacity: 0.7;
        }
        
        /* أنماط الصور */
        .group-emoji {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            overflow: hidden;
        }
        
        .group-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
        }
        
        .emoji-text {
            font-size: 24px;
        }
        
        /* تحديد حجم الصور في المجموعات العامة */
        .public-group-item .group-emoji {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }
        
        .public-group-item .group-image {
            border-radius: 8px;
        }
        
        /* معاينة الرمز في المودال */
        .emoji-preview {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-top: 5px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        /* إضافة إحصائيات المداومة الشخصية */
        .consistency-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .stats-title {
            font-size: 16px;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 12px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <main>
        <header>
            <div class="header-content">
                <div class="header-icon">
                    <i class="bi bi-arrow-right-circle" onclick="window.location.href='statistics.php'" style="cursor: pointer;"></i>
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
                <a href="statistics.php" class="item-link">للوحة التحكم</a>
            </div>
            <div class="switch-item">
                <a href="statistics.php" class="item-link">عبادات</a>
            </div>
            <div class="switch-item">
                <a href="statiy.php" class="item-link">احصائيات</a>
            </div>
            <div class="switch-item">
                <a href="groups.php" class="item-link active">مجموعة</a>
            </div>
        </div>
    </section>
    
    <div class="container">
        <!-- رسائل التبليغ -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- المحتوى الرئيسي -->
        <div class="main-container">
            <!-- إحصائيات المداومة الشخصية -->
            <div class="consistency-stats" style="display: none;">
                <div class="stats-title">📊 إحصائيات مداومتك</div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $user_stats['current_streak'] ?? 0; ?></div>
                        <div class="stat-label">المداومة الحالية</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $user_stats['max_streak'] ?? 0; ?></div>
                        <div class="stat-label">أقصى مداومة</div>
                    </div>
                </div>
            </div>
            
            <!-- رأس الصفحة -->
            <div class="page-header">
                <h2>مجموعاتي</h2>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openModal('createGroupModal')">أنشئ مجموعة</button>
                </div>
            </div>
            
            <!-- مجموعاتي -->
            <div class="section" id="my-groups-section">
                <?php if (count($user_groups) > 0): ?>
                    <div class="groups-list">
                        <?php foreach ($user_groups as $group): ?>
                            <?php 
                            $display_name = getGroupDisplayName($group);
                            $is_hidden = (isset($group['hide_group_name']) && $group['hide_group_name'] == 1) || 
                                         (isset($group['hide_group_image']) && $group['hide_group_image'] == 1);
                            ?>
                            
                            <!-- رابط كامل للـ Card -->
                            <a href="groups/group_details.php?id=<?php echo $group['id']; ?>" class="group-card-link">
                                <div class="group-card <?php echo $is_hidden ? 'hidden-group' : ''; ?>">
                                    <div class="group-emoji emoji-display <?php echo (isset($group['hide_group_image']) && $group['hide_group_image'] == 1) ? 'hidden-emoji' : ''; ?>">
                                        <?php echo displayGroupImage($group); ?>
                                    </div>
                                    <div class="group-name"><?php echo $display_name; ?></div>
                                    <?php if ($is_hidden): ?>
                                        <div class="hidden-badge" style="font-size: 10px; color: #999; margin-top: 5px;">
                                            <i class="bi bi-eye-slash"></i> مخفية
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <h4>لا توجد مجموعات بعد</h4>
                        <p>أنشئ مجموعة جديدة للبدء</p>
                        <button class="btn btn-primary" onclick="openModal('createGroupModal')">
                            أنشئ مجموعتك الأولى
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- أول 10 مجموعات عامة -->
            <div class="section" id="public-groups-section">
                <h3 class="section-title">
                    أول 10 مجموعات عامة
                    <span class="section-badge"><?php echo count($public_groups); ?>/<?php echo $total_public_groups; ?></span>
                </h3>
                
                <?php if (count($public_groups) > 0): ?>
                    <div class="public-groups-list">
                        <?php foreach ($public_groups as $index => $group): ?>
                            <?php 
                            $rank = $index + 1;
                            $score = $group['consistency_score'];
                            $display_name = getGroupDisplayName($group);
                            $is_hidden = (isset($group['hide_group_name']) && $group['hide_group_name'] == 1) || 
                                         (isset($group['hide_group_image']) && $group['hide_group_image'] == 1);
                            ?>
                            
                            <!-- عرض المجموعة العامة فقط (لا رابط) -->
                            <div class="public-group-item <?php echo $is_hidden ? 'hidden-group' : ''; ?>">
                                <!-- الجانب الأيسر: الرمز + المداومة -->
                                <div class="group-left">
                                    <div class="group-emoji emoji-display <?php echo (isset($group['hide_group_image']) && $group['hide_group_image'] == 1) ? 'hidden-emoji' : ''; ?>">
                                        <?php echo displayGroupImage($group); ?>
                                    </div>
                                    <div>
                                        <div class="consistency-score"><?php echo number_format($score); ?></div>
                                        <div class="day-label">يوم مداومة</div>
                                    </div>
                                </div>
                                
                                <!-- الجانب الأيمن: الاسم + الترتيب -->
                                <div class="group-right">
                                    <div class="group-info">
                                        <div class="group-name"><?php echo $display_name; ?></div>
                                        <?php if (!empty($group['description']) && (!isset($group['hide_group_name']) || $group['hide_group_name'] == 0)): ?>
                                            <div class="group-description"><?php echo htmlspecialchars($group['description']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($is_hidden): ?>
                                            <div class="hidden-badge" style="font-size: 11px; color: #777; margin-top: 3px;">
                                                <i class="bi bi-eye-slash"></i> إعدادات مخفية
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="rank-badge">#<?php echo $rank; ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- ملاحظة عن إجمالي المجموعات -->
                    <?php if ($total_public_groups > 10): ?>
                        <div style="text-align: center; margin-top: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 6px; font-size: 14px; color: #666;">
                            إجمالي المجموعات العامة: <strong><?php echo $total_public_groups; ?></strong> مجموعة
                            <br>
                            <small>يتم عرض أول 10 مجموعات حسب مجموع مداومة أعضائها</small>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🌍</div>
                        <h4>لا توجد مجموعات عامة بعد</h4>
                        <p>كن أول من ينشئ مجموعة عامة</p>
                        <button class="btn btn-primary" onclick="openModal('createGroupModal')">
                            أنشئ مجموعة عامة
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal إنشاء مجموعة -->
    <div id="createGroupModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>إنشاء مجموعة جديدة</h3>
                <span class="close" onclick="closeModal('createGroupModal')">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>اسم المجموعة *</label>
                        <input type="text" class="form-control" name="group_name" required 
                               placeholder="أدخل اسم المجموعة">
                    </div>
                    <div class="form-group">
                        <label>الرمز التعبيري</label>
                        <input type="text" class="form-control" id="emoji" name="emoji" required 
                               placeholder="😊 👥 🙏" maxlength="2" value="👥">
                        <div class="emoji-preview emoji-display" id="emojiPreview">
                            <span class="emoji-text">👥</span>
                        </div>
                        <small class="form-text">يمكنك استخدام أي رمز تعبيري</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>نوع المجموعة *</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="group_type" value="public">
                            <span class="radio-text">
                                <span class="badge badge-success">عامة</span> - ستظهر في القائمة العامة
                            </span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="group_type" value="private" checked>
                            <span class="radio-text">
                                <span class="badge badge-secondary">خاصة</span> - للمجموعات المغلقة فقط
                            </span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>وصف مختصر عن المجموعة</label>
                    <textarea class="form-control" name="description" rows="3" 
                              placeholder="أدخل وصفًا مختصرًا عن المجموعة وأهدافها" maxlength="200"></textarea>
                    <small class="form-text">الحد الأقصى 200 حرف</small>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal('createGroupModal')">إلغاء</button>
                    <button type="submit" name="create_group" class="btn btn-primary">إنشاء المجموعة</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // دالة لفتح النافذة المنبثقة
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
            }
        }

        // دالة لإغلاق النافذة المنبثقة
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // إغلاق النافذة المنبثقة عند النقر خارجها
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // إغلاق النافذة المنبثقة بمفتاح ESC
        document.onkeydown = function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        }

        // تحديث معاينة الرمز التعبيري
        const emojiInput = document.getElementById('emoji');
        if (emojiInput) {
            emojiInput.addEventListener('input', function() {
                const preview = document.getElementById('emojiPreview');
                if (preview) {
                    const emojiText = preview.querySelector('.emoji-text');
                    if (emojiText) {
                        emojiText.textContent = this.value || '👥';
                    } else {
                        preview.innerHTML = '<span class="emoji-text">' + (this.value || '👥') + '</span>';
                    }
                }
            });
        }
    </script>
</body>
</html>