<?php
session_start();
require_once 'includes/config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// إضافة عبادة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_worship'])) {
    $name = $_POST['name'];
    $type = $_POST['type'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO worship (name, type) VALUES (?, ?)");
        $stmt->execute([$name, $type]);
        $success = "تمت إضافة العبادة بنجاح!";
    } catch (PDOException $e) {
        $error = "خطأ في إضافة العبادة: " . $e->getMessage();
    }
}

// جلب جميع العبادات
$stmt = $pdo->query("SELECT * FROM worship ORDER BY created_at DESC");
$worships = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة العبادات</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            background-color: #f9fafb;
            color: #111827;
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 0;
            border-bottom: 2px solid #047857;
        }
        
        h1 {
            color: #111827;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 800;
        }
        
        .subtitle {
            color: #047857;
            font-size: 1.2rem;
            font-weight: 400;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        @media (min-width: 768px) {
            .main-content {
                grid-template-columns: 300px 1fr;
            }
        }
        
        .sidebar {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .add-worship-btn {
            background-color: #059669;
            color: white;
            border: none;
            padding: 15px 25px;
            font-size: 1.1rem;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-family: 'Tajawal', sans-serif;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .add-worship-btn:hover {
            background-color: #047857;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(5, 150, 105, 0.2);
        }
        
        .worships-list {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .worships-list h2 {
            color: #111827;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
            font-weight: 700;
        }
        
        .worship-item {
            padding: 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-right: 4px solid #059669;
            transition: transform 0.2s;
        }
        
        .worship-item:hover {
            transform: translateX(-5px);
            background: #f0fdf4;
        }
        
        .worship-name {
            font-size: 1.2rem;
            color: #111827;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .worship-type {
            display: inline-block;
            padding: 5px 15px;
            background: #d1fae5;
            color: #047857;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .worship-date {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            background: none;
            border: none;
            padding: 5px;
        }
        
        .close-modal:hover {
            color: #111827;
        }
        
        .modal h2 {
            color: #111827;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 700;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #111827;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-family: 'Tajawal', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #059669;
        }
        
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        
        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .radio-option input[type="radio"] {
            accent-color: #059669;
        }
        
        .submit-btn {
            background-color: #059669;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-family: 'Tajawal', sans-serif;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #047857;
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6b7280;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #d1d5db;
        }
        
        /* Navigation */
        .nav-links {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .nav-link {
            padding: 12px 20px;
            background: #f3f4f6;
            border-radius: 8px;
            color: #111827;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background: #e5e7eb;
            color: #047857;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>صفحة العبادات</h1>
            <p class="subtitle">ادارة وتتبع عباداتك اليومية</p>
        </header>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="main-content">
            <div class="sidebar">
                <button class="add-worship-btn" onclick="openModal()">
                    <span>+</span>
                    إضافة عبادة جديدة
                </button>
                
                <div class="nav-links">
                    <a href="dashboard.php" class="nav-link">الرئيسية</a>
                    <a href="profile.php" class="nav-link">الملف الشخصي</a>
                    <a href="reports.php" class="nav-link">التقارير</a>
                    <a href="logout.php" class="nav-link">تسجيل الخروج</a>
                </div>
                
                <div style="margin-top: 30px; padding: 20px; background: #f0fdf4; border-radius: 10px;">
                    <h3 style="color: #047857; margin-bottom: 15px;">إحصائيات</h3>
                    <p style="color: #111827; margin-bottom: 10px;">عدد العبادات: <strong><?php echo count($worships); ?></strong></p>
                    <?php
                    $night_prayers = array_filter($worships, function($w) { return $w['type'] == 'night_prayer'; });
                    $daily_reminders = array_filter($worships, function($w) { return $w['type'] == 'daily_reminder'; });
                    ?>
                    <p style="color: #111827; margin-bottom: 10px;">قيام الليل: <strong><?php echo count($night_prayers); ?></strong></p>
                    <p style="color: #111827;">الأوراد اليومية: <strong><?php echo count($daily_reminders); ?></strong></p>
                </div>
            </div>
            
            <div class="worships-list">
                <h2>قائمة العبادات</h2>
                
                <?php if (empty($worships)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🕌</div>
                        <h3 style="color: #6b7280; margin-bottom: 10px;">لا توجد عبادات مضافة</h3>
                        <p>ابدأ بإضافة أول عبادة لك بالضغط على زر "إضافة عبادة جديدة"</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($worships as $worship): ?>
                        <div class="worship-item">
                            <div class="worship-name"><?php echo htmlspecialchars($worship['name']); ?></div>
                            <span class="worship-type">
                                <?php echo $worship['type'] == 'night_prayer' ? 'قيام الليل' : 'ورد يومي'; ?>
                            </span>
                            <div class="worship-date">
                                📅 تم الإضافة: <?php echo date('Y-m-d', strtotime($worship['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal for Adding Worship -->
    <div id="addWorshipModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal()">&times;</button>
            <h2>إضافة عبادة جديدة</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">اسم العبادة</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           placeholder="أدخل اسم العبادة" required>
                </div>
                
                <div class="form-group">
                    <label>نوع العبادة</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="type" value="night_prayer" required checked>
                            <span>قيام الليل</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="type" value="daily_reminder" required>
                            <span>ورد يومي</span>
                        </label>
                    </div>
                </div>
                
                <input type="hidden" name="add_worship" value="1">
                <button type="submit" class="submit-btn">إضافة العبادة</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('addWorshipModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('addWorshipModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addWorshipModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Handle form submission with AJAX for better UX
        document.querySelector('form').addEventListener('submit', function(e) {
            const formData = new FormData(this);
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Reload page to show new data
                    window.location.reload();
                }
            };
            
            xhr.open('POST', '', true);
            xhr.send(formData);
        });
    </script>
</body>
</html>