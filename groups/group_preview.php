<?php
// group_preview.php
session_start();
require_once '../includes/config.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$group = null;
$is_member = false;
$member_count = 0;
$block_groups_enabled = false;

// التحقق من تفعيل ميزة "عدم الدخول في أي مجموعة"
try {
    $stmt = $pdo->prepare("SELECT block_all_groups FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($settings && $settings['block_all_groups'] == 1) {
        $block_groups_enabled = true;
    }
} catch (PDOException $e) {
    // تجاهل الخطأ
}

// الحصول على رمز المجموعة من GET أو من الجلسة
$join_code = $_GET['code'] ?? ($_SESSION['pending_join_code'] ?? '');

if ($join_code) {
    try {
        // البحث عن المجموعة باستخدام الكود
        $stmt = $pdo->prepare("SELECT * FROM `groups` WHERE join_code = ?");
        $stmt->execute([$join_code]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($group) {
            // التحقق من انضمام المستخدم الحالي
            $stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group['id'], $user_id]);
            $is_member = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
            
            // حساب عدد الأعضاء
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM group_members WHERE group_id = ?");
            $stmt->execute([$group['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $member_count = $result['count'];
        }
    } catch (PDOException $e) {
        $error = "حدث خطأ في تحميل بيانات المجموعة";
    }
}

// تنظيف الجلسة بعد استخدامها
if (isset($_SESSION['pending_join_code'])) {
    unset($_SESSION['pending_join_code']);
}

if (!$group && $join_code) {
    header('Location: ../groups.php?error=invalid_code');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاينة المجموعة - <?php echo htmlspecialchars($group['group_name'] ?? 'مجموعة'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #059669;
            --primary-light: #d1fae5;
            --primary-dark: #047857;
            --text-color: #111827;
            --text-light: #6b7280;
            --bg-color: #047857;
            --light-bg: #f9fafb;
            --border-color: #e5e7eb;
            --hover-color: #047857;
            --danger-color: #dc2626;
            --danger-light: #fee2e2;
            --warning-color: #d97706;
            --warning-light: #fef3c7;
            --info-color: #3b82f6;
            --info-light: #dbeafe;
            --success-color: #10b981;
            --success-light: #d1fae5;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--text-color);
            line-height: 1.6;
            font-family: 'Tajawal', 'Cairo', sans-serif;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 500px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .preview-card {
            background-color: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .preview-card:hover {
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .group-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .group-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            opacity: 0.5;
        }

        .group-emoji {
            font-size: 64px;
            margin-bottom: 20px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .group-name {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
            font-family: 'Cairo', sans-serif;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .group-type {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: 500;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        /* رسالة الميزة المفعلة */
        .feature-info {
            background: linear-gradient(135deg, var(--warning-light) 0%, #fde68a 100%);
            padding: 25px;
            border-right: 4px solid var(--warning-color);
            margin: 0;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .feature-info-content {
            max-width: 100%;
            margin: 0 auto;
        }

        .feature-icon {
            font-size: 42px;
            color: var(--warning-color);
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .feature-title {
            color: #92400e;
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 10px;
            font-family: 'Cairo', sans-serif;
        }

        .feature-text {
            color: #92400e;
            margin-bottom: 12px;
            line-height: 1.6;
            font-size: 15px;
            font-weight: 400;
        }

        .feature-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .group-info {
            padding: 35px;
        }

        .info-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .info-section:last-child {
            border-bottom: none;
        }

        .info-section h3 {
            color: var(--primary-color);
            font-size: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-family: 'Cairo', sans-serif;
        }

        .info-section p {
            color: var(--text-color);
            font-size: 16px;
            line-height: 1.7;
            padding-right: 10px;
            font-weight: 400;
        }

        .stats {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            padding: 25px;
            background: linear-gradient(135deg, var(--light-bg) 0%, #f0f4f8 100%);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary-color);
            display: block;
            font-family: 'Cairo', sans-serif;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 35px;
        }

        .btn {
            flex: 1;
            padding: 18px 25px;
            border-radius: var(--radius-md);
            font-weight: 700;
            cursor: pointer;
            border: none;
            font-size: 16px;
            transition: var(--transition);
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: 'Tajawal', sans-serif;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255,255,255,0.1), transparent);
            opacity: 0;
            transition: var(--transition);
        }

        .btn:hover::before {
            opacity: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #b45309 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(217, 119, 6, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info-color) 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .join-disabled {
            position: relative;
            opacity: 0.7;
            overflow: hidden;
            border-radius: var(--radius-md);
        }

        .join-disabled::after {
            content: "⛔";
            position: absolute;
            top: 50%;
            right: 50%;
            transform: translate(50%, -50%);
            font-size: 28px;
            background: rgba(0,0,0,0.8);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
            border: 2px solid white;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            z-index: 2;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: white;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .back-link:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            text-decoration: none;
        }

        .error-message {
            background: linear-gradient(135deg, var(--danger-light) 0%, #fecaca 100%);
            border-right: 4px solid var(--danger-color);
            color: var(--danger-color);
            padding: 25px;
            border-radius: var(--radius-md);
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .success-message {
            background: linear-gradient(135deg, var(--success-light) 0%, #a7f3d0 100%);
            border-right: 4px solid var(--success-color);
            color: var(--primary-dark);
            padding: 25px;
            border-radius: var(--radius-md);
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .settings-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .settings-link a {
            color: var(--warning-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            padding: 10px 20px;
            background: var(--warning-light);
            border-radius: 50px;
            border: 1px solid rgba(217, 119, 6, 0.2);
            transition: var(--transition);
        }

        .settings-link a:hover {
            background: var(--warning-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-left: 10px;
        }

        .badge-success {
            background: var(--success-light);
            color: var(--primary-dark);
        }

        .badge-warning {
            background: var(--warning-light);
            color: #92400e;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .group-header {
                padding: 30px 20px;
            }
            
            .group-info {
                padding: 25px 20px;
            }
            
            .group-name {
                font-size: 24px;
            }
            
            .stats {
                padding: 20px 15px;
            }
            
            .stat-value {
                font-size: 24px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .feature-actions {
                flex-direction: column;
            }
            
            .feature-actions .btn,
            .action-buttons .btn {
                width: 100%;
            }
            
            .btn {
                padding: 16px 20px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .group-emoji {
                width: 80px;
                height: 80px;
                font-size: 48px;
            }
            
            .group-name {
                font-size: 22px;
            }
            
            .info-section h3 {
                font-size: 15px;
            }
            
            .info-section p {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <a href="../groups.php" class="back-link">
                <i class="fas fa-arrow-right"></i>
                العودة إلى المجموعات
            </a>
        <?php elseif ($group): ?>
            <div class="preview-card">
                <!-- رسالة الميزة المفعلة -->
                <?php if ($block_groups_enabled && !$is_member): ?>
                    <div class="feature-info">
                        <div class="feature-info-content">
                            <div class="feature-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h3 class="feature-title">خاصية الحماية مفعلة</h3>
                            <p class="feature-text">
                                <i class="fas fa-female"></i>
                                تم تفعيل خاصية "عدم الدخول في أي مجموعة" لحماية خصوصيتك.
                            </p>
                            <p class="feature-text">
                                يمكنك عرض معلومات المجموعة ولكن لا يمكنك الانضمام إليها حالياً.
                            </p>
                            
                            <div class="feature-actions">
                                <a href="../settings.php?tab=group" class="btn btn-warning">
                                    <i class="fas fa-cog"></i> 
                                    <span>تغيير الإعدادات</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="group-header">
                    <div class="group-emoji"><?php echo htmlspecialchars($group['emoji'] ?: '👥'); ?></div>
                    <h1 class="group-name"><?php echo htmlspecialchars($group['group_name']); ?></h1>
                    <div class="group-type">
                        <?php echo $group['group_type'] == 'public' ? 'مجموعة عامة' : 'مجموعة خاصة'; ?>
                        <?php if ($is_member): ?>
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle"></i>
                                عضو
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="group-info">
                    <?php if ($group['description']): ?>
                        <div class="info-section">
                            <h3>
                                <i class="fas fa-align-left" style="color: var(--primary-color);"></i>
                                وصف المجموعة
                            </h3>
                            <p><?php echo nl2br(htmlspecialchars($group['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="info-section">
                        <h3>
                            <i class="fas fa-user-friends" style="color: var(--primary-color);"></i>
                            معلومات المنشئ
                        </h3>
                        <p>تم إنشاء المجموعة بواسطة <strong><?php echo get_creator_name($group['created_by']); ?></strong></p>
                    </div>
                    
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo $member_count; ?></span>
                            <span class="stat-label">عدد الأعضاء</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo date('Y/m/d', strtotime($group['created_at'])); ?></span>
                            <span class="stat-label">تاريخ الإنشاء</span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <?php if ($is_member): ?>
                            <!-- المستخدم عضو بالفعل -->
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-check-circle"></i>
                                <span>أنت عضو بالفعل</span>
                            </button>
                            <a href="../groups/group_details.php?id=<?php echo $group['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-door-open"></i>
                                <span>الانتقال إلى المجموعة</span>
                            </a>
                        <?php else: ?>
                            <!-- المستخدم ليس عضواً -->
                            <?php if ($block_groups_enabled): ?>
                                <!-- الميزة مفعلة: زر الانضمام معطل -->
                                <div class="join-disabled" style="flex: 1;">
                                    <button class="btn btn-primary" disabled style="width: 100%; background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);">
                                        <i class="fas fa-ban"></i>
                                        <span>الانضمام غير متاح</span>
                                    </button>
                                </div>
                                
                                <!-- زر إلغاء تفعيل الميزة -->
                                <a href="../settings.php?tab=group" class="btn btn-info">
                                    <i class="fas fa-user-slash"></i>
                                    <span>إلغاء الحماية</span>
                                </a>
                            <?php else: ?>
                                <!-- الميزة معطلة: نموذج الانضمام العادي -->
                                <form action="join_group.php" method="POST" style="flex: 1;">
                                    <input type="hidden" name="join_code" value="<?php echo htmlspecialchars($group['join_code']); ?>">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-user-plus"></i>
                                        <span>الانضمام إلى المجموعة</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- زر الإلغاء -->
                        <a href="" class="btn btn-secondary"  onclick="history.back()" style="cursor: pointer;">
                            <i class="fas fa-times"></i>
                            <span>إلغاء</span>
                        </a>
                    </div>
                    
                    <?php if ($block_groups_enabled && !$is_member): ?>
                        <div class="settings-link">
                            <p>
                                <a href="../settings.php?tab=group">
                                    <i class="fas fa-info-circle"></i>
                                    <span>لإلغاء خاصية الحماية والانضمام للمجموعة، اضغط هنا</span>
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <a href="../groups.php" class="back-link">
                <i class="fas fa-arrow-right"></i>
                <span>العودة إلى جميع المجموعات</span>
            </a>
        <?php else: ?>
            <div class="error-message">
                <div style="margin-bottom: 15px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 48px;"></i>
                </div>
                <h3 style="margin-bottom: 10px; font-size: 20px;">لم يتم العثور على المجموعة</h3>
                <p style="margin-bottom: 0;">الرمز المدخل غير صالح أو المجموعة غير موجودة</p>
            </div>
            <a href="../groups.php" class="back-link">
                <i class="fas fa-arrow-right"></i>
                <span>العودة إلى المجموعات</span>
            </a>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // تحسين تجربة المستخدم
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // إضافة تأثير الضغط
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });

            // منع النقر على زر الانضمام إذا كانت الميزة مفعلة
            const joinForms = document.querySelectorAll('form[action="join_group.php"]');
            joinForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    <?php if ($block_groups_enabled && !$is_member): ?>
                        e.preventDefault();
                        // عرض رسالة أنيقة
                        const message = document.createElement('div');
                        message.style.cssText = `
                            position: fixed;
                            top: 20px;
                            left: 20px;
                            background: linear-gradient(135deg, var(--warning-light) 0%, #fde68a 100%);
                            color: #92400e;
                            padding: 20px;
                            border-radius: var(--radius-md);
                            border-right: 4px solid var(--warning-color);
                            z-index: 1000;
                            animation: slideIn 0.3s ease;
                            max-width: 400px;
                            box-shadow: var(--shadow-lg);
                        `;
                        message.innerHTML = `
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
                                <h4 style="margin: 0; font-weight: 700;">خاصية الحماية مفعلة</h4>
                            </div>
                            <p style="margin-bottom: 15px;">يجب إلغاء خاصية الحماية أولاً من الإعدادات للانضمام للمجموعة.</p>
                            <button onclick="window.location.href='../settings.php?tab=group'" 
                                    style="background: var(--warning-color); color: white; border: none; padding: 10px 20px; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600;">
                                الذهاب إلى الإعدادات
                            </button>
                        `;
                        document.body.appendChild(message);
                        
                        // إزالة الرسالة بعد 5 ثواني
                        setTimeout(() => {
                            message.style.animation = 'slideOut 0.3s ease';
                            setTimeout(() => {
                                document.body.removeChild(message);
                            }, 300);
                        }, 5000);
                        
                        return false;
                    <?php endif; ?>
                });
            });
            
            // إضافة تأثير عند التمرير على زر الانضمام المعطل
            const disabledButtons = document.querySelectorAll('.btn:disabled');
            disabledButtons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    if (this.disabled) {
                        this.style.cursor = 'not-allowed';
                        this.title = 'خاصية الحماية مفعلة. إذهب إلى الإعدادات لإلغائها.';
                    }
                });
            });
        });

        // إضافة أنيميشن للرسالة
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(-100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(-100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>

<?php
function get_creator_name($creator_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$creator_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ? htmlspecialchars($user['name']) : "مستخدم غير معروف";
    } catch (PDOException $e) {
        return "مستخدم غير معروف";
    }
}
?>