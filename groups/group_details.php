<?php
session_start();
require_once '../includes/config.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// التحقق من وجود معرف المجموعة
if (!isset($_GET['id'])) {
    header('Location: ../groups.php');
    exit();
}

$group_id = intval($_GET['id']);

// جلب معلومات المجموعة والتحقق من العضوية
try {
    $stmt = $pdo->prepare("SELECT g.*, gm.role, u.name as creator_name 
                          FROM groups g 
                          JOIN group_members gm ON g.id = gm.group_id 
                          JOIN users u ON g.created_by = u.id 
                          WHERE g.id = ? AND gm.user_id = ?");
    $stmt->execute([$group_id, $user_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        $_SESSION['error_message'] = "لا يمكنك الوصول إلى هذه المجموعة";
        header('Location: ../groups.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "حدث خطأ أثناء تحميل بيانات المجموعة";
    header('Location: ../groups.php');
    exit();
}

// جلب أعضاء المجموعة
try {
    $stmt = $pdo->prepare("SELECT u.id, u.name, u.profile_picture, u.email, gm.role, 
                          DATE_FORMAT(gm.joined_at, '%Y-%m-%d %H:%i') as joined_at 
                          FROM group_members gm 
                          JOIN users u ON gm.user_id = u.id 
                          WHERE gm.group_id = ? 
                          ORDER BY gm.role DESC, gm.joined_at ASC");
    $stmt->execute([$group_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "حدث خطأ أثناء تحميل الأعضاء";
}

// معالجة مغادرة المجموعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_group'])) {
    try {
        // التحقق من أن المستخدم ليس المدير الوحيد
        $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM group_members 
                              WHERE group_id = ? AND role = 'admin'");
        $stmt->execute([$group_id]);
        $admin_count = $stmt->fetch(PDO::FETCH_ASSOC)['admin_count'];
        
        if ($group['role'] === 'admin' && $admin_count <= 1) {
            $_SESSION['error_message'] = "لا يمكنك مغادرة المجموعة لأنك المدير الوحيد. يجب تعيين مدير آخر أولاً.";
        } else {
            // مغادرة المجموعة
            $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group_id, $user_id]);
            
            $_SESSION['success_message'] = "تم مغادرة المجموعة بنجاح!";
            header('Location: ../groups.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ أثناء مغادرة المجموعة";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($group['group_name']); ?> - تفاصيل المجموعة</title>
    
    <!-- Font Awesome for icons -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4e54c8;
            --secondary-color: #8f94fb;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Tajawal", sans-serif;

        }
        
        body {
            font-family: "Tajawal", sans-serif;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 100%;
            padding: 10px;
        }
        
        /* رأس المجموعة */
        .group-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 7.5px;
            border-radius: 17px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .back-btn {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 6px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .menu-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: background 0.3s;
        }
        
        .menu-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 60px;
            left: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            width: 200px;
            z-index: 100;
            display: none;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-item i {
            margin-left: 10px;
            width: 20px;
            text-align: center;
        }
        
        .group-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .group-emoji {
            font-size: 2.5rem;
        }
        
        .group-info h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .group-description {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .group-stats {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .stat {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* قائمة الأعضاء */
        .members-section {
            background: white;
            padding: 0px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-header h2 {
            font-size: 1rem;
            color: #333;
        }
        
        .member-count {
            /* background: var(--primary-color); */
            color: #222;
            padding: 4px 12px;
            text-align: center;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .members-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .member-item {
            display: flex;
            align-items: center;
            padding: 5px;
            background: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .member-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .member-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: 15px;
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .member-info {
            flex: 1;
        }
        
        .member-name {
            font-weight: 600;
            color: #333;
        }
        
        .member-email {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .member-meta {
            display: flex;
            gap: 15px;
            font-size: 0.8rem;
            color: #888;
        }
        
        .member-role {
            margin-right: auto;
        }
        
        .role-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .role-admin {
            background: #ffc107;
            color: #000;
        }
        
        .role-member {
            background: #6c757d;
            color: white;
        }
        
        /* الرسائل */
        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s;
        }
        
        .message-success {
            background: #d4edda;
            color: #155724;
            border-right: 4px solid #c3e6cb;
        }
        
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border-right: 4px solid #f5c6cb;
        }
        
        .message i {
            margin-left: 10px;
        }
        
        /* الفورم */
        .form-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .form-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s;
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-header h3 {
            font-size: 1.3rem;
            color: #333;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #666;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s;
        }
        
        .close-btn:hover {
            background: #f5f5f5;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #3f43b3, #7a7ff0);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-full {
            width: 100%;
        }
        
        /* الأنيميشن */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* حالة عدم وجود أعضاء */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ccc;
        }
        
        /* النصوص */
        .text-muted {
            color: #888;
        }
        
        /* التجاوب */
        @media (max-width: 768px) {

            .group-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .group-info h1 {
                font-size: 1.5rem;
            }
            
            .member-item {
                flex-direction: row;
                align-items: center;
                gap: 12px;
            }
            
            .member-avatar {
                margin-left: 0;
            }
            
            .member-meta {
                flex-direction: column;
                gap: 5px;
            }
            
            .member-role {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- رسائل التبليغ -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message message-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="message message-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error_message']; ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- رأس المجموعة مع القائمة -->
        <div class="group-header">
            <div class="header-top">
                <a href="../groups.php" class="back-btn">
                    <i class="fas fa-arrow-right"></i>
                </a>
                
                <div class="menu-container">
                    <button class="menu-btn" id="menuBtn">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="#" class="dropdown-item" id="shareBtn">
                            <i class="fas fa-share-alt"></i>
                            مشاركة المجموعة
                        </a>
                        
                        <?php if ($group['role'] === 'admin'): ?>
                            <a href="group_manage.php?id=<?php echo $group['id']; ?>" class="dropdown-item">
                                <i class="fas fa-cogs"></i>
                                إدارة المجموعة
                            </a>
                        <?php endif; ?>
                        
                        <a href="#" class="dropdown-item" id="leaveBtn">
                            <i class="fas fa-sign-out-alt"></i>
                            مغادرة المجموعة
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="group-title">
                <div class="group-emoji"><?php echo htmlspecialchars($group['emoji']); ?></div>
                <div class="group-info">
                    <h1><?php echo htmlspecialchars($group['group_name']); ?></h1>
                    <p class="group-description"><?php echo htmlspecialchars($group['description']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- قائمة الأعضاء -->
        <div class="members-section">
            <div class="section-header">
                <h2>أعضاء المجموعة</h2>
                <span class="member-count"><i class="bi bi-share-fill"></i> <?php echo count($members); ?> </span>
            </div>
            
            <div class="members-list">
                <?php if (count($members) > 0): ?>
                    <?php foreach ($members as $member): ?>
                        <div class="member-item">
                            <img src="<?php echo $member['profile_picture'] ? htmlspecialchars($member['profile_picture']) : 'https://via.placeholder.com/50'; ?>" 
                                 alt="صورة <?php echo htmlspecialchars($member['name']); ?>" 
                                 class="member-avatar">
                            <div class="member-info">
                                <div class="member-name"><?php echo htmlspecialchars($member['name']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>لا يوجد أعضاء في هذه المجموعة بعد</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- نموذج مشاركة الرابط -->
        <div class="form-container" id="shareForm" style="display: none;">
            <div class="form-box">
                <div class="form-header">
                    <h3><i class="fas fa-share-alt"></i> مشاركة المجموعة</h3>
                    <button class="close-btn" id="closeShareForm">&times;</button>
                </div>
                <p class="text-muted" style="margin-bottom: 20px;">
                    شارك هذا الرابط مع الآخرين للانضمام إلى المجموعة:
                </p>
                <div class="form-group">
                    <input type="text" class="form-control" id="shareLinkInput" 
                           value="<?php echo $group['join_link']; ?>" readonly>
                </div>
                <div class="message" style="background: #e7f3ff; color: #0c5460; margin-bottom: 20px;">
                    <i class="fas fa-info-circle"></i>
                    سيتمكن أي شخص لديه هذا الرابط من الانضمام إلى المجموعة
                </div>
                <button class="btn btn-primary btn-full" onclick="copyShareLink()">
                    <i class="fas fa-copy"></i>
                    نسخ الرابط
                </button>
            </div>
        </div>
        
        <!-- نموذج تأكيد مغادرة المجموعة -->
        <div class="form-container" id="leaveForm" style="display: none;">
            <div class="form-box">
                <div class="form-header">
                    <h3><i class="fas fa-sign-out-alt"></i> مغادرة المجموعة</h3>
                    <button class="close-btn" id="closeLeaveForm">&times;</button>
                </div>
                <p style="margin-bottom: 20px;">
                    هل أنت متأكد من رغبتك في مغادرة مجموعة 
                    <strong><?php echo htmlspecialchars($group['group_name']); ?></strong>؟
                </p>
                
                <?php if ($group['role'] === 'admin'): ?>
                    <?php
                    // جلب عدد المديرين
                    $stmt = $pdo->prepare("SELECT COUNT(*) as admin_count FROM group_members 
                                          WHERE group_id = ? AND role = 'admin'");
                    $stmt->execute([$group_id]);
                    $admin_count = $stmt->fetch(PDO::FETCH_ASSOC)['admin_count'];
                    ?>
                    
                    <?php if ($admin_count <= 1): ?>
                        <div class="message message-error" style="margin-bottom: 20px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            لا يمكنك مغادرة المجموعة لأنك المدير الوحيد. يجب تعيين مدير آخر أولاً.
                        </div>
                        <button class="btn btn-secondary btn-full" id="closeLeaveFormBtn">
                            إغلاق
                        </button>
                    <?php else: ?>
                        <form method="POST" action="" id="leaveGroupForm">
                            <button type="submit" name="leave_group" class="btn btn-danger btn-full">
                                <i class="fas fa-sign-out-alt"></i>
                                نعم، مغادرة المجموعة
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <form method="POST" action="" id="leaveGroupForm">
                        <button type="submit" name="leave_group" class="btn btn-danger btn-full">
                            <i class="fas fa-sign-out-alt"></i>
                            نعم، مغادرة المجموعة
                        </button>
                    </form>
                <?php endif; ?>
                
                <button class="btn btn-secondary btn-full" style="margin-top: 10px;" id="cancelLeaveBtn">
                    إلغاء
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // التحكم في القائمة المنسدلة
        const menuBtn = document.getElementById('menuBtn');
        const dropdownMenu = document.getElementById('dropdownMenu');
        
        menuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        
        // إغلاق القائمة عند النقر خارجها
        document.addEventListener('click', function() {
            dropdownMenu.classList.remove('show');
        });
        
        // منع إغلاق القائمة عند النقر داخلها
        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // التحكم في نموذج المشاركة
        const shareBtn = document.getElementById('shareBtn');
        const shareForm = document.getElementById('shareForm');
        const closeShareForm = document.getElementById('closeShareForm');
        
        shareBtn.addEventListener('click', function(e) {
            e.preventDefault();
            dropdownMenu.classList.remove('show');
            shareForm.style.display = 'flex';
        });
        
        closeShareForm.addEventListener('click', function() {
            shareForm.style.display = 'none';
        });
        
        // التحكم في نموذج المغادرة
        const leaveBtn = document.getElementById('leaveBtn');
        const leaveForm = document.getElementById('leaveForm');
        const closeLeaveForm = document.getElementById('closeLeaveForm');
        const closeLeaveFormBtn = document.getElementById('closeLeaveFormBtn');
        const cancelLeaveBtn = document.getElementById('cancelLeaveBtn');
        
        leaveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            dropdownMenu.classList.remove('show');
            leaveForm.style.display = 'flex';
        });
        
        closeLeaveForm.addEventListener('click', function() {
            leaveForm.style.display = 'none';
        });
        
        if (closeLeaveFormBtn) {
            closeLeaveFormBtn.addEventListener('click', function() {
                leaveForm.style.display = 'none';
            });
        }
        
        if (cancelLeaveBtn) {
            cancelLeaveBtn.addEventListener('click', function() {
                leaveForm.style.display = 'none';
            });
        }
        
        // نسخ رابط المشاركة
        function copyShareLink() {
            const linkInput = document.getElementById('shareLinkInput');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999);
            
            try {
                navigator.clipboard.writeText(linkInput.value).then(() => {
                    const copyBtn = document.querySelector('#shareForm .btn-primary');
                    const originalHTML = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check"></i> تم النسخ!';
                    copyBtn.style.background = '#28a745';
                    
                    setTimeout(() => {
                        copyBtn.innerHTML = originalHTML;
                        copyBtn.style.background = '';
                    }, 2000);
                });
            } catch (err) {
                document.execCommand('copy');
                alert('تم نسخ الرابط إلى الحافظة');
            }
        }
        
        // إغلاق النماذج عند النقر خارجها
        document.addEventListener('click', function(e) {
            if (e.target === shareForm) {
                shareForm.style.display = 'none';
            }
            if (e.target === leaveForm) {
                leaveForm.style.display = 'none';
            }
        });
    </script>
</body>
</html>