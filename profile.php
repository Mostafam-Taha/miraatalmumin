<?php
session_start(); // بدء الجلسة

ini_set('display_errors', 0); // إيقاف عرض الأخطاء للمستخدم
error_reporting(E_ALL);

$host     = 'sql207.infinityfree.com';
$dbname   = 'if0_39304815_miraatalmuminif';
$username = 'if0_39304815';
$password = 'NIHOIGYPkLGsq0';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]));
}

// ========== تجهيز مجلد رفع الصور ==========
$uploadDir = __DIR__ . '/uploads/profile_pictures/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// ===== دوال مساعدة =====
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function getUserDeviceInfo() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $deviceType = 'web';
    $browser = 'Unknown';
    $os = 'Unknown';
    
    if (preg_match('/(android)/i', $userAgent)) {
        $deviceType = 'android';
    } elseif (preg_match('/(iphone|ipad|ipod)/i', $userAgent)) {
        $deviceType = 'ios';
    } elseif (preg_match('/(windows nt|macintosh|mac os x|linux)/i', $userAgent)) {
        $deviceType = 'desktop';
    }
    
    if (preg_match('/(chrome|chromium)/i', $userAgent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/(firefox|fxios)/i', $userAgent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/(safari)/i', $userAgent) && !preg_match('/(chrome)/i', $userAgent)) {
        $browser = 'Safari';
    } elseif (preg_match('/(edg)/i', $userAgent)) {
        $browser = 'Edge';
    } elseif (preg_match('/(opera|opr)/i', $userAgent)) {
        $browser = 'Opera';
    }
    
    if (preg_match('/windows nt 10.0/i', $userAgent)) {
        $os = 'Windows 10';
    } elseif (preg_match('/windows nt 6.3/i', $userAgent)) {
        $os = 'Windows 8.1';
    } elseif (preg_match('/windows nt 6.2/i', $userAgent)) {
        $os = 'Windows 8';
    } elseif (preg_match('/windows nt 6.1/i', $userAgent)) {
        $os = 'Windows 7';
    } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
        $os = 'macOS';
    } elseif (preg_match('/android/i', $userAgent)) {
        $os = 'Android';
    } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
        $os = 'iOS';
    } elseif (preg_match('/linux/i', $userAgent)) {
        $os = 'Linux';
    }
    
    return [
        'device_type' => $deviceType,
        'browser' => $browser,
        'os' => $os,
        'user_agent' => $userAgent
    ];
}

function createSessionToken() {
    return bin2hex(random_bytes(32));
}

// ===== إنشاء الجداول إذا لم تكن موجودة =====
try {
    // إنشاء جدول user_sessions
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
            `location` varchar(255) DEFAULT NULL,
            `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `is_current` tinyint(1) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `session_token` (`session_token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // إنشاء جدول user_security_settings
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_security_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `notify_new_device` tinyint(1) DEFAULT 1,
            `allow_multiple_devices` tinyint(1) DEFAULT 1,
            `session_timeout_hours` int(11) DEFAULT 24,
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // إنشاء جدول device_activity_log
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `device_activity_log` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `session_id` int(11) DEFAULT NULL,
            `action` enum('login','logout','forced_logout','device_added','device_removed','settings_changed') NOT NULL,
            `device_name` varchar(255) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `session_id` (`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // التأكد من وجود عمود profile_picture في جدول users
    try {
        $pdo->exec("ALTER TABLE `users` ADD `profile_picture` VARCHAR(255) DEFAULT NULL");
    } catch (PDOException $e) {
        // العمود موجود مسبقاً
    }
} catch (PDOException $e) {
    // تجاهل أخطاء إنشاء الجداول إذا كانت موجودة بالفعل
}

// حفظ جلسة المستخدم بعد تسجيل الدخول
function saveUserSession($pdo, $userId, $sessionToken) {
    $deviceInfo = getUserDeviceInfo();
    $ipAddress = getUserIP();
    
    // التحقق من وجود جلسة سابقة للجهاز الحالي
    $checkStmt = $pdo->prepare("SELECT id FROM user_sessions WHERE user_id = ? AND session_token = ?");
    $checkStmt->execute([$userId, $sessionToken]);
    if (!$checkStmt->fetch()) {
        $deviceName = $deviceInfo['os'] . ' - ' . $deviceInfo['browser'];
        
        $stmt = $pdo->prepare("
            INSERT INTO user_sessions (user_id, session_token, device_name, device_type, browser, os, ip_address, last_activity, is_current, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1, 1)
        ");
        $stmt->execute([$userId, $sessionToken, $deviceName, $deviceInfo['device_type'], $deviceInfo['browser'], $deviceInfo['os'], $ipAddress]);
        $sessionId = $pdo->lastInsertId();
        
        // تسجيل النشاط
        $logStmt = $pdo->prepare("
            INSERT INTO device_activity_log (user_id, session_id, action, device_name, ip_address, user_agent)
            VALUES (?, ?, 'login', ?, ?, ?)
        ");
        $logStmt->execute([$userId, $sessionId, $deviceName, $ipAddress, $deviceInfo['user_agent']]);
    } else {
        // تحديث وقت آخر نشاط
        $updateStmt = $pdo->prepare("
            UPDATE user_sessions 
            SET last_activity = NOW(), ip_address = ?, is_current = 1 
            WHERE user_id = ? AND session_token = ?
        ");
        $updateStmt->execute([$ipAddress, $userId, $sessionToken]);
    }
    
    // إلغاء تعيين is_current للجلسات الأخرى
    $updateStmt = $pdo->prepare("
        UPDATE user_sessions 
        SET is_current = 0 
        WHERE user_id = ? AND session_token != ?
    ");
    $updateStmt->execute([$userId, $sessionToken]);
}

// التحقق من صحة الجلسة
function validateSession($pdo, $userId, $sessionToken) {
    $stmt = $pdo->prepare("
        SELECT id, is_active, last_activity 
        FROM user_sessions 
        WHERE user_id = ? AND session_token = ? AND is_active = 1
    ");
    $stmt->execute([$userId, $sessionToken]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        return false;
    }
    
    // التحقق من انتهاء الجلسة
    $securityStmt = $pdo->prepare("SELECT session_timeout_hours FROM user_security_settings WHERE user_id = ?");
    $securityStmt->execute([$userId]);
    $settings = $securityStmt->fetch(PDO::FETCH_ASSOC);
    $timeoutHours = $settings ? $settings['session_timeout_hours'] : 24;
    
    $lastActivity = strtotime($session['last_activity']);
    $now = time();
    if (($now - $lastActivity) > ($timeoutHours * 3600)) {
        $deactivateStmt = $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE id = ?");
        $deactivateStmt->execute([$session['id']]);
        return false;
    }
    
    $updateStmt = $pdo->prepare("UPDATE user_sessions SET last_activity = NOW() WHERE id = ?");
    $updateStmt->execute([$session['id']]);
    
    return true;
}

// ===== معالجة الصورة الشخصية (جديدة) =====
function processProfilePicture($file) {
    global $uploadDir;
    
    // إنشاء اسم فريد للملف
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    $destination = $uploadDir . $newFileName;
    
    // فتح الصورة المصدر حسب النوع
    switch (exif_imagetype($file['tmp_name'])) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($file['tmp_name']);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($file['tmp_name']);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($file['tmp_name']);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            return false;
    }
    
    if (!$sourceImage) {
        return false;
    }
    
    // الأبعاد الجديدة (مربع 400×400 بكسل)
    $newWidth = 400;
    $newHeight = 400;
    $origWidth = imagesx($sourceImage);
    $origHeight = imagesy($sourceImage);
    
    // إنشاء صورة جديدة بالأبعاد المطلوبة
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // التعامل مع الشفافية لصور PNG و GIF
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
    imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    
    // تغيير الحجم مع الحفاظ على النسبة (اقتصاص للمربع)
    $origAspect = $origWidth / $origHeight;
    $newAspect = $newWidth / $newHeight;
    
    if ($origAspect > $newAspect) {
        // الصورة أعرض - نقص الطول
        $srcHeight = $origHeight;
        $srcWidth = $origHeight * $newAspect;
        $srcX = ($origWidth - $srcWidth) / 2;
        $srcY = 0;
    } else {
        // الصورة أطول - نقص العرض
        $srcWidth = $origWidth;
        $srcHeight = $origWidth / $newAspect;
        $srcX = 0;
        $srcY = ($origHeight - $srcHeight) / 2;
    }
    
    imagecopyresampled(
        $newImage, $sourceImage,
        0, 0, $srcX, $srcY,
        $newWidth, $newHeight,
        $srcWidth, $srcHeight
    );
    
    // حفظ الصورة بصيغة JPEG بجودة 80%
    $result = imagejpeg($newImage, $destination, 80);
    
    // تنظيف الذاكرة
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    if ($result) {
        // إرجاع المسار النسبي
        return 'http://miraat-almumin.xo.je/uploads/profile_pictures/' . $newFileName;
    }
    return false;
}

// ===== معالجة تسجيل الدخول (للمستخدمين الذين لديهم كلمة مرور فقط) =====
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['session_token'] = createSessionToken();
        
        saveUserSession($pdo, $user['id'], $_SESSION['session_token']);
        
        header('Location: profile.php');
        exit;
    } else {
        $login_error = "البريد الإلكتروني أو كلمة المرور غير صحيحة";
    }
}

// ===== معالجة تسجيل الخروج =====
if (isset($_GET['logout'])) {
    if (isLoggedIn() && isset($_SESSION['session_token'])) {
        $stmt = $pdo->prepare("
            UPDATE user_sessions 
            SET is_active = 0, is_current = 0 
            WHERE user_id = ? AND session_token = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
        
        $logStmt = $pdo->prepare("
            INSERT INTO device_activity_log (user_id, action, ip_address)
            VALUES (?, 'logout', ?)
        ");
        $logStmt->execute([$_SESSION['user_id'], getUserIP()]);
    }
    session_destroy();
    header('Location: profile.php');
    exit;
}

// ===== معالجة AJAX =====
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'غير مصرح به، يرجى تسجيل الدخول']);
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    
    // تحديث البيانات الشخصية (مُعدَّل لدعم الصورة)
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'الاسم والبريد الإلكتروني مطلوبان']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صحيح']);
            exit;
        }

        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $userId]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل']);
            exit;
        }

        // معالجة الصورة الشخصية إذا تم رفعها
        $profilePicturePath = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            
            // التحقق من نوع الملف
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $file['tmp_name']);
            finfo_close($fileInfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'نوع الملف غير مدعوم. الأنواع المسموحة: JPG, PNG, GIF, WEBP']);
                exit;
            }
            
            // التحقق من الحجم (5 ميجابايت كحد أقصى)
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت']);
                exit;
            }
            
            // معالجة الصورة وحفظها
            $profilePicturePath = processProfilePicture($file);
            if (!$profilePicturePath) {
                echo json_encode(['success' => false, 'message' => 'فشل في معالجة الصورة']);
                exit;
            }
        }

        try {
            // حذف الصورة القديمة إذا تم رفع صورة جديدة
            if ($profilePicturePath) {
                $stmtOld = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
                $stmtOld->execute([$userId]);
                $oldPicture = $stmtOld->fetchColumn();
                if ($oldPicture && file_exists(__DIR__ . '/' . ltrim($oldPicture, '/'))) {
                    unlink(__DIR__ . '/' . ltrim($oldPicture, '/'));
                }
                
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?");
                $stmt->execute([$name, $email, $profilePicturePath, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $userId]);
            }
            
            $_SESSION['user_name'] = $name;
            
            echo json_encode([
                'success' => true,
                'message' => 'تم تحديث البيانات بنجاح',
                'profile_picture_url' => $profilePicturePath ?? null
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء الحفظ']);
        }
        exit;
    }
    
    // جلب الأجهزة المتصلة
    if ($action === 'get_devices') {
        $currentSessionToken = $_SESSION['session_token'];
        
        $stmt = $pdo->prepare("
            SELECT id, device_name, device_type, browser, os, ip_address, location, last_activity, 
                   created_at, is_current, is_active,
                   CASE WHEN session_token = ? THEN 1 ELSE 0 END as is_current_device
            FROM user_sessions 
            WHERE user_id = ? 
            ORDER BY is_current_device DESC, last_activity DESC
        ");
        $stmt->execute([$currentSessionToken, $userId]);
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'devices' => $devices]);
        exit;
    }
    
    // تسجيل خروج جهاز محدد
    if ($action === 'revoke_device') {
        $sessionId = intval($_POST['session_id'] ?? 0);
        $currentSessionToken = $_SESSION['session_token'];
        
        $checkStmt = $pdo->prepare("
            SELECT session_token, device_name FROM user_sessions 
            WHERE id = ? AND user_id = ? AND is_active = 1
        ");
        $checkStmt->execute([$sessionId, $userId]);
        $session = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($session) {
            if ($session['session_token'] === $currentSessionToken) {
                echo json_encode(['success' => false, 'message' => 'لا يمكن تسجيل خروج الجهاز الحالي بهذه الطريقة']);
                exit;
            }
            
            $updateStmt = $pdo->prepare("
                UPDATE user_sessions 
                SET is_active = 0 
                WHERE id = ? AND user_id = ?
            ");
            $updateStmt->execute([$sessionId, $userId]);
            
            $logStmt = $pdo->prepare("
                INSERT INTO device_activity_log (user_id, session_id, action, device_name, ip_address)
                VALUES (?, ?, 'forced_logout', ?, ?)
            ");
            $logStmt->execute([$userId, $sessionId, $session['device_name'] ?? 'Unknown', getUserIP()]);
            
            echo json_encode(['success' => true, 'message' => 'تم تسجيل خروج الجهاز بنجاح']);
        } else {
            echo json_encode(['success' => false, 'message' => 'الجهاز غير موجود أو غير نشط']);
        }
        exit;
    }
    
    // تسجيل خروج جميع الأجهزة الأخرى
    if ($action === 'revoke_all_devices') {
        $currentSessionToken = $_SESSION['session_token'];
        
        $devicesStmt = $pdo->prepare("
            SELECT id, device_name FROM user_sessions 
            WHERE user_id = ? AND session_token != ? AND is_active = 1
        ");
        $devicesStmt->execute([$userId, $currentSessionToken]);
        $devices = $devicesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updateStmt = $pdo->prepare("
            UPDATE user_sessions 
            SET is_active = 0 
            WHERE user_id = ? AND session_token != ?
        ");
        $updateStmt->execute([$userId, $currentSessionToken]);
        
        foreach ($devices as $device) {
            $logStmt = $pdo->prepare("
                INSERT INTO device_activity_log (user_id, session_id, action, device_name, ip_address)
                VALUES (?, ?, 'forced_logout', ?, ?)
            ");
            $logStmt->execute([$userId, $device['id'], $device['device_name'], getUserIP()]);
        }
        
        echo json_encode(['success' => true, 'message' => 'تم تسجيل خروج جميع الأجهزة الأخرى بنجاح']);
        exit;
    }
    
    // جلب إعدادات الأمان
    if ($action === 'get_security_settings') {
        $stmt = $pdo->prepare("
            SELECT notify_new_device, allow_multiple_devices, session_timeout_hours 
            FROM user_security_settings 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$settings) {
            $settings = [
                'notify_new_device' => 1,
                'allow_multiple_devices' => 1,
                'session_timeout_hours' => 24
            ];
        }
        
        echo json_encode(['success' => true, 'settings' => $settings]);
        exit;
    }
    
    // تحديث إعدادات الأمان
    if ($action === 'update_security_settings') {
        $notifyNewDevice = isset($_POST['notify_new_device']) ? 1 : 0;
        $allowMultipleDevices = isset($_POST['allow_multiple_devices']) ? 1 : 0;
        $sessionTimeout = intval($_POST['session_timeout'] ?? 24);
        
        $stmt = $pdo->prepare("
            INSERT INTO user_security_settings (user_id, notify_new_device, allow_multiple_devices, session_timeout_hours)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            notify_new_device = VALUES(notify_new_device),
            allow_multiple_devices = VALUES(allow_multiple_devices),
            session_timeout_hours = VALUES(session_timeout_hours)
        ");
        $stmt->execute([$userId, $notifyNewDevice, $allowMultipleDevices, $sessionTimeout]);
        
        $logStmt = $pdo->prepare("
            INSERT INTO device_activity_log (user_id, action, ip_address)
            VALUES (?, 'settings_changed', ?)
        ");
        $logStmt->execute([$userId, getUserIP()]);
        
        echo json_encode(['success' => true, 'message' => 'تم تحديث إعدادات الأمان بنجاح']);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    exit;
}

// ===== التحقق من صحة الجلسة =====
if (isLoggedIn() && isset($_SESSION['session_token'])) {
    if (!validateSession($pdo, $_SESSION['user_id'], $_SESSION['session_token'])) {
        session_destroy();
        header('Location: profile.php?session_expired=1');
        exit;
    }
}

// ===== عرض الصفحة =====

// إذا لم يكن المستخدم مسجل الدخول، اعرض نموذج تسجيل الدخول
if (!isLoggedIn()) {
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>تسجيل الدخول - مرآة المؤمن</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Tajawal', sans-serif;
                background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }
            .login-card {
                background: white;
                border-radius: 28px;
                padding: 40px;
                max-width: 440px;
                width: 100%;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                text-align: center;
            }
            .login-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #059669, #047857);
                border-radius: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
            }
            .login-icon i { font-size: 40px; color: white; }
            h1 { font-size: 26px; color: #065f46; margin-bottom: 8px; }
            .subtitle { color: #6b7280; margin-bottom: 32px; font-size: 14px; }
            .form-group { margin-bottom: 20px; text-align: right; }
            .form-group label {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: #374151;
                margin-bottom: 8px;
            }
            .form-group input {
                width: 100%;
                padding: 12px 16px;
                border: 1.5px solid #e5e7eb;
                border-radius: 12px;
                font-size: 15px;
                font-family: 'Tajawal', sans-serif;
                transition: all 0.2s;
            }
            .form-group input:focus {
                outline: none;
                border-color: #059669;
                box-shadow: 0 0 0 3px rgba(5,150,105,0.1);
            }
            .login-btn {
                width: 100%;
                padding: 14px;
                background: #059669;
                color: white;
                border: none;
                border-radius: 12px;
                font-size: 16px;
                font-weight: 700;
                font-family: 'Tajawal', sans-serif;
                cursor: pointer;
                transition: background 0.2s;
            }
            .login-btn:hover { background: #047857; }
            .error-msg {
                background: #fee2e2;
                color: #dc2626;
                padding: 12px;
                border-radius: 12px;
                font-size: 14px;
                margin-bottom: 20px;
            }
            .session-warning {
                background: #fef3c7;
                color: #92400e;
                padding: 12px;
                border-radius: 12px;
                font-size: 14px;
                margin-bottom: 20px;
                display: <?php echo isset($_GET['session_expired']) ? 'block' : 'none'; ?>;
            }
            .google-btn {
                width: 100%;
                padding: 12px;
                background: white;
                color: #374151;
                border: 1.5px solid #e5e7eb;
                border-radius: 12px;
                font-size: 14px;
                font-weight: 600;
                font-family: 'Tajawal', sans-serif;
                cursor: pointer;
                transition: all 0.2s;
                margin-top: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                text-decoration: none;
            }
            .google-btn:hover {
                background: #f9fafb;
                border-color: #059669;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            <div class="login-icon">
                <i class="fas fa-praying-hands"></i>
            </div>
            <h1>مرحباً بك</h1>
            <p class="subtitle">سجل دخولك لمتابعة تتبع صلواتك</p>
            
            <div class="session-warning">
                <i class="fas fa-clock"></i> انتهت صلاحية الجلسة، يرجى تسجيل الدخول مرة أخرى
            </div>
            
            <?php if (isset($login_error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $login_error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> البريد الإلكتروني</label>
                    <input type="email" name="email" required placeholder="example@email.com">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> كلمة المرور</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" name="login" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                </button>
            </form>
            
            <a href="login.php" class="google-btn">
                <i class="fab fa-google"></i> تسجيل الدخول بـ Google
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ===== المستخدم مسجل دخوله =====
$userId = $_SESSION['user_id'];

// جلب بيانات المستخدم من قاعدة البيانات
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: profile.php');
    exit;
}

$initials = mb_substr($user['name'] ?? 'U', 0, 1, 'UTF-8');
$joinDate = $user['created_at'] ? date('d/m/Y', strtotime($user['created_at'])) : '-';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملفي الشخصي - <?php echo htmlspecialchars($user['name'] ?? ''); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Tajawal', sans-serif;
            background: #f0fdf4;
            color: #111827;
            min-height: 100vh;
        }
        .profile-wrap { max-width: 1200px; margin: 0 auto; padding: 32px 20px; }
        
        .logout-btn {
            display: inline-flex; align-items: center; gap: 8px;
            background: #ef4444; color: white;
            text-decoration: none; padding: 8px 16px;
            border-radius: 10px; font-weight: 600; font-size: 14px;
            margin-bottom: 20px;
            transition: background 0.2s;
        }
        .logout-btn:hover { background: #dc2626; }
        
        .profile-header {
            background: linear-gradient(135deg, #047857, #059669);
            border-radius: 20px;
            padding: 32px;
            color: white;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        .profile-header::before {
            content: '';
            position: absolute; top: -60px; left: -60px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
        }
        .ph-content { display: flex; align-items: center; gap: 20px; position: relative; z-index: 1; flex-wrap: wrap; }
        .ph-avatar-wrap { position: relative; flex-shrink: 0; }
        .ph-avatar, .ph-avatar-text {
            width: 88px; height: 88px; border-radius: 50%;
            border: 3px solid rgba(255,255,255,0.5);
            object-fit: cover;
        }
        .ph-avatar-text {
            background: rgba(255,255,255,0.2);
            color: white; font-weight: 800; font-size: 34px;
            display: flex; align-items: center; justify-content: center;
        }
        .ph-info { flex: 1; }
        .ph-name { font-size: 24px; font-weight: 800; }
        .ph-email { font-size: 14px; opacity: 0.85; margin-top: 4px; }
        .ph-joined { font-size: 12px; opacity: 0.7; margin-top: 6px; }
        .ph-edit-btn {
            background: rgba(255,255,255,0.2);
            border: 1.5px solid rgba(255,255,255,0.4);
            color: white; padding: 9px 20px;
            border-radius: 10px; cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            font-weight: 600; font-size: 14px;
            display: flex; align-items: center; gap: 8px;
            transition: background 0.2s;
        }
        .ph-edit-btn:hover { background: rgba(255,255,255,0.3); }
        
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-section {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1.5px solid #d1fae5;
        }
        .section-title {
            font-size: 16px; font-weight: 700;
            color: #065f46;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1.5px solid #d1fae5;
            display: flex; align-items: center; gap: 8px;
        }
        .field-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0fdf4;
        }
        .field-row:last-child { border-bottom: none; }
        .field-label { font-size: 13px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
        .field-value { font-size: 14px; font-weight: 600; color: #111827; text-align: left; }
        
        .security-settings {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1.5px solid #d1fae5;
        }
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0fdf4;
        }
        .setting-item:last-child { border-bottom: none; }
        .setting-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .setting-info p {
            font-size: 11px;
            color: #6b7280;
        }
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 24px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e5e7eb;
            transition: 0.3s;
            border-radius: 24px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }
        input:checked + .toggle-slider {
            background-color: #059669;
        }
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        .select-input {
            padding: 6px 12px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-family: 'Tajawal', sans-serif;
            background: white;
        }
        
        .devices-section {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1.5px solid #d1fae5;
            margin-bottom: 20px;
        }
        .devices-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .revoke-all-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .revoke-all-btn:hover { background: #dc2626; }
        
        .device-card {
            background: #f9fafb;
            border-radius: 14px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            transition: all 0.2s;
            border-right: 4px solid #e5e7eb;
        }
        .device-card.current-device {
            background: #ecfdf5;
            border-right-color: #059669;
        }
        .device-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }
        .device-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .device-details h4 {
            font-size: 15px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .device-details p {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 2px;
        }
        .device-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            margin-right: 8px;
        }
        .badge-current {
            background: #059669;
            color: white;
        }
        .badge-inactive {
            background: #e5e7eb;
            color: #6b7280;
        }
        .device-actions {
            display: flex;
            gap: 10px;
        }
        .revoke-device-btn {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 18px;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .revoke-device-btn:hover {
            background: #fee2e2;
            transform: scale(1.05);
        }
        .device-last-active {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 4px;
        }
        
        .edit-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.55);
            display: none; justify-content: center; align-items: center;
            z-index: 1000; padding: 20px;
            backdrop-filter: blur(4px);
        }
        .edit-modal {
            background: white;
            border-radius: 20px;
            width: 100%; max-width: 500px;
            overflow: hidden;
            animation: popIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes popIn {
            from { opacity:0; transform: scale(0.9); }
            to   { opacity:1; transform: scale(1); }
        }
        .edit-header {
            background: linear-gradient(135deg, #047857, #059669);
            padding: 20px 24px;
            color: white;
            display: flex; align-items: center; justify-content: space-between;
        }
        .edit-close {
            width: 34px; height: 34px;
            background: rgba(255,255,255,0.2);
            border: none; border-radius: 50%;
            color: white; cursor: pointer;
        }
        .edit-body { padding: 24px; }
        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block; font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 8px;
        }
        .form-input {
            width: 100%; padding: 11px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            font-family: 'Tajawal', sans-serif;
        }
        .form-input:focus { outline: none; border-color: #059669; }
        /* إضافة تنسيقات لمعاينة الصورة */
        #photoPreview {
            margin-top: 8px;
        }
        #photoPreview img {
            max-width: 100px;
            border-radius: 50%;
            border: 2px solid #d1fae5;
        }
        .edit-footer {
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex; gap: 10px;
        }
        .save-btn {
            flex: 1; padding: 12px;
            background: #059669; color: white;
            border: none; border-radius: 10px;
            font-weight: 700; cursor: pointer;
        }
        .save-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .cancel-btn {
            padding: 12px 20px;
            background: #e5e7eb; color: #374151;
            border: none; border-radius: 10px;
            cursor: pointer;
        }
        .toast {
            position: fixed; bottom: 24px; left: 50%;
            transform: translateX(-50%);
            padding: 12px 28px;
            border-radius: 12px; color: white;
            font-weight: 600; font-size: 14px;
            z-index: 2000; display: none;
        }
        .toast.success { background: #059669; }
        .toast.error   { background: #ef4444; }
        .toast.info    { background: #3b82f6; }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #e5e7eb;
            border-top-color: #059669;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .text-center { text-align: center; }
        .py-4 { padding: 20px 0; }
        .text-gray { color: #9ca3af; }
        
        @media (max-width: 768px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
            .device-info {
                flex-direction: column;
                align-items: flex-start;
                text-align: right;
            }
            .device-card {
                flex-direction: column;
                align-items: stretch;
            }
            .device-actions {
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>

<div class="profile-wrap">
    <a href="logout.php" class="logout-btn" onclick="return confirm('هل أنت متأكد من تسجيل الخروج؟')">
        <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
    </a>

    <!-- Header -->
    <div class="profile-header">
        <div class="ph-content">
            <div class="ph-avatar-wrap">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img class="ph-avatar" src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="avatar">
                <?php else: ?>
                    <div class="ph-avatar-text"><?php echo $initials; ?></div>
                <?php endif; ?>
            </div>
            <div class="ph-info">
                <div class="ph-name" id="displayName"><?php echo htmlspecialchars($user['name'] ?? 'غير معروف'); ?></div>
                <div class="ph-email" id="displayEmail"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                <div class="ph-joined">📅 انضم <?php echo $joinDate; ?></div>
            </div>
            <button class="ph-edit-btn" onclick="openEdit()">
                <i class="fas fa-pen"></i> تعديل
            </button>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="two-columns">
        <!-- Left Column: Account Info -->
        <div class="info-section">
            <div class="section-title"><i class="fas fa-user-circle"></i> معلومات الحساب</div>
            <div class="field-row">
                <div class="field-label"><i class="fas fa-hashtag"></i> المعرف</div>
                <div class="field-value">#<?php echo $user['id']; ?></div>
            </div>
            <div class="field-row">
                <div class="field-label"><i class="fas fa-user"></i> الاسم</div>
                <div class="field-value" id="nameDisplay"><?php echo htmlspecialchars($user['name'] ?? '-'); ?></div>
            </div>
            <div class="field-row">
                <div class="field-label"><i class="fas fa-envelope"></i> البريد</div>
                <div class="field-value" id="emailDisplay"><?php echo htmlspecialchars($user['email'] ?? '-'); ?></div>
            </div>
            <div class="field-row">
                <div class="field-label"><i class="fas fa-calendar-plus"></i> تاريخ التسجيل</div>
                <div class="field-value"><?php echo $joinDate; ?></div>
            </div>
            <div class="field-row">
                <div class="field-label"><i class="fab fa-google"></i> تسجيل عبر</div>
                <div class="field-value">
                    <span class="device-badge" style="background: #d1fae5; color: #065f46;">Google</span>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Security Settings -->
        <div class="security-settings">
            <div class="section-title"><i class="fas fa-shield-alt"></i> إعدادات الأمان</div>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>إشعار عند تسجيل دخول جديد</h4>
                    <p>تنبيه عند تسجيل الدخول من جهاز جديد</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" id="notifyNewDevice" onchange="updateSecuritySettings()">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>السماح بأجهزة متعددة</h4>
                    <p>تسجيل الدخول من أكثر من جهاز في نفس الوقت</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" id="allowMultipleDevices" onchange="updateSecuritySettings()">
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <div class="setting-item">
                <div class="setting-info">
                    <h4>مدة بقاء الجلسة</h4>
                    <p>المدة التي تبقى فيها الجلسة نشطة</p>
                </div>
                <select id="sessionTimeout" class="select-input" onchange="updateSecuritySettings()">
                    <option value="12">12 ساعة</option>
                    <option value="24">24 ساعة</option>
                    <option value="48">48 ساعة</option>
                    <option value="168">7 أيام</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Devices Section -->
    <div class="devices-section">
        <div class="devices-header">
            <div class="section-title" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0;">
                <i class="fas fa-mobile-alt"></i> الأجهزة المتصلة
            </div>
            <button class="revoke-all-btn" onclick="revokeAllDevices()">
                <i class="fas fa-sign-out-alt"></i> تسجيل خروج جميع الأجهزة الأخرى
            </button>
        </div>
        <div id="devicesList">
            <div class="text-center py-4">
                <div class="loading-spinner"></div>
                <p class="text-gray" style="margin-top: 10px;">جاري تحميل الأجهزة...</p>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal (مُعدَّل) -->
<div class="edit-overlay" id="editOverlay">
    <div class="edit-modal">
        <div class="edit-header">
            <h3><i class="fas fa-pen"></i> تعديل البيانات</h3>
            <button class="edit-close" onclick="closeEdit()">✕</button>
        </div>
        <div class="edit-body">
            <div class="form-group">
                <label class="form-label">الاسم الكامل</label>
                <input type="text" class="form-input" id="editName" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" class="form-input" id="editEmail" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
            </div>
			<a href="hk/index.html">_</a>
            <!-- حقل الصورة الشخصية -->
            <div class="form-group">
                <label class="form-label">الصورة الشخصية</label>
                <input type="file" class="form-input" id="editPhoto" accept="image/*">
                <div id="photoPreview">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="صورة حالية">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="edit-footer">
            <button class="save-btn" id="saveBtn" onclick="saveProfile()">
                <i class="fas fa-save"></i> حفظ التغييرات
            </button>
            <button class="cancel-btn" onclick="closeEdit()">إلغاء</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const overlay = document.getElementById('editOverlay');
const toast = document.getElementById('toast');

function openEdit() { overlay.style.display = 'flex'; }
function closeEdit() { overlay.style.display = 'none'; }

overlay.addEventListener('click', e => { if (e.target === overlay) closeEdit(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEdit(); });

// معاينة الصورة قبل الرفع
document.getElementById('editPhoto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('photoPreview');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            preview.innerHTML = `<img src="${event.target.result}" alt="معاينة">`;
        };
        reader.readAsDataURL(file);
    } else {
        // إذا ألغى الاختيار، نعيد الصورة القديمة إن وجدت
        <?php if (!empty($user['profile_picture'])): ?>
            preview.innerHTML = `<img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="صورة حالية">`;
        <?php else: ?>
            preview.innerHTML = '';
        <?php endif; ?>
    }
});

function showToast(msg, type = 'success') {
    toast.textContent = msg;
    toast.className = `toast ${type}`;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 4000);
}

// Save Profile (مُعدَّلة)
async function saveProfile() {
    const name = document.getElementById('editName').value.trim();
    const email = document.getElementById('editEmail').value.trim();

    if (!name || !email) {
        showToast('يرجى ملء جميع الحقول', 'error');
        return;
    }

    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<div class="loading-spinner"></div> جاري الحفظ...';

    try {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'update_profile');
        formData.append('name', name);
        formData.append('email', email);
        
        // إضافة الصورة إذا تم اختيارها
        const photoInput = document.getElementById('editPhoto');
        if (photoInput.files.length > 0) {
            formData.append('profile_picture', photoInput.files[0]);
        }

        const res = await fetch('', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            showToast(result.message, 'success');
            document.getElementById('displayName').textContent = name;
            document.getElementById('displayEmail').textContent = email;
            document.getElementById('nameDisplay').textContent = name;
            document.getElementById('emailDisplay').textContent = email;
            
            // تحديث الصورة في الواجهة إذا تم إرجاع مسار جديد
            if (result.profile_picture_url) {
                // تحديث صورة الهيدر
                const avatarContainer = document.querySelector('.ph-avatar-wrap');
                avatarContainer.innerHTML = `<img class="ph-avatar" src="${result.profile_picture_url}" alt="avatar">`;
                // تحديث المعاينة في المودال
                document.getElementById('photoPreview').innerHTML = 
                    `<img src="${result.profile_picture_url}" alt="صورة تم تحديثها">`;
            }
            
            setTimeout(closeEdit, 1500);
        } else {
            showToast(result.message, 'error');
        }
    } catch(e) {
        console.error(e);
        showToast('خطأ في الاتصال', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save"></i> حفظ التغييرات';
    }
}

// Load Devices
async function loadDevices() {
    const devicesList = document.getElementById('devicesList');
    
    try {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'get_devices');
        
        const res = await fetch('', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success && data.devices) {
            if (data.devices.length === 0) {
                devicesList.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-desktop" style="font-size: 48px; color: #9ca3af; margin-bottom: 10px; display: block;"></i>
                        <p class="text-gray">لا توجد أجهزة متصلة</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            for (const device of data.devices) {
                const isCurrent = device.is_current_device === 1;
                const isActive = device.is_active === 1;
                const deviceIcon = getDeviceIcon(device.device_type);
                const iconColor = getDeviceIconColor(device.device_type);
                
                const lastActive = formatDate(device.last_activity);
                
                html += `
                    <div class="device-card ${isCurrent ? 'current-device' : ''}">
                        <div class="device-info">
                            <div class="device-icon" style="background: ${iconColor}20;">
                                <i class="${deviceIcon}" style="color: ${iconColor};"></i>
                            </div>
                            <div class="device-details">
                                <h4>
                                    ${escapeHtml(device.device_name || 'جهاز غير معروف')}
                                    ${isCurrent ? '<span class="device-badge badge-current">الجهاز الحالي</span>' : ''}
                                    ${!isActive ? '<span class="device-badge badge-inactive">غير نشط</span>' : ''}
                                </h4>
                                <p><i class="fas fa-browser"></i> ${escapeHtml(device.browser || 'غير معروف')} | <i class="fas fa-desktop"></i> ${escapeHtml(device.os || 'غير معروف')}</p>
                                <p><i class="fas fa-globe"></i> ${escapeHtml(device.ip_address || 'عنوان IP غير معروف')}</p>
                                <div class="device-last-active">
                                    <i class="fas fa-clock"></i> آخر نشاط: ${lastActive}
                                </div>
                            </div>
                        </div>
                        ${!isCurrent && isActive ? `
                            <div class="device-actions">
                                <button class="revoke-device-btn" onclick="revokeDevice(${device.id})" title="تسجيل خروج هذا الجهاز">
                                    <i class="fas fa-sign-out-alt"></i>
                                </button>
                            </div>
                        ` : ''}
                    </div>
                `;
            }
            devicesList.innerHTML = html;
        } else {
            devicesList.innerHTML = '<div class="text-center py-4"><p class="text-gray">حدث خطأ في تحميل الأجهزة</p></div>';
        }
    } catch(e) {
        console.error(e);
        devicesList.innerHTML = '<div class="text-center py-4"><p class="text-gray">حدث خطأ في تحميل الأجهزة</p></div>';
    }
}

// Load Security Settings
async function loadSecuritySettings() {
    try {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'get_security_settings');
        
        const res = await fetch('', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success && data.settings) {
            document.getElementById('notifyNewDevice').checked = data.settings.notify_new_device == 1;
            document.getElementById('allowMultipleDevices').checked = data.settings.allow_multiple_devices == 1;
            document.getElementById('sessionTimeout').value = data.settings.session_timeout_hours;
        }
    } catch(e) {
        console.error(e);
    }
}

// Update Security Settings
async function updateSecuritySettings() {
    const notifyNewDevice = document.getElementById('notifyNewDevice').checked ? 1 : 0;
    const allowMultipleDevices = document.getElementById('allowMultipleDevices').checked ? 1 : 0;
    const sessionTimeout = document.getElementById('sessionTimeout').value;
    
    try {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'update_security_settings');
        formData.append('notify_new_device', notifyNewDevice);
        formData.append('allow_multiple_devices', allowMultipleDevices);
        formData.append('session_timeout', sessionTimeout);
        
        const res = await fetch('', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'حدث خطأ', 'error');
        }
    } catch(e) {
        console.error(e);
        showToast('حدث خطأ أثناء حفظ الإعدادات', 'error');
    }
}

// Revoke Single Device
async function revokeDevice(sessionId) {
    if (!confirm('هل أنت متأكد من تسجيل خروج هذا الجهاز؟')) return;
    
    try {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'revoke_device');
        formData.append('session_id', sessionId);
        
        const res = await fetch('', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            loadDevices();
        } else {
            showToast(data.message, 'error');
        }
    } catch(e) {
        console.error(e);
        showToast('حدث خطأ أثناء محاولة تسجيل الخروج', 'error');
    }
}

// Revoke All Other Devices
async function revokeAllDevices() {
    if (!confirm('تحذير: سيتم تسجيل خروج جميع الأجهزة الأخرى باستثناء هذا الجهاز. هل أنت متأكد؟')) return;
    
    try {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'revoke_all_devices');
        
        const res = await fetch('', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            loadDevices();
        } else {
            showToast(data.message, 'error');
        }
    } catch(e) {
        console.error(e);
        showToast('حدث خطأ أثناء محاولة تسجيل الخروج', 'error');
    }
}

// Helper Functions
function getDeviceIcon(deviceType) {
    const icons = {
        'android': 'fab fa-android',
        'ios': 'fab fa-apple',
        'desktop': 'fas fa-desktop',
        'web': 'fas fa-globe'
    };
    return icons[deviceType] || 'fas fa-mobile-alt';
}

function getDeviceIconColor(deviceType) {
    const colors = {
        'android': '#3ddc84',
        'ios': '#000000',
        'desktop': '#6b7280',
        'web': '#059669'
    };
    return colors[deviceType] || '#059669';
}

function formatDate(dateString) {
    if (!dateString) return 'غير معروف';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'الآن';
    if (diffMins < 60) return `منذ ${diffMins} دقيقة`;
    if (diffHours < 24) return `منذ ${diffHours} ساعة`;
    return `منذ ${diffDays} يوم`;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadDevices();
    loadSecuritySettings();
    
    // تسجيل الجلسة الحالية عند تحميل الصفحة (لأول مرة)
    <?php if (!isset($_SESSION['session_token']) && isLoggedIn()): ?>
    (async function() {
        const formData = new FormData();
        formData.append('ajax', 'true');
        formData.append('action', 'init_session');
        await fetch('', { method: 'POST', body: formData });
    })();
    <?php endif; ?>
});
</script>
</body>
</html>