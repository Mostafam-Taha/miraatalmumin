<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

$pdo = Config::getInstance()->getConnection();
$auth = new Auth($pdo);
$userId = $auth->validateSession();

if (!$userId) {
    jsonResponse(false, null, 'Not authenticated');
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'send':
        sendMessage($pdo, $userId);
        break;
    case 'get':
        getMessages($pdo, $userId);
        break;
    case 'get_users':
        getUsers($auth, $userId);
        break;
    case 'get_unread_counts':
        getUnreadCounts($pdo, $userId);
        break;
    case 'delete':
        deleteMessage($pdo, $userId);
        break;
    case 'edit':
        editMessage($pdo, $userId);
        break;
    default:
        jsonResponse(false, null, 'Invalid action');
}

function sendMessage($pdo, $senderId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['receiver_id'], $data['message_text'])) {
        jsonResponse(false, null, 'Missing required fields');
    }
    
    $messageText = htmlspecialchars($data['message_text'], ENT_QUOTES, 'UTF-8');
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, message_text, has_file)
            VALUES (?, ?, ?, 0)
        ");
        
        $stmt->execute([$senderId, $data['receiver_id'], $messageText]);
        
        jsonResponse(true, ['message_id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        error_log("Send message error: " . $e->getMessage());
        jsonResponse(false, null, 'Failed to send message');
    }
}

function deleteMessage($pdo, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['message_id'])) {
        jsonResponse(false, null, 'Missing message_id');
    }
    
    $messageId = (int)$data['message_id'];
    
    try {
        // التأكد أن المستخدم هو مُرسل الرسالة فقط
        $stmt = $pdo->prepare("SELECT sender_id FROM messages WHERE id = ?");
        $stmt->execute([$messageId]);
        $msg = $stmt->fetch();
        
        if (!$msg) {
            jsonResponse(false, null, 'Message not found');
        }
        
        if ((int)$msg['sender_id'] !== (int)$userId) {
            jsonResponse(false, null, 'You can only delete your own messages');
        }
        
        // حذف الملفات المرتبطة أولاً (إن وجدت)
        $stmtFiles = $pdo->prepare("DELETE FROM files WHERE message_id = ?");
        $stmtFiles->execute([$messageId]);
        
        // حذف الرسالة
        $stmtDel = $pdo->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
        $stmtDel->execute([$messageId, $userId]);
        
        jsonResponse(true, ['deleted_id' => $messageId]);
    } catch (PDOException $e) {
        error_log("Delete message error: " . $e->getMessage());
        jsonResponse(false, null, 'Failed to delete message');
    }
}

function editMessage($pdo, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['message_id'], $data['message_text'])) {
        jsonResponse(false, null, 'Missing required fields');
    }
    
    $messageId = (int)$data['message_id'];
    $newText = htmlspecialchars(trim($data['message_text']), ENT_QUOTES, 'UTF-8');
    
    if (empty($newText)) {
        jsonResponse(false, null, 'Message text cannot be empty');
    }
    
    try {
        // التأكد أن المستخدم هو مُرسل الرسالة فقط
        $stmt = $pdo->prepare("SELECT sender_id FROM messages WHERE id = ?");
        $stmt->execute([$messageId]);
        $msg = $stmt->fetch();
        
        if (!$msg) {
            jsonResponse(false, null, 'Message not found');
        }
        
        if ((int)$msg['sender_id'] !== (int)$userId) {
            jsonResponse(false, null, 'You can only edit your own messages');
        }
        
        // تحديث الرسالة مع وضع علامة is_edited
        $stmtUpd = $pdo->prepare("
            UPDATE messages 
            SET message_text = ?, is_edited = 1, edited_at = NOW()
            WHERE id = ? AND sender_id = ?
        ");
        $stmtUpd->execute([$newText, $messageId, $userId]);
        
        jsonResponse(true, [
            'message_id' => $messageId,
            'new_text'   => $newText
        ]);
    } catch (PDOException $e) {
        error_log("Edit message error: " . $e->getMessage());
        jsonResponse(false, null, 'Failed to edit message');
    }
}

function getMessages($pdo, $userId) {
    $withUserId = $_GET['with_user'] ?? 0;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                m.sender_id,
                m.receiver_id,
                m.message_text,
                m.sent_at,
                m.has_file,
                m.is_edited,
                u.username as sender_username,
                f.id as file_id,
                f.file_path,
                f.thumbnail_path,
                f.file_type,
                f.original_name,
                f.file_size,
                f.mime_type,
                f.file_width,
                f.file_height
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            LEFT JOIN files f ON m.id = f.message_id
            WHERE (m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.sent_at ASC
            LIMIT 500
        ");
        
        $stmt->execute([$userId, $withUserId, $withUserId, $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group files by message
        $grouped = [];
        foreach ($messages as $msg) {
            $msgId = $msg['id'];
            if (!isset($grouped[$msgId])) {
                $grouped[$msgId] = [
                    'id'              => $msg['id'],
                    'sender_id'       => $msg['sender_id'],
                    'receiver_id'     => $msg['receiver_id'],
                    'message_text'    => $msg['message_text'],
                    'sent_at'         => $msg['sent_at'],
                    'has_file'        => (bool)$msg['has_file'],
                    'is_edited'       => (bool)$msg['is_edited'],
                    'sender_username' => $msg['sender_username'],
                    'files'           => []
                ];
            }
            if ($msg['file_id']) {
                $grouped[$msgId]['files'][] = [
                    'id'             => $msg['file_id'],
                    'path'           => $msg['file_path'],
                    'thumbnail_path' => $msg['thumbnail_path'],
                    'file_type'      => $msg['file_type'],
                    'original_name'  => $msg['original_name'],
                    'file_size'      => $msg['file_size'],
                    'mime_type'      => $msg['mime_type'],
                    'width'          => $msg['file_width'],
                    'height'         => $msg['file_height']
                ];
            }
        }
        
        // Mark messages as read
        $updateStmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
        ");
        $updateStmt->execute([$withUserId, $userId]);
        
        jsonResponse(true, array_values($grouped));
    } catch (PDOException $e) {
        error_log("Get messages error: " . $e->getMessage());
        jsonResponse(false, null, 'Failed to get messages');
    }
}

function getUsers($auth, $userId) {
    $users = $auth->getUsers($userId);
    jsonResponse(true, $users);
}

function getUnreadCounts($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT sender_id as from_user_id, COUNT(*) as unread_count
            FROM messages
            WHERE receiver_id = ? AND is_read = 0
            GROUP BY sender_id
        ");
        $stmt->execute([$userId]);
        $unreadRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $unreadMap = [];
        foreach ($unreadRows as $row) {
            $unreadMap[$row['from_user_id']] = (int)$row['unread_count'];
        }
        
        $stmt2 = $pdo->prepare("
            SELECT 
                CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as other_user_id,
                message_text,
                has_file,
                sent_at
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?
            ORDER BY sent_at DESC
        ");
        $stmt2->execute([$userId, $userId, $userId]);
        $allMsgs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        $seen = [];
        foreach ($allMsgs as $msg) {
            $otherId = $msg['other_user_id'];
            if (!isset($seen[$otherId])) {
                $seen[$otherId] = true;
                $result[$otherId] = [
                    'unread_count'      => $unreadMap[$otherId] ?? 0,
                    'last_message'      => $msg['message_text'],
                    'last_message_time' => $msg['sent_at'],
                    'last_has_file'     => (bool)$msg['has_file']
                ];
            }
        }
        
        jsonResponse(true, $result);
    } catch (PDOException $e) {
        error_log("Get unread counts error: " . $e->getMessage());
        jsonResponse(false, null, 'Failed to get unread counts');
    }
}
?>