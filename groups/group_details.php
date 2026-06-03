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
    <link rel="stylesheet" href="../assets/css/group_details.css">
    
    
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
            
            <div class="group-stats">
                <div class="stat">
                    <i class="fas fa-users"></i>
                    <span><?php echo count($members); ?> عضو</span>
                </div>
                <?php if ($group['role'] === 'admin'): ?>
                    <div class="stat">
                        <i class="fas fa-crown"></i>
                        <span>مدير المجموعة</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- قائمة الأعضاء -->
        <div class="members-section">
            <div class="section-header">
                <h2>أعضاء المجموعة</h2>
                <span class="member-count"><i class="fas fa-users"></i> <?php echo count($members); ?> عضو</span>
            </div>
            
            <div class="members-list">
                <?php if (count($members) > 0): ?>
                    <?php foreach ($members as $member): ?>
                        <div class="member-item">
                            <img src="<?php echo $member['profile_picture'] ? htmlspecialchars($member['profile_picture']) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=059669&color=fff&size=50'; ?>" 
                                 alt="صورة <?php echo htmlspecialchars($member['name']); ?>" 
                                 class="member-avatar">
                            <div class="member-info">
                                <div class="member-name"><?php echo htmlspecialchars($member['name']); ?></div>
                                <div class="member-email"><?php echo htmlspecialchars($member['email']); ?></div>
                                <div class="member-meta">
                                    <div class="member-role">
                                        <span class="role-badge <?php echo $member['role'] === 'admin' ? 'role-admin' : 'role-member'; ?>">
                                            <?php echo $member['role'] === 'admin' ? 'مدير' : 'عضو'; ?>
                                        </span>
                                    </div>
                                    <div class="joined-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?php echo $member['joined_at']; ?>
                                    </div>
                                </div>
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
                <div class="message" style="background: #e0f2fe; color: #075985; margin-bottom: 20px; border-right-color: #38bdf8;">
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
                <p style="margin-bottom: 20px; color: var(--text-color);">
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
                    copyBtn.style.background = '#059669';
                    
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
        
        // تحسين تجربة المستخدم
        document.addEventListener('DOMContentLoaded', function() {
            // إضافة تأثير للبطاقات
            const memberItems = document.querySelectorAll('.member-item');
            memberItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>