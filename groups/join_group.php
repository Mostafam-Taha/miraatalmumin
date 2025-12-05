<?php
session_start();
require_once '../includes/config.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// التحقق من ميزة "عدم الدخول في أي مجموعة"
try {
    $stmt = $pdo->prepare("SELECT block_all_groups FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // إذا كانت الميزة مفعلة (1)
    if ($settings && $settings['block_all_groups'] == 1) {
        // تخزين المعلومات في الجلسة لعرضها في صفحة المعاينة
        if (isset($_POST['join_code'])) {
            $join_code = trim($_POST['join_code']);
            $_SESSION['pending_join_code'] = $join_code;
        } elseif (isset($_GET['code'])) {
            $_SESSION['pending_join_code'] = $_GET['code'];
        }
        
        // توجيه دائم إلى صفحة المعاينة (للعرض فقط)
        $redirect_code = $_SESSION['pending_join_code'] ?? '';
        header('Location: group_preview.php?code=' . urlencode($redirect_code));
        exit();
    }
} catch (PDOException $e) {
    // تجاهل الخطأ والمتابعة بشكل طبيعي
}

// إذا لم تكن الميزة مفعلة، المتابعة بالانضمام المباشر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join_code'])) {
    $join_code = trim($_POST['join_code']);
    
    // استخراج الكود من الرابط إذا كان رابط
    if (filter_var($join_code, FILTER_VALIDATE_URL)) {
        $url_parts = parse_url($join_code);
        parse_str($url_parts['query'], $query_params);
        $join_code = $query_params['code'] ?? $join_code;
    }
    
    try {
        // البحث عن المجموعة باستخدام الكود
        $stmt = $pdo->prepare("SELECT * FROM groups WHERE join_code = ?");
        $stmt->execute([$join_code]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($group) {
            // التحقق من عدم انضمام المستخدم مسبقًا
            $stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group['id'], $user_id]);
            $existing_member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing_member) {
                // إضافة المستخدم إلى المجموعة
                $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role, joined_at) 
                                      VALUES (?, ?, 'member', NOW())");
                $stmt->execute([$group['id'], $user_id]);
                
                $_SESSION['success_message'] = "تم الانضمام إلى المجموعة بنجاح!";
            } else {
                $_SESSION['error_message'] = "أنت بالفعل عضو في هذه المجموعة";
            }
        } else {
            $_SESSION['error_message'] = "رمز الانضمام غير صالح أو المجموعة غير موجودة";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ أثناء الانضمام إلى المجموعة: " . $e->getMessage();
    }
    
    header('Location: ../groups.php');
    exit();
}

// إذا كان هناك رمز في رابط GET والميزة معطلة
if (isset($_GET['code'])) {
    $join_code = $_GET['code'];
    
    try {
        // البحث عن المجموعة باستخدام الكود
        $stmt = $pdo->prepare("SELECT * FROM groups WHERE join_code = ?");
        $stmt->execute([$join_code]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($group) {
            // التحقق من عدم انضمام المستخدم مسبقًا
            $stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group['id'], $user_id]);
            $existing_member = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing_member) {
                // إضافة المستخدم إلى المجموعة
                $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role, joined_at) 
                                      VALUES (?, ?, 'member', NOW())");
                $stmt->execute([$group['id'], $user_id]);
                
                $_SESSION['success_message'] = "تم الانضمام إلى المجموعة بنجاح!";
            } else {
                $_SESSION['error_message'] = "أنت بالفعل عضو في هذه المجموعة";
            }
        } else {
            $_SESSION['error_message'] = "رمز الانضمام غير صالح أو المجموعة غير موجودة";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ أثناء الانضمام إلى المجموعة: " . $e->getMessage();
    }
    
    header('Location: ../groups.php');
    exit();
}

header('Location: ../groups.php');
exit();
?>