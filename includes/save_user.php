<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'prayer_tracker';
$username = 'root';
$password = '';

// دالة لتحديد نوع الجهاز
function detectDeviceType($user_agent) {
    $user_agent = strtolower($user_agent);
    
    // قائمة بأنماط الأجهزة
    $device_patterns = [
        'iphone' => '/iphone/',
        'ipad' => '/ipad/',
        'android' => '/android/',
        'windows phone' => '/windows phone/',
        'windows' => '/windows nt/',
        'mac' => '/macintosh|mac os x/',
        'linux' => '/linux/',
        'chromeos' => '/cros/'
    ];
    
    // التحقق من كل نوع
    foreach ($device_patterns as $device => $pattern) {
        if (preg_match($pattern, $user_agent)) {
            return $device;
        }
    }
    
    // إذا لم يتم التعرف، نرجع المجموعة
    if (preg_match('/mobile/', $user_agent)) {
        return 'mobile';
    } elseif (preg_match('/tablet/', $user_agent)) {
        return 'tablet';
    } else {
        return 'desktop';
    }
}

// دالة لتحديد نظام التشغيل
function detectOS($user_agent) {
    $user_agent = strtolower($user_agent);
    
    $os_list = [
        'Android' => '/android/',
        'iOS' => '/iphone|ipad|ipod/',
        'Windows' => '/windows nt|windows phone/',
        'Mac OS' => '/macintosh|mac os x/',
        'Linux' => '/linux/',
        'Chrome OS' => '/cros/'
    ];
    
    foreach ($os_list as $os => $pattern) {
        if (preg_match($pattern, $user_agent)) {
            return $os;
        }
    }
    
    return 'Unknown';
}

// دالة لتحديد المتصفح
function detectBrowser($user_agent) {
    $user_agent = strtolower($user_agent);
    
    $browsers = [
        'Chrome' => '/chrome/',
        'Safari' => '/safari/',
        'Firefox' => '/firefox/',
        'Edge' => '/edge|edg/',
        'Opera' => '/opera/',
        'Internet Explorer' => '/msie|trident/'
    ];
    
    foreach ($browsers as $browser => $pattern) {
        if (preg_match($pattern, $user_agent)) {
            return $browser;
        }
    }
    
    return 'Unknown';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $credential = $data['credential'] ?? '';
    
    if (empty($credential)) {
        throw new Exception('لم يتم استلام بيانات الاعتماد');
    }
    
    $parts = explode('.', $credential);
    if (count($parts) !== 3) {
        throw new Exception('بيانات الاعتماد غير صالحة');
    }
    
    $payload = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', $parts[1]))), true);
    
    $google_id = $payload['sub'];
    $email = $payload['email'];
    $name = $payload['name'] ?? '';
    $profile_picture = $payload['picture'] ?? '';
    
    // الحصول على معلومات المستخدم
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $device_type = detectDeviceType($user_agent);
    $os_type = detectOS($user_agent);
    $browser = detectBrowser($user_agent);
    
    // إنشاء وصف مفصل للجهاز
    $device_description = "$device_type - $os_type - $browser";
    
    $stmt = $pdo->prepare("SELECT id, device_type FROM users WHERE google_id = :google_id OR email = :email");
    $stmt->execute(['google_id' => $google_id, 'email' => $email]);
    $existing_user = $stmt->fetch();
    
    if ($existing_user) {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                name = :name, 
                profile_picture = :profile_picture,
                device_type = :device_type,
                last_device_login = :last_device_login,
                user_agent = :user_agent,
                last_login = NOW()
            WHERE id = :id
        ");
        
        $stmt->execute([
            'name' => $name,
            'profile_picture' => $profile_picture,
            'device_type' => $device_type,
            'last_device_login' => $device_description,
            'user_agent' => $user_agent,
            'id' => $existing_user['id']
        ]);
        
        $user_id = $existing_user['id'];
        
        // تسجيل تغيير الجهاز إذا كان مختلفاً
        if ($existing_user['device_type'] !== $device_type) {
            // يمكنك هنا إضافة تسجيل في جدول منفصل لتاريخ الأجهزة
            error_log("User {$user_id} changed device from {$existing_user['device_type']} to {$device_type}");
        }
        
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO users (
                google_id, 
                email, 
                name, 
                profile_picture,
                device_type,
                last_device_login,
                user_agent,
                last_login
            ) VALUES (
                :google_id, 
                :email, 
                :name, 
                :profile_picture,
                :device_type,
                :last_device_login,
                :user_agent,
                NOW()
            )
        ");
        
        $stmt->execute([
            'google_id' => $google_id,
            'email' => $email,
            'name' => $name,
            'profile_picture' => $profile_picture,
            'device_type' => $device_type,
            'last_device_login' => $device_description,
            'user_agent' => $user_agent
        ]);
        
        $user_id = $pdo->lastInsertId();
    }
    
    // إنشاء جلسة المستخدم مع معلومات الجهاز
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_picture'] = $profile_picture;
    $_SESSION['device_type'] = $device_type;
    $_SESSION['os_type'] = $os_type;
    $_SESSION['browser'] = $browser;
    $_SESSION['user_agent'] = $user_agent;
    $_SESSION['login_time'] = time();
    
    echo json_encode([
        'success' => true,
        'message' => 'تم تسجيل الدخول بنجاح',
        'user_id' => $user_id,
        'device_info' => [
            'type' => $device_type,
            'os' => $os_type,
            'browser' => $browser
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in save_user.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'خطأ: ' . $e->getMessage()
    ]);
}
?>