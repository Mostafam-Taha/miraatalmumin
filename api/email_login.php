<?php
header('Content-Type: application/json');

session_start();
require_once '../includes/config.php';

// دالة لإنشاء رمز فريد
function generateVerificationCode() {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(16));
    } else {
        return md5(uniqid(rand(), true));
    }
}

// دالة لتشفير كلمة المرور
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// دالة للتحقق من كلمة المرور
function verifyPassword($password, $hash) {
    // التحقق مما إذا كان الهاش من جوجل أو من كلمة مرور
    if (strlen($hash) > 50 && !str_starts_with($hash, '$2y$') && !str_starts_with($hash, '$2a$')) {
        // هذا معرف جوجل، ليس كلمة مرور مشفرة
        return false;
    }
    return password_verify($password, $hash);
}

// التحقق من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صحيحة']);
    exit;
}

// قراءة البيانات المرسلة
$input = file_get_contents('php://input');
if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'لا توجد بيانات']);
    exit;
}

$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة: ' . json_last_error_msg()]);
    exit;
}

if (!isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'الإجراء غير محدد']);
    exit;
}

$action = $data['action'];

try {
    switch ($action) {
        case 'login':
            // تسجيل الدخول
            if (!isset($data['email']) || !isset($data['password'])) {
                echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
                exit;
            }
            
            $email = trim($data['email']);
            $password = $data['password'];
            
            // البحث عن المستخدم
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة']);
                exit;
            }
            
            // التحقق من كلمة المرور
            if (!verifyPassword($password, $user['google_id'])) {
                echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة']);
                exit;
            }
            
            // إنشاء الجلسة
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_pro'] = $user['Pro'];
            $_SESSION['login_method'] = 'email';
            
            // لا نحتاج لتحديث last_login لأنه غير موجود في الجدول
            // $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            // $stmt->execute([$user['id']]);
            
            echo json_encode(['success' => true, 'message' => 'تم تسجيل الدخول بنجاح']);
            break;
            
        case 'register':
            // إنشاء حساب جديد
            if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
                echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
                exit;
            }
            
            $name = trim($data['name']);
            $email = trim($data['email']);
            $password = $data['password'];
            
            // التحقق من صحة البريد الإلكتروني
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صالح']);
                exit;
            }
            
            // التحقق من طول كلمة المرور
            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل']);
                exit;
            }
            
            // التحقق من عدم وجود البريد مسبقاً
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'هذا البريد الإلكتروني مستخدم بالفعل']);
                exit;
            }
            
            // تشفير كلمة المرور
            $hashedPassword = hashPassword($password);
            
            // إنشاء صورة الملف الشخصي من الاسم
            $profilePicture = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=059669&color=fff&size=256';
            
            // إدخال المستخدم في قاعدة البيانات
            $stmt = $pdo->prepare("
                INSERT INTO users (google_id, email, name, profile_picture, created_at, Pro) 
                VALUES (?, ?, ?, ?, NOW(), 0)
            ");
            
            $stmt->execute([
                $hashedPassword,
                $email,
                $name,
                $profilePicture
            ]);
            
            $userId = $pdo->lastInsertId();
            
            // إنشاء جلسة للمستخدم الجديد
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_pro'] = 0;
            $_SESSION['login_method'] = 'email';
            
            echo json_encode(['success' => true, 'message' => 'تم إنشاء الحساب بنجاح']);
            break;
            
        case 'forgot_password':
            // استعادة كلمة المرور
            if (!isset($data['email'])) {
                echo json_encode(['success' => false, 'message' => 'يرجى إدخال البريد الإلكتروني']);
                exit;
            }
            
            $email = trim($data['email']);
            
            // التحقق من وجود المستخدم
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'لا يوجد حساب مرتبط بهذا البريد الإلكتروني']);
                exit;
            }
            
            // إنشاء رمز استعادة
            $resetToken = generateVerificationCode();
            $expiryTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // حفظ رمز الاستعادة في قاعدة البيانات
            $stmt = $pdo->prepare("
                INSERT INTO password_resets (user_id, token, expires_at) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE token = ?, expires_at = ?
            ");
            
            $stmt->execute([
                $user['id'],
                $resetToken,
                $expiryTime,
                $resetToken,
                $expiryTime
            ]);
            
            // إنشاء رابط الاستعادة
            $resetLink = SITE_URL . '/reset_password.php?token=' . $resetToken;
            
            echo json_encode([
                'success' => true, 
                'message' => 'تم إنشاء رابط الاستعادة. الرابط: ' . $resetLink
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Database error in email_login.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error in email_login.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
}
?>