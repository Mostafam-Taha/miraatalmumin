<?php
session_start();

$host = 'localhost';
$dbname = 'prayer_tracker';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// الحصول على معرف الطلب
$subscription_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

// جلب تفاصيل الطلب
$subscription = null;
if ($subscription_id > 0 && $user_id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT ps.*, u.name, u.email, u.Pro as user_pro_status
            FROM pro_subscriptions ps
            LEFT JOIN users u ON ps.user_id = u.id
            WHERE ps.id = ? AND ps.user_id = ?
        ");
        $stmt->execute([$subscription_id, $user_id]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// إذا لم يتم العثور على الطلب
if (!$subscription) {
    header("Location: ../pro_subscription.php");
    exit;
}

// الحصول على حالة الطلب بالعربية
$status_text = [
    'pending' => 'قيد المراجعة',
    'verified' => 'مفعل',
    'rejected' => 'مرفوض'
];

$status_color = [
    'pending' => '#f59e0b',
    'verified' => '#059669',
    'rejected' => '#ef4444'
];

$status_icon = [
    'pending' => 'fa-clock',
    'verified' => 'fa-check-circle',
    'rejected' => 'fa-times-circle'
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حالة الطلب - مرآة المؤمن</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            min-height: 100vh;
            padding: 20px;
        }
        
        .status-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .status-header {
            padding: 30px;
            text-align: center;
            background: linear-gradient(135deg, <?php echo $status_color[$subscription['status']]; ?>, 
                <?php echo $subscription['status'] === 'pending' ? '#d97706' : 
                       ($subscription['status'] === 'verified' ? '#047857' : '#dc2626'); ?>);
            color: white;
        }
        
        .status-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .status-title {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .order-number {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .status-body {
            padding: 40px;
        }
        
        .status-card {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            padding: 25px;
            background: #f8fafc;
            border-radius: 15px;
            border: 2px dashed <?php echo $status_color[$subscription['status']]; ?>;
        }
        
        .status-message {
            font-size: 20px;
            font-weight: 600;
            color: <?php echo $status_color[$subscription['status']]; ?>;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border-right: 4px solid <?php echo $status_color[$subscription['status']]; ?>;
        }
        
        .detail-label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 8px;
            display: block;
        }
        
        .detail-value {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .primary-btn {
            background: <?php echo $status_color[$subscription['status']]; ?>;
            color: white;
        }
        
        .primary-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px <?php echo $status_color[$subscription['status']]; ?>40;
        }
        
        .secondary-btn {
            background: #f1f5f9;
            color: #475569;
            border: 2px solid #cbd5e1;
        }
        
        .secondary-btn:hover {
            background: #e2e8f0;
        }
        
        .note-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .note-box h4 {
            color: #92400e;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .status-body {
                padding: 20px;
            }
            
            .status-title {
                font-size: 24px;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="status-container">
        <div class="status-header">
            <div class="status-icon">
                <i class="fas <?php echo $status_icon[$subscription['status']]; ?>"></i>
            </div>
            <h1 class="status-title"><?php echo $status_text[$subscription['status']]; ?></h1>
            <p class="order-number">رقم الطلب: #<?php echo $subscription['id']; ?></p>
        </div>
        
        <div class="status-body">
            <div class="status-card">
                <div class="status-message">
                    <?php if ($subscription['status'] === 'pending'): ?>
                        طلبك قيد المراجعة حالياً، سيتم الرد عليك في غضون 24 ساعة.
                    <?php elseif ($subscription['status'] === 'verified'): ?>
                        تم تفعيل اشتراكك بنجاح! يمكنك الآن الاستمتاع بجميع ميزات Pro.
                    <?php elseif ($subscription['status'] === 'rejected'): ?>
                        تم رفض طلبك، يرجى التواصل مع الدعم الفني لمزيد من المعلومات.
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="details-grid">
                <div class="detail-card">
                    <span class="detail-label">المستخدم</span>
                    <span class="detail-value"><?php echo htmlspecialchars($subscription['name']); ?></span>
                </div>
                
                <div class="detail-card">
                    <span class="detail-label">البريد الإلكتروني</span>
                    <span class="detail-value"><?php echo htmlspecialchars($subscription['email']); ?></span>
                </div>
                
                <div class="detail-card">
                    <span class="detail-label">رقم الهاتف</span>
                    <span class="detail-value"><?php echo htmlspecialchars($subscription['phone']); ?></span>
                </div>
                
                <div class="detail-card">
                    <span class="detail-label">نوع الاشتراك</span>
                    <span class="detail-value"><?php echo $subscription['plan_type'] === 'yearly' ? 'سنوي' : 'شهري'; ?></span>
                </div>
                
                <div class="detail-card">
                    <span class="detail-label">المبلغ</span>
                    <span class="detail-value"><?php echo number_format($subscription['amount'], 2); ?> جنيه</span>
                </div>
                
                <div class="detail-card">
                    <span class="detail-label">تاريخ الطلب</span>
                    <span class="detail-value"><?php echo date('Y-m-d H:i', strtotime($subscription['created_at'])); ?></span>
                </div>
                
                <?php if ($subscription['status'] === 'verified' && $subscription['payment_date']): ?>
                <div class="detail-card">
                    <span class="detail-label">تاريخ التفعيل</span>
                    <span class="detail-value"><?php echo date('Y-m-d H:i', strtotime($subscription['payment_date'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($subscription['status'] === 'pending'): ?>
            <div class="note-box">
                <h4><i class="fas fa-info-circle"></i> ملاحظة هامة</h4>
                <p>• سيتم مراجعة طلبك في أقرب وقت ممكن</p>
                <p>• متوسط وقت المراجعة 24 ساعة</p>
                <p>• ستتلقى إشعاراً عند تغيير حالة طلبك</p>
                <p>• يمكنك متابعة حالة الطلب من هذه الصفحة</p>
            </div>
            <?php endif; ?>
            
            <div class="actions">
                <?php if ($subscription['status'] === 'verified' && $subscription['user_pro_status'] == 1): ?>
                <a href="../statistics.php" class="action-btn primary-btn">
                    <i class="fas fa-tachometer-alt"></i> الانتقال للوحة التحكم
                </a>
                <?php endif; ?>
                
                <a href="../index.php" class="action-btn secondary-btn">
                    <i class="fas fa-home"></i> الصفحة الرئيسية
                </a>
                
                <?php if ($subscription['status'] === 'pending'): ?>
                <button onclick="location.reload()" class="action-btn secondary-btn">
                    <i class="fas fa-sync-alt"></i> تحديث الحالة
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // تحديث تلقائي للحالة كل 30 ثانية (لطلبات قيد المراجعة)
        <?php if ($subscription['status'] === 'pending'): ?>
        setInterval(function() {
            location.reload();
        }, 30000); // 30 ثانية
        <?php endif; ?>
    </script>
</body>
</html>