<?php
session_start();
require_once 'config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'يرجى ملء جميع الحقول';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif ($password !== $confirm_password) {
        $error = 'كلمة المرور وتأكيدها غير متطابقين';
    } else {
        try {
            // التحقق من صلاحية الرمز
            $stmt = $pdo->prepare("
                SELECT pr.user_id, u.email 
                FROM password_resets pr 
                JOIN users u ON pr.user_id = u.id 
                WHERE pr.token = ? AND pr.expires_at > NOW()
            ");
            $stmt->execute([$token]);
            $resetData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resetData) {
                $error = 'رابط استعادة كلمة المرور غير صالح أو منتهي الصلاحية';
            } else {
                // تحديث كلمة المرور
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $resetData['user_id']]);
                
                // حذف رمز الاستعادة
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $stmt->execute([$resetData['user_id']]);
                
                $success = true;
            }
        } catch (PDOException $e) {
            $error = 'حدث خطأ في الخادم';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور - مرآة المؤمن</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .logo {
            color: #059669;
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #111827;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
        }
        
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-right: 4px solid #dc2626;
        }
        
        .success-message {
            background: #d1fae5;
            color: #059669;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-right: 4px solid #059669;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }
        
        label {
            display: block;
            color: #111827;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-family: inherit;
            font-size: 16px;
        }
        
        input:focus {
            outline: none;
            border-color: #059669;
        }
        
        button {
            background: #059669;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }
        
        button:hover {
            background: #047857;
        }
        
        .login-link {
            margin-top: 20px;
            color: #6b7280;
        }
        
        .login-link a {
            color: #059669;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-pray"></i>
        </div>
        
        <h1>إعادة تعيين كلمة المرور</h1>
        <p class="subtitle">أدخل كلمة المرور الجديدة لحسابك</p>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> تم إعادة تعيين كلمة المرور بنجاح
                <div style="margin-top: 10px;">
                    <a href="login.php" style="color: #047857; font-weight: bold;">انقر هنا لتسجيل الدخول</a>
                </div>
            </div>
        <?php elseif ($token): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="password">كلمة المرور الجديدة</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit">
                    <i class="fas fa-sync-alt"></i> إعادة تعيين كلمة المرور
                </button>
            </form>
        <?php else: ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> رابط غير صالح
            </div>
        <?php endif; ?>
        
        <div class="login-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> العودة لتسجيل الدخول</a>
        </div>
    </div>
</body>
</html>