<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // إذا كان المستخدم مسجلاً بالفعل، ارجع إلى الصفحة الرئيسية
    if (isset($_GET['redirect'])) {
        header('Location: ' . $_GET['redirect']);
    } else {
        header('Location: index.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>تسجيل الدخول - متابعة الصلوات</title>
    <style>
        :root {
            --primary-color: #059669;
            --primary-light: #d1fae5;
            --primary-dark: #047857;
            --text-color: #111827;
            --text-light: #6b7280;
            --light-bg: #f9fafb;
            --border-color: #e5e7eb;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --info-color: #3b82f6;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--text-color);
            font-family: 'Tajawal', 'Cairo', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(5, 150, 105, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(217, 119, 6, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-xl);
            padding: 50px 40px;
            box-shadow: var(--shadow-lg);
            text-align: center;
            max-width: 480px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            margin-bottom: 30px;
            position: relative;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 32px;
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .app-title {
            color: var(--primary-dark);
            margin-bottom: 10px;
            font-size: 32px;
            font-weight: 800;
            font-family: 'Cairo', sans-serif;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .app-subtitle {
            color: var(--text-light);
            margin-bottom: 40px;
            font-size: 16px;
            line-height: 1.6;
            max-width: 320px;
            margin-left: auto;
            margin-right: auto;
        }

        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
        }

        .feature-item {
            background: var(--primary-light);
            padding: 10px 15px;
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--primary-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(5, 150, 105, 0.2);
        }

        .feature-item i {
            font-size: 16px;
        }

        .google-section {
            background: var(--light-bg);
            padding: 30px;
            border-radius: var(--radius-lg);
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }

        .section-title {
            color: var(--text-color);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: 'Cairo', sans-serif;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .google-btn-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .g_id_signin {
            margin: 0 auto;
        }

        .alternative-login {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid var(--border-color);
        }

        .alternative-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 15px 25px;
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-color);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            margin-top: 10px;
            width: 100%;
            max-width: 300px;
        }

        .alternative-btn:hover {
            background: var(--light-bg);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }

        .alternative-btn i {
            color: var(--primary-color);
            font-size: 18px;
        }

        .loading {
            display: none;
            margin-top: 30px;
            padding: 20px;
            background: var(--light-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        .loading-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .spinner {
            width: 24px;
            height: 24px;
            border: 3px solid var(--border-color);
            border-top-color: var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: var(--text-color);
            font-weight: 600;
        }

        .privacy-notice {
            margin-top: 25px;
            padding: 15px;
            background: var(--light-bg);
            border-radius: var(--radius-md);
            font-size: 13px;
            color: var(--text-light);
            line-height: 1.6;
        }

        .privacy-notice a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .privacy-notice a:hover {
            text-decoration: underline;
        }

        .guest-info {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-light) 0%, rgba(5, 150, 105, 0.1) 100%);
            border-radius: var(--radius-md);
            border-right: 4px solid var(--primary-color);
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .guest-title {
            color: var(--primary-dark);
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .guest-text {
            color: var(--text-color);
            font-size: 14px;
            line-height: 1.6;
        }

        /* One Tap Prompt Styling */
        .g_id_onload {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .login-container {
                padding: 40px 25px;
                margin: 20px;
            }
            
            .app-title {
                font-size: 28px;
            }
            
            .features {
                gap: 10px;
            }
            
            .feature-item {
                padding: 8px 12px;
                font-size: 13px;
            }
            
            .google-section {
                padding: 25px 20px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .login-container {
                padding: 35px 20px;
            }
            
            .logo-icon {
                width: 70px;
                height: 70px;
                font-size: 28px;
            }
            
            .app-title {
                font-size: 24px;
            }
            
            .app-subtitle {
                font-size: 15px;
            }
            
            .feature-item {
                font-size: 12px;
                padding: 6px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo-icon">
                <i class="fas fa-pray"></i>
            </div>
            <h1 class="app-title">مرآة المؤمن</h1>
            <p class="app-subtitle">سجل دخولك لمتابعة صلواتك اليومية ومراجعة تقدمك الروحي</p>
        </div>
        
        <div class="features">
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <span>تتبع الصلوات اليومية</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-chart-line"></i>
                <span>إحصائيات مفصلة</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-users"></i>
                <span>مجموعات داعمة</span>
            </div>
        </div>
        
        <!-- One Tap Sign In -->
        <div id="g_id_onload"
             data-client_id="939332338996-875ui8gonoocvr3msupiis6e7ab2oq9t.apps.googleusercontent.com"
             data-context="signin"
             data-ux_mode="popup"
             data-login_uri="<?php echo (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/includes/save_user.php'; ?>"
             data-auto_prompt="true"
             data-callback="handleGoogleLogin"
             data-itp_support="true">
        </div>

        <!-- Google Sign In Button -->
        <div class="google-section">
            <h3 class="section-title">
                <i class="fas fa-sign-in-alt"></i>
                تسجيل الدخول باستخدام Google
            </h3>
            
            <div class="google-btn-container">
                <div class="g_id_signin"
                     data-type="standard"
                     data-shape="pill"
                     data-theme="outline"
                     data-text="signin_with"
                     data-size="large"
                     data-logo_alignment="left"
                     data-width="300">
                </div>
            </div>
            
            <div class="alternative-login">
                <button class="alternative-btn" onclick="showOneTap()">
                    <i class="fas fa-bolt"></i>
                    تسجيل سريع (One Tap)
                </button>
            </div>
        </div>
        
        <!-- Guest Information -->
        <div class="guest-info">
            <h4 class="guest-title">
                <i class="fas fa-info-circle"></i>
                لماذا التسجيل باستخدام Google؟
            </h4>
            <p class="guest-text">
                • تسجيل آمن وسريع بدون كلمات مرور<br>
                • بيانات محمية وفق معايير Google<br>
                • سهولة استعادة الحساب عند الحاجة<br>
                • تجربة مستخدم أفضل وأسرع
            </p>
        </div>
        
        <!-- Loading State -->
        <div class="loading" id="loading">
            <div class="loading-content">
                <div class="spinner"></div>
                <div class="loading-text">جاري تسجيل الدخول، يرجى الانتظار...</div>
            </div>
        </div>
        
        <!-- Privacy Notice -->
        <div class="privacy-notice">
            <i class="fas fa-shield-alt" style="margin-left: 5px;"></i>
            باستخدامك للتطبيق، فإنك توافق على 
            <a href="privacy.php">سياسة الخصوصية</a> 
            و 
            <a href="terms.php">شروط الخدمة</a>
        </div>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        // دالة التعامل مع تسجيل الدخول
        function handleGoogleLogin(response) {
            showLoading();
            
            fetch('includes/save_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    credential: response.credential
                })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    // عرض رسالة نجاح
                    showSuccessMessage();
                    
                    // التوجيه بعد فترة قصيرة
                    setTimeout(() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const redirect = urlParams.get('redirect');
                        
                        if (redirect) {
                            window.location.href = redirect;
                        } else {
                            window.location.href = 'index.php';
                        }
                    }, 1500);
                } else {
                    hideLoading();
                    showErrorMessage(data.message || 'حدث خطأ في التسجيل');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                showErrorMessage('حدث خطأ أثناء الاتصال بالخادم');
            });
        }

        // دالة لعرض One Tap يدويًا
        function showOneTap() {
            google.accounts.id.prompt((notification) => {
                if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
                    // يمكن عرض زر تسجيل الدخول التقليدي هنا
                    alert('للتسجيل السريع، تأكد من السماح لـ Google One Tap في متصفحك.');
                }
            });
        }

        // دالة عرض حالة التحميل
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        // دالة لعرض رسالة النجاح
        function showSuccessMessage() {
            const loadingDiv = document.getElementById('loading');
            loadingDiv.innerHTML = `
                <div class="loading-content" style="color: var(--primary-color);">
                    <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                    <div class="loading-text">تم تسجيل الدخول بنجاح! سيتم توجيهك الآن...</div>
                </div>
            `;
        }

        // دالة لعرض رسالة الخطأ
        function showErrorMessage(message) {
            // إنشاء عنصر الرسالة
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = `
                position: fixed;
                top: 20px;
                left: 20px;
                background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
                color: var(--danger-color);
                padding: 20px;
                border-radius: var(--radius-md);
                border-right: 4px solid var(--danger-color);
                z-index: 1000;
                animation: slideIn 0.3s ease;
                max-width: 400px;
                box-shadow: var(--shadow-lg);
                display: flex;
                align-items: center;
                gap: 15px;
            `;
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
                <div>
                    <h4 style="margin: 0 0 5px 0; font-weight: 700;">خطأ في التسجيل</h4>
                    <p style="margin: 0;">${message}</p>
                </div>
            `;
            document.body.appendChild(errorDiv);
            
            // إزالة الرسالة بعد 5 ثواني
            setTimeout(() => {
                errorDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(errorDiv);
                }, 300);
            }, 5000);
        }

        // إضافة أنيميشن للرسائل
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(-100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(-100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // تهيئة One Tap عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            // يمكن إضافة أي تهيئات إضافية هنا
            
            // تتبع أحداث One Tap
            google.accounts.id.initialize({
                client_id: '939332338996-875ui8gonoocvr3msupiis6e7ab2oq9t.apps.googleusercontent.com',
                callback: handleGoogleLogin,
                context: 'signin',
                ux_mode: 'popup',
                auto_select: false,
                itp_support: true
            });
            
            // محاولة عرض One Tap تلقائيًا للمستخدمين المناسبين
            google.accounts.id.prompt((notification) => {
                if (notification.isDisplayed()) {
                    console.log('One Tap is displayed');
                }
                if (notification.isNotDisplayed()) {
                    console.log('One Tap was not displayed');
                }
                if (notification.isSkippedMoment()) {
                    console.log('One Tap was skipped');
                }
                if (notification.isDismissedMoment()) {
                    console.log('One Tap was dismissed');
                }
            });
        });
    </script>
</body>
</html>