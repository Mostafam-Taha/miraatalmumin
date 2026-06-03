<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'miraatalmumin';
$username = 'root';
$password = '#';

// دالة لتحديد نوع الجهاز
function detectDeviceType($user_agent) {
    $user_agent = strtolower($user_agent);
    
    if (preg_match('/iphone|ipad|ipod/', $user_agent)) {
        return 'ios';
    } elseif (preg_match('/android/', $user_agent)) {
        return 'android';
    } elseif (preg_match('/windows nt|macintosh|linux/', $user_agent)) {
        return 'desktop';
    } elseif (preg_match('/mobile/', $user_agent)) {
        return 'mobile';
    }
    
    return 'web';
}

// دالة لتحديد نظام التشغيل
function detectOS($user_agent) {
    $user_agent = strtolower($user_agent);
    
    if (preg_match('/android/', $user_agent)) return 'Android';
    if (preg_match('/iphone|ipad|ipod/', $user_agent)) return 'iOS';
    if (preg_match('/windows nt 10/', $user_agent)) return 'Windows 10';
    if (preg_match('/windows nt 6.3/', $user_agent)) return 'Windows 8.1';
    if (preg_match('/windows nt 6.2/', $user_agent)) return 'Windows 8';
    if (preg_match('/windows nt 6.1/', $user_agent)) return 'Windows 7';
    if (preg_match('/macintosh|mac os x/', $user_agent)) return 'macOS';
    if (preg_match('/linux/', $user_agent)) return 'Linux';
    if (preg_match('/cros/', $user_agent)) return 'Chrome OS';
    
    return 'Unknown';
}

// دالة لتحديد المتصفح
function detectBrowser($user_agent) {
    $user_agent = strtolower($user_agent);
    
    if (preg_match('/edg|edge/', $user_agent)) return 'Edge';
    if (preg_match('/opr|opera/', $user_agent)) return 'Opera';
    if (preg_match('/chrome/', $user_agent) && !preg_match('/edg/', $user_agent)) return 'Chrome';
    if (preg_match('/safari/', $user_agent) && !preg_match('/chrome/', $user_agent)) return 'Safari';
    if (preg_match('/firefox/', $user_agent)) return 'Firefox';
    if (preg_match('/msie|trident/', $user_agent)) return 'Internet Explorer';
    
    return 'Unknown';
}

// دالة لإنشاء رمز جلسة فريد
function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

// دالة للحصول على IP المستخدم
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// دالة لإنشاء الجداول إذا لم تكن موجودة
function createTablesIfNotExist($pdo) {
    // جدول الأجهزة المتصلة
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_sessions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `session_token` varchar(255) NOT NULL,
            `device_name` varchar(255) DEFAULT NULL,
            `device_type` enum('android','ios','web','desktop') DEFAULT 'web',
            `browser` varchar(100) DEFAULT NULL,
            `os` varchar(100) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `is_current` tinyint(1) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `session_token` (`session_token`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // جدول إعدادات الأمان
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_security_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `notify_new_device` tinyint(1) DEFAULT 1,
            `allow_multiple_devices` tinyint(1) DEFAULT 1,
            `session_timeout_hours` int(11) DEFAULT 24,
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // جدول سجل الأجهزة
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `device_activity_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `session_id` int(11) DEFAULT NULL,
            `action` enum('login','logout','forced_logout','device_added','device_removed','settings_changed','device_changed') NOT NULL,
            `device_name` varchar(255) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `session_id` (`session_id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
}

// دالة لحفظ جلسة المستخدم
function saveUserSession($pdo, $userId, $sessionToken, $deviceInfo, $ipAddress) {
    $deviceName = $deviceInfo['os'] . ' - ' . $deviceInfo['browser'];
    
    // التحقق من وجود جلسة نشطة لنفس الجهاز
    $checkStmt = $pdo->prepare("
        SELECT id FROM user_sessions 
        WHERE user_id = ? AND device_type = ? AND browser = ? AND os = ? AND is_active = 1
    ");
    $checkStmt->execute([$userId, $deviceInfo['device_type'], $deviceInfo['browser'], $deviceInfo['os']]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // تحديث الجلسة الموجودة
        $updateStmt = $pdo->prepare("
            UPDATE user_sessions 
            SET session_token = ?, last_activity = NOW(), ip_address = ?, is_current = 1
            WHERE id = ?
        ");
        $updateStmt->execute([$sessionToken, $ipAddress, $existing['id']]);
        $sessionId = $existing['id'];
    } else {
        // إنشاء جلسة جديدة
        $insertStmt = $pdo->prepare("
            INSERT INTO user_sessions (user_id, session_token, device_name, device_type, browser, os, ip_address, last_activity, is_current, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1, 1)
        ");
        $insertStmt->execute([$userId, $sessionToken, $deviceName, $deviceInfo['device_type'], $deviceInfo['browser'], $deviceInfo['os'], $ipAddress]);
        $sessionId = $pdo->lastInsertId();
    }
    
    // إلغاء تعيين is_current للجلسات الأخرى
    $updateStmt = $pdo->prepare("
        UPDATE user_sessions 
        SET is_current = 0 
        WHERE user_id = ? AND id != ?
    ");
    $updateStmt->execute([$userId, $sessionId]);
    
    // تسجيل النشاط
    $logStmt = $pdo->prepare("
        INSERT INTO device_activity_log (user_id, session_id, action, device_name, ip_address, user_agent)
        VALUES (?, ?, 'login', ?, ?, ?)
    ");
    $logStmt->execute([$userId, $sessionId, $deviceName, $ipAddress, $deviceInfo['user_agent']]);
    
    return $sessionId;
}

// دالة للتحقق من إعدادات الأمان
function checkSecuritySettings($pdo, $userId, $deviceInfo) {
    // الحصول على إعدادات الأمان
    $stmt = $pdo->prepare("
        SELECT allow_multiple_devices, notify_new_device 
        FROM user_security_settings 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        // إعدادات افتراضية
        return ['allow_multiple' => true, 'notify' => true];
    }
    
    return [
        'allow_multiple' => $settings['allow_multiple_devices'] == 1,
        'notify' => $settings['notify_new_device'] == 1
    ];
}

// دالة لتسجيل خروج الأجهزة الأخرى
function revokeOtherDevices($pdo, $userId, $currentSessionToken) {
    // الحصول على الأجهزة الأخرى
    $stmt = $pdo->prepare("
        SELECT id, device_name FROM user_sessions 
        WHERE user_id = ? AND session_token != ? AND is_active = 1
    ");
    $stmt->execute([$userId, $currentSessionToken]);
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // إلغاء تنشيط الأجهزة الأخرى
    $updateStmt = $pdo->prepare("
        UPDATE user_sessions 
        SET is_active = 0 
        WHERE user_id = ? AND session_token != ?
    ");
    $updateStmt->execute([$userId, $currentSessionToken]);
    
    // تسجيل النشاط لكل جهاز
    foreach ($devices as $device) {
        $logStmt = $pdo->prepare("
            INSERT INTO device_activity_log (user_id, session_id, action, device_name)
            VALUES (?, ?, 'forced_logout', ?)
        ");
        $logStmt->execute([$userId, $device['id'], $device['device_name']]);
    }
    
    return count($devices);
}

// دالة للحصول على عدد الأجهزة النشطة
function getActiveDevicesCount($pdo, $userId, $excludeToken = null) {
    $sql = "SELECT COUNT(*) FROM user_sessions WHERE user_id = ? AND is_active = 1";
    $params = [$userId];
    
    if ($excludeToken) {
        $sql .= " AND session_token != ?";
        $params[] = $excludeToken;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // إنشاء الجداول إذا لم تكن موجودة
    createTablesIfNotExist($pdo);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $credential = $data['credential'] ?? '';
    $deviceInfoFromClient = $data['device_info'] ?? [];
    $forceLogin = isset($data['force_login']) && $data['force_login'] === true;
    
    if (empty($credential)) {
        throw new Exception('لم يتم استلام بيانات الاعتماد');
    }
    
    // فك تشفير بيانات Google
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        throw new Exception('بيانات الاعتماد غير صالحة');
    }
    
    $payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', $parts[1]))), true);
    
    $google_id = $payload['sub'];
    $email = $payload['email'];
    $name = $payload['name'] ?? '';
    $profile_picture = $payload['picture'] ?? '';
    
    // الحصول على معلومات الجهاز
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ip_address = getUserIP();
    $device_type = detectDeviceType($user_agent);
    $os_type = detectOS($user_agent);
    $browser = detectBrowser($user_agent);
    
    // استخدام معلومات الجهاز من العميل إذا كانت متوفرة
    if (!empty($deviceInfoFromClient)) {
        $device_type = $deviceInfoFromClient['device_type'] ?? $device_type;
        $os_type = $deviceInfoFromClient['os'] ?? $os_type;
        $browser = $deviceInfoFromClient['browser'] ?? $browser;
    }
    
    $deviceInfo = [
        'device_type' => $device_type,
        'os' => $os_type,
        'browser' => $browser,
        'user_agent' => $user_agent
    ];
    
    // البحث عن المستخدم
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE google_id = :google_id OR email = :email");
    $stmt->execute(['google_id' => $google_id, 'email' => $email]);
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_user) {
        $user_id = $existing_user['id'];
        
        // تحديث بيانات المستخدم
        $stmt = $pdo->prepare("
            UPDATE users SET 
                name = :name, 
                profile_picture = :profile_picture,
                last_login = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'name' => $name,
            'profile_picture' => $profile_picture,
            'id' => $user_id
        ]);
        
        // التحقق من إعدادات الأمان
        $securitySettings = checkSecuritySettings($pdo, $user_id, $deviceInfo);
        
        // التحقق من الأجهزة النشطة
        $activeDevicesCount = getActiveDevicesCount($pdo, $user_id);
        
        // إذا كان هناك أجهزة نشطة أخرى ولم يتم السماح بأجهزة متعددة
        if ($activeDevicesCount > 0 && !$securitySettings['allow_multiple'] && !$forceLogin) {
            echo json_encode([
                'success' => false,
                'message' => 'لديك أجهزة أخرى متصلة. هل تريد تسجيل الخروج منها؟',
                'devices_count' => $activeDevicesCount,
                'require_force' => true
            ]);
            exit;
        }
        
    } else {
        // إنشاء مستخدم جديد
        $stmt = $pdo->prepare("
            INSERT INTO users (
                google_id, email, name, profile_picture, created_at, last_login
            ) VALUES (
                :google_id, :email, :name, :profile_picture, NOW(), NOW()
            )
        ");
        $stmt->execute([
            'google_id' => $google_id,
            'email' => $email,
            'name' => $name,
            'profile_picture' => $profile_picture
        ]);
        $user_id = $pdo->lastInsertId();
        
        // إنشاء إعدادات أمان افتراضية للمستخدم الجديد
        $stmt = $pdo->prepare("
            INSERT INTO user_security_settings (user_id, notify_new_device, allow_multiple_devices, session_timeout_hours)
            VALUES (?, 1, 1, 24)
        ");
        $stmt->execute([$user_id]);
    }
    
    // إنشاء رمز جلسة جديد
    $sessionToken = generateSessionToken();
    
    // إذا كان تسجيل الدخول قسرياً، قم بتسجيل خروج الأجهزة الأخرى
    if ($forceLogin) {
        revokeOtherDevices($pdo, $user_id, $sessionToken);
    }
    
    // حفظ جلسة المستخدم
    $sessionId = saveUserSession($pdo, $user_id, $sessionToken, $deviceInfo, $ip_address);
    
    // إعداد بيانات الجلسة
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_picture'] = $profile_picture;
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['device_type'] = $device_type;
    $_SESSION['os_type'] = $os_type;
    $_SESSION['browser'] = $browser;
    $_SESSION['session_id'] = $sessionId;
    $_SESSION['login_time'] = time();
    
    // الحصول على عدد الأجهزة النشطة بعد تسجيل الدخول
    $activeDevicesCount = getActiveDevicesCount($pdo, $user_id, $sessionToken);
    
    echo json_encode([
        'success' => true,
        'message' => 'تم تسجيل الدخول بنجاح',
        'user_id' => $user_id,
        'user_name' => $name,
        'user_email' => $email,
        'device_info' => [
            'type' => $device_type,
            'os' => $os_type,
            'browser' => $browser
        ],
        'active_devices' => $activeDevicesCount,
        'session_token' => $sessionToken
    ]);
    
} catch (Exception $e) {
    error_log("Error in save_user.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطأ: ' . $e->getMessage()
    ]);
}
?>
