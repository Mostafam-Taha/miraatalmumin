<?php
require_once 'includes/session_config.php';

session_start();

// إذا كان المستخدم مسجلاً بالفعل، ارجع إلى الصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
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
    <link rel="stylesheet" href="assets/css/login.css">
    <title>تسجيل الدخول - مرآة المؤمن</title>
    <style>
        /* تحسينات إضافية للواجهة */
        .device-info {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            padding: 12px 15px;
            margin-top: 20px;
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .device-info i {
            color: #059669;
        }
        
        .device-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            background: #e2e8f0;
            border-radius: 20px;
            font-size: 11px;
        }
        
        .session-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #92400e;
            display: none;
        }
        
        .session-warning.show {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .session-warning i {
            font-size: 18px;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .secure-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(5, 150, 105, 0.1);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            color: #059669;
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="secure-badge">
            <i class="fas fa-shield-alt"></i>
            <span>تسجيل دخول آمن</span>
        </div>
        
        <div class="logo-container">
            <div class="logo-icon">
                <i class="fas fa-pray"></i>
            </div>
            <h1 class="app-title">مرآة المؤمن</h1>
            <p class="app-subtitle">سجل دخولك لمتابعة صلواتك اليومية ومراجعة تقدمك الروحي</p>
        </div>
        
        <!-- رسالة انتهاء الجلسة -->
        <div class="session-warning" id="sessionWarning">
            <i class="fas fa-clock"></i>
            <span>انتهت صلاحية الجلسة السابقة، يرجى تسجيل الدخول مرة أخرى</span>
        </div>
        
        <!-- معلومات الجهاز الحالي -->
        <div class="device-info" id="deviceInfo">
            <i class="fas fa-mobile-alt"></i>
            <span>جاري تحديد معلومات جهازك...</span>
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
            <div class="feature-item">
                <i class="fas fa-mobile-alt"></i>
                <span>إدارة الأجهزة المتصلة</span>
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
                <i class="fab fa-google"></i>
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
        </div>
        
        <!-- Email Login Section -->
        <div class="email-login-section">
            <h3 class="section-title">
                <i class="fas fa-envelope"></i>
                تسجيل الدخول باستخدام البريد الإلكتروني
            </h3>
            
            <button class="email-login-btn" onclick="openEmailModal('login')">
                <i class="fas fa-sign-in-alt"></i>
                تسجيل الدخول / إنشاء حساب
            </button>
            
            <p style="margin-top: 15px; color: var(--text-light); font-size: 14px;">
                <i class="fas fa-info-circle"></i>
                يمكنك استخدام البريد الإلكتروني وكلمة المرور
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
        </div>
    </div>

    <!-- نافذة تسجيل الدخول بالبريد -->
    <div class="modal-overlay" id="emailModal">
        <div class="email-modal">
            <div class="modal-header">
                <button class="modal-close" onclick="closeEmailModal()">&times;</button>
                <h2 id="modalTitle">تسجيل الدخول</h2>
                <p id="modalSubtitle">أدخل بياناتك للدخول إلى حسابك</p>
            </div>
            
            <div class="modal-body">
                <!-- رسالة النجاح/الخطأ -->
                <div class="form-message" id="formMessage"></div>
                
                <!-- نموذج تسجيل الدخول -->
                <div class="form-section active" id="loginForm">
                    <form id="loginFormElement" onsubmit="handleLogin(event)">
                        <div class="form-group">
                            <label for="loginEmail">
                                <i class="fas fa-envelope"></i> البريد الإلكتروني
                            </label>
                            <input type="email" 
                                   id="loginEmail" 
                                   class="form-input" 
                                   placeholder="example@email.com" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="loginPassword">
                                <i class="fas fa-lock"></i> كلمة المرور
                            </label>
                            <div class="password-container">
                                <input type="password" 
                                       id="loginPassword" 
                                       class="form-input" 
                                       placeholder="أدخل كلمة المرور" 
                                       required>
                                <button type="button" class="toggle-password" onclick="togglePassword('loginPassword')">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <label class="remember-me">
                                <input type="checkbox" id="rememberMe">
                                تذكرني
                            </label>
                            <a href="#" class="forgot-password" onclick="showForgotPassword()">
                                نسيت كلمة المرور؟
                            </a>
                        </div>
                        
                        <button type="submit" class="submit-btn" id="loginSubmitBtn">
                            <i class="fas fa-sign-in-alt"></i>
                            تسجيل الدخول
                        </button>
                    </form>
                    
                    <div class="form-switch">
                        <span class="switch-text">ليس لديك حساب؟</span>
                        <button class="switch-btn" onclick="showRegisterForm()">
                            إنشاء حساب جديد
                        </button>
                    </div>
                </div>
                
                <!-- نموذج إنشاء حساب -->
                <div class="form-section" id="registerForm">
                    <form id="registerFormElement" onsubmit="handleRegister(event)">
                        <div class="form-group">
                            <label for="registerName">
                                <i class="fas fa-user"></i> الاسم الكامل
                            </label>
                            <input type="text" 
                                   id="registerName" 
                                   class="form-input" 
                                   placeholder="أدخل اسمك الكامل" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="registerEmail">
                                <i class="fas fa-envelope"></i> البريد الإلكتروني
                            </label>
                            <input type="email" 
                                   id="registerEmail" 
                                   class="form-input" 
                                   placeholder="example@email.com" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="registerPassword">
                                <i class="fas fa-lock"></i> كلمة المرور
                            </label>
                            <div class="password-container">
                                <input type="password" 
                                       id="registerPassword" 
                                       class="form-input" 
                                       placeholder="كلمة مرور قوية (6 أحرف على الأقل)" 
                                       minlength="6" 
                                       required>
                                <button type="button" class="toggle-password" onclick="togglePassword('registerPassword')">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <small style="color: var(--text-light); font-size: 12px; display: block; margin-top: 5px;">
                                يجب أن تكون كلمة المرور 6 أحرف على الأقل
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="registerConfirmPassword">
                                <i class="fas fa-lock"></i> تأكيد كلمة المرور
                            </label>
                            <div class="password-container">
                                <input type="password" 
                                       id="registerConfirmPassword" 
                                       class="form-input" 
                                       placeholder="أعد إدخال كلمة المرور" 
                                       required>
                                <button type="button" class="toggle-password" onclick="togglePassword('registerConfirmPassword')">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="submit-btn" id="registerSubmitBtn">
                            <i class="fas fa-user-plus"></i>
                            إنشاء حساب جديد
                        </button>
                    </form>
                    
                    <div class="form-switch">
                        <span class="switch-text">لديك حساب بالفعل؟</span>
                        <button class="switch-btn" onclick="showLoginForm()">
                            تسجيل الدخول
                        </button>
                    </div>
                </div>
                
                <!-- نموذج استعادة كلمة المرور -->
                <div class="form-section" id="forgotPasswordForm">
                    <form id="forgotPasswordFormElement" onsubmit="handleForgotPassword(event)">
                        <div class="form-group">
                            <label for="forgotEmail">
                                <i class="fas fa-envelope"></i> البريد الإلكتروني
                            </label>
                            <input type="email" 
                                   id="forgotEmail" 
                                   class="form-input" 
                                   placeholder="أدخل بريدك الإلكتروني" 
                                   required>
                        </div>
                        
                        <p style="color: var(--text-light); font-size: 14px; margin-bottom: 20px;">
                            <i class="fas fa-info-circle"></i>
                            سنرسل لك رابطاً لإعادة تعيين كلمة المرور
                        </p>
                        
                        <button type="submit" class="submit-btn" id="forgotSubmitBtn">
                            <i class="fas fa-paper-plane"></i>
                            إرسال رابط الاستعادة
                        </button>
                    </form>
                    
                    <div class="form-switch">
                        <button class="switch-btn" onclick="showLoginForm()">
                            <i class="fas fa-arrow-right"></i>
                            العودة لتسجيل الدخول
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        // متغيرات عامة
        let currentForm = 'login';
        let deviceInfo = {};
        
        // الحصول على معلومات الجهاز
        function getDeviceInfo() {
            const userAgent = navigator.userAgent;
            let deviceType = 'web';
            let os = 'Unknown';
            let browser = 'Unknown';
            
            // تحديد نوع الجهاز
            if (/android/i.test(userAgent)) {
                deviceType = 'android';
                os = 'Android';
            } else if (/iphone|ipad|ipod/i.test(userAgent)) {
                deviceType = 'ios';
                os = 'iOS';
            } else if (/windows|mac|linux/i.test(userAgent)) {
                deviceType = 'desktop';
                if (/windows/i.test(userAgent)) os = 'Windows';
                else if (/mac/i.test(userAgent)) os = 'macOS';
                else if (/linux/i.test(userAgent)) os = 'Linux';
            }
            
            // تحديد المتصفح
            if (/chrome|chromium/i.test(userAgent) && !/edg/i.test(userAgent)) {
                browser = 'Chrome';
            } else if (/firefox|fxios/i.test(userAgent)) {
                browser = 'Firefox';
            } else if (/safari/i.test(userAgent) && !/chrome/i.test(userAgent)) {
                browser = 'Safari';
            } else if (/edg/i.test(userAgent)) {
                browser = 'Edge';
            } else if (/opera|opr/i.test(userAgent)) {
                browser = 'Opera';
            }
            
            // الحصول على الشاشة
            const screenSize = `${screen.width}x${screen.height}`;
            
            // الحصول على المنطقة الزمنية
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            
            deviceInfo = {
                device_type: deviceType,
                os: os,
                browser: browser,
                screen_size: screenSize,
                timezone: timezone,
                language: navigator.language,
                user_agent: userAgent
            };
            
            // تحديث عرض معلومات الجهاز
            updateDeviceInfoDisplay();
        }
        
        // تحديث عرض معلومات الجهاز
        function updateDeviceInfoDisplay() {
            const deviceInfoDiv = document.getElementById('deviceInfo');
            if (deviceInfoDiv) {
                const icons = {
                    'android': '<i class="fab fa-android"></i>',
                    'ios': '<i class="fab fa-apple"></i>',
                    'desktop': '<i class="fas fa-desktop"></i>',
                    'web': '<i class="fas fa-globe"></i>'
                };
                const icon = icons[deviceInfo.device_type] || icons.web;
                
                deviceInfoDiv.innerHTML = `
                    ${icon}
                    <span>${deviceInfo.os} | ${deviceInfo.browser}</span>
                    <span class="device-badge">
                        <i class="fas fa-fingerprint"></i>
                        تم التعرف على جهازك
                    </span>
                `;
            }
        }
        
        // التحقق من وجود جلسة سابقة
        function checkPreviousSession() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('session_expired') === '1') {
                const sessionWarning = document.getElementById('sessionWarning');
                sessionWarning.classList.add('show');
                setTimeout(() => {
                    sessionWarning.classList.remove('show');
                }, 5000);
            }
        }
        
        // دالة التعامل مع تسجيل الدخول بالجوجل
        function handleGoogleLogin(response) {
            showLoading();
            
            // إرسال معلومات الجهاز مع طلب تسجيل الدخول
            const loginData = {
                credential: response.credential,
                device_info: deviceInfo
            };
            
            fetch('includes/save_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(loginData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage();
                    setTimeout(() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const redirect = urlParams.get('redirect');
                        window.location.href = redirect || 'index.php';
                    }, 1500);
                } else {
                    hideLoading();
                    if (data.devices_count) {
                        // إذا كان هناك أجهزة متعددة، عرض خيار تسجيل الخروج
                        showMultipleDevicesWarning(data.devices_count, data.message);
                    } else {
                        showErrorMessage(data.message || 'حدث خطأ في التسجيل');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideLoading();
                showErrorMessage('حدث خطأ أثناء الاتصال بالخادم');
            });
        }
        
        // عرض تحذير الأجهزة المتعددة
        function showMultipleDevicesWarning(devicesCount, message) {
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = `
                position: fixed;
                top: 20px;
                left: 20px;
                background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
                color: #92400e;
                padding: 20px;
                border-radius: var(--radius-md);
                border-right: 4px solid #f59e0b;
                z-index: 1000;
                animation: slideIn 0.3s ease;
                max-width: 400px;
                box-shadow: var(--shadow-lg);
            `;
            errorDiv.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
                    <div>
                        <h4 style="margin: 0 0 5px 0; font-weight: 700;">تنبيه: أجهزة متعددة</h4>
                        <p style="margin: 0;">${message || 'لديك ${devicesCount} أجهزة متصلة أخرى'}</p>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button onclick="handleForceLogin()" style="flex: 1; padding: 8px; background: #f59e0b; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        تسجيل الدخول وتسجيل خروج الآخرين
                    </button>
                    <button onclick="this.parentElement.parentElement.remove()" style="flex: 1; padding: 8px; background: #e5e7eb; border: none; border-radius: 8px; cursor: pointer;">
                        إلغاء
                    </button>
                </div>
            `;
            document.body.appendChild(errorDiv);
            
            // حفظ بيانات الاعتماد للمحاولة مرة أخرى
            window.pendingGoogleCredential = window.pendingGoogleCredential || null;
        }
        
        // معالجة تسجيل الدخول القسري (تسجيل خروج الأجهزة الأخرى)
        function handleForceLogin() {
            showLoading();
            
            fetch('includes/save_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    credential: window.pendingGoogleCredential,
                    device_info: deviceInfo,
                    force_login: true
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage();
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    hideLoading();
                    showErrorMessage(data.message || 'حدث خطأ');
                }
            })
            .catch(error => {
                hideLoading();
                showErrorMessage('حدث خطأ');
            });
        }
        
        // فتح نافذة البريد
        function openEmailModal(formType = 'login') {
            document.getElementById('emailModal').style.display = 'flex';
            showForm(formType);
        }
        
        // إغلاق نافذة البريد
        function closeEmailModal() {
            document.getElementById('emailModal').style.display = 'none';
            resetForms();
        }
        
        // إظهار النموذج المطلوب
        function showForm(formType) {
            document.querySelectorAll('.form-section').forEach(form => {
                form.classList.remove('active');
            });
            
            document.getElementById(formType + 'Form').classList.add('active');
            
            const titles = {
                'login': { title: 'تسجيل الدخول', subtitle: 'أدخل بياناتك للدخول إلى حسابك' },
                'register': { title: 'إنشاء حساب جديد', subtitle: 'املأ البيانات التالية لإنشاء حساب' },
                'forgotPassword': { title: 'استعادة كلمة المرور', subtitle: 'أدخل بريدك الإلكتروني لاستعادة الحساب' }
            };
            
            document.getElementById('modalTitle').textContent = titles[formType].title;
            document.getElementById('modalSubtitle').textContent = titles[formType].subtitle;
            
            currentForm = formType;
            hideFormMessage();
        }
        
        function showRegisterForm() { showForm('register'); }
        function showLoginForm() { showForm('login'); }
        function showForgotPassword() { showForm('forgotPassword'); }
        
        function resetForms() {
            document.getElementById('loginFormElement').reset();
            document.getElementById('registerFormElement').reset();
            document.getElementById('forgotPasswordFormElement').reset();
            hideFormMessage();
            showForm('login');
        }
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function showFormMessage(message, type = 'error') {
            const messageDiv = document.getElementById('formMessage');
            messageDiv.textContent = message;
            messageDiv.className = `form-message ${type}`;
            messageDiv.style.display = 'block';
            
            if (type === 'success') {
                setTimeout(hideFormMessage, 5000);
            }
        }
        
        function hideFormMessage() {
            document.getElementById('formMessage').style.display = 'none';
        }
        
        // معالجة تسجيل الدخول
        async function handleLogin(event) {
            event.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const rememberMe = document.getElementById('rememberMe').checked;
            
            if (!email || !password) {
                showFormMessage('يرجى ملء جميع الحقول', 'error');
                return;
            }
            
            const submitBtn = document.getElementById('loginSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحقق...';
            
            try {
                const response = await fetch('api/email_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email: email,
                        password: password,
                        remember: rememberMe,
                        device_info: deviceInfo
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showFormMessage('تم تسجيل الدخول بنجاح!', 'success');
                    setTimeout(() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const redirect = urlParams.get('redirect');
                        window.location.href = redirect || 'index.php';
                    }, 1500);
                } else {
                    if (data.devices_count) {
                        showFormMessage(data.message + ' الرجاء محاولة تسجيل الدخول مرة أخرى.', 'error');
                    } else {
                        showFormMessage(data.message || 'فشل تسجيل الدخول', 'error');
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> تسجيل الدخول';
                }
            } catch (error) {
                console.error('Error:', error);
                showFormMessage('حدث خطأ في الاتصال بالخادم', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> تسجيل الدخول';
            }
        }
        
        // معالجة إنشاء حساب
        async function handleRegister(event) {
            event.preventDefault();
            
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('registerConfirmPassword').value;
            
            if (!name || !email || !password || !confirmPassword) {
                showFormMessage('يرجى ملء جميع الحقول', 'error');
                return;
            }
            
            if (password.length < 6) {
                showFormMessage('كلمة المرور يجب أن تكون 6 أحرف على الأقل', 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                showFormMessage('كلمة المرور وتأكيدها غير متطابقين', 'error');
                return;
            }
            
            const submitBtn = document.getElementById('registerSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري إنشاء الحساب...';
            
            try {
                const response = await fetch('api/email_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'register',
                        name: name,
                        email: email,
                        password: password,
                        device_info: deviceInfo
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showFormMessage('تم إنشاء الحساب بنجاح! يمكنك تسجيل الدخول الآن.', 'success');
                    setTimeout(() => {
                        showLoginForm();
                        document.getElementById('loginEmail').value = email;
                        document.getElementById('loginPassword').value = password;
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> إنشاء حساب جديد';
                    }, 2000);
                } else {
                    showFormMessage(data.message || 'فشل إنشاء الحساب', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> إنشاء حساب جديد';
                }
            } catch (error) {
                console.error('Error:', error);
                showFormMessage('حدث خطأ في الاتصال بالخادم', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-user-plus"></i> إنشاء حساب جديد';
            }
        }
        
        // معالجة استعادة كلمة المرور
        async function handleForgotPassword(event) {
            event.preventDefault();
            
            const email = document.getElementById('forgotEmail').value;
            
            if (!email) {
                showFormMessage('يرجى إدخال البريد الإلكتروني', 'error');
                return;
            }
            
            const submitBtn = document.getElementById('forgotSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';
            
            try {
                const response = await fetch('api/email_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'forgot_password',
                        email: email
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showFormMessage(data.message || 'تم إرسال رابط الاستعادة إلى بريدك الإلكتروني', 'success');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> إرسال رابط الاستعادة';
                    setTimeout(showLoginForm, 3000);
                } else {
                    showFormMessage(data.message || 'حدث خطأ أثناء إرسال رابط الاستعادة', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> إرسال رابط الاستعادة';
                }
            } catch (error) {
                console.error('Error:', error);
                showFormMessage('حدث خطأ في الاتصال بالخادم', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> إرسال رابط الاستعادة';
            }
        }
        
        // إغلاق النافذة بالضغط على ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeEmailModal();
            }
        });
        
        // إغلاق النافذة بالضغط خارجها
        document.getElementById('emailModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeEmailModal();
            }
        });
        
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }
        
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }
        
        function showSuccessMessage() {
            const loadingDiv = document.getElementById('loading');
            loadingDiv.innerHTML = `
                <div class="loading-content" style="color: var(--primary-color);">
                    <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                    <div class="loading-text">تم تسجيل الدخول بنجاح! سيتم توجيهك الآن...</div>
                </div>
            `;
        }
        
        function showErrorMessage(message) {
            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = `
                position: fixed;
                top: 20px;
                left: 20px;
                background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
                color: #dc2626;
                padding: 20px;
                border-radius: 12px;
                border-right: 4px solid #dc2626;
                z-index: 1000;
                animation: slideIn 0.3s ease;
                max-width: 400px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                display: flex;
                align-items: center;
                gap: 15px;
            `;
            errorDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle" style="font-size: 24px;"></i>
                <div>
                    <h4 style="margin: 0 0 5px 0; font-weight: 700;">خطأ</h4>
                    <p style="margin: 0;">${message}</p>
                </div>
            `;
            document.body.appendChild(errorDiv);
            
            setTimeout(() => {
                errorDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    if (errorDiv.parentElement) errorDiv.remove();
                }, 300);
            }, 5000);
        }
        
        // إضافة الأنيميشن
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
        
        // تهيئة الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            getDeviceInfo();
            checkPreviousSession();
        });
    </script>
</body>
</html>