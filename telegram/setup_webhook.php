<?php
require_once '../includes/config.php';
// session_start();

// // التحقق من صلاحيات المستخدم
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
//     header('Location: login.php');
//     exit();
// }

class TelegramWebhookSetup {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function setWebhook($bot_token, $webhook_url) {
        $api_url = "https://api.telegram.org/bot{$bot_token}/setWebhook";
        
        $data = [
            'url' => $webhook_url,
            'max_connections' => 40,
            'allowed_updates' => ['message', 'callback_query', 'inline_query']
        ];
        
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data),
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        
        try {
            $response = file_get_contents($api_url, false, $context);
            $result = json_decode($response, true);
            
            if ($result['ok']) {
                // حفظ معلومات الـ webhook في قاعدة البيانات
                $this->saveWebhookInfo($bot_token, $webhook_url);
                return [
                    'success' => true,
                    'message' => '✅ تم تعيين الـ webhook بنجاح!',
                    'details' => $result['description'] ?? ''
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '❌ فشل في تعيين الـ webhook',
                    'error' => $result['description'] ?? 'Unknown error'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '❌ حدث خطأ في الاتصال',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function deleteWebhook($bot_token) {
        $api_url = "https://api.telegram.org/bot{$bot_token}/deleteWebhook";
        
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        
        try {
            $response = file_get_contents($api_url, false, $context);
            $result = json_decode($response, true);
            
            if ($result['ok']) {
                // حذف معلومات الـ webhook من قاعدة البيانات
                $this->deleteWebhookInfo($bot_token);
                return [
                    'success' => true,
                    'message' => '✅ تم حذف الـ webhook بنجاح!',
                    'details' => $result['description'] ?? ''
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '❌ فشل في حذف الـ webhook',
                    'error' => $result['description'] ?? 'Unknown error'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '❌ حدث خطأ في الاتصال',
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getWebhookInfo($bot_token) {
        $api_url = "https://api.telegram.org/bot{$bot_token}/getWebhookInfo";
        
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        
        try {
            $response = file_get_contents($api_url, false, $context);
            return json_decode($response, true);
        } catch (Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function testWebhook($bot_token) {
        $api_url = "https://api.telegram.org/bot{$bot_token}/getMe";
        
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => 30
            ]
        ];
        
        $context = stream_context_create($options);
        
        try {
            $response = file_get_contents($api_url, false, $context);
            $result = json_decode($response, true);
            
            if ($result['ok']) {
                return [
                    'success' => true,
                    'bot_name' => $result['result']['first_name'],
                    'bot_username' => $result['result']['username'],
                    'bot_id' => $result['result']['id']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => '❌ فشل في اختبار البوت',
                    'error' => $result['description'] ?? 'Unknown error'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '❌ حدث خطأ في الاتصال',
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function saveWebhookInfo($bot_token, $webhook_url) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO telegram_webhooks 
                (bot_token, webhook_url, setup_at) 
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                webhook_url = VALUES(webhook_url),
                setup_at = NOW()
            ");
            $stmt->execute([$bot_token, $webhook_url]);
        } catch (PDOException $e) {
            error_log("Error saving webhook info: " . $e->getMessage());
        }
    }
    
    private function deleteWebhookInfo($bot_token) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM telegram_webhooks WHERE bot_token = ?");
            $stmt->execute([$bot_token]);
        } catch (PDOException $e) {
            error_log("Error deleting webhook info: " . $e->getMessage());
        }
    }
}

// واجهة المستخدم
$setup = new TelegramWebhookSetup($pdo);
$message = '';
$error = '';
$bot_info = null;
$webhook_info = null;

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bot_token = trim($_POST['bot_token'] ?? '');
    
    if (empty($bot_token)) {
        $error = 'يرجى إدخال Token البوت';
    } else {
        switch ($action) {
            case 'test':
                $result = $setup->testWebhook($bot_token);
                if ($result['success']) {
                    $bot_info = $result;
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'set':
                $webhook_url = SITE_URL . '/webhook.php?bot_token=' . urlencode($bot_token);
                $result = $setup->setWebhook($bot_token, $webhook_url);
                $message = $result['message'];
                if (!$result['success']) {
                    $error = $result['error'];
                }
                break;
                
            case 'delete':
                $result = $setup->deleteWebhook($bot_token);
                $message = $result['message'];
                if (!$result['success']) {
                    $error = $result['error'];
                }
                break;
                
            case 'info':
                $webhook_info = $setup->getWebhookInfo($bot_token);
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد ويب هوك Telegram</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .header h1 {
            color: #0088cc;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 16px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0088cc;
            box-shadow: 0 0 0 2px rgba(0, 136, 204, 0.2);
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-primary {
            background-color: #0088cc;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0077b3;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background-color: #138496;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .bot-info {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .bot-info h3 {
            color: #0088cc;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            padding: 10px;
            background: white;
            border-radius: 5px;
            border: 1px solid #eaeaea;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
            margin-top: 5px;
            word-break: break-all;
        }
        
        .webhook-info {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .webhook-info pre {
            background: white;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: monospace;
            font-size: 14px;
            border: 1px solid #eaeaea;
        }
        
        .instructions {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .instructions h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .instructions ol {
            padding-right: 20px;
        }
        
        .instructions li {
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fab fa-telegram"></i> إعداد ويب هوك Telegram</h1>
            <p>إعداد وتكوين ويب هوك لربط بوت Telegram مع التطبيق</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="bot_token">Telegram Bot Token</label>
                <input type="password" 
                       id="bot_token" 
                       name="bot_token" 
                       class="form-control" 
                       placeholder="أدخل Token البوت هنا"
                       required>
                <small style="color: #666; display: block; margin-top: 5px;">
                    احصل على Token من @BotFather على Telegram
                </small>
            </div>
            
            <div class="btn-group">
                <button type="submit" name="action" value="test" class="btn btn-info">
                    🔍 اختبار البوت
                </button>
                <button type="submit" name="action" value="set" class="btn btn-success">
                    🔗 تعيين ويب هوك
                </button>
                <button type="submit" name="action" value="delete" class="btn btn-danger">
                    ❌ حذف ويب هوك
                </button>
                <button type="submit" name="action" value="info" class="btn btn-primary">
                    ℹ️ معلومات ويب هوك
                </button>
            </div>
        </form>
        
        <?php if ($bot_info && $bot_info['success']): ?>
            <div class="bot-info">
                <h3>✅ معلومات البوت</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">اسم البوت</div>
                        <div class="info-value"><?php echo htmlspecialchars($bot_info['bot_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">معرف البوت</div>
                        <div class="info-value">@<?php echo htmlspecialchars($bot_info['bot_username']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">رقم البوت</div>
                        <div class="info-value"><?php echo htmlspecialchars($bot_info['bot_id']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">الحالة</div>
                        <div class="info-value" style="color: #28a745;">✅ متصل بنجاح</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($webhook_info): ?>
            <div class="webhook-info">
                <h3>معلومات ويب هوك</h3>
                <pre><?php echo json_encode($webhook_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            </div>
        <?php endif; ?>
        
        <div class="instructions">
            <h3>📋 تعليمات الإعداد</h3>
            <ol>
                <li>احصل على Token البوت من @BotFather على Telegram</li>
                <li>أدخل الـ Token في الحقل أعلاه</li>
                <li>اضغط على "اختبار البوت" للتحقق من صحة الـ Token</li>
                <li>اضغط على "تعيين ويب هوك" لربط البوت مع التطبيق</li>
                <li>تأكد من أن عنوان الـ webhook يحتوي على SSL (https)</li>
                <li>يمكنك حذف الـ webhook في أي وقت باستخدام زر "حذف ويب هوك"</li>
            </ol>
            <p><strong>ملاحظة:</strong> تأكد من أن خادمك يدعم SSL وأن الـ webhook URL متاح للعامة.</p>
        </div>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>