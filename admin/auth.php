<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// ── DB Config ──────────────────────────────────────────────────────────────
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

// ── Create admin-stv table if not exists ──────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS `admin-stv` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin','admin','moderator') DEFAULT 'admin',
    avatar VARCHAR(255) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// ── Theme ──────────────────────────────────────────────────────────────────
$btn_color   = '#059669';
$sec_color   = '#047857';
$text_color  = '#111827';

// ── AJAX Handlers ──────────────────────────────────────────────────────────
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    // ── LOGIN ──────────────────────────────────────────────────────────────
    if ($_POST['action'] === 'login') {
        $admin_id = trim($_POST['admin_id'] ?? '');
        $pass     = trim($_POST['password']  ?? '');

        if (!$admin_id || !$pass) {
            echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM `admin-stv` WHERE admin_id = ? LIMIT 1");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin || !password_verify($pass, $admin['password'])) {
            echo json_encode(['success' => false, 'message' => 'معرّف الإدارة أو كلمة المرور غير صحيحة']);
            exit;
        }

        // Update last login
        $pdo->prepare("UPDATE `admin-stv` SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $admin['id'];
        $_SESSION['admin_uid']       = $admin['admin_id'];
        $_SESSION['admin_name']      = $admin['name'];
        $_SESSION['admin_role']      = $admin['role'];

        echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
        exit;
    }

    // ── REGISTER ──────────────────────────────────────────────────────────
    if ($_POST['action'] === 'register') {
        $admin_id = trim($_POST['admin_id'] ?? '');
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $pass     = trim($_POST['password'] ?? '');
        $confirm  = trim($_POST['confirm']  ?? '');
        $secret   = trim($_POST['secret']   ?? '');

        // Simple secret key to prevent random registrations
        if ($secret !== 'MIRAAT_ADMIN_2025') {
            echo json_encode(['success' => false, 'message' => 'مفتاح التسجيل السري غير صحيح']);
            exit;
        }

        if (!$admin_id || !$name || !$email || !$pass || !$confirm) {
            echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول']);
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $admin_id)) {
            echo json_encode(['success' => false, 'message' => 'معرّف الإدارة: 4-20 حرف (أحرف، أرقام، _) فقط']);
            exit;
        }

        if (strlen($pass) < 8) {
            echo json_encode(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل']);
            exit;
        }

        if ($pass !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'كلمتا المرور غير متطابقتين']);
            exit;
        }

        // Check duplicates
        $stmt = $pdo->prepare("SELECT id FROM `admin-stv` WHERE admin_id = ? OR email = ?");
        $stmt->execute([$admin_id, $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'معرّف الإدارة أو البريد الإلكتروني مستخدم مسبقاً']);
            exit;
        }

        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO `admin-stv` (admin_id, name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, $name, $email, $hashed]);

        echo json_encode(['success' => true, 'message' => 'تم إنشاء الحساب بنجاح! يمكنك تسجيل الدخول الآن.']);
        exit;
    }
}

// Redirect if already logged in
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$page = $_GET['page'] ?? 'login'; // login | register
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page === 'register' ? 'إنشاء حساب إداري' : 'تسجيل دخول الإدارة'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Tajawal', sans-serif;
            background: #f1f5f9;
            color: <?php echo $text_color; ?>;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Animated Background */
        .bg-anim {
            position: fixed; inset: 0; z-index: 0;
            background: linear-gradient(135deg, #064e3b 0%, #065f46 40%, #047857 70%, #059669 100%);
            overflow: hidden;
        }

        .bg-anim::before, .bg-anim::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            animation: floatBubble 8s ease-in-out infinite;
        }

        .bg-anim::before {
            width: 500px; height: 500px;
            top: -100px; right: -100px;
        }

        .bg-anim::after {
            width: 400px; height: 400px;
            bottom: -100px; left: -100px;
            animation-delay: -4s;
        }

        @keyframes floatBubble {
            0%, 100% { transform: translate(0,0) scale(1); }
            50%       { transform: translate(30px,30px) scale(1.05); }
        }

        .bubble {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.03);
            animation: floatBubble var(--d,10s) ease-in-out infinite;
            animation-delay: var(--delay, 0s);
        }

        /* Card */
        .auth-card {
            position: relative; z-index: 1;
            background: white;
            border-radius: 24px;
            width: 100%; max-width: 460px;
            margin: 20px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.25);
            overflow: hidden;
            animation: cardIn 0.5s cubic-bezier(0.34,1.56,0.64,1) both;
        }

        @keyframes cardIn {
            from { opacity:0; transform: translateY(40px) scale(0.96); }
            to   { opacity:1; transform: translateY(0) scale(1); }
        }

        .card-header {
            background: linear-gradient(135deg, <?php echo $sec_color; ?>, <?php echo $btn_color; ?>);
            padding: 32px 32px 28px;
            text-align: center;
            color: white;
        }

        .logo-icon {
            width: 70px; height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px;
            margin: 0 auto 16px;
            backdrop-filter: blur(4px);
        }

        .card-header h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
        .card-header p  { font-size: 13px; opacity: 0.85; }

        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 2px solid #f1f5f9;
            background: #fafafa;
        }

        .tab {
            flex: 1; padding: 14px;
            text-align: center;
            font-size: 14px; font-weight: 600;
            color: #9ca3af;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
            text-decoration: none;
            display: block;
        }

        .tab:hover { color: <?php echo $btn_color; ?>; }
        .tab.active { color: <?php echo $btn_color; ?>; border-bottom-color: <?php echo $btn_color; ?>; }

        /* Form */
        .card-body { padding: 28px 32px 32px; }

        .form-group { margin-bottom: 18px; }

        .form-label {
            display: flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 7px;
        }

        .form-label i { color: <?php echo $btn_color; ?>; font-size: 12px; }

        .input-wrap { position: relative; }

        .input-wrap i.prefix {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; font-size: 15px;
        }

        .form-input {
            width: 100%;
            padding: 12px 44px 12px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            color: #111827;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
        }

        .form-input:focus {
            border-color: <?php echo $btn_color; ?>;
            box-shadow: 0 0 0 3px rgba(5,150,105,0.12);
            background: white;
        }

        .form-input.ltr { direction: ltr; text-align: left; }

        .toggle-pass {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; cursor: pointer; font-size: 15px;
            background: none; border: none; padding: 4px;
        }

        .toggle-pass:hover { color: <?php echo $btn_color; ?>; }

        .hint { font-size: 11px; color: #9ca3af; margin-top: 5px; }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, <?php echo $sec_color; ?>, <?php echo $btn_color; ?>);
            color: white;
            border: none; border-radius: 12px;
            font-family: 'Tajawal', sans-serif;
            font-size: 16px; font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(5,150,105,0.3);
        }

        .submit-btn:hover    { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(5,150,105,0.4); }
        .submit-btn:active   { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* Alert */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px; font-weight: 500;
            margin-bottom: 16px;
            display: none;
            align-items: center; gap: 8px;
        }

        .alert.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert.success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }

        .secret-note {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 12px;
            color: #92400e;
            margin-bottom: 18px;
            display: flex; align-items: flex-start; gap: 8px;
        }

        .secret-note i { margin-top: 2px; flex-shrink: 0; }
    </style>
</head>
<body>

<div class="bg-anim">
    <div class="bubble" style="width:300px;height:300px;top:10%;left:5%;--d:12s;--delay:-2s;"></div>
    <div class="bubble" style="width:200px;height:200px;top:60%;right:10%;--d:9s;--delay:-5s;"></div>
    <div class="bubble" style="width:150px;height:150px;top:30%;left:40%;--d:14s;--delay:-7s;"></div>
</div>

<div class="auth-card">
    <div class="card-header">
        <div class="logo-icon"><i class="fas fa-shield-halved"></i></div>
        <h1>لوحة تحكم مرآة المؤمن</h1>
        <p><?php echo $page === 'register' ? 'إنشاء حساب إداري جديد' : 'منطقة الدخول الآمنة للمديرين'; ?></p>
    </div>

    <div class="tabs">
        <a href="?page=login"    class="tab <?php echo $page==='login'    ? 'active':'' ?>"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</a>
        <a href="?page=register" class="tab <?php echo $page==='register' ? 'active':'' ?>"><i class="fas fa-user-plus"></i> إنشاء حساب</a>
    </div>

    <div class="card-body">

        <?php if ($page === 'login'): ?>
        <!-- ────────────── LOGIN FORM ────────────── -->
        <div class="alert" id="alert"><i class="fas fa-circle-exclamation"></i> <span id="alertMsg"></span></div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-id-badge"></i> معرّف الإدارة (admin_id)</label>
            <div class="input-wrap">
                <i class="fas fa-at prefix"></i>
                <input type="text" class="form-input ltr" id="admin_id" placeholder="أدخل معرّفك الإداري" autocomplete="username">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-lock"></i> كلمة المرور</label>
            <div class="input-wrap">
                <i class="fas fa-key prefix"></i>
                <input type="password" class="form-input ltr" id="password" placeholder="••••••••" autocomplete="current-password">
                <button type="button" class="toggle-pass" onclick="togglePass('password',this)"><i class="fas fa-eye"></i></button>
            </div>
        </div>

        <button class="submit-btn" id="loginBtn" onclick="doLogin()">
            <i class="fas fa-right-to-bracket"></i> دخول إلى لوحة التحكم
        </button>

        <?php else: ?>
        <!-- ────────────── REGISTER FORM ────────────── -->
        <div class="secret-note">
            <i class="fas fa-triangle-exclamation"></i>
            <span>إنشاء الحسابات الإدارية يتطلب <strong>مفتاح سري</strong> لمنع الوصول غير المصرح به.</span>
        </div>

        <div class="alert" id="alert"><i class="fas fa-circle-exclamation"></i> <span id="alertMsg"></span></div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-id-badge"></i> معرّف الإدارة (admin_id)</label>
            <div class="input-wrap">
                <i class="fas fa-at prefix"></i>
                <input type="text" class="form-input ltr" id="reg_admin_id" placeholder="مثال: admin_ali" autocomplete="off">
            </div>
            <p class="hint">4-20 حرف، أحرف إنجليزية وأرقام وشرطة سفلية فقط</p>
        </div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-user"></i> الاسم الكامل</label>
            <div class="input-wrap">
                <i class="fas fa-user prefix"></i>
                <input type="text" class="form-input" id="reg_name" placeholder="الاسم الكامل للمدير">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-envelope"></i> البريد الإلكتروني</label>
            <div class="input-wrap">
                <i class="fas fa-envelope prefix"></i>
                <input type="email" class="form-input ltr" id="reg_email" placeholder="admin@example.com">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-lock"></i> كلمة المرور</label>
            <div class="input-wrap">
                <i class="fas fa-key prefix"></i>
                <input type="password" class="form-input ltr" id="reg_pass" placeholder="8 أحرف على الأقل">
                <button type="button" class="toggle-pass" onclick="togglePass('reg_pass',this)"><i class="fas fa-eye"></i></button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-lock"></i> تأكيد كلمة المرور</label>
            <div class="input-wrap">
                <i class="fas fa-key prefix"></i>
                <input type="password" class="form-input ltr" id="reg_confirm" placeholder="••••••••">
                <button type="button" class="toggle-pass" onclick="togglePass('reg_confirm',this)"><i class="fas fa-eye"></i></button>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label"><i class="fas fa-shield-keyhole"></i> المفتاح السري</label>
            <div class="input-wrap">
                <i class="fas fa-shield prefix"></i>
                <input type="password" class="form-input ltr" id="reg_secret" placeholder="أدخل المفتاح السري">
                <button type="button" class="toggle-pass" onclick="togglePass('reg_secret',this)"><i class="fas fa-eye"></i></button>
            </div>
        </div>

        <button class="submit-btn" id="regBtn" onclick="doRegister()">
            <i class="fas fa-user-plus"></i> إنشاء الحساب الإداري
        </button>
        <?php endif; ?>

    </div>
</div>

<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

function showAlert(msg, type='error') {
    const el  = document.getElementById('alert');
    const msg_el = document.getElementById('alertMsg');
    el.className = `alert ${type}`;
    msg_el.textContent = msg;
    el.style.display = 'flex';
    if (type === 'success') setTimeout(() => el.style.display='none', 5000);
}

async function doLogin() {
    const admin_id = document.getElementById('admin_id').value.trim();
    const password = document.getElementById('password').value.trim();
    const btn = document.getElementById('loginBtn');

    if (!admin_id || !password) return showAlert('يرجى ملء جميع الحقول');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحقق...';

    try {
        const res = await fetch('', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `ajax=true&action=login&admin_id=${encodeURIComponent(admin_id)}&password=${encodeURIComponent(password)}`
        });
        const data = await res.json();
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> تم التحقق، جاري التحويل...';
            setTimeout(() => window.location.href = data.redirect, 600);
        } else {
            showAlert(data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-right-to-bracket"></i> دخول إلى لوحة التحكم';
        }
    } catch(e) {
        showAlert('خطأ في الاتصال بالخادم');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-right-to-bracket"></i> دخول إلى لوحة التحكم';
    }
}

async function doRegister() {
    const admin_id = document.getElementById('reg_admin_id').value.trim();
    const name     = document.getElementById('reg_name').value.trim();
    const email    = document.getElementById('reg_email').value.trim();
    const pass     = document.getElementById('reg_pass').value.trim();
    const confirm  = document.getElementById('reg_confirm').value.trim();
    const secret   = document.getElementById('reg_secret').value.trim();
    const btn      = document.getElementById('regBtn');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإنشاء...';

    try {
        const body = `ajax=true&action=register&admin_id=${encodeURIComponent(admin_id)}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(pass)}&confirm=${encodeURIComponent(confirm)}&secret=${encodeURIComponent(secret)}`;
        const res  = await fetch('', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body });
        const data = await res.json();
        if (data.success) {
            showAlert(data.message, 'success');
            btn.innerHTML = '<i class="fas fa-check"></i> تم الإنشاء بنجاح';
            setTimeout(() => window.location.href = '?page=login', 2000);
        } else {
            showAlert(data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-plus"></i> إنشاء الحساب الإداري';
        }
    } catch(e) {
        showAlert('خطأ في الاتصال بالخادم');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-plus"></i> إنشاء الحساب الإداري';
    }
}

// Enter key
document.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
        <?php if ($page==='login'): ?>
        doLogin();
        <?php else: ?>
        doRegister();
        <?php endif; ?>
    }
});
</script>
</body>
</html>