<?php
// إعداد قاعدة البيانات والجلسة
session_start();

$host = 'localhost';
$dbname = 'prayer_tracker';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // إنشاء جدول الاشتراكات إذا لم يكن موجودًا
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS pro_subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        phone VARCHAR(20) NOT NULL,
        insta_user VARCHAR(100),
        plan_type ENUM('monthly', 'yearly') NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        receipt_image VARCHAR(255),
        extracted_data TEXT,
        payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_status (user_id, status)
    )";
    $pdo->exec($createTableSQL);
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// تعريف مسار الموقع
define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\'));

// التحقق من حالة Pro للمستخدم
$user_id = $_SESSION['user_id'] ?? 0;
$userProStatus = false;
$lastPage = isset($_SESSION['last_page']) ? $_SESSION['last_page'] : 'index.php';
$hasPendingRequest = false;
$hasVerifiedSubscription = false;
$hasRejectedRequest = false;
$latestSubscription = null;

if ($user_id > 0) {
    try {
        // التحقق من حالة Pro في جدول users
        $stmt = $pdo->prepare("SELECT Pro FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['Pro'] == 1) {
            // إذا كان المستخدم لديه Pro، إعادة التوجيه إلى آخر صفحة كان فيها
            $userProStatus = true;
            header("Location: " . $lastPage);
            exit();
        }
        
        // التحقق من حالة طلبات الاشتراك للمستخدم
        $stmt = $pdo->prepare("
            SELECT * FROM pro_subscriptions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $latestSubscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($latestSubscription) {
            if ($latestSubscription['status'] === 'pending') {
                $hasPendingRequest = true;
            } elseif ($latestSubscription['status'] === 'verified') {
                $hasVerifiedSubscription = true;
            } elseif ($latestSubscription['status'] === 'rejected') {
                $hasRejectedRequest = true;
                
                // التحقق إذا كان الرفض حديث (خلال 24 ساعة)
                $rejectionTime = strtotime($latestSubscription['created_at']);
                $currentTime = time();
                $hoursDiff = ($currentTime - $rejectionTime) / 3600;
                
                if ($hoursDiff < 24) {
                    // لا يسمح بإعادة الطلب قبل 24 ساعة من الرفض
                    $recentlyRejected = true;
                }
            }
        }
        
    } catch (PDOException $e) {
        // في حالة حدوث خطأ، نستمر في عرض الصفحة
        error_log("Database error: " . $e->getMessage());
    }
}

// إذا كان لديه طلب قيد الانتظار أو اشتراك مفعل، إعادة التوجيه
if ($hasPendingRequest || $hasVerifiedSubscription) {
    header("Location: subscription/subscription_status.php?id=" . $latestSubscription['id']);
    exit();
}

// إذا كان الرفض حديث (أقل من 24 ساعة)، عرض رسالة خاصة
if (isset($recentlyRejected) && $recentlyRejected) {
    $rejectionMessage = "لا يمكنك تقديم طلب جديد قبل 24 ساعة من رفض الطلب السابق.";
    $rejectionTimeLeft = ceil(24 - $hoursDiff) . " ساعة";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['planType'])) {
    // التحقق من عدم وجود طلب قيد الانتظار
    if ($hasPendingRequest) {
        echo json_encode(['success' => false, 'message' => 'لديك بالفعل طلب قيد المراجعة']);
        exit;
    }
    
    // التحقق من عدم وجود اشتراك مفعل
    if ($hasVerifiedSubscription) {
        echo json_encode(['success' => false, 'message' => 'لديك اشتراك مفعل بالفعل']);
        exit;
    }
    
    // التحقق من وقت الرفض إذا كان موجوداً
    if (isset($recentlyRejected) && $recentlyRejected) {
        echo json_encode([
            'success' => false, 
            'message' => 'لا يمكنك تقديم طلب جديد قبل ' . $rejectionTimeLeft
        ]);
        exit;
    }
    
    // جمع بيانات النموذج
    $phone = $_POST['phone'] ?? '';
    $instaUser = $_POST['instaUser'] ?? '';
    $planType = $_POST['planType'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $extractedData = $_POST['extractedData'] ?? '';
    
    // التحقق من البيانات الأساسية
    if (empty($phone) || empty($planType)) {
        echo json_encode(['success' => false, 'message' => 'البيانات غير مكتملة']);
        exit;
    }
    
    // معالجة صورة الإيصال
    $receiptImage = '';
    if (isset($_FILES['receiptImage']) && $_FILES['receiptImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/receipts/';
        
        // إنشاء المجلد إذا لم يكن موجودًا
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // توليد اسم فريد للملف
        $fileName = time() . '_' . basename($_FILES['receiptImage']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // نقل الملف إلى المجلد المحدد
        if (move_uploaded_file($_FILES['receiptImage']['tmp_name'], $targetPath)) {
            $receiptImage = $targetPath;
        }
    }
    
    try {
        // إدخال البيانات في قاعدة البيانات
        $stmt = $pdo->prepare("
            INSERT INTO pro_subscriptions 
            (user_id, phone, insta_user, plan_type, amount, receipt_image, extracted_data, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $user_id, 
            $phone, 
            $instaUser, 
            $planType, 
            $amount, 
            $receiptImage, 
            $extractedData
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // هنا يمكنك إرسال إشعار أو بريد إلكتروني
        
        echo json_encode([
            'success' => true, 
            'orderId' => $orderId,
            'message' => 'تم استلام طلبك بنجاح',
            'redirect' => 'subscription/subscription_status.php?id=' . $orderId
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
    }
    
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرآة المؤمن - الاشتراك الاحترافي</title>
    <link rel="stylesheet" href="assets/css/pro_subscription.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* إضافة أنماط لحالة Pro */
        .pro-user-banner,
        .pending-request-banner,
        .rejected-request-banner {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin: 30px auto;
            max-width: 800px;
            box-shadow: 0 6px 25px rgba(5, 150, 105, 0.25);
            animation: slideIn 0.6s ease;
        }
        
        .pending-request-banner {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .rejected-request-banner {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .banner-icon {
            font-size: 60px;
            margin-bottom: 20px;
            display: block;
        }
        
        .banner-title {
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .banner-message {
            font-size: 18px;
            margin-bottom: 20px;
            opacity: 0.95;
            line-height: 1.6;
        }
        
        .subscription-details {
            background: rgba(255, 255, 255, 0.15);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: right;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
        }
        
        .detail-value {
            opacity: 0.9;
        }
        
        .countdown-timer {
            font-size: 24px;
            font-weight: bold;
            color: #fbbf24;
            margin: 15px 0;
            font-family: monospace;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }
        
        .action-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
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
            background: white;
            color: #059669;
        }
        
        .primary-btn:hover {
            background: #f8fafc;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 255, 255, 0.2);
        }
        
        .secondary-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .secondary-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .redirect-message {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 16px;
        }
        
        .redirect-count {
            font-weight: bold;
            color: #fbbf24;
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translateY(-30px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
        
        /* إخفاء واجهة الاشتراك عندما لا يكون مسموحاً */
        .subscription-disabled {
            display: none !important;
        }
    </style>
</head>
<body>
    <?php if ($userProStatus): ?>
        <!-- عرض رسالة للمستخدمين الذين لديهم Pro -->
        <div class="pro-user-banner">
            <i class="fas fa-crown banner-icon"></i>
            <h2 class="banner-title">مبروك! أنت مشترك في Pro بالفعل 🎉</h2>
            <p class="banner-message">حسابك مفعل في الخدمة المميزة، يمكنك الاستمتاع بجميع المزايا.</p>
            
            <div class="redirect-message">
                <p>سيتم إعادة توجيهك إلى الصفحة السابقة خلال <span class="redirect-count" id="countdown">5</span> ثوانٍ</p>
                <p>إذا لم يتم التوجيه تلقائياً، <a href="<?php echo htmlspecialchars($lastPage); ?>" class="action-btn secondary-btn" style="display: inline-flex; padding: 8px 20px;">انقر هنا</a></p>
            </div>
        </div>
        
        <script>
            // عد تنازلي لإعادة التوجيه
            let seconds = 5;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                seconds--;
                countdownElement.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = '<?php echo htmlspecialchars($lastPage); ?>';
                }
            }, 1000);
            
            // يمكن للمستخدم إلغاء إعادة التوجيه
            document.addEventListener('click', function() {
                clearInterval(countdownInterval);
                document.querySelector('.redirect-message').innerHTML = 
                    '<p>تم إلغاء إعادة التوجيه التلقائي.</p>' +
                    '<p><a href="<?php echo htmlspecialchars($lastPage); ?>" class="action-btn secondary-btn" style="display: inline-flex; padding: 8px 20px;">انقر هنا للعودة</a></p>';
            });
        </script>
        
    <?php elseif ($hasPendingRequest): ?>
        <!-- عرض رسالة للمستخدمين الذين لديهم طلب قيد المراجعة -->
        <div class="pending-request-banner">
            <i class="fas fa-clock banner-icon"></i>
            <h2 class="banner-title">طلبك قيد المراجعة ⏳</h2>
            <p class="banner-message">لديك بالفعل طلب اشتراك قيد المراجعة، سيتم الرد عليك في غضون 24 ساعة.</p>
            
            <?php if ($latestSubscription): ?>
            <div class="subscription-details">
                <div class="detail-row">
                    <span class="detail-label">رقم الطلب:</span>
                    <span class="detail-value">#<?php echo $latestSubscription['id']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">نوع الاشتراك:</span>
                    <span class="detail-value"><?php echo $latestSubscription['plan_type'] === 'yearly' ? 'سنوي' : 'شهري'; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">المبلغ:</span>
                    <span class="detail-value"><?php echo number_format($latestSubscription['amount'], 2); ?> جنيه</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">تاريخ الطلب:</span>
                    <span class="detail-value"><?php echo date('Y-m-d H:i', strtotime($latestSubscription['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">الحالة:</span>
                    <span class="detail-value" style="color: #fbbf24; font-weight: bold;">قيد المراجعة</span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="subscription/subscription_status.php?id=<?php echo $latestSubscription['id']; ?>" class="action-btn primary-btn">
                    <i class="fas fa-eye"></i> مشاهدة حالة الطلب
                </a>
                <a href="<?php echo htmlspecialchars($lastPage); ?>" class="action-btn secondary-btn">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            </div>
        </div>
        
    <?php elseif ($hasVerifiedSubscription): ?>
        <!-- عرض رسالة للمستخدمين الذين لديهم اشتراك مفعل -->
        <div class="pro-user-banner">
            <i class="fas fa-check-circle banner-icon"></i>
            <h2 class="banner-title">اشتراكك مفعل بنجاح ✅</h2>
            <p class="banner-message">تم تفعيل اشتراكك Pro بنجاح، يمكنك الآن الاستمتاع بجميع المزايا.</p>
            
            <?php if ($latestSubscription): ?>
            <div class="subscription-details">
                <div class="detail-row">
                    <span class="detail-label">رقم الطلب:</span>
                    <span class="detail-value">#<?php echo $latestSubscription['id']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">نوع الاشتراك:</span>
                    <span class="detail-value"><?php echo $latestSubscription['plan_type'] === 'yearly' ? 'سنوي' : 'شهري'; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">تاريخ التفعيل:</span>
                    <span class="detail-value"><?php echo date('Y-m-d H:i', strtotime($latestSubscription['payment_date'])); ?></span>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="dashboard.php" class="action-btn primary-btn">
                    <i class="fas fa-tachometer-alt"></i> الانتقال للوحة التحكم
                </a>
                <a href="<?php echo htmlspecialchars($lastPage); ?>" class="action-btn secondary-btn">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            </div>
        </div>
        
    <?php elseif (isset($recentlyRejected) && $recentlyRejected): ?>
        <!-- عرض رسالة للمستخدمين الذين تم رفض طلبهم مؤخراً -->
        <div class="rejected-request-banner">
            <i class="fas fa-hourglass-half banner-icon"></i>
            <h2 class="banner-title">انتظر قليلاً ⏳</h2>
            <p class="banner-message"><?php echo $rejectionMessage; ?></p>
            
            <div class="countdown-timer" id="countdownTimer">
                <?php echo $rejectionTimeLeft; ?>
            </div>
            
            <?php if ($latestSubscription): ?>
            <div class="subscription-details">
                <div class="detail-row">
                    <span class="detail-label">آخر طلب:</span>
                    <span class="detail-value">#<?php echo $latestSubscription['id']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">تاريخ الرفض:</span>
                    <span class="detail-value"><?php echo date('Y-m-d H:i', strtotime($latestSubscription['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">الحالة:</span>
                    <span class="detail-value" style="color: #fca5a5;">مرفوض</span>
                </div>
            </div>
            <?php endif; ?>
            
            <p class="banner-message">يمكنك المحاولة مرة أخرى بعد انتهاء المهلة.</p>
            
            <div class="action-buttons">
                <a href="subscription/subscription_status.php?id=<?php echo $latestSubscription['id']; ?>" class="action-btn secondary-btn">
                    <i class="fas fa-eye"></i> مشاهدة تفاصيل الرفض
                </a>
                <a href="<?php echo htmlspecialchars($lastPage); ?>" class="action-btn primary-btn">
                    <i class="fas fa-arrow-right"></i> العودة
                </a>
            </div>
        </div>
        
        <script>
            // عد تنازلي للمهلة
            function updateCountdown() {
                const timerElement = document.getElementById('countdownTimer');
                const hoursLeft = <?php echo ceil(24 - $hoursDiff); ?>;
                
                if (hoursLeft > 0) {
                    const minutesLeft = (24 - <?php echo $hoursDiff; ?>) * 60;
                    const hours = Math.floor(minutesLeft / 60);
                    const minutes = Math.floor(minutesLeft % 60);
                    const seconds = Math.floor((minutesLeft * 60) % 60);
                    
                    timerElement.textContent = 
                        hours.toString().padStart(2, '0') + ':' + 
                        minutes.toString().padStart(2, '0') + ':' + 
                        seconds.toString().padStart(2, '0');
                } else {
                    timerElement.textContent = '00:00:00';
                    location.reload(); // إعادة تحميل الصفحة عند انتهاء المهلة
                }
            }
            
            // تحديث العد التنازلي كل ثانية
            setInterval(updateCountdown, 1000);
            updateCountdown(); // التشغيل الأولي
        </script>
        
    <?php else: ?>
        <!-- عرض صفحة الاشتراك العادية للمستخدمين الذين ليس لديهم طلبات نشطة -->
        <div class="container subscription-disabled">
            <!-- المحتوى الأصلي لصفحة الاشتراك يبقى كما هو -->
            <header class="header">
                <div class="logo">
                    <i class="fas fa-mosque"></i>
                    <h1>مرآة المؤمن</h1>
                </div>
                <h2 class="page-title">الاشتراك الاحترافي</h2>
                <p class="subtitle">اختر الخطة المناسبة لك واستمتع بمميزات حصرية</p>
            </header>

            <div class="plans-container">
                <!-- الخطة الشهرية -->
                <div class="plan-card monthly">
                    <div class="plan-header">
                        <h3 class="plan-title">الخطة الشهرية</h3>
                        <div class="plan-price">
                            <span class="amount">59</span>
                            <span class="currency">جنية</span>
                        </div>
                        <p class="plan-period">/ شهريًا</p>
                    </div>
                    
                    <div class="plan-features">
                        <h4>المميزات:</h4>
                        <ul>
                            <li><i class="fas fa-chart-line"></i> إحصائيات غير محدودة</li>
                            <li><i class="fas fa-pray"></i> إضافة عبادات غير محدودة</li>
                            <li><i class="fab fa-telegram"></i> ربط حسابك على مرآة المؤمن بـ Telegram</li>
                            <li><i class="fas fa-fire"></i> إضافة ستريك على مجموعاتك</li>
                            <li><i class="fas fa-headset"></i> دعم فني مميز</li>
                        </ul>
                    </div>
                    
                    <button class="select-plan-btn" data-plan="monthly" data-amount="59">
                        اختر هذه الخطة
                    </button>
                </div>

                <!-- الخطة السنوية -->
                <div class="plan-card yearly">
                    <div class="plan-header">
                        <h3 class="plan-title">الخطة السنوية</h3>
                        <div class="plan-price">
                            <span class="amount">659</span>
                            <span class="currency">جنية</span>
                        </div>
                        <p class="plan-period">/ سنويًا</p>
                        <div class="discount-badge">وفر 12%</div>
                    </div>
                    
                    <div class="plan-features">
                        <h4>المميزات:</h4>
                        <ul>
                            <li><i class="fas fa-chart-line"></i> إحصائيات غير محدودة</li>
                            <li><i class="fas fa-pray"></i> إضافة عبادات غير محدودة</li>
                            <li><i class="fab fa-telegram"></i> ربط حسابك على مرآة المؤمن بـ Telegram</li>
                            <li><i class="fas fa-fire"></i> إضافة ستريك على مجموعاتك</li>
                            <li><i class="fas fa-headset"></i> دعم فني مميز</li>
                            <li><i class="fas fa-gift"></i> + شهرين مجانًا</li>
                        </ul>
                    </div>
                    
                    <button class="select-plan-btn" data-plan="yearly" data-amount="659">
                        اختر هذه الخطة
                    </button>
                </div>
            </div>

            <!-- نموذج الدفع -->
            <div class="payment-form-container" id="paymentFormContainer" style="display: none;">
                <div class="form-header">
                    <h3>تفاصيل الدفع</h3>
                    <p id="selectedPlanText"></p>
                </div>
                
                <form id="paymentForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="planType" name="planType" value="">
                    <input type="hidden" id="amount" name="amount" value="">
                    
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> رقم الهاتف</label>
                        <input type="tel" id="phone" name="phone" required placeholder="أدخل رقم هاتفك">
                    </div>
                    
                    <div class="form-group">
                        <label for="instaUser"><i class="fab fa-instagram"></i> اسم المستخدم في InstaPay (اختياري)</label>
                        <input type="text" id="instaUser" name="instaUser" placeholder="@username">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i> طريقة الدفع</label>
                        <div class="payment-methods">
                            <div class="payment-method selected">
                                <i class="fas fa-mobile-alt"></i>
                                <span>تحويل بنكي</span>
                            </div>
                            <div class="payment-method">
                                <i class="fab fa-instalod"></i>
                                <span>InstaPay</span>
                            </div>
                        </div>
                        
                        <div class="payment-info">
                            <p><strong>رقم الحساب:</strong> 1234 5678 9012 3456</p>
                            <p><strong>اسم البنك:</strong> بنك التجارة والتنمية</p>
                            <p><strong>اسم المستفيد:</strong> مرآة المؤمن</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="button" id="nextStepBtn" class="next-btn">
                            التالي <i class="fas fa-arrow-left"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- خطوة رفع الإيصال -->
            <div class="receipt-container" id="receiptContainer" style="display: none;">
                <div class="form-header">
                    <h3>رفع إيصال الدفع</h3>
                    <p>قم بتصوير أو تحميل إيصال الدفع</p>
                </div>
                
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>اسحب وأفلت الصورة هنا أو <span>انقر للتصفح</span></p>
                    <input type="file" id="receiptImage" name="receiptImage" accept="image/*" capture="environment">
                    <p class="upload-note">يُفضل تصوير الإيصال بوضوح لتسهيل معالجته</p>
                </div>
                
                <div class="preview-container" id="previewContainer" style="display: none;">
                    <div class="image-preview">
                        <img id="imagePreview" src="" alt="معاينة الإيصال">
                        <button class="remove-image-btn" id="removeImageBtn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <!-- تحديث قسم معالجة الصورة -->
                    <div class="processing-status" id="processingStatus">
                        <div class="spinner"></div>
                        <p>جاري معالجة الصورة واستخراج البيانات...</p>
                        
                        <!-- إضافة شريط التقدم -->
                        <div class="quality-indicator">
                            <div class="quality-bar">
                                <div class="quality-fill" id="qualityFill"></div>
                            </div>
                            <div class="progress-text" id="progressText">0%</div>
                        </div>
                        
                        <!-- نص تحسين الجودة -->
                        <div class="quality-note">
                            <i class="fas fa-lightbulb"></i>
                            للحصول على أفضل نتائج، تأكد أن الإيصال واضح والإضاءة جيدة
                        </div>
                    </div>
                    
                    <div class="extracted-data" id="extractedData" style="display: none;">
                        <h4><i class="fas fa-database"></i> البيانات المستخرجة:</h4>
                        <div class="data-fields">
                            <div class="data-field">
                                <label>المبلغ:</label>
                                <span id="extractedAmount">---</span>
                            </div>
                            <div class="data-field">
                                <label>التاريخ:</label>
                                <span id="extractedDate">---</span>
                            </div>
                            <div class="data-field">
                                <label>رقم المرجع:</label>
                                <span id="extractedReference">---</span>
                            </div>
                            <div class="data-field">
                                <label>اسم المرسل:</label>
                                <span id="extractedSender">---</span>
                            </div>
                        </div>
                        
                        <button type="button" id="confirmUploadBtn" class="confirm-btn">
                            تأكيد الرفع <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="backToPaymentBtn" class="back-btn">
                        <i class="fas fa-arrow-right"></i> العودة
                    </button>
                </div>
            </div>

            <!-- رسالة النجاح -->
            <div class="success-container" id="successContainer" style="display: none;">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>تم استلام طلبك بنجاح!</h3>
                <p>سيتم مراجعة الإيصال وتفعيل اشتراكك في غضون 24 ساعة</p>
                <div class="success-details">
                    <p><strong>رقم الطلب:</strong> <span id="orderId">---</span></p>
                    <p><strong>تاريخ الطلب:</strong> <span id="orderDate">---</span></span></p>
                </div>
                <button class="home-btn" onclick="window.location.href='index.php'">
                    العودة للرئيسية
                </button>
            </div>
        </div>

        <!-- نافذة التأكيد -->
        <div class="modal-overlay" id="confirmationModal" style="display: none;">
            <div class="modal">
                <h3><i class="fas fa-check-circle"></i> تأكيد الطلب</h3>
                <p>هل أنت متأكد من إرسال طلب الاشتراك؟</p>
                <div class="modal-actions">
                    <button class="modal-cancel" id="modalCancel">إلغاء</button>
                    <button class="modal-confirm" id="modalConfirm">تأكيد الإرسال</button>
                </div>
            </div>
        </div>

        <script src="assets/js/pro_subscription.js"></script>
        <!-- مكتبة Tesseract.js لاستخراج النصوص من الصور -->
        <script src="https://cdn.jsdelivr.net/npm/tesseract.js@v2.1.0/dist/tesseract.min.js"></script>
        </div>
    <?php endif; ?>
    
    <?php if (!$userProStatus && !$hasPendingRequest && !$hasVerifiedSubscription && !isset($recentlyRejected)): ?>
    <script>
        // إظهار واجهة الاشتراك فقط عندما يكون مسموحاً
        document.addEventListener('DOMContentLoaded', function() {
            const subscriptionContainer = document.querySelector('.subscription-disabled');
            if (subscriptionContainer) {
                subscriptionContainer.classList.remove('subscription-disabled');
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>