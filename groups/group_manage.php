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

// التحقق من صلاحيات المدير
try {
    $stmt = $pdo->prepare("SELECT g.*, gm.role FROM groups g 
                          JOIN group_members gm ON g.id = gm.group_id 
                          WHERE g.id = ? AND gm.user_id = ? AND gm.role = 'admin'");
    $stmt->execute([$group_id, $user_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        $_SESSION['error_message'] = "ليس لديك صلاحية إدارة هذه المجموعة";
        header('Location: ../groups.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "حدث خطأ أثناء التحقق من الصلاحيات";
    header('Location: ../groups.php');
    exit();
}

// جلب أعضاء المجموعة
try {
    $stmt = $pdo->prepare("SELECT u.id, u.name, u.profile_picture, u.email, gm.role, gm.joined_at 
                          FROM group_members gm 
                          JOIN users u ON gm.user_id = u.id 
                          WHERE gm.group_id = ? 
                          ORDER BY gm.role DESC, gm.joined_at ASC");
    $stmt->execute([$group_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "حدث خطأ أثناء تحميل الأعضاء";
}

// معالجة تحديث معلومات المجموعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_group'])) {
    $group_name = trim($_POST['group_name']);
    $emoji = trim($_POST['emoji']);
    $group_type = $_POST['group_type'];
    $description = trim($_POST['description']);
    
    try {
        $stmt = $pdo->prepare("UPDATE groups SET group_name = ?, emoji = ?, group_type = ?, description = ? WHERE id = ?");
        $stmt->execute([$group_name, $emoji, $group_type, $description, $group_id]);
        
        $_SESSION['success_message'] = "تم تحديث معلومات المجموعة بنجاح!";
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $error_message = "حدث خطأ أثناء تحديث المجموعة: " . $e->getMessage();
    }
}

// معالجة تغيير دور العضو
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $member_id = intval($_POST['member_id']);
    $new_role = $_POST['new_role'];
    
    try {
        // التحقق من أن المستخدم ليس يحاول تغيير دوره الخاص
        if ($member_id == $user_id) {
            $_SESSION['error_message'] = "لا يمكنك تغيير دورك الخاص";
        } else {
            $stmt = $pdo->prepare("UPDATE group_members SET role = ? WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$new_role, $group_id, $member_id]);
            
            $_SESSION['success_message'] = "تم تغيير دور العضو بنجاح!";
        }
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $error_message = "حدث خطأ أثناء تغيير الدور: " . $e->getMessage();
    }
}

// معالجة إزالة عضو
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member'])) {
    $member_id = intval($_POST['member_id']);
    
    try {
        // التحقق من أن المستخدم ليس يحاول إزالة نفسه
        if ($member_id == $user_id) {
            $_SESSION['error_message'] = "لا يمكنك إزالة نفسك من المجموعة. استخدم زر مغادرة المجموعة بدلاً من ذلك.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group_id, $member_id]);
            
            $_SESSION['success_message'] = "تم إزالة العضو من المجموعة بنجاح!";
        }
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $error_message = "حدث خطأ أثناء إزالة العضو: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المجموعة - <?php echo htmlspecialchars($group['group_name']); ?></title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --secondary: #3a0ca3;
            --success: #06d6a0;
            --danger: #ef476f;
            --warning: #ffd166;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f7ff;
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* رأس الصفحة */
        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.1;
        }
        
        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateX(-5px);
        }
        
        .header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .group-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }
        
        .member-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(5px);
        }
        
        /* الكروت */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 25px;
            transition: var(--transition);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-light);
        }
        
        .card-header i {
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .card-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        /* النماذج */
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-light);
            border-radius: 8px;
            font-family: 'Tajawal', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .radio-item input {
            width: 18px;
            height: 18px;
        }
        
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: var(--success);
            color: white;
        }
        
        .badge-info {
            background-color: var(--primary);
            color: white;
        }
        
        textarea.form-input {
            resize: vertical;
            min-height: 100px;
        }
        
        .emoji-preview {
            font-size: 2rem;
            text-align: center;
            margin-top: 10px;
            padding: 10px;
            background: var(--primary-light);
            border-radius: 8px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* الأزرار */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-family: 'Tajawal', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.3);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #d43f5a;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--gray-light);
            color: var(--dark);
        }
        
        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        
        .btn-full {
            width: 100%;
        }
        
        /* قائمة الأعضاء */
        .members-list {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .member-item {
            background: var(--light);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-right: 4px solid var(--primary);
            transition: var(--transition);
        }
        
        .member-item:hover {
            background: var(--gray-light);
        }
        
        .member-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .member-info {
            flex: 1;
        }
        
        .member-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .member-email {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .member-role {
            background: var(--primary-light);
            color: var(--primary);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .member-role.admin {
            background: #fff3cd;
            color: #856404;
        }
        
        .member-actions {
            display: flex;
            gap: 10px;
        }
        
        .role-select {
            padding: 8px 12px;
            border: 1px solid var(--gray-light);
            border-radius: 6px;
            font-family: 'Tajawal', sans-serif;
            font-size: 0.9rem;
            background: white;
        }
        
        /* منطقة الخطر */
        .danger-zone {
            background: white;
            border: 2px solid var(--danger);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-top: 40px;
        }
        
        .danger-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            color: var(--danger);
        }
        
        .danger-header i {
            font-size: 1.5rem;
        }
        
        .danger-header h3 {
            font-size: 1.3rem;
        }
        
        .danger-content p {
            color: var(--gray);
            margin-bottom: 20px;
        }
        
        .danger-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        /* التنبيهات */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background-color: #d1f7e5;
            color: #0c6b4d;
            border-right: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: #fce8e8;
            color: #c81e3d;
            border-right: 4px solid var(--danger);
        }
        
        .close-btn {
            margin-right: auto;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.7;
            transition: var(--transition);
        }
        
        .close-btn:hover {
            opacity: 1;
        }
        
        /* المودال */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            animation: modalAppear 0.3s ease;
        }
        
        @keyframes modalAppear {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 25px 30px;
            background: var(--danger);
            color: white;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .modal-footer {
            padding: 20px 30px;
            background: var(--light);
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        /* تخطيط متجاوب */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }
        
        /* شريط التمرير */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--gray-light);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary);
        }
        
        /* التجاوب */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .card {
                padding: 20px;
            }
            
            .danger-actions {
                grid-template-columns: 1fr;
            }
            
            .member-item {
                flex-direction: column;
                text-align: center;
            }
            
            .member-actions {
                flex-direction: column;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- رأس الصفحة -->
        <div class="header">
            <a href="group_details.php?id=<?php echo $group_id; ?>" class="back-btn">
                <i class="fas fa-arrow-right"></i> العودة للمجموعة
            </a>
            
            <h1><i class="fas fa-cogs"></i> إدارة المجموعة</h1>
            <p><?php echo htmlspecialchars($group['group_name']); ?></p>
            
            <div class="group-info">
                <div class="member-count">
                    <i class="fas fa-users"></i>
                    <?php echo count($members); ?> عضو
                </div>
            </div>
        </div>
        
        <!-- رسائل التبليغ -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $_SESSION['success_message']; ?></span>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $_SESSION['error_message']; ?></span>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error_message; ?></span>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>
        
        <div class="grid">
            <!-- تعديل معلومات المجموعة -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-edit"></i>
                    <h3>تعديل معلومات المجموعة</h3>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="group_name">اسم المجموعة *</label>
                        <input type="text" class="form-input" id="group_name" name="group_name" 
                               value="<?php echo htmlspecialchars($group['group_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="emoji">الرمز التعبيري</label>
                        <input type="text" class="form-input" id="emoji" name="emoji" 
                               value="<?php echo htmlspecialchars($group['emoji']); ?>" maxlength="2">
                        <div class="emoji-preview" id="emojiPreview">
                            <?php echo htmlspecialchars($group['emoji'] ?: '👥'); ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>نوع المجموعة *</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" name="group_type" id="public" 
                                       value="public" <?php echo $group['group_type'] == 'public' ? 'checked' : ''; ?>>
                                <label for="public">
                                    <span class="badge badge-success">عامة للجميع</span>
                                </label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="group_type" id="private" 
                                       value="private" <?php echo $group['group_type'] == 'private' ? 'checked' : ''; ?>>
                                <label for="private">
                                    <span class="badge badge-info">خاصة</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">وصف المجموعة</label>
                        <textarea class="form-input" id="description" name="description" rows="3" 
                                  maxlength="200"><?php echo htmlspecialchars($group['description']); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_group" class="btn btn-primary btn-full">
                        <i class="fas fa-save"></i> حفظ التغييرات
                    </button>
                </form>
            </div>
            
            <!-- إدارة الأعضاء -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users-cog"></i>
                    <h3>إدارة الأعضاء (<?php echo count($members); ?>)</h3>
                </div>
                
                <div class="members-list">
                    <?php if (count($members) > 0): ?>
                        <?php foreach ($members as $member): ?>
                            <div class="member-item">
                                <img src="<?php echo $member['profile_picture'] ? htmlspecialchars($member['profile_picture']) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=4361ee&color=fff'; ?>" 
                                     alt="صورة <?php echo htmlspecialchars($member['name']); ?>" 
                                     class="member-avatar">
                                <div class="member-info">
                                    <div class="member-name"><?php echo htmlspecialchars($member['name']); ?></div>
                                    <div class="member-email"><?php echo htmlspecialchars($member['email']); ?></div>
                                </div>
                                <div class="member-role <?php echo $member['role']; ?>">
                                    <?php echo $member['role'] == 'admin' ? 'مدير' : 'عضو'; ?>
                                </div>
                                
                                <?php if ($member['id'] != $user_id): ?>
                                    <div class="member-actions">
                                        <form method="POST" action="" class="role-form">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <select name="new_role" class="role-select" onchange="this.form.submit()">
                                                <option value="member" <?php echo $member['role'] == 'member' ? 'selected' : ''; ?>>عضو</option>
                                                <option value="admin" <?php echo $member['role'] == 'admin' ? 'selected' : ''; ?>>مدير</option>
                                            </select>
                                            <input type="hidden" name="change_role" value="1">
                                        </form>
                                        
                                        <form method="POST" action="" 
                                              onsubmit="return confirm('هل أنت متأكد من إزالة <?php echo htmlspecialchars(addslashes($member['name'])); ?> من المجموعة؟');">
                                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                            <button type="submit" name="remove_member" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="member-role" style="background: #e2e3e5; color: #383d41;">
                                        أنت
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: var(--gray);">
                            <i class="fas fa-users-slash" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                            <p>لا يوجد أعضاء في هذه المجموعة</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- منطقة الخطر -->
        <div class="danger-zone">
            <div class="danger-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>منطقة الخطر</h3>
            </div>
            <div class="danger-content">
                <p>الإجراءات في هذه المنطقة لا يمكن التراجع عنها</p>
                
                <div class="danger-actions">
                    <button class="btn btn-danger btn-full" onclick="showDeleteModal()">
                        <i class="fas fa-trash-alt"></i> حذف المجموعة نهائياً
                    </button>
                    
                    <form method="POST" action="../groups.php" onsubmit="return confirm('هل أنت متأكد من مغادرة المجموعة؟');">
                        <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                        <button type="submit" name="leave_group" class="btn btn-outline btn-full">
                            <i class="fas fa-sign-out-alt"></i> مغادرة المجموعة
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal حذف المجموعة -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>حذف المجموعة</h3>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>تحذير:</strong> هذا الإجراء لا يمكن التراجع عنه
                </div>
                <p>سيتم حذف المجموعة وجميع بياناتها بما في ذلك:</p>
                <ul style="margin: 15px 0 15px 20px; color: var(--gray);">
                    <li>جميع الأعضاء سيفقدون عضوية المجموعة</li>
                    <li>جميع السجلات والأنشطة المرتبطة بالمجموعة</li>
                    <li>رابط الانضمام لن يعمل بعد الآن</li>
                </ul>
                <p style="color: var(--danger); font-weight: 600;">
                    هل أنت متأكد من حذف المجموعة "<?php echo htmlspecialchars($group['group_name']); ?>"؟
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="hideDeleteModal()">إلغاء</button>
                <form method="POST" action="../api/delete_group.php" style="display: inline;">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> نعم، حذف المجموعة
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // تحديث معاينة الرمز التعبيري
        document.getElementById('emoji').addEventListener('input', function() {
            document.getElementById('emojiPreview').textContent = this.value || '👥';
        });
        
        // إدارة المودال
        function showDeleteModal() {
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }
        
        // إغلاق المودال عند النقر خارجها
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideDeleteModal();
            }
        });
        
        // إغلاق التنبيهات تلقائياً بعد 5 ثوانٍ
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>