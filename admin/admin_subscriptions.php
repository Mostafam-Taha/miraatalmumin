<?php
// إعدادات الاتصال بقاعدة البيانات
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

// تعريف الألوان
$button_color = '#059669';
$text_color = '#111827';
$secondary_color = '#047857';
$bg_color = '#ffffff';

// دالة لإرسال رسالة Telegram
function sendTelegramMessage($botToken, $chatId, $message) {
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode == 200;
}

// دالة لإعداد رسالة الموافقة
function getVerificationMessage($userName, $planType, $amount) {
    $planText = ($planType === 'yearly') ? 'سنوي' : 'شهري';
    
    return "🎉 <b>مبروك! تم تفعيل اشتراكك Pro</b>\n\n"
         . "👤 <b>المستخدم:</b> {$userName}\n"
         . "📋 <b>نوع الاشتراك:</b> {$planText}\n"
         . "💰 <b>المبلغ:</b> {$amount} ريال\n"
         . "✅ <b>الحالة:</b> تم التحقق والتفعيل\n\n"
         . "🔓 الآن يمكنك الوصول لجميع ميزات Pro المميزة!\n"
         . "📱 تم تحديث حسابك تلقائياً في التطبيق.\n\n"
         . "شكراً لثقتك بنا! 🙏";
}

// دالة لإعداد رسالة الرفض
function getRejectionMessage($userName, $planType, $amount) {
    $planText = ($planType === 'yearly') ? 'سنوي' : 'شهري';
    
    return "❌ <b>تم رفض طلب الاشتراك</b>\n\n"
         . "👤 <b>المستخدم:</b> {$userName}\n"
         . "📋 <b>نوع الاشتراك:</b> {$planText}\n"
         . "💰 <b>المبلغ:</b> {$amount} ريال\n"
         . "❌ <b>الحالة:</b> مرفوض\n\n"
         . "⚠️ <b>سبب الرفض:</b>\n"
         . "• يرجى التحقق من صحة الإيصال المرفوع\n"
         . "• أو التواصل مع الدعم الفني للمساعدة\n\n"
         . "يمكنك إعادة المحاولة برفع إيصال واضح وصحيح.";
}

// معالجة طلبات AJAX
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'get_subscription') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("SELECT 
                ps.*, 
                u.name, 
                u.email,
                u.profile_picture,
                u.current_prayer_streak,
                u.max_prayer_streak,
                u.created_at as user_created_at,
                u.Pro as user_pro_status
            FROM pro_subscriptions ps
            LEFT JOIN users u ON ps.user_id = u.id
            WHERE ps.id = ?");
        $stmt->execute([$id]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // الحصول على إعدادات Telegram للمستخدم إن وجدت
        $stmt = $pdo->prepare("SELECT * FROM telegram_settings WHERE user_id = ?");
        $stmt->execute([$subscription['user_id']]);
        $telegramSettings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $subscription,
            'telegram' => $telegramSettings
        ]);
        exit;
    }
    
    if ($_POST['action'] === 'update_status') {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $rejection_reason = $_POST['rejection_reason'] ?? '';
        
        $pdo->beginTransaction();
        
        try {
            // تحديث حالة الاشتراك
            $stmt = $pdo->prepare("UPDATE pro_subscriptions SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            // الحصول على بيانات الاشتراك والمستخدم
            $stmt = $pdo->prepare("SELECT 
                    ps.*, 
                    u.name, 
                    u.email,
                    u.Pro as user_pro_status
                FROM pro_subscriptions ps
                LEFT JOIN users u ON ps.user_id = u.id
                WHERE ps.id = ?");
            $stmt->execute([$id]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $response = [
                'success' => true,
                'message' => 'تم تحديث الحالة بنجاح',
                'telegram_sent' => false,
                'telegram_error' => ''
            ];
            
            // إذا كانت الموافقة، تحديث حالة المستخدم
            if ($status === 'verified') {
                $stmt = $pdo->prepare("UPDATE users SET Pro = 1 WHERE id = ?");
                $stmt->execute([$subscription['user_id']]);
                
                // إرسال رسالة Telegram إذا كان المستخدم لديه بوت
                $stmt = $pdo->prepare("SELECT * FROM telegram_settings WHERE user_id = ? AND enable_notifications = 1");
                $stmt->execute([$subscription['user_id']]);
                $telegramSettings = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($telegramSettings && !empty($telegramSettings['token']) && !empty($telegramSettings['chat_id'])) {
                    $message = getVerificationMessage(
                        $subscription['name'],
                        $subscription['plan_type'],
                        number_format($subscription['amount'], 2)
                    );
                    
                    if (sendTelegramMessage($telegramSettings['token'], $telegramSettings['chat_id'], $message)) {
                        $response['telegram_sent'] = true;
                        $response['telegram_message'] = 'تم إرسال رسالة الموافقة إلى Telegram';
                    } else {
                        $response['telegram_sent'] = false;
                        $response['telegram_error'] = 'فشل إرسال رسالة Telegram';
                    }
                }
            } 
            elseif ($status === 'rejected') {
                // إرسال رسالة رفض إلى Telegram إذا كان المستخدم لديه بوت
                $stmt = $pdo->prepare("SELECT * FROM telegram_settings WHERE user_id = ? AND enable_notifications = 1");
                $stmt->execute([$subscription['user_id']]);
                $telegramSettings = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($telegramSettings && !empty($telegramSettings['token']) && !empty($telegramSettings['chat_id'])) {
                    $message = getRejectionMessage(
                        $subscription['name'],
                        $subscription['plan_type'],
                        number_format($subscription['amount'], 2)
                    );
                    
                    // إضافة سبب الرفض إذا تم تقديمه
                    if (!empty($rejection_reason)) {
                        $message .= "\n\n📝 <b>ملاحظة الإدمن:</b>\n{$rejection_reason}";
                    }
                    
                    if (sendTelegramMessage($telegramSettings['token'], $telegramSettings['chat_id'], $message)) {
                        $response['telegram_sent'] = true;
                        $response['telegram_message'] = 'تم إرسال رسالة الرفض إلى Telegram';
                    } else {
                        $response['telegram_sent'] = false;
                        $response['telegram_error'] = 'فشل إرسال رسالة Telegram';
                    }
                }
            }
            
            $pdo->commit();
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}

// جلب الإحصائيات
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status = 'pending'")->fetchColumn(),
    'verified' => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status = 'verified'")->fetchColumn(),
    'rejected' => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status = 'rejected'")->fetchColumn()
];

// جلب الاشتراكات للجدول
$sql = "SELECT 
            ps.*, 
            u.name, 
            u.email,
            u.Pro as user_pro_status
        FROM pro_subscriptions ps
        LEFT JOIN users u ON ps.user_id = u.id
        ORDER BY ps.created_at DESC
        LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الاشتراكات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* الأنماط السابقة تبقى كما هي مع بعض الإضافات */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            overflow-y: scroll;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            background-color: <?php echo $bg_color; ?>;
            color: <?php echo $text_color; ?>;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .header h1 {
            color: <?php echo $text_color; ?>;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
        }
        
        .stat-box.total { border-top: 4px solid <?php echo $secondary_color; ?>; }
        .stat-box.pending { border-top: 4px solid #f59e0b; }
        .stat-box.verified { border-top: 4px solid <?php echo $button_color; ?>; }
        .stat-box.rejected { border-top: 4px solid #ef4444; }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-box.total .stat-number { color: <?php echo $secondary_color; ?>; }
        .stat-box.pending .stat-number { color: #f59e0b; }
        .stat-box.verified .stat-number { color: <?php echo $button_color; ?>; }
        .stat-box.rejected .stat-number { color: #ef4444; }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: #f8fafc;
        }
        
        th {
            padding: 15px;
            text-align: right;
            font-weight: 600;
            color: <?php echo $text_color; ?>;
            border-bottom: 2px solid #e5e7eb;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        th:hover {
            background-color: #f1f5f9;
        }
        
        th i {
            margin-right: 5px;
            opacity: 0.5;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        tbody tr {
            transition: background-color 0.3s;
            cursor: pointer;
        }
        
        tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-verified { background-color: #d1fae5; color: #065f46; }
        .badge-rejected { background-color: #fee2e2; color: #991b1b; }
        .badge-pro { background-color: <?php echo $button_color; ?>; color: white; }
        
        .amount {
            font-weight: bold;
            color: <?php echo $secondary_color; ?>;
        }
        
        /* نافذة منبثقة */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
        }
        
        .modal {
            background: white;
            border-radius: 15px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.3s ease;
            overflow-y: scroll;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, <?php echo $secondary_color; ?>, <?php echo $button_color; ?>);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-header h2 {
            font-size: 22px;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }
        
        .close-modal:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .modal-section {
            margin-bottom: 25px;
        }
        
        .modal-section h3 {
            color: <?php echo $text_color; ?>;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-section h3 i {
            color: <?php echo $secondary_color; ?>;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-label {
            display: block;
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: <?php echo $text_color; ?>;
            font-weight: 500;
            font-size: 15px;
        }
        
        .receipt-image {
            max-width: 100%;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .receipt-image:hover {
            transform: scale(1.02);
        }
        
        .extracted-data-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid <?php echo $secondary_color; ?>;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            font-size: 13px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 0 0 15px 15px;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1;
        }
        
        .btn-verify {
            background-color: <?php echo $button_color; ?>;
            color: white;
        }
        
        .btn-verify:hover {
            background-color: <?php echo $secondary_color; ?>;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(5, 150, 105, 0.4);
        }
        
        .btn-reject {
            background-color: #ef4444;
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
        }
        
        .btn-close {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-close:hover {
            background-color: #4b5563;
            transform: translateY(-2px);
        }
        
        .status-display {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1001;
            animation: slideIn 0.3s ease;
            display: none;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
        }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .message.success {
            background-color: <?php echo $button_color; ?>;
        }
        
        .message.error {
            background-color: #ef4444;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #d1d5db;
        }
        
        /* فلاتر البحث */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .filter-btn:hover {
            background: #f3f4f6;
        }
        
        .filter-btn.active {
            background: <?php echo $button_color; ?>;
            color: white;
            border-color: <?php echo $button_color; ?>;
        }
        
        .search-box {
            flex-grow: 1;
            max-width: 300px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .modal-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
        }
        
            .search-box {
                max-width: 100%;
        }
        
            th, td {
                padding: 10px;
                font-size: 14px;
            }
        }

                /* إضافة أنماط جديدة */
        .telegram-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: linear-gradient(135deg, #0088cc, #34b7f1);
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-right: 5px;
        }
        
        .telegram-badge.inactive {
            background: #9ca3af;
        }
        
        .rejection-reason-container {
            margin-top: 15px;
            padding: 15px;
            background: #fef3c7;
            border-radius: 8px;
            border: 1px solid #f59e0b;
            display: none;
        }
        
        .rejection-reason-container h4 {
            color: #92400e;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .rejection-reason-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }
        
        .telegram-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f0f9ff;
            border-radius: 8px;
            border: 1px solid #bae6fd;
            margin: 10px 0;
        }
        
        .telegram-info i {
            color: #0088cc;
            font-size: 18px;
        }
        
        /* البقية كما هي مع تعديلات بسيطة */
        .modal-body {
            padding: 25px;
        }
        
        .modal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .modal-section {
            margin-bottom: 25px;
        }
        
        .modal-section h3 {
            color: <?php echo $text_color; ?>;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-section h3 i {
            color: <?php echo $secondary_color; ?>;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-label {
            display: block;
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: <?php echo $text_color; ?>;
            font-weight: 500;
            font-size: 15px;
        }
        
        .receipt-image {
            max-width: 100%;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .receipt-image:hover {
            transform: scale(1.02);
        }
        
        .extracted-data-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid <?php echo $secondary_color; ?>;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            font-size: 13px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 0 0 15px 15px;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1;
        }
        
        .btn-verify {
            background-color: <?php echo $button_color; ?>;
            color: white;
        }
        
        .btn-verify:hover {
            background-color: <?php echo $secondary_color; ?>;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(5, 150, 105, 0.4);
        }
        
        .btn-reject {
            background-color: #ef4444;
            color: white;
        }
        
        .btn-reject:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
        }
        
        .btn-close {
            background-color: #6b7280;
            color: white;
        }
        
        .btn-close:hover {
            background-color: #4b5563;
            transform: translateY(-2px);
        }
        
        .status-display {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .telegram-message {
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 8px;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #0369a1;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .telegram-message.error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }
        
        .telegram-message.success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #059669;
        }
        
        /* نافذة منبثقة */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
        }
        
        .modal {
            background: white;
            border-radius: 15px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalFadeIn 0.3s ease;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: linear-gradient(135deg, <?php echo $secondary_color; ?>, <?php echo $button_color; ?>);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-header h2 {
            font-size: 22px;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }
        
        .close-modal:hover {
            background-color: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-crown"></i> لوحة تحكم اشتراكات Pro</h1>
            <p>إدارة جميع طلبات الاشتراكات المميزة</p>
        </div>
        
        <!-- إحصائيات سريعة -->
        <div class="stats-grid">
            <div class="stat-box total">
                <div class="stat-label">إجمالي الطلبات</div>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-box pending">
                <div class="stat-label">قيد الانتظار</div>
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
            </div>
            <div class="stat-box verified">
                <div class="stat-label">مفعلة</div>
                <div class="stat-number"><?php echo $stats['verified']; ?></div>
            </div>
            <div class="stat-box rejected">
                <div class="stat-label">مرفوضة</div>
                <div class="stat-number"><?php echo $stats['rejected']; ?></div>
            </div>
        </div>
        
        <!-- فلاتر -->
        <div class="filters">
            <button class="filter-btn active" data-filter="all">الكل</button>
            <button class="filter-btn" data-filter="pending">قيد الانتظار</button>
            <button class="filter-btn" data-filter="verified">مفعلة</button>
            <button class="filter-btn" data-filter="rejected">مرفوضة</button>
            <div class="search-box">
                <input type="text" class="search-input" placeholder="بحث بالاسم أو البريد الإلكتروني...">
            </div>
        </div>
        
        <!-- جدول الاشتراكات -->
        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-table"></i> قائمة الاشتراكات</h3>
                <div>عرض <span id="row-count"><?php echo count($subscriptions); ?></span> طلب</div>
            </div>
            
            <?php if (empty($subscriptions)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>لا توجد طلبات اشتراك</h3>
                    <p>لم يتم العثور على أي طلبات اشتراك حتى الآن</p>
                </div>
            <?php else: ?>
                <table id="subscriptions-table">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>رقم الهاتف</th>
                            <th>نوع الاشتراك</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>تاريخ الطلب</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $sub): 
                            // التحقق مما إذا كان المستخدم لديه Telegram
                            $stmt = $pdo->prepare("SELECT COUNT(*) as has_telegram FROM telegram_settings WHERE user_id = ? AND token IS NOT NULL AND chat_id IS NOT NULL");
                            $stmt->execute([$sub['user_id']]);
                            $hasTelegram = $stmt->fetch(PDO::FETCH_ASSOC)['has_telegram'] > 0;
                        ?>
                            <tr class="subscription-row" 
                                data-id="<?php echo $sub['id']; ?>"
                                data-status="<?php echo $sub['status']; ?>">
                                <td>
                                    <div class="user-cell">
                                        <div>
                                            <div class="user-name">
                                                <?php if ($sub['user_pro_status'] == 1): ?>
                                                    <span class="badge badge-pro">PRO</span>
                                                <?php endif; ?>
                                                <?php if ($hasTelegram): ?>
                                                    <span class="telegram-badge" title="يوجد Telegram Bot">
                                                        <i class="fab fa-telegram"></i> Bot
                                                    </span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($sub['name']); ?>
                                            </div>
                                            <small style="color: #6b7280;"><?php echo htmlspecialchars($sub['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($sub['phone']); ?></td>
                                <td><?php echo $sub['plan_type'] === 'yearly' ? 'سنوي' : 'شهري'; ?></td>
                                <td class="amount"><?php echo number_format($sub['amount'], 2); ?> ر.س</td>
                                <td>
                                    <span class="badge badge-<?php echo $sub['status']; ?>">
                                        <?php 
                                        $status_text = [
                                            'pending' => 'قيد الانتظار',
                                            'verified' => 'مفعل',
                                            'rejected' => 'مرفوض'
                                        ];
                                        echo $status_text[$sub['status']];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($sub['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- نافذة منبثقة لعرض التفاصيل -->
    <div class="modal-overlay" id="subscriptionModal">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-file-invoice"></i> تفاصيل الاشتراك</h2>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- سيتم ملء المحتوى عبر JavaScript -->
            </div>
            
            <!-- حقل سبب الرفض -->
            <div class="rejection-reason-container" id="rejectionReasonContainer">
                <h4><i class="fas fa-comment-dots"></i> سبب الرفض (اختياري)</h4>
                <textarea class="rejection-reason-textarea" id="rejectionReason" 
                          placeholder="يمكنك كتابة سبب الرفض هنا... سيتم إرساله للمستخدم عبر Telegram إذا كان لديه بوت"></textarea>
            </div>
            
            <div class="modal-actions" id="modalActions">
                <!-- سيتم ملء الأزرار عبر JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- رسالة تنبيه -->
    <div class="message" id="message"></div>
    
    <script>
        // عناصر DOM
        const modal = document.getElementById('subscriptionModal');
        const modalContent = document.getElementById('modalContent');
        const modalActions = document.getElementById('modalActions');
        const closeModal = document.getElementById('closeModal');
        const message = document.getElementById('message');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const searchInput = document.querySelector('.search-input');
        const tableRows = document.querySelectorAll('.subscription-row');
        const rowCount = document.getElementById('row-count');
        const rejectionReasonContainer = document.getElementById('rejectionReasonContainer');
        const rejectionReason = document.getElementById('rejectionReason');
        
        // متغيرات حالة
        let currentSubscriptionId = null;
        let currentSubscriptionStatus = null;
        let hasTelegramBot = false;
        
        // فتح النافذة المنبثقة عند النقر على صف
        tableRows.forEach(row => {
            row.addEventListener('click', function() {
                const subscriptionId = this.dataset.id;
                currentSubscriptionId = subscriptionId;
                currentSubscriptionStatus = this.dataset.status;
                loadSubscriptionDetails(subscriptionId);
            });
        });
        
        // إغلاق النافذة المنبثقة
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
            resetRejectionReason();
        });
        
        // إغلاق النافذة عند النقر خارجها
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                resetRejectionReason();
            }
        });
        
        // فلترة الجدول
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                filterTable(filter);
            });
        });
        
        // بحث في الجدول
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterTable(null, searchTerm);
        });
        
        // دالة لتحميل تفاصيل الاشتراك
        async function loadSubscriptionDetails(id) {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax=true&action=get_subscription&id=${id}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    hasTelegramBot = result.telegram && 
                                    result.telegram.token && 
                                    result.telegram.chat_id && 
                                    result.telegram.enable_notifications == 1;
                    
                    displaySubscriptionModal(data, result.telegram);
                    modal.style.display = 'flex';
                    rejectionReasonContainer.style.display = 'none';
                } else {
                    showMessage('حدث خطأ في تحميل البيانات', 'error');
                }
            } catch (error) {
                showMessage('حدث خطأ في الاتصال', 'error');
            }
        }
        
        // دالة لعرض النافذة المنبثقة
        function displaySubscriptionModal(data, telegramSettings) {
            const statusText = {
                'pending': 'قيد الانتظار',
                'verified': 'مفعل',
                'rejected': 'مرفوض'
            };
            
            const statusColor = {
                'pending': '#f59e0b',
                'verified': '#059669',
                'rejected': '#ef4444'
            };
            
            const planTypeText = data.plan_type === 'yearly' ? 'سنوي' : 'شهري';
            const userCreated = new Date(data.user_created_at).toLocaleDateString('ar-SA');
            const paymentDate = new Date(data.payment_date).toLocaleString('ar-SA');
            const subscriptionDate = new Date(data.created_at).toLocaleString('ar-SA');
            
            // عرض معلومات Telegram إذا كان متوفراً
            let telegramInfo = '';
            if (telegramSettings && telegramSettings.token) {
                const telegramStatus = telegramSettings.enable_notifications == 1 ? 'مفعل' : 'معطل';
                telegramInfo = `
                    <div class="telegram-info">
                        <i class="fab fa-telegram"></i>
                        <div>
                            <strong>Telegram Bot متوفر</strong>
                            <div style="font-size: 12px; color: #4b5563;">
                                ${telegramSettings.bot_name ? 'الاسم: ' + telegramSettings.bot_name : ''}
                                • الإشعارات: ${telegramStatus}
                            </div>
                        </div>
                    </div>
                `;
            }
            
            modalContent.innerHTML = `
                ${telegramInfo}
                
                <div class="modal-grid">
                    <div class="modal-section">
                        <h3><i class="fas fa-user"></i> معلومات المستخدم</h3>
                        <div class="info-group">
                            <span class="info-label">الاسم:</span>
                            <div class="info-value">${data.name || 'غير متوفر'}</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">البريد الإلكتروني:</span>
                            <div class="info-value">${data.email || 'غير متوفر'}</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">تاريخ التسجيل:</span>
                            <div class="info-value">${userCreated}</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">حالة Pro:</span>
                            <div class="info-value">
                                <span class="badge ${data.user_pro_status == 1 ? 'badge-pro' : ''}">
                                    ${data.user_pro_status == 1 ? 'مفعل' : 'غير مفعل'}
                                </span>
                            </div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">المتواصل الحالي:</span>
                            <div class="info-value">${data.current_prayer_streak || 0} يوم</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">أقصى متواصل:</span>
                            <div class="info-value">${data.max_prayer_streak || 0} يوم</div>
                        </div>
                    </div>
                    
                    <div class="modal-section">
                        <h3><i class="fas fa-file-invoice-dollar"></i> معلومات الاشتراك</h3>
                        <div class="info-group">
                            <span class="info-label">رقم الهاتف:</span>
                            <div class="info-value">${data.phone || 'غير متوفر'}</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">إنستجرام:</span>
                            <div class="info-value">${data.insta_user || 'غير متوفر'}</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">نوع الاشتراك:</span>
                            <div class="info-value">${planTypeText}</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">المبلغ:</span>
                            <div class="info-value amount">${parseFloat(data.amount).toFixed(2)} ر.س</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">تاريخ الدفع:</span>
                            <div class="info-value">${paymentDate}</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">تاريخ الطلب:</span>
                            <div class="info-value">${subscriptionDate}</div>
                        </div>
                        <div class="info-group">
                            <span class="info-label">الحالة:</span>
                            <div class="info-value">
                                <span class="status-display" style="background-color: ${statusColor[data.status] + '20'}; color: ${statusColor[data.status]}">
                                    <i class="fas fa-circle" style="font-size: 10px;"></i>
                                    ${statusText[data.status]}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${data.receipt_image ? `
                <div class="modal-section">
                    <h3><i class="fas fa-receipt"></i> صورة الإيصال</h3>
                    <img src="${data.receipt_image}" alt="صورة الإيصال" class="receipt-image" 
                         onclick="window.open('${data.receipt_image}', '_blank')">
                </div>
                ` : ''}
                
                ${data.extracted_data ? `
                <div class="modal-section">
                    <h3><i class="fas fa-database"></i> البيانات المستخرجة</h3>
                    <div class="extracted-data-box">${data.extracted_data}</div>
                </div>
                ` : ''}
            `;
            
            // إعداد أزرار الإجراءات
            if (data.status === 'pending') {
                modalActions.innerHTML = `
                    <button class="btn btn-verify" onclick="handleVerify()">
                        <i class="fas fa-check-circle"></i> الموافقة وتفعيل Pro
                        ${hasTelegramBot ? '<i class="fab fa-telegram"></i>' : ''}
                    </button>
                    <button class="btn btn-reject" onclick="showRejectionReason()">
                        <i class="fas fa-times-circle"></i> رفض الطلب
                        ${hasTelegramBot ? '<i class="fab fa-telegram"></i>' : ''}
                    </button>
                    <button class="btn btn-close" onclick="closeModalWindow()">
                        <i class="fas fa-times"></i> إغلاق
                    </button>
                `;
                
                // إظهار ملاحظة عن Telegram إذا كان متوفراً
                if (hasTelegramBot) {
                    const telegramNote = document.createElement('div');
                    telegramNote.className = 'telegram-message';
                    telegramNote.innerHTML = `
                        <i class="fab fa-telegram"></i>
                        <span>سيتم إرسال إشعار للمستخدم عبر Telegram Bot عند الموافقة أو الرفض</span>
                    `;
                    modalContent.insertBefore(telegramNote, modalContent.firstChild);
                }
            } else {
                modalActions.innerHTML = `
                    <button class="btn btn-close" style="flex: none; width: auto;" onclick="closeModalWindow()">
                        <i class="fas fa-times"></i> إغلاق
                    </button>
                `;
            }
        }
        
        // دالة لإظهار حقل سبب الرفض
        function showRejectionReason() {
            rejectionReasonContainer.style.display = 'block';
            modalActions.innerHTML = `
                <button class="btn btn-reject" onclick="handleReject()">
                    <i class="fas fa-times-circle"></i> تأكيد الرفض
                    ${hasTelegramBot ? '<i class="fab fa-telegram"></i>' : ''}
                </button>
                <button class="btn btn-close" onclick="cancelRejection()">
                    <i class="fas fa-times"></i> إلغاء
                </button>
            `;
        }
        
        // دالة لإلغاء الرفض
        function cancelRejection() {
            rejectionReasonContainer.style.display = 'none';
            resetRejectionReason();
            
            // إعادة عرض الأزرار الأصلية
            if (currentSubscriptionId) {
                // إعادة تحميل التفاصيل
                loadSubscriptionDetails(currentSubscriptionId);
            }
        }
        
        // دالة لتفريغ حقل سبب الرفض
        function resetRejectionReason() {
            rejectionReason.value = '';
        }
        
        // دالة للموافقة
        async function handleVerify() {
            if (confirm('هل أنت متأكد من الموافقة على هذا الاشتراك وتفعيل حساب Pro للمستخدم؟')) {
                await updateSubscriptionStatus(currentSubscriptionId, 'verified');
            }
        }
        
        // دالة للرفض
        async function handleReject() {
            if (confirm('هل أنت متأكد من رفض هذا الاشتراك؟')) {
                await updateSubscriptionStatus(currentSubscriptionId, 'rejected', rejectionReason.value);
            }
        }
        
        // دالة لإغلاق النافذة
        function closeModalWindow() {
            modal.style.display = 'none';
            resetRejectionReason();
        }
        
        // دالة لتحديث حالة الاشتراك
        async function updateSubscriptionStatus(id, status, rejectionReasonText = '') {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax=true&action=update_status&id=${id}&status=${status}&rejection_reason=${encodeURIComponent(rejectionReasonText)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    let messageText = result.message;
                    
                    // إضافة رسالة Telegram إلى التنبيه
                    if (result.telegram_sent) {
                        messageText += ' - ' + result.telegram_message;
                        showMessage(messageText, 'success');
                    } else if (result.telegram_error) {
                        messageText += ' (فشل إرسال Telegram)';
                        showMessage(messageText, 'error');
                    } else {
                        showMessage(messageText, 'success');
                    }
                    
                    // تحديث الصف في الجدول
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        const badge = row.querySelector('.badge');
                        
                        // تحديث الحالة
                        row.dataset.status = status;
                        
                        // تحديث البادج
                        badge.className = `badge badge-${status}`;
                        badge.textContent = status === 'pending' ? 'قيد الانتظار' : 
                                          status === 'verified' ? 'مفعل' : 'مرفوض';
                        
                        // إذا تمت الموافقة، إضافة بادج PRO
                        if (status === 'verified') {
                            const userNameCell = row.querySelector('.user-name');
                            if (!userNameCell.querySelector('.badge-pro')) {
                                const telegramBadge = userNameCell.querySelector('.telegram-badge');
                                const nameText = userNameCell.textContent.replace('Bot', '').trim();
                                userNameCell.innerHTML = `
                                    <span class="badge badge-pro">PRO</span>
                                    ${telegramBadge ? '<span class="telegram-badge"><i class="fab fa-telegram"></i> Bot</span>' : ''}
                                    ${nameText}
                                `;
                            }
                        }
                    }
                    
                    // إغلاق النافذة بعد ثانيتين
                    setTimeout(() => {
                        modal.style.display = 'none';
                        resetRejectionReason();
                        updateStats();
                    }, 2000);
                    
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('حدث خطأ في الاتصال', 'error');
            }
        }
        
        // دالة لعرض رسائل التنبيه
        function showMessage(text, type) {
            message.textContent = text;
            message.className = `message ${type}`;
            message.style.display = 'block';
            
            setTimeout(() => {
                message.style.display = 'none';
            }, 5000);
        }
        
        // دالة لفلترة الجدول
        function filterTable(statusFilter, searchTerm = '') {
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                const status = row.dataset.status;
                const userName = row.querySelector('.user-name').textContent.toLowerCase();
                const userEmail = row.querySelector('small').textContent.toLowerCase();
                
                let shouldShow = true;
                
                // تطبيق فلتر الحالة
                if (statusFilter && statusFilter !== 'all' && status !== statusFilter) {
                    shouldShow = false;
                }
                
                // تطبيق البحث
                if (searchTerm && !userName.includes(searchTerm) && !userEmail.includes(searchTerm)) {
                    shouldShow = false;
                }
                
                if (shouldShow) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            rowCount.textContent = visibleCount;
        }
        
        // دالة لتحديث الإحصائيات
        async function updateStats() {
            // في تطبيق حقيقي، يمكنك إعادة تحميل الإحصائيات من الخادم
            // يمكنك إضافة AJAX call هنا لتحديث الإحصائيات من الخادم
        }
        
        // إغلاق النافذة بالزر ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                modal.style.display = 'none';
                resetRejectionReason();
            }
        });
    </script>
</body>
</html>