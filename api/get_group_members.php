<?php
session_start();
require_once '../includes/config.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح بالدخول']);
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['group_id'])) {
    $group_id = intval($_GET['group_id']);
    
    // التحقق من أن المستخدم عضو في المجموعة
    try {
        $stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->execute([$group_id, $user_id]);
        $is_member = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($is_member) {
            // جلب أعضاء المجموعة
            $stmt = $pdo->prepare("SELECT u.name, u.profile_picture, gm.role, DATE_FORMAT(gm.joined_at, '%Y-%m-%d %H:%i') as joined_at 
                                  FROM group_members gm 
                                  JOIN users u ON gm.user_id = u.id 
                                  WHERE gm.group_id = ? 
                                  ORDER BY gm.joined_at ASC");
            $stmt->execute([$group_id]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'members' => $members]);
        } else {
            echo json_encode(['success' => false, 'message' => 'أنت لست عضوًا في هذه المجموعة']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'معرف المجموعة مطلوب']);
}
?>