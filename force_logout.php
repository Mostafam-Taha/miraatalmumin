<?php
/**
 * force_logout.php
 * صفحة لتسجيل الخروج القسري عند اكتشاف أن الجلسة تم إبطالها من جهاز آخر
 */

session_start();

// التحقق من وجود رسالة
$message = isset($_GET['message']) ? $_GET['message'] : 'تم تسجيل خروجك من هذا الجهاز من قبل جهاز آخر';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// تدمير الجلسة بالكامل
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تسجيل الخروج - مرآة المؤمن</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .logout-card {
            background: white;
            border-radius: 28px;
            padding: 40px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            animation: fadeInUp 0.5s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logout-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .logout-icon i {
            font-size: 40px;
            color: white;
        }
        
        h1 {
            font-size: 24px;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .message {
            color: #6b7280;
            font-size: 15px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #059669;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
            margin: 0 5px;
        }
        
        .btn:hover {
            background: #047857;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #059669;
            color: #059669;
        }
        
        .btn-outline:hover {
            background: #059669;
            color: white;
        }
        
        .countdown {
            margin-top: 20px;
            font-size: 13px;
            color: #9ca3af;
        }
        
        .countdown span {
            color: #059669;
            font-weight: 600;
        }
        
        .device-info {
            background: #f9fafb;
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
            font-size: 13px;
            color: #6b7280;
        }
        
        .device-info i {
            margin-left: 5px;
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="logout-card">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        
        <h1>تم تسجيل الخروج</h1>
        
        <div class="message">
            <i class="fas fa-info-circle" style="color: #f59e0b; margin-left: 5px;"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <div class="device-info">
            <i class="fas fa-shield-alt"></i>
            تم تسجيل الخروج من هذا الجهاز لحماية حسابك
        </div>
        
        <a href="<?php echo $redirect; ?>" class="btn">
            <i class="fas fa-sign-in-alt"></i>
            تسجيل الدخول مرة أخرى
        </a>
        
        <div class="countdown">
            سيتم توجيهك تلقائياً خلال <span id="countdown">5</span> ثواني
        </div>
    </div>
    
    <script>
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        const redirectUrl = '<?php echo $redirect; ?>';
        
        const interval = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = redirectUrl;
            }
        }, 1000);
    </script>
</body>
</html>