<?php
// ip_check.php - يتم تضمينه في register.php و login.php
// ضع require_once 'ip_check.php'; في أول الملفات

function checkBannedIP($pdo) {
    // جيب IP الزائر
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    // لو في قائمة IPs خد أول واحد فقط
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
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

        $stmt = $pdo->prepare("SELECT reason FROM banned_ips WHERE ip = ?");
        $stmt->execute([$ip]);
        $banned = $stmt->fetch();

        if ($banned) {
            jsonResponse(false, null, 'Access denied. Your network has been restricted.');
        }
    } catch (PDOException $e) {
        // لو في مشكلة في الجدول، خلي التسجيل يكمل
        error_log("IP check error: " . $e->getMessage());
    }
}
?>