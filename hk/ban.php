<?php
// ban.php - API بسيط للأدمن
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

define('ADMIN_KEY', 'YOUR_SECRET_KEY_HERE'); // ← غيّر هذا

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$key   = $input['key'] ?? $_GET['key'] ?? '';

if ($key !== ADMIN_KEY) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $pdo = Config::getInstance()->getConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

// تأكد إن الجداول موجودة
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `banned_ips` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ip_address` varchar(45) NOT NULL,
        `banned_at` datetime NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_ip` (`ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// أضف عمود is_banned لو مش موجود
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN is_banned tinyint(1) NOT NULL DEFAULT 0");
} catch (Exception $e) { /* العمود موجود بالفعل */ }

$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'stats':
        $users = $pdo->query("
            SELECT id, username, email, registration_ip, last_login_ip, last_login_at, created_at,
                   IFNULL(is_banned, 0) as is_banned
            FROM users ORDER BY created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $totalMsg  = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
        $totalFiles= $pdo->query("SELECT COUNT(*) FROM files")->fetchColumn();
        $banned    = $pdo->query("SELECT COUNT(*) FROM users WHERE is_banned = 1")->fetchColumn();

        $msgChart  = $pdo->query("
            SELECT DATE(sent_at) as day, COUNT(*) as cnt
            FROM messages
            WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(sent_at) ORDER BY day ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'users'   => $users,
            'total_messages' => (int)$totalMsg,
            'total_files'    => (int)$totalFiles,
            'banned_count'   => (int)$banned,
            'msg_chart'      => $msgChart,
        ]);
        break;

    case 'ban':
        $uid = (int)($input['user_id'] ?? 0);
        if (!$uid) { echo json_encode(['success'=>false,'error'=>'Missing user_id']); exit; }

        $row = $pdo->prepare("SELECT registration_ip FROM users WHERE id = ?");
        $row->execute([$uid]);
        $user = $row->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['registration_ip']) {
            echo json_encode(['success'=>false,'error'=>'No IP found']);
            exit;
        }
        $ip = $user['registration_ip'];

        $pdo->prepare("INSERT IGNORE INTO banned_ips (ip_address) VALUES (?)")->execute([$ip]);
        $pdo->prepare("UPDATE users SET is_banned = 1 WHERE registration_ip = ?")->execute([$ip]);

        echo json_encode(['success'=>true, 'banned_ip'=>$ip]);
        break;

    case 'unban':
        $uid = (int)($input['user_id'] ?? 0);
        if (!$uid) { echo json_encode(['success'=>false,'error'=>'Missing user_id']); exit; }

        $row = $pdo->prepare("SELECT registration_ip FROM users WHERE id = ?");
        $row->execute([$uid]);
        $user = $row->fetch(PDO::FETCH_ASSOC);

        if (!$user) { echo json_encode(['success'=>false,'error'=>'Not found']); exit; }
        $ip = $user['registration_ip'];

        $pdo->prepare("DELETE FROM banned_ips WHERE ip_address = ?")->execute([$ip]);
        $pdo->prepare("UPDATE users SET is_banned = 0 WHERE registration_ip = ?")->execute([$ip]);

        echo json_encode(['success'=>true, 'unbanned_ip'=>$ip]);
        break;

    default:
        echo json_encode(['success'=>false,'error'=>'Invalid action']);
}