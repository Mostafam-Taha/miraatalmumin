<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

require_once '../includes/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit();
}

$user_id = $_SESSION['user_id'];
$id = intval($data['id']);

try {
    $stmt = $pdo->prepare("DELETE FROM additional_prayers WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $id, 'user_id' => $user_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'تم الحذف بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'لم يتم العثور على السجل']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
}
?>