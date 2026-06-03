<?php
// admin_api.php - لوحة الإدارة
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// حماية بسيطة بكلمة سر
$ADMIN_PASSWORD = 'admin2026secure'; // غيّر دي

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// التحقق من كلمة السر لعمليات الكتابة
if (in_array($action, ['ban_ip', 'unban_ip', 'delete_user', 'delete_sessions'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['admin_token'] ?? $_GET['token'] ?? '';
    if ($token !== $ADMIN_PASSWORD) {
        jsonResponse(false, null, 'Unauthorized');
    }
}

$pdo = Config::getInstance()->getConnection();

switch ($action) {
    case 'login':
        adminLogin();
        break;
    case 'stats':
        getStats($pdo);
        break;
    case 'users':
        getUsers($pdo);
        break;
    case 'messages_stats':
        getMessagesStats($pdo);
        break;
    case 'banned_ips':
        getBannedIPs($pdo);
        break;
    case 'ban_ip':
        banIP($pdo);
        break;
    case 'unban_ip':
        unbanIP($pdo);
        break;
    case 'delete_sessions':
        deleteSessions($pdo);
        break;
    default:
        jsonResponse(false, null, 'Invalid action');
}

function adminLogin() {
    global $ADMIN_PASSWORD;
    $data = json_decode(file_get_contents('php://input'), true);
    $password = $data['password'] ?? '';
    if ($password === $ADMIN_PASSWORD) {
        jsonResponse(true, ['token' => $ADMIN_PASSWORD]);
    } else {
        jsonResponse(false, null, 'Wrong password');
    }
}

function getStats($pdo) {
    try {
        $stats = [];

        // إجمالي المستخدمين
        $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

        // إجمالي الرسائل
        $stats['total_messages'] = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();

        // إجمالي الملفات
        $stats['total_files'] = $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn();

        // الجلسات النشطة
        $stats['active_sessions'] = $pdo->query("SELECT COUNT(*) FROM sessions WHERE expires_at > NOW()")->fetchColumn();

        // IPs المحظورة
        $stats['banned_ips'] = $pdo->query("SELECT COUNT(*) FROM banned_ips")->fetchColumn();

        // تسجيلات اليوم
        $stats['registered_today'] = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        // رسائل اليوم
        $stats['messages_today'] = $pdo->query("SELECT COUNT(*) FROM messages WHERE DATE(sent_at) = CURDATE()")->fetchColumn();

        // مستخدمين بـ public key
        $stats['users_with_key'] = $pdo->query("SELECT COUNT(*) FROM users WHERE public_key IS NOT NULL")->fetchColumn();

        // تسجيلات آخر 7 أيام
        $regStmt = $pdo->query("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stats['registrations_chart'] = $regStmt->fetchAll();

        // رسائل آخر 7 أيام
        $msgStmt = $pdo->query("
            SELECT DATE(sent_at) as date, COUNT(*) as count
            FROM messages
            WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(sent_at)
            ORDER BY date ASC
        ");
        $stats['messages_chart'] = $msgStmt->fetchAll();

        // أكثر المستخدمين إرسالاً
        $topStmt = $pdo->query("
            SELECT u.username, COUNT(m.id) as msg_count
            FROM users u
            LEFT JOIN messages m ON u.id = m.sender_id
            GROUP BY u.id, u.username
            ORDER BY msg_count DESC
            LIMIT 5
        ");
        $stats['top_senders'] = $topStmt->fetchAll();

        jsonResponse(true, $stats);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'DB error: ' . $e->getMessage());
    }
}

function getUsers($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT
                u.id,
                u.username,
                u.email,
                u.registration_ip,
                u.last_login_ip,
                u.last_login_at,
                u.created_at,
                u.user_agent,
                CASE WHEN u.public_key IS NOT NULL THEN 1 ELSE 0 END as has_key,
                COUNT(DISTINCT m.id) as message_count,
                COUNT(DISTINCT s.session_token) as session_count,
                CASE WHEN bi.ip IS NOT NULL THEN 1 ELSE 0 END as ip_banned
            FROM users u
            LEFT JOIN messages m ON u.id = m.sender_id
            LEFT JOIN sessions s ON u.id = s.user_id AND s.expires_at > NOW()
            LEFT JOIN banned_ips bi ON u.registration_ip = bi.ip
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        $users = $stmt->fetchAll();
        jsonResponse(true, $users);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'DB error: ' . $e->getMessage());
    }
}

function getBannedIPs($pdo) {
    try {
        // إنشاء الجدول لو مش موجود
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS banned_ips (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip VARCHAR(45) NOT NULL UNIQUE,
                reason VARCHAR(255) DEFAULT 'Banned by admin',
                banned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                banned_by VARCHAR(50) DEFAULT 'admin'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $stmt = $pdo->query("
            SELECT bi.*,
                GROUP_CONCAT(DISTINCT u.username SEPARATOR ', ') as affected_users
            FROM banned_ips bi
            LEFT JOIN users u ON u.registration_ip = bi.ip
            GROUP BY bi.id
            ORDER BY bi.banned_at DESC
        ");
        jsonResponse(true, $stmt->fetchAll());
    } catch (PDOException $e) {
        jsonResponse(false, null, 'DB error: ' . $e->getMessage());
    }
}

function banIP($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $ip = trim($data['ip'] ?? '');
    $reason = trim($data['reason'] ?? 'Banned by admin');

    if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
        jsonResponse(false, null, 'Invalid IP address');
    }

    try {
        // إنشاء الجدول لو مش موجود
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS banned_ips (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip VARCHAR(45) NOT NULL UNIQUE,
                reason VARCHAR(255) DEFAULT 'Banned by admin',
                banned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                banned_by VARCHAR(50) DEFAULT 'admin'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $stmt = $pdo->prepare("INSERT INTO banned_ips (ip, reason) VALUES (?, ?) ON DUPLICATE KEY UPDATE reason=?, banned_at=NOW()");
        $stmt->execute([$ip, $reason, $reason]);

        // حذف جلسات المستخدمين اللي IP التسجيل بتاعهم محظور
        $delStmt = $pdo->prepare("
            DELETE s FROM sessions s
            JOIN users u ON s.user_id = u.id
            WHERE u.registration_ip = ?
        ");
        $delStmt->execute([$ip]);

        jsonResponse(true, ['message' => "IP $ip banned and sessions terminated"]);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'DB error: ' . $e->getMessage());
    }
}

function unbanIP($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $ip = trim($data['ip'] ?? '');

    if (!$ip) {
        jsonResponse(false, null, 'IP required');
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM banned_ips WHERE ip = ?");
        $stmt->execute([$ip]);
        jsonResponse(true, ['message' => "IP $ip unbanned"]);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'DB error: ' . $e->getMessage());
    }
}

function deleteSessions($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? null;

    try {
        if ($userId) {
            $stmt = $pdo->prepare("DELETE FROM sessions WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $pdo->exec("DELETE FROM sessions WHERE expires_at < NOW()");
        }
        jsonResponse(true, ['message' => 'Sessions deleted']);
    } catch (PDOException $e) {
        jsonResponse(false, null, 'DB error: ' . $e->getMessage());
    }
}
?>