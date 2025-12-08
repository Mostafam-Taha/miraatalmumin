<?php
// ملف مبسط لعرض الرسالة بدون الحاجة إلى API
session_start();
require_once '../includes/config.php';

if (!isset($_GET['id'])) {
    die('معرف غير محدد');
}

$feedback_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("
        SELECT f.*, u.email as user_email, u.name as user_name 
        FROM feedbacks f 
        LEFT JOIN users u ON f.user_id = u.id 
        WHERE f.id = ?
    ");
    
    $stmt->execute([$feedback_id]);
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$feedback) {
        die('الملاحظة غير موجودة');
    }
} catch (PDOException $e) {
    die('خطأ في قاعدة البيانات: ' . $e->getMessage());
}

$type_names = [
    'suggestion' => 'اقتراح',
    'complaint' => 'شكوى',
    'bug' => 'خطأ',
    'thanks' => 'شكر'
];

$status_names = [
    'new' => 'جديد',
    'read' => 'تم القراءة',
    'in_progress' => 'قيد المعالجة',
    'resolved' => 'تم الحل'
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الرسالة - #<?php echo $feedback['id']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Tajawal', sans-serif; background: #f8fafc; padding: 30px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #059669, #047857); color: white; padding: 25px; border-radius: 15px; margin-bottom: 30px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; padding: 20px; background: #f8fafc; border-radius: 15px; }
        .info-item { display: flex; flex-direction: column; gap: 5px; }
        .info-label { font-size: 14px; color: #6b7280; font-weight: 600; }
        .info-value { font-size: 16px; font-weight: 600; color: #1e293b; }
        .message-box { background: white; padding: 25px; border-radius: 15px; border: 2px solid #e5e7eb; font-size: 18px; line-height: 1.8; margin-bottom: 20px; white-space: pre-wrap; min-height: 200px; }
        .actions { display: flex; gap: 10px; margin-top: 30px; }
        .btn { padding: 12px 25px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-copy { background: #3b82f6; color: white; }
        .btn-close { background: #6b7280; color: white; }
        .timestamp { color: #6b7280; font-size: 14px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ملاحظة #<?php echo $feedback['id']; ?></h1>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">المستخدم</span>
                <span class="info-value"><?php echo htmlspecialchars($feedback['user_name']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">البريد الإلكتروني</span>
                <span class="info-value"><?php echo htmlspecialchars($feedback['user_email']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">النوع</span>
                <span class="info-value"><?php echo $type_names[$feedback['feedback_type']]; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">الحالة</span>
                <span class="info-value"><?php echo $status_names[$feedback['status']]; ?></span>
            </div>
        </div>
        
        <div class="message-box">
            <?php if (!empty($feedback['message'])): ?>
                <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
            <?php else: ?>
                <div style="text-align: center; color: #9ca3af; font-style: italic;">لا توجد رسالة مكتوبة</div>
            <?php endif; ?>
        </div>
        
        <div class="timestamp">
            <strong>تاريخ الإرسال:</strong> <?php echo date('Y-m-d H:i', strtotime($feedback['created_at'])); ?>
            <?php if ($feedback['created_at'] != $feedback['updated_at']): ?>
                <br><strong>آخر تحديث:</strong> <?php echo date('Y-m-d H:i', strtotime($feedback['updated_at'])); ?>
            <?php endif; ?>
        </div>
        
        <div class="actions">
            <button class="btn btn-copy" onclick="copyMessage()">
                <i class="fas fa-copy"></i> نسخ الرسالة
            </button>
            <button class="btn btn-close" onclick="window.close()">
                <i class="fas fa-times"></i> إغلاق
            </button>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        function copyMessage() {
            const messageBox = document.querySelector('.message-box');
            const textToCopy = messageBox.innerText;
            
            navigator.clipboard.writeText(textToCopy)
                .then(() => {
                    alert('تم نسخ الرسالة إلى الحافظة');
                })
                .catch(err => {
                    console.error('Failed to copy: ', err);
                    alert('فشل نسخ الرسالة');
                });
        }
    </script>
</body>
</html>