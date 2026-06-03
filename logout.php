<?php
/**
 * logout.php
 * تسجيل الخروج - يدعم تسجيل الخروج من جهاز واحد أو جميع الأجهزة
 */

session_start();

// تضمين إعدادات قاعدة البيانات
$host = 'localhost';
$dbname = 'miraatalmumin';
$username = 'root';
$password = '#';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // إذا فشل الاتصال، استمر في تسجيل الخروج العادي
    error_log("Logout DB Error: " . $e->getMessage());
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

// دالة لتسجيل نشاط الخروج
function logLogoutActivity($pdo, $userId, $sessionId, $action, $deviceName = null) {
    if (!$pdo || !$userId) return;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO device_activity_log (user_id, session_id, action, device_name, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $sessionId,
            $action,
            $deviceName,
            getUserIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Logout log error: " . $e->getMessage());
    }
}

// التحقق من وجود معامل logout_all (تسجيل خروج جميع الأجهزة)
$logoutAll = isset($_GET['all']) && $_GET['all'] == '1';
$logoutSessionId = isset($_GET['session_id']) ? intval($_GET['session_id']) : null;
$forceLogout = isset($_GET['force']) && $_GET['force'] == '1';

if (isset($_SESSION['user_id']) && isset($pdo)) {
    $userId = $_SESSION['user_id'];
    $currentSessionToken = $_SESSION['session_token'] ?? null;
    $currentSessionId = $_SESSION['session_id'] ?? null;
    
    try {
        if ($logoutAll) {
            // تسجيل خروج جميع الأجهزة بما فيها الجهاز الحالي
            $stmt = $pdo->prepare("
                UPDATE user_sessions 
                SET is_active = 0, is_current = 0 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            
            // تسجيل النشاط لكل جلسة
            $stmt = $pdo->prepare("
                SELECT id, device_name FROM user_sessions 
                WHERE user_id = ? AND is_active = 0
            ");
            $stmt->execute([$userId]);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($sessions as $session) {
                logLogoutActivity($pdo, $userId, $session['id'], 'forced_logout', $session['device_name']);
            }
            
            // تخزين رسالة في الجلسة قبل تدميرها
            $_SESSION['logout_message'] = 'تم تسجيل الخروج من جميع الأجهزة بنجاح';
            
        } elseif ($logoutSessionId && $currentSessionId != $logoutSessionId) {
            // تسجيل خروج جهاز محدد فقط (غير الجهاز الحالي)
            $stmt = $pdo->prepare("
                SELECT device_name, is_active FROM user_sessions 
                WHERE id = ? AND user_id = ? AND is_active = 1
            ");
            $stmt->execute([$logoutSessionId, $userId]);
            $targetSession = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($targetSession) {
                $stmt = $pdo->prepare("
                    UPDATE user_sessions 
                    SET is_active = 0, is_current = 0 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$logoutSessionId, $userId]);
                
                logLogoutActivity($pdo, $userId, $logoutSessionId, 'forced_logout', $targetSession['device_name']);
                
                $_SESSION['logout_message'] = 'تم تسجيل خروج الجهاز المحدد بنجاح';
            }
            
            // إذا كان هناك معامل redirect، نعيد التوجيه دون تدمير الجلسة الحالية
            if (isset($_GET['redirect'])) {
                header('Location: ' . $_GET['redirect'] . '?logout_success=1');
                exit;
            }
            
            // الاستمرار في الصفحة الحالية دون تسجيل خروج
            header('Location: profile.php?devices_updated=1');
            exit;
            
        } elseif ($forceLogout && $currentSessionToken) {
            // تسجيل خروج قسري من جهاز آخر (يُستخدم من API)
            $stmt = $pdo->prepare("
                SELECT id, device_name FROM user_sessions 
                WHERE session_token = ? AND user_id = ?
            ");
            $stmt->execute([$currentSessionToken, $userId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($session) {
                $stmt = $pdo->prepare("
                    UPDATE user_sessions 
                    SET is_active = 0, is_current = 0 
                    WHERE session_token = ? AND user_id = ?
                ");
                $stmt->execute([$currentSessionToken, $userId]);
                
                logLogoutActivity($pdo, $userId, $session['id'], 'forced_logout', $session['device_name']);
            }
            
            // إذا كان طلب AJAX، نعيد JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'تم تسجيل الخروج القسري']);
                exit;
            }
            
        } else {
            // تسجيل خروج عادي (جهاز واحد فقط)
            if ($currentSessionId) {
                $stmt = $pdo->prepare("
                    SELECT device_name FROM user_sessions 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$currentSessionId, $userId]);
                $currentSession = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $pdo->prepare("
                    UPDATE user_sessions 
                    SET is_active = 0, is_current = 0 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$currentSessionId, $userId]);
                
                logLogoutActivity($pdo, $userId, $currentSessionId, 'logout', $currentSession['device_name'] ?? null);
            }
            
            $_SESSION['logout_message'] = 'تم تسجيل الخروج بنجاح';
        }
        
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}

// تدمير الجلسة بالكامل (لحالة تسجيل الخروج العادي أو تسجيل خروج الكل)
if (!$logoutSessionId || ($logoutSessionId && $currentSessionId == $logoutSessionId)) {
    // حفظ أي رسالة قبل تدمير الجلسة
    $message = $_SESSION['logout_message'] ?? null;
    
    // تدمير الجلسة
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    // بدء جلسة مؤقتة لعرض الرسالة
    if ($message) {
        session_start();
        $_SESSION['logout_message'] = $message;
        session_write_close();
    }
}

// إعادة التوجيه
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// إذا كان هناك رسالة، إضافتها كمعامل في URL
if (isset($_SESSION['logout_message'])) {
    $message = $_SESSION['logout_message'];
    session_destroy();
    header("Location: $redirect?message=" . urlencode($message));
    exit;
}

header("Location: $redirect");
exit;
?>
