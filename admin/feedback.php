<?php
session_start();
// يمكنك إضافة نظام مصادقة للإدمن هنا

require_once '../includes/config.php';

// جلب جميع التغذية الراجعة
try {
    $sql = "SELECT f.*, u.email as user_email, u.Pro as user_pro 
            FROM feedbacks f 
            LEFT JOIN users u ON f.user_id = u.id 
            ORDER BY f.created_at DESC";
    
    $stmt = $pdo->query($sql);
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}

// تحديث حالة التغذية الراجعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $feedback_id = $_POST['id'];
    $action = $_POST['action'];
    
    $allowed_status = ['new', 'read', 'in_progress', 'resolved'];
    
    if (in_array($action, $allowed_status)) {
        try {
            $stmt = $pdo->prepare("UPDATE feedbacks SET status = ? WHERE id = ?");
            $stmt->execute([$action, $feedback_id]);
            
            header('Location: feedback.php?success=1');
            exit;
        } catch (PDOException $e) {
            $error = "خطأ في تحديث الحالة: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التغذية الراجعة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .header h1 {
            color: #059669;
            margin-bottom: 10px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .feedback-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            padding: 15px;
            background: #f8fafc;
            text-align: right;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .type-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .type-suggestion { background: #dbeafe; color: #1e40af; }
        .type-complaint { background: #fee2e2; color: #991b1b; }
        .type-bug { background: #fef3c7; color: #92400e; }
        .type-thanks { background: #d1fae5; color: #065f46; }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-new { background: #3b82f6; color: white; }
        .status-read { background: #6b7280; color: white; }
        .status-in_progress { background: #f59e0b; color: white; }
        .status-resolved { background: #10b981; color: white; }
        
        .message-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .message-preview:hover {
            color: #059669;
        }
        
        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            background: #f1f5f9;
            border-radius: 12px;
            font-size: 11px;
        }
        
        .user-badge.pro {
            background: #d1fae5;
            color: #065f46;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .btn-view { background: #3b82f6; color: white; }
        .btn-mark-read { background: #6b7280; color: white; }
        .btn-resolve { background: #10b981; color: white; }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        /* نافذة عرض الرسالة بالكامل */
        .message-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
            animation: fadeIn 0.3s ease;
            width: 100%;
            overflow-y: scroll;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .message-modal-content {
            background: white;
            border-radius: 20px;
            width: 100%;
            max-width: 700px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            animation: slideUp 0.4s ease;
            scrollbar-width: none;
            -ms-overflow-style: none;
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
        
        .message-modal-header {
            background: linear-gradient(135deg, #059669, #047857);
            padding: 25px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 20px 20px 0 0;
        }
        
        .message-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .message-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .message-modal-body {
            padding: 30px;
            overflow-y: auto;
            max-height: calc(85vh - 100px);
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        
        .message-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 15px;
            border: 1px solid #e5e7eb;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .message-full-content {
            background: white;
            padding: 25px;
            border-radius: 15px;
            border: 2px solid #f1f5f9;
            font-size: 16px;
            line-height: 1.8;
            white-space: pre-wrap;
            word-wrap: break-word;
            min-height: 150px;
            max-height: 400px;
            overflow-y: auto;
            direction: rtl;
            text-align: right;
        }
        
        .no-message {
            color: #9ca3af;
            font-style: italic;
            text-align: center;
            padding: 40px 20px;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn-copy {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-copy:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        
        .btn-copied {
            background: #10b981;
        }
        
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
            animation: fadeIn 0.3s;
        }
        
        .message-timestamp {
            color: #6b7280;
            font-size: 14px;
            margin-top: 10px;
            text-align: left;
            direction: ltr;
        }
        
        @media (max-width: 768px) {
            .table-header {
                flex-direction: column;
                gap: 10px;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .message-modal-content {
                max-height: 90vh;
            }
            
            .message-modal-body {
                max-height: calc(90vh - 100px);
            }
            
            .message-info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-comments"></i> إدارة التغذية الراجعة</h1>
            <p>عرض وإدارة ملاحظات المستخدمين</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> تم تحديث الحالة بنجاح
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div>إجمالي الملاحظات</div>
                <div class="stat-number"><?php echo count($feedbacks); ?></div>
            </div>
            <div class="stat-card">
                <div>جديدة</div>
                <div class="stat-number" style="color: #3b82f6;">
                    <?php echo count(array_filter($feedbacks, fn($f) => $f['status'] === 'new')); ?>
                </div>
            </div>
            <div class="stat-card">
                <div>قيد المعالجة</div>
                <div class="stat-number" style="color: #f59e0b;">
                    <?php echo count(array_filter($feedbacks, fn($f) => $f['status'] === 'in_progress')); ?>
                </div>
            </div>
            <div class="stat-card">
                <div>تم الحل</div>
                <div class="stat-number" style="color: #10b981;">
                    <?php echo count(array_filter($feedbacks, fn($f) => $f['status'] === 'resolved')); ?>
                </div>
            </div>
        </div>
        
        <div class="feedback-table">
            <div class="table-header">
                <h3>قائمة الملاحظات</h3>
                <div>عرض <?php echo count($feedbacks); ?> ملاحظة</div>
            </div>
            
            <?php if (empty($feedbacks)): ?>
                <div style="text-align: center; padding: 40px; color: #6b7280;">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h3>لا توجد ملاحظات حالياً</h3>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>النوع</th>
                            <th>الرسالة</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $feedback): ?>
                        <tr>
                            <td>
                                <div class="user-badge <?php echo $feedback['user_pro'] == 1 ? 'pro' : ''; ?>">
                                    <?php echo htmlspecialchars($feedback['user_name']); ?>
                                    <?php if ($feedback['user_pro'] == 1): ?>
                                        <i class="fas fa-crown"></i>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 5px;">
                                    <?php echo htmlspecialchars($feedback['user_email']); ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $type_classes = [
                                    'suggestion' => 'type-suggestion',
                                    'complaint' => 'type-complaint',
                                    'bug' => 'type-bug',
                                    'thanks' => 'type-thanks'
                                ];
                                $type_names = [
                                    'suggestion' => 'اقتراح',
                                    'complaint' => 'شكوى',
                                    'bug' => 'خطأ',
                                    'thanks' => 'شكر'
                                ];
                                ?>
                                <span class="type-badge <?php echo $type_classes[$feedback['feedback_type']]; ?>">
                                    <?php echo $type_names[$feedback['feedback_type']]; ?>
                                </span>
                            </td>
                            <td>
                                <div class="message-preview" onclick="viewFullMessage(<?php echo $feedback['id']; ?>)">
                                    <?php if (!empty($feedback['message'])): ?>
                                        <?php echo htmlspecialchars(substr($feedback['message'], 0, 50)); ?>
                                        <?php echo strlen($feedback['message']) > 50 ? '...' : ''; ?>
                                    <?php else: ?>
                                        <span style="color: #9ca3af; font-style: italic;">لا توجد رسالة</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php echo date('Y-m-d H:i', strtotime($feedback['created_at'])); ?>
                            </td>
                            <td>
                                <?php
                                $status_classes = [
                                    'new' => 'status-new',
                                    'read' => 'status-read',
                                    'in_progress' => 'status-in_progress',
                                    'resolved' => 'status-resolved'
                                ];
                                $status_names = [
                                    'new' => 'جديد',
                                    'read' => 'تم القراءة',
                                    'in_progress' => 'قيد المعالجة',
                                    'resolved' => 'تم الحل'
                                ];
                                ?>
                                <span class="status-badge <?php echo $status_classes[$feedback['status']]; ?>">
                                    <?php echo $status_names[$feedback['status']]; ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="action-btn btn-view" onclick="viewFullMessage(<?php echo $feedback['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $feedback['id']; ?>">
                                        <input type="hidden" name="action" value="read">
                                        <button type="submit" class="action-btn btn-mark-read" title="تمت القراءة">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $feedback['id']; ?>">
                                        <input type="hidden" name="action" value="resolved">
                                        <button type="submit" class="action-btn btn-resolve" title="تم الحل">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- نافذة عرض الرسالة بالكامل -->
    <div class="message-modal" id="messageModal">
        <div class="message-modal-content">
            <div class="message-modal-header">
                <h3 id="messageModalTitle">تفاصيل الملاحظة</h3>
                <button class="message-modal-close" onclick="closeMessageModal()">&times;</button>
            </div>
            
            <div class="message-modal-body">
                <!-- رسالة النجاح -->
                <div class="success-message" id="copySuccessMessage">
                    <i class="fas fa-check-circle"></i> تم نسخ الرسالة إلى الحافظة
                </div>
                
                <!-- معلومات الملاحظة -->
                <div class="message-info-grid" id="messageInfo">
                    <!-- سيتم ملؤه بالجافاسكريبت -->
                </div>
                
                <!-- الرسالة الكاملة -->
                <h4 style="margin-bottom: 15px; color: #374151;">الرسالة:</h4>
                <div class="message-full-content" id="messageFullContent">
                    <!-- سيتم ملؤه بالجافاسكريبت -->
                </div>
                
                <!-- الطابع الزمني -->
                <div class="message-timestamp" id="messageTimestamp"></div>
                
                <!-- أزرار الإجراء -->
                <div class="message-actions">
                    <button class="btn-copy" onclick="copyMessageToClipboard()" id="copyMessageBtn">
                        <i class="fas fa-copy"></i> نسخ الرسالة
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // دالة لعرض الرسالة الكاملة
        async function viewFullMessage(feedbackId) {
            try {
                const response = await fetch(`../api/get_feedback.php?id=${feedbackId}`);
                const data = await response.json();
                
                if (data.success) {
                    const feedback = data.feedback;
                    
                    // تعريف الأسماء
                    const typeNames = {
                        'suggestion': 'اقتراح',
                        'complaint': 'شكوى', 
                        'bug': 'خطأ',
                        'thanks': 'شكر'
                    };
                    
                    const statusNames = {
                        'new': 'جديد',
                        'read': 'تم القراءة',
                        'in_progress': 'قيد المعالجة',
                        'resolved': 'تم الحل'
                    };
                    
                    const statusColors = {
                        'new': '#3b82f6',
                        'read': '#6b7280',
                        'in_progress': '#f59e0b',
                        'resolved': '#10b981'
                    };
                    
                    // تحديث العنوان
                    document.getElementById('messageModalTitle').textContent = `ملاحظة #${feedback.id}`;
                    
                    // تحديث معلومات الملاحظة
                    document.getElementById('messageInfo').innerHTML = `
                        <div class="info-item">
                            <span class="info-label">المستخدم</span>
                            <span class="info-value">
                                ${feedback.user_name}
                                ${feedback.user_pro == 1 ? '<i class="fas fa-crown" style="color: #f59e0b; margin-right: 5px;"></i>' : ''}
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">البريد الإلكتروني</span>
                            <span class="info-value">${feedback.user_email || 'غير متوفر'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">نوع الملاحظة</span>
                            <span class="info-value">${typeNames[feedback.feedback_type]}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">الحالة</span>
                            <span class="info-value" style="color: ${statusColors[feedback.status]}">
                                ${statusNames[feedback.status]}
                            </span>
                        </div>
                    `;
                    
                    // تحديث الرسالة الكاملة
                    const messageContent = document.getElementById('messageFullContent');
                    if (feedback.message && feedback.message.trim() !== '') {
                        messageContent.innerHTML = feedback.message.replace(/\n/g, '<br>');
                        messageContent.classList.remove('no-message');
                    } else {
                        messageContent.innerHTML = '<div class="no-message">لا توجد رسالة مكتوبة</div>';
                        messageContent.classList.add('no-message');
                    }
                    
                    // تحديث الطابع الزمني
                    const createdDate = new Date(feedback.created_at);
                    const updatedDate = new Date(feedback.updated_at);
                    const isUpdated = createdDate.getTime() !== updatedDate.getTime();
                    
                    let timestampHTML = `
                        <strong>تاريخ الإرسال:</strong> ${createdDate.toLocaleString('ar-SA')}
                    `;
                    
                    if (isUpdated) {
                        timestampHTML += `
                            <br>
                            <strong>آخر تحديث:</strong> ${updatedDate.toLocaleString('ar-SA')}
                        `;
                    }
                    
                    document.getElementById('messageTimestamp').innerHTML = timestampHTML;
                    
                    // عرض النافذة
                    document.getElementById('messageModal').style.display = 'flex';
                    
                    // إخفاء رسالة النسخ السابقة
                    document.getElementById('copySuccessMessage').style.display = 'none';
                    document.getElementById('copyMessageBtn').classList.remove('btn-copied');
                    document.getElementById('copyMessageBtn').innerHTML = '<i class="fas fa-copy"></i> نسخ الرسالة';
                    
                } else {
                    alert('حدث خطأ في تحميل البيانات: ' + (data.message || 'يرجى المحاولة مرة أخرى'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال بالخادم');
            }
        }
        
        // دالة نسخ الرسالة إلى الحافظة
        function copyMessageToClipboard() {
            const messageContent = document.getElementById('messageFullContent');
            
            // إذا كانت هناك رسالة
            if (!messageContent.classList.contains('no-message')) {
                // نسخ النص (بدون HTML tags)
                const textToCopy = messageContent.innerText;
                
                // استخدام Clipboard API الحديثة
                navigator.clipboard.writeText(textToCopy)
                    .then(() => {
                        // عرض رسالة النجاح
                        const copyBtn = document.getElementById('copyMessageBtn');
                        const successMessage = document.getElementById('copySuccessMessage');
                        
                        copyBtn.classList.add('btn-copied');
                        copyBtn.innerHTML = '<i class="fas fa-check"></i> تم النسخ!';
                        successMessage.style.display = 'block';
                        
                        // إعادة الزر إلى حالته الأصلية بعد 2 ثانية
                        setTimeout(() => {
                            copyBtn.classList.remove('btn-copied');
                            copyBtn.innerHTML = '<i class="fas fa-copy"></i> نسخ الرسالة';
                        }, 2000);
                    })
                    .catch(err => {
                        console.error('Failed to copy: ', err);
                        alert('فشل نسخ الرسالة. يرجى المحاولة يدوياً.');
                    });
            }
        }
        
        // دالة إغلاق نافذة الرسالة
        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }
        
        // إغلاق النافذة بالضغط خارجها
        document.getElementById('messageModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeMessageModal();
            }
        });
        
        // إغلاق النافذة بالضغط على ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMessageModal();
            }
        });
        
        // إضافة تأثير للرسائل المعروضة عند التمرير عليها
        document.addEventListener('DOMContentLoaded', function() {
            const messagePreviews = document.querySelectorAll('.message-preview');
            
            messagePreviews.forEach(preview => {
                preview.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f1f5f9';
                    this.style.padding = '5px 10px';
                    this.style.borderRadius = '8px';
                });
                
                preview.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = 'transparent';
                    this.style.padding = '0';
                    this.style.borderRadius = '0';
                });
            });
        });
    </script>
</body>
</html>