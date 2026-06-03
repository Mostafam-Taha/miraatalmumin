<?php
// ==============================================
// File: admin_login.php
// Admin Authentication System - Fixed Version
// ==============================================

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// ── Database Configuration ─────────────────────────────────────────────────
$host     = 'sql207.infinityfree.com';
$dbname   = 'if0_39304815_miraatalmuminif';
$username = 'if0_39304815';
$password = 'NIHOIGYPkLGsq0';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ── Create Admins Table if Not Exists ─────────────────────────────────────
// First, check if table exists and create it with correct structure
$createTableSQL = "
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin', 'moderator') DEFAULT 'admin',
    avatar VARCHAR(255) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin_id (admin_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($createTableSQL);
} catch (PDOException $e) {
    // Table might exist with different structure, try to alter it
    die("Table creation error: " . $e->getMessage());
}

// Check if table is empty and create default admin
$stmt = $pdo->query("SELECT COUNT(*) FROM admins");
$adminCount = $stmt->fetchColumn();

if ($adminCount == 0) {
    // Create default superadmin
    $defaultAdminId = 'admin';
    $defaultName = 'Super Administrator';
    $defaultEmail = 'admin@miraat-almumin.com';
    $defaultPassword = password_hash('Admin@2025', PASSWORD_DEFAULT);
    $defaultRole = 'superadmin';
    
    $insertStmt = $pdo->prepare("INSERT INTO admins (admin_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->execute([$defaultAdminId, $defaultName, $defaultEmail, $defaultPassword, $defaultRole]);
}

// ── Theme Colors ──────────────────────────────────────────────────────────
$btn_color   = '#059669';
$sec_color   = '#047857';
$text_color  = '#111827';

// ── AJAX Handlers ─────────────────────────────────────────────────────────
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    // ── LOGIN ACTION ──────────────────────────────────────────────────────
    if ($_POST['action'] === 'login') {
        $admin_id = trim($_POST['admin_id'] ?? '');
        $pass     = trim($_POST['password']  ?? '');

        if (!$admin_id || !$pass) {
            echo json_encode(['success' => false, 'message' => 'Please fill all fields / يرجى ملء جميع الحقول']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ? OR email = ? LIMIT 1");
        $stmt->execute([$admin_id, $admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($pass, $admin['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid admin ID or password / معرّف الإدارة أو كلمة المرور غير صحيحة']);
            exit;
        }

        // Update last login
        $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
        $updateStmt->execute([$admin['id']]);

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $admin['id'];
        $_SESSION['admin_uid']       = $admin['admin_id'];
        $_SESSION['admin_name']      = $admin['name'];
        $_SESSION['admin_email']     = $admin['email'];
        $_SESSION['admin_role']      = $admin['role'];

        echo json_encode(['success' => true, 'redirect' => 'admin_dashboard.php']);
        exit;
    }

    // ── REGISTER ACTION ───────────────────────────────────────────────────
    if ($_POST['action'] === 'register') {
        $admin_id = trim($_POST['admin_id'] ?? '');
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $pass     = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm']  ?? '');
        $secret   = trim($_POST['secret']   ?? '');
        $role     = trim($_POST['role']     ?? 'admin');

        // Simple secret key to prevent random registrations
        if ($secret !== 'MIRAAT_ADMIN_2025') {
            echo json_encode(['success' => false, 'message' => 'Invalid secret key / مفتاح التسجيل السري غير صحيح']);
            exit;
        }

        // Validation
        if (!$admin_id || !$name || !$email || !$pass || !$confirm) {
            echo json_encode(['success' => false, 'message' => 'Please fill all fields / يرجى ملء جميع الحقول']);
            exit;
        }

        // Validate admin_id format
        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $admin_id)) {
            echo json_encode(['success' => false, 'message' => 'Admin ID: 4-20 characters (letters, numbers, underscore) only']);
            exit;
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }

        // Validate password
        if (strlen($pass) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
            exit;
        }

        if ($pass !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
            exit;
        }

        // Check for duplicates
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE admin_id = ? OR email = ?");
        $stmt->execute([$admin_id, $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Admin ID or email already exists']);
            exit;
        }

        // Validate role
        $allowedRoles = ['superadmin', 'admin', 'moderator'];
        if (!in_array($role, $allowedRoles)) {
            $role = 'admin';
        }

        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare("INSERT INTO admins (admin_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $insertStmt->execute([$admin_id, $name, $email, $hashed, $role]);

        echo json_encode(['success' => true, 'message' => 'Account created successfully! You can now login']);
        exit;
    }
}

// Redirect if already logged in
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$page = $_GET['page'] ?? 'login';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page === 'register' ? 'إنشاء حساب إداري' : 'تسجيل دخول الإدارة'; ?> | مرآة المؤمن</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .bg-animation .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            animation: float 20s infinite ease-in-out;
        }

        .shape-1 { width: 300px; height: 300px; top: -100px; right: -100px; animation-delay: 0s; }
        .shape-2 { width: 200px; height: 200px; bottom: 50px; left: -80px; animation-delay: -5s; }
        .shape-3 { width: 150px; height: 150px; bottom: 30%; right: 20%; animation-delay: -10s; }
        .shape-4 { width: 400px; height: 400px; top: 40%; left: -150px; animation-delay: -15s; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        /* Auth Card */
        .auth-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .auth-card:hover {
            transform: translateY(-5px);
        }

        /* Header */
        .auth-header {
            background: linear-gradient(135deg, <?php echo $sec_color; ?>, <?php echo $btn_color; ?>);
            padding: 32px 32px 28px;
            text-align: center;
            color: white;
        }

        .logo-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 36px;
            backdrop-filter: blur(4px);
        }

        .auth-header h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .auth-header p {
            font-size: 13px;
            opacity: 0.85;
        }

        /* Tabs */
        .auth-tabs {
            display: flex;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .tab-btn {
            flex: 1;
            padding: 16px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            border-bottom: 3px solid transparent;
        }

        .tab-btn i {
            margin-left: 8px;
        }

        .tab-btn:hover {
            color: <?php echo $btn_color; ?>;
            background: #f1f5f9;
        }

        .tab-btn.active {
            color: <?php echo $btn_color; ?>;
            border-bottom-color: <?php echo $btn_color; ?>;
            background: white;
        }

        /* Form Body */
        .auth-body {
            padding: 32px;
        }

        .alert-message {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .alert-message.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            display: flex;
        }

        .alert-message.success {
            background: #ecfdf5;
            color: <?php echo $btn_color; ?>;
            border: 1px solid #a7f3d0;
            display: flex;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-label i {
            color: <?php echo $btn_color; ?>;
            font-size: 12px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px 42px 12px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            color: #1e293b;
            background: #fafbfc;
            transition: all 0.3s;
            outline: none;
        }

        .form-input:focus {
            border-color: <?php echo $btn_color; ?>;
            background: white;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .form-input.ltr {
            direction: ltr;
            text-align: left;
        }

        .toggle-password {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            font-size: 14px;
        }

        .toggle-password:hover {
            color: <?php echo $btn_color; ?>;
        }

        .input-hint {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 6px;
        }

        .secret-note {
            background: #fefce8;
            border: 1px solid #fde047;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 12px;
            color: #854d0e;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .secret-note i {
            margin-top: 2px;
            flex-shrink: 0;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, <?php echo $sec_color; ?>, <?php echo $btn_color; ?>);
            color: white;
            border: none;
            border-radius: 14px;
            font-family: 'Tajawal', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            margin-top: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .role-select {
            cursor: pointer;
        }

        hr {
            margin: 20px 0;
            border: none;
            border-top: 1px solid #e2e8f0;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }

        .footer-note a {
            color: <?php echo $btn_color; ?>;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="bg-animation">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
</div>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo-wrapper">
                <i class="fas fa-mosque"></i>
            </div>
            <h1>مرآة المؤمن</h1>
            <p><?php echo $page === 'register' ? 'إنشاء حساب إداري جديد' : 'لوحة تحكم الإدارة'; ?></p>
        </div>

        <div class="auth-tabs">
            <a href="?page=login" class="tab-btn <?php echo $page === 'login' ? 'active' : ''; ?>">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </a>
            <a href="?page=register" class="tab-btn <?php echo $page === 'register' ? 'active' : ''; ?>">
                <i class="fas fa-user-plus"></i> إنشاء حساب
            </a>
        </div>

        <div class="auth-body">
            <div id="alertMessage" class="alert-message">
                <i class="fas fa-circle-exclamation"></i>
                <span id="alertText"></span>
            </div>

            <?php if ($page === 'login'): ?>
            <!-- LOGIN FORM -->
            <form id="loginForm" onsubmit="event.preventDefault(); doLogin();">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-id-card"></i> معرّف الإدارة أو البريد الإلكتروني
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-input ltr" id="login_username" 
                               placeholder="admin_id أو email@example.com" autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> كلمة المرور
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" class="form-input ltr" id="login_password" 
                               placeholder="••••••••" autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePassword('login_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="loginBtn">
                    <i class="fas fa-right-to-bracket"></i> دخول إلى لوحة التحكم
                </button>
            </form>

            <hr>
            <div class="footer-note">
                الحساب الافتراضي: <strong>admin</strong> | كلمة المرور: <strong>Admin@2025</strong>
            </div>

            <?php else: ?>
            <!-- REGISTER FORM -->
            <div class="secret-note">
                <i class="fas fa-shield-alt"></i>
                <span>إنشاء الحسابات الإدارية يتطلب <strong>مفتاح سري</strong> لمنع الوصول غير المصرح به.</span>
            </div>

            <form id="registerForm" onsubmit="event.preventDefault(); doRegister();">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-id-badge"></i> معرّف الإدارة (admin_id)
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-at input-icon"></i>
                        <input type="text" class="form-input ltr" id="reg_admin_id" 
                               placeholder="مثال: admin_ali" autocomplete="off">
                    </div>
                    <div class="input-hint">4-20 حرف، أحرف إنجليزية وأرقام وشرطة سفلية فقط</div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> الاسم الكامل
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" class="form-input" id="reg_name" placeholder="الاسم الكامل للمدير">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i> البريد الإلكتروني
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" class="form-input ltr" id="reg_email" placeholder="admin@example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user-shield"></i> الصلاحية
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-shield input-icon"></i>
                        <select class="form-input role-select" id="reg_role">
                            <option value="admin">مدير (Admin)</option>
                            <option value="moderator">مراقب (Moderator)</option>
                            <option value="superadmin">مشرف عام (Super Admin)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> كلمة المرور
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" class="form-input ltr" id="reg_password" placeholder="8 أحرف على الأقل">
                        <button type="button" class="toggle-password" onclick="togglePassword('reg_password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="input-hint">يجب أن تحتوي على حرف كبير ورقم على الأقل</div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> تأكيد كلمة المرور
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" class="form-input ltr" id="reg_confirm" placeholder="••••••••">
                        <button type="button" class="toggle-password" onclick="togglePassword('reg_confirm', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-key"></i> المفتاح السري
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-shield input-icon"></i>
                        <input type="password" class="form-input ltr" id="reg_secret" placeholder="أدخل المفتاح السري">
                        <button type="button" class="toggle-password" onclick="togglePassword('reg_secret', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="registerBtn">
                    <i class="fas fa-user-plus"></i> إنشاء الحساب الإداري
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Show alert message
function showAlert(message, type = 'error') {
    const alertDiv = document.getElementById('alertMessage');
    const alertText = document.getElementById('alertText');
    
    alertDiv.className = `alert-message ${type}`;
    alertText.textContent = message;
    
    // Auto hide after 5 seconds for success messages
    if (type === 'success') {
        setTimeout(() => {
            alertDiv.style.display = 'none';
        }, 5000);
    }
}

// Login function
async function doLogin() {
    const username = document.getElementById('login_username').value.trim();
    const password = document.getElementById('login_password').value.trim();
    const btn = document.getElementById('loginBtn');
    
    if (!username || !password) {
        showAlert('يرجى ملء جميع الحقول');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحقق...';
    
    try {
        const formData = new URLSearchParams();
        formData.append('ajax', 'true');
        formData.append('action', 'login');
        formData.append('admin_id', username);
        formData.append('password', password);
        
        const response = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('تم تسجيل الدخول بنجاح! جاري التحويل...', 'success');
            btn.innerHTML = '<i class="fas fa-check"></i> تم التحقق';
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1000);
        } else {
            showAlert(data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-right-to-bracket"></i> دخول إلى لوحة التحكم';
        }
    } catch (error) {
        console.error('Login error:', error);
        showAlert('خطأ في الاتصال بالخادم');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-right-to-bracket"></i> دخول إلى لوحة التحكم';
    }
}

// Register function
async function doRegister() {
    const admin_id = document.getElementById('reg_admin_id').value.trim();
    const name = document.getElementById('reg_name').value.trim();
    const email = document.getElementById('reg_email').value.trim();
    const password = document.getElementById('reg_password').value.trim();
    const confirm = document.getElementById('reg_confirm').value.trim();
    const secret = document.getElementById('reg_secret').value.trim();
    const role = document.getElementById('reg_role').value;
    const btn = document.getElementById('registerBtn');
    
    if (!admin_id || !name || !email || !password || !confirm || !secret) {
        showAlert('يرجى ملء جميع الحقول');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري إنشاء الحساب...';
    
    try {
        const formData = new URLSearchParams();
        formData.append('ajax', 'true');
        formData.append('action', 'register');
        formData.append('admin_id', admin_id);
        formData.append('name', name);
        formData.append('email', email);
        formData.append('password', password);
        formData.append('confirm', confirm);
        formData.append('secret', secret);
        formData.append('role', role);
        
        const response = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            btn.innerHTML = '<i class="fas fa-check"></i> تم الإنشاء بنجاح';
            setTimeout(() => {
                window.location.href = '?page=login';
            }, 2000);
        } else {
            showAlert(data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-plus"></i> إنشاء الحساب الإداري';
        }
    } catch (error) {
        console.error('Register error:', error);
        showAlert('خطأ في الاتصال بالخادم');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-plus"></i> إنشاء الحساب الإداري';
    }
}

// Enter key support
document.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        <?php if ($page === 'login'): ?>
        doLogin();
        <?php else: ?>
        doRegister();
        <?php endif; ?>
    }
});
</script>
</body>
</html>