<?php
session_start();
require_once '../includes/config.php';

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// التحقق من وجود معرف المجموعة
if (!isset($_GET['id'])) {
    header('Location: ../groups.php');
    exit();
}

$group_id = intval($_GET['id']);

// التحقق من صلاحيات المدير
try {
    $stmt = $pdo->prepare("SELECT g.*, gm.role FROM groups g 
                          JOIN group_members gm ON g.id = gm.group_id 
                          WHERE g.id = ? AND gm.user_id = ? AND gm.role = 'admin'");
    $stmt->execute([$group_id, $user_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        $_SESSION['error_message'] = "ليس لديك صلاحية إدارة هذه المجموعة";
        header('Location: ../groups.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "حدث خطأ أثناء التحقق من الصلاحيات";
    header('Location: ../groups.php');
    exit();
}

// جلب أعضاء المجموعة
try {
    $stmt = $pdo->prepare("SELECT u.id, u.name, u.profile_picture, u.email, gm.role, gm.joined_at 
                          FROM group_members gm 
                          JOIN users u ON gm.user_id = u.id 
                          WHERE gm.group_id = ? 
                          ORDER BY gm.role DESC, gm.joined_at ASC");
    $stmt->execute([$group_id]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "حدث خطأ أثناء تحميل الأعضاء";
}


// معالجة تحديث إعدادات المجموعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $hide_group_name = isset($_POST['hide_group_name']) ? 1 : 0;
    $hide_group_image = isset($_POST['hide_group_image']) ? 1 : 0;
    $allow_public_members_view = isset($_POST['allow_public_members_view']) ? 1 : 0;
    $custom_join_link = isset($_POST['custom_join_link']) ? trim($_POST['custom_join_link']) : null;
    
    try {
        // التحقق من أن رابط الانضمام المخصص فريد
        if ($custom_join_link) {
            $stmt = $pdo->prepare("SELECT id FROM groups WHERE custom_join_link = ? AND id != ?");
            $stmt->execute([$custom_join_link, $group_id]);
            if ($stmt->fetch()) {
                $_SESSION['error_message'] = "رابط الانضمام المخصص موجود بالفعل. الرجاء اختيار رابط آخر.";
                header('Location: group_manage.php?id=' . $group_id);
                exit();
            }
        }
        
        $stmt = $pdo->prepare("UPDATE groups SET 
                              hide_group_name = ?, 
                              hide_group_image = ?, 
                              allow_public_members_view = ?, 
                              custom_join_link = ?,
                              settings_updated_at = CURRENT_TIMESTAMP 
                              WHERE id = ?");
        $stmt->execute([$hide_group_name, $hide_group_image, $allow_public_members_view, $custom_join_link, $group_id]);
        
        $_SESSION['success_message'] = "تم تحديث إعدادات المجموعة بنجاح!";
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $error_message = "حدث خطأ أثناء تحديث الإعدادات: " . $e->getMessage();
    }
}


// دالة لتحويل الصور إلى webp
function convertToWebP($source, $destination, $quality = 80) {
    $image_info = getimagesize($source);
    $mime_type = $image_info['mime'];
    
    switch ($mime_type) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            // الحفاظ على الشفافية للصور PNG
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    $result = imagewebp($image, $destination, $quality);
    imagedestroy($image);
    
    return $result;
}

// معالجة تحديث معلومات المجموعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_group'])) {
    $group_name = trim($_POST['group_name']);
    $emoji = trim($_POST['emoji']);
    $group_type = $_POST['group_type'];
    $description = trim($_POST['description']);
    
    try {
        $stmt = $pdo->prepare("UPDATE groups SET group_name = ?, emoji = ?, group_type = ?, description = ? WHERE id = ?");
        $stmt->execute([$group_name, $emoji, $group_type, $description, $group_id]);
        
        $_SESSION['success_message'] = "تم تحديث معلومات المجموعة بنجاح!";
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $error_message = "حدث خطأ أثناء تحديث المجموعة: " . $e->getMessage();
    }
}

// معالجة تغيير دور العضو
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $member_id = intval($_POST['member_id']);
    $new_role = $_POST['new_role'];
    
    try {
        // التحقق من أن المستخدم ليس يحاول تغيير دوره الخاص
        if ($member_id == $user_id) {
            $_SESSION['error_message'] = "لا يمكنك تغيير دورك الخاص";
        } else {
            $stmt = $pdo->prepare("UPDATE group_members SET role = ? WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$new_role, $group_id, $member_id]);
            
            $_SESSION['success_message'] = "تم تغيير دور العضو بنجاح!";
        }
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $error_message = "حدث خطأ أثناء تغيير الدور: " . $e->getMessage();
    }
}

// معالجة إزالة عضو
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_member'])) {
    $member_id = intval($_POST['member_id']);
    
    try {
        // التحقق من أن المستخدم ليس يحاول إزالة نفسه
        if ($member_id == $user_id) {
            $_SESSION['error_message'] = "لا يمكنك إزالة نفسك من المجموعة. استخدم زر مغادرة المجموعة بدلاً من ذلك.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group_id, $member_id]);
            
            $_SESSION['success_message'] = "تم إزالة العضو من المجموعة بنجاح!";
        }
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $error_message = "حدث خطأ أثناء إزالة العضو: " . $e->getMessage();
    }
}

// معالجة رفع صورة المجموعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_group_image'])) {
    if (isset($_FILES['group_image']) && $_FILES['group_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/groups/';
        
        // إنشاء المجلد إذا لم يكن موجوداً
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_tmp = $_FILES['group_image']['tmp_name'];
        $file_name = uniqid('group_' . $group_id . '_', true);
        $original_extension = strtolower(pathinfo($_FILES['group_image']['name'], PATHINFO_EXTENSION));
        
        // السماح بأنواع الصور فقط
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($original_extension, $allowed_types)) {
            // اسم ملف webp الجديد
            $webp_file = $file_name . '.webp';
            $webp_path = $upload_dir . $webp_file;
            
            // تحويل الصورة إلى webp
            if (convertToWebP($file_tmp, $webp_path, 80)) {
                // تحديث قاعدة البيانات
                try {
                    $stmt = $pdo->prepare("UPDATE groups SET group_image = ? WHERE id = ?");
                    $stmt->execute([$webp_file, $group_id]);
                    
                    $_SESSION['success_message'] = "تم تحديث صورة المجموعة بنجاح وتحويلها إلى تنسيق WebP!";
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = "حدث خطأ أثناء حفظ معلومات الصورة: " . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = "فشل في تحويل الصورة إلى تنسيق WebP";
            }
        } else {
            $_SESSION['error_message'] = "نوع الملف غير مدعوم. الرجاء رفع صورة (JPG, PNG, GIF)";
        }
    } else {
        $_SESSION['error_message'] = "حدث خطأ أثناء رفع الملف";
    }
    
    header('Location: group_manage.php?id=' . $group_id);
    exit();
}

// معالجة حذف صورة المجموعة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_group_image'])) {
    try {
        // جلب اسم الصورة الحالية
        $stmt = $pdo->prepare("SELECT group_image FROM groups WHERE id = ?");
        $stmt->execute([$group_id]);
        $current_image = $stmt->fetchColumn();
        
        // حذف الملف من السيرفر
        if ($current_image) {
            $file_path = '../uploads/groups/' . $current_image;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // تحديث قاعدة البيانات
        $stmt = $pdo->prepare("UPDATE groups SET group_image = NULL WHERE id = ?");
        $stmt->execute([$group_id]);
        
        $_SESSION['success_message'] = "تم حذف صورة المجموعة بنجاح!";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ أثناء حذف الصورة: " . $e->getMessage();
    }
    
    header('Location: group_manage.php?id=' . $group_id);
    exit();
}



// معالجة ربط Telegram
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_telegram'])) {
    $bot_token = trim($_POST['telegram_bot_token']);
    $chat_identifier = trim($_POST['telegram_chat_identifier']);
    
    try {
        // التحقق من صحة البوت والتوكن
        if (!empty($bot_token) && !empty($chat_identifier)) {
            // إذا كان المعرف يحتوي على @ فهو username
            if (strpos($chat_identifier, '@') === 0) {
                $telegram_username = $chat_identifier;
                $telegram_chat_id = null;
            } else {
                // إذا كان chat_id رقماً
                $telegram_chat_id = $chat_identifier;
                $telegram_username = null;
            }
            
            // اختبار إرسال رسالة تجريبية للتحقق من الربط
            $test_message_sent = false;
            
            if ($telegram_chat_id) {
                // إرسال رسالة تجريبية باستخدام chat_id
                $api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
                $postData = [
                    'chat_id' => $telegram_chat_id,
                    'text' => "🔗 تم ربط مجموعة '{$group['group_name']}' بنجاح!",
                    'parse_mode' => 'HTML'
                ];
                
                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response = curl_exec($ch);
                curl_close($ch);
                
                $response_data = json_decode($response, true);
                $test_message_sent = $response_data['ok'] ?? false;
            }
            
            // تحديث قاعدة البيانات
            $stmt = $pdo->prepare("UPDATE groups SET 
                                  telegram_bot_token = ?, 
                                  telegram_chat_id = ?, 
                                  telegram_username = ?,
                                  telegram_linked_at = CURRENT_TIMESTAMP,
                                  telegram_notifications = 1
                                  WHERE id = ?");
            $stmt->execute([$bot_token, $telegram_chat_id, $telegram_username, $group_id]);
            
            if ($test_message_sent) {
                $_SESSION['success_message'] = "✅ تم ربط Telegram بنجاح وإرسال رسالة تأكيد!";
            } else {
                $_SESSION['success_message'] = "✅ تم ربط Telegram بنجاح!";
            }
        } else {
            $_SESSION['error_message'] = "الرجاء ملء جميع الحقول المطلوبة";
        }
        
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ أثناء الربط: " . $e->getMessage();
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    }
}

// معالجة فصل Telegram
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlink_telegram'])) {
    try {
        $stmt = $pdo->prepare("UPDATE groups SET 
                              telegram_bot_token = NULL, 
                              telegram_chat_id = NULL, 
                              telegram_username = NULL,
                              telegram_linked_at = NULL,
                              telegram_notifications = 0
                              WHERE id = ?");
        $stmt->execute([$group_id]);
        
        $_SESSION['success_message'] = "✅ تم فصل Telegram بنجاح!";
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ أثناء الفصل: " . $e->getMessage();
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    }
}

// معالجة تغيير إعدادات الإشعارات
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_telegram_settings'])) {
    $telegram_notifications = isset($_POST['telegram_notifications']) ? 1 : 0;
    
    try {
        $stmt = $pdo->prepare("UPDATE groups SET telegram_notifications = ? WHERE id = ?");
        $stmt->execute([$telegram_notifications, $group_id]);
        
        $_SESSION['success_message'] = "✅ تم تحديث إعدادات Telegram!";
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "حدث خطأ أثناء تحديث الإعدادات: " . $e->getMessage();
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    }
}

// معالجة إرسال رسالة اختبار
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test_message'])) {
    try {
        // جلب بيانات Telegram للمجموعة
        $stmt = $pdo->prepare("SELECT telegram_bot_token, telegram_chat_id, telegram_username FROM groups WHERE id = ?");
        $stmt->execute([$group_id]);
        $telegram_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($telegram_data['telegram_bot_token']) {
            $bot_token = $telegram_data['telegram_bot_token'];
            $chat_id = $telegram_data['telegram_chat_id'];
            $username = $telegram_data['telegram_username'];
            
            if ($chat_id || $username) {
                $target = $chat_id ?: $username;
                
                $api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
                $postData = [
                    'chat_id' => $target,
                    'text' => "📣 *رسالة اختبار من مجموعة '{$group['group_name']}'*\n\n✅ تم ربط المجموعة بنجاح!\n📊 يمكنك الآن استقبال الإشعارات والتنبيهات.",
                    'parse_mode' => 'Markdown'
                ];
                
                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response = curl_exec($ch);
                curl_close($ch);
                
                $response_data = json_decode($response, true);
                
                if ($response_data['ok'] ?? false) {
                    $_SESSION['success_message'] = "✅ تم إرسال رسالة الاختبار بنجاح!";
                } else {
                    $_SESSION['error_message'] = "❌ فشل إرسال الرسالة: " . ($response_data['description'] ?? 'خطأ غير معروف');
                }
            } else {
                $_SESSION['error_message'] = "❌ لم يتم تحديد Chat ID أو Username";
            }
        } else {
            $_SESSION['error_message'] = "❌ لم يتم ربط Telegram بعد";
        }
        
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "حدث خطأ: " . $e->getMessage();
        header('Location: group_manage.php?id=' . $group_id);
        exit();
    }
}

// دالة مساعدة لإرسال رسائل Telegram
function sendTelegramMessage($bot_token, $chat_id, $message, $parse_mode = 'HTML') {
    if (!$bot_token || !$chat_id) return false;
    
    $api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $postData = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => $parse_mode
    ];
    
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $response_data = json_decode($response, true);
    return $response_data['ok'] ?? false;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المجموعة - <?php echo htmlspecialchars($group['group_name']); ?></title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/group_manage.css">
    
    
    
    
    <!-- Google tag (gtag.js) -->

<script async src="https://www.googletagmanager.com/gtag/js?id=G-RSG9M1LGJD"></script>

<script>

  window.dataLayer = window.dataLayer || [];

  function gtag(){dataLayer.push(arguments);}

  gtag('js', new Date());

  gtag('config', 'G-RSG9M1LGJD');

</script>
    
</head>
<body>
    <div class="container">
        <!-- رأس الصفحة -->
        <div class="header">
            <a href="group_details.php?id=<?php echo $group_id; ?>" class="back-btn">
                <i class="fas fa-arrow-right"></i> العودة للمجموعة
            </a>
            
            <h1><i class="fas fa-cogs"></i> إدارة المجموعة</h1>
            <p><?php echo htmlspecialchars($group['group_name']); ?></p>
            
            <div class="group-info">
                <div class="member-count">
                    <i class="fas fa-users"></i>
                    <?php echo count($members); ?> عضو
                </div>
            </div>
        </div>
        
        <!-- رسائل التبليغ -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $_SESSION['success_message']; ?></span>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $_SESSION['error_message']; ?></span>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error_message; ?></span>
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>
        
        <!-- زر تفعيل قسم التعديل -->
        <button class="toggle-section-btn" id="toggleEditSection">
            <i class="fas fa-edit"></i>
            <span id="toggleText">إظهار خيارات التعديل</span>
        </button>
        
        <!-- قسم التعديل المخفي -->
        <div class="edit-section" id="editSection">
            <!-- صورة المجموعة -->
            <div class="group-image-section">
                <h3 style="margin-bottom: 20px; color: var(--dark);"><i class="fas fa-image"></i> صورة المجموعة</h3>
                
                <div class="group-image-container">
                    <?php if ($group['group_image']): ?>
                        <img src="../uploads/groups/<?php echo htmlspecialchars($group['group_image']); ?>" 
                             alt="صورة المجموعة" 
                             class="current-group-image">
                    <?php else: ?>
                        <div style="width: 200px; height: 200px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-light), var(--primary)); 
                                   display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; border: 5px solid white;">
                            <i class="fas fa-users"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="image-actions">
                    <div class="file-input-wrapper">
                        <input type="file" id="groupImageInput" name="group_image" accept="image/jpeg,image/png,image/gif" 
                               onchange="handleImageUpload(event)">
                        <label for="groupImageInput" class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i> اختر صورة جديدة
                        </label>
                    </div>
                    
                    <?php if ($group['group_image']): ?>
                        <form method="POST" action="" onsubmit="return confirm('هل أنت متأكد من حذف صورة المجموعة؟');">
                            <button type="submit" name="delete_group_image" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i> حذف الصورة
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- معاينة الصورة -->
                <img id="imagePreview" class="image-preview" alt="معاينة الصورة">
                
                <!-- معلومات الملف -->
                <div id="fileInfo" class="file-info" style="display: none;">
                    <div><strong>اسم الملف:</strong> <span id="fileName"></span></div>
                    <div><strong>الحجم:</strong> <span id="fileSize"></span></div>
                    <div><strong>النوع:</strong> <span id="fileType"></span></div>
                </div>
                
                <!-- بار التحميل -->
                <div class="upload-progress" id="uploadProgress">
                    <div class="progress-info">
                        <span>جاري رفع وتحويل الصورة...</span>
                        <span id="progressPercent">0%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="status-message" id="statusMessage">
                        الصورة ستتم معالجتها وتحويلها إلى تنسيق WebP...
                    </div>
                </div>
            </div>
            
            <div class="grid">
                <!-- تعديل معلومات المجموعة -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-edit"></i>
                        <h3>تعديل معلومات المجموعة</h3>
                    </div>
                    
                    <form method="POST" action="" id="groupForm">
                        <div class="form-group">
                            <label for="group_name">اسم المجموعة *</label>
                            <input type="text" class="form-input" id="group_name" name="group_name" 
                                   value="<?php echo htmlspecialchars($group['group_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="emoji">الرمز التعبيري</label>
                            <input type="text" class="form-input" id="emoji" name="emoji" 
                                   value="<?php echo htmlspecialchars($group['emoji']); ?>" maxlength="2">
                            <div class="emoji-preview" id="emojiPreview">
                                <?php echo htmlspecialchars($group['emoji'] ?: '👥'); ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>نوع المجموعة *</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" name="group_type" id="public" 
                                           value="public" <?php echo $group['group_type'] == 'public' ? 'checked' : ''; ?>>
                                    <label for="public">
                                        <span class="badge badge-success">عامة للجميع</span>
                                    </label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" name="group_type" id="private" 
                                           value="private" <?php echo $group['group_type'] == 'private' ? 'checked' : ''; ?>>
                                    <label for="private">
                                        <span class="badge badge-info">خاصة</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">وصف المجموعة</label>
                            <textarea class="form-input" id="description" name="description" rows="3" 
                                      maxlength="200"><?php echo htmlspecialchars($group['description']); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_group" class="btn btn-primary btn-full">
                            <i class="fas fa-save"></i> حفظ التغييرات
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- إدارة الأعضاء -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users-cog"></i>
                <h3>إدارة الأعضاء (<?php echo count($members); ?>)</h3>
            </div>
            
            <div class="members-list">
                <?php if (count($members) > 0): ?>
                    <?php foreach ($members as $member): ?>
                        <div class="member-item">
                            <img src="<?php echo $member['profile_picture'] ? htmlspecialchars($member['profile_picture']) : 'https://ui-avatars.com/api/?name=' . urlencode($member['name']) . '&background=059669&color=fff'; ?>" 
                                 alt="صورة <?php echo htmlspecialchars($member['name']); ?>" 
                                 class="member-avatar">
                            <div class="member-info">
                                <div class="member-name"><?php echo htmlspecialchars($member['name']); ?></div>
                                <div class="member-email"><?php echo htmlspecialchars($member['email']); ?></div>
                            </div>
                            <div class="member-role <?php echo $member['role']; ?>">
                                <?php echo $member['role'] == 'admin' ? 'مدير' : 'عضو'; ?>
                            </div>
                            
                            <?php if ($member['id'] != $user_id): ?>
                                <div class="member-actions">
                                    <form method="POST" action="" class="role-form">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <select name="new_role" class="role-select" onchange="this.form.submit()">
                                            <option value="member" <?php echo $member['role'] == 'member' ? 'selected' : ''; ?>>عضو</option>
                                            <option value="admin" <?php echo $member['role'] == 'admin' ? 'selected' : ''; ?>>مدير</option>
                                        </select>
                                        <input type="hidden" name="change_role" value="1">
                                    </form>
                                    
                                    <form method="POST" action="" 
                                          onsubmit="return confirm('هل أنت متأكد من إزالة <?php echo htmlspecialchars(addslashes($member['name'])); ?> من المجموعة؟');">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <button type="submit" name="remove_member" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="member-role you">
                                    أنت
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <p>لا يوجد أعضاء في هذه المجموعة</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- قسم الإعدادات -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-sliders-h"></i>
                <h3>إعدادات المجموعة</h3>
            </div>
            
            <form method="POST" action="" id="settingsForm">
                <div class="settings-grid">
                    <!-- إعداد الخصوصية -->
                    <div class="setting-item">
                        <div class="setting-header">
                            <div class="setting-title">
                                <h4><i class="fas fa-eye-slash"></i> إخفاء اسم المجموعة</h4>
                                <p class="setting-description">إخفاء اسم المجموعة من العرض العام للمستخدمين الآخرين</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="hide_group_name" id="hide_group_name" 
                                    value="1" <?php echo $group['hide_group_name'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- إعداد الصورة -->
                    <div class="setting-item">
                        <div class="setting-header">
                            <div class="setting-title">
                                <h4><i class="fas fa-image"></i> إخفاء صورة المجموعة</h4>
                                <p class="setting-description">إخفاء صورة المجموعة من العرض العام</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="hide_group_image" id="hide_group_image" 
                                    value="1" <?php echo $group['hide_group_image'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- إعداد عرض الأعضاء -->
                    <div class="setting-item">
                        <div class="setting-header">
                            <div class="setting-title">
                                <h4><i class="fas fa-users"></i> عرض الأعضاء للعامة</h4>
                                <p class="setting-description">السماح للعامة برؤية قائمة أعضاء المجموعة</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="allow_public_members_view" id="allow_public_members_view" 
                                    value="1" <?php echo $group['allow_public_members_view'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- رابط مخصص -->
                    <div class="setting-item full-width">
                        <div class="setting-header">
                            <div class="setting-title">
                                <h4><i class="fas fa-link"></i> رابط انضمام مخصص</h4>
                                <p class="setting-description">إنشاء رابط مخصص سهل التذكر للانضمام إلى المجموعة</p>
                            </div>
                        </div>
                        <div class="setting-content">
                            <div class="input-group">
                                <span class="input-prefix"><?php echo $_SERVER['HTTP_HOST']; ?>/join/</span>
                                <input type="text" class="form-input" name="custom_join_link" 
                                    id="custom_join_link" placeholder="اسم-مخصص" 
                                    value="<?php echo htmlspecialchars($group['custom_join_link'] ?? ''); ?>">
                            </div>
                            <div class="setting-hint">
                                <i class="fas fa-info-circle"></i>
                                يمكن استخدام أحرف إنجليزية وأرقام وشرطة فقط
                            </div>
                        </div>
                    </div>
                    
                    <!-- معلومات الإعدادات -->
                    <?php if ($group['settings_updated_at']): ?>
                    <div class="setting-info">
                        <i class="far fa-clock"></i>
                        <span>آخر تحديث للإعدادات: <?php echo date('Y-m-d H:i', strtotime($group['settings_updated_at'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" name="update_settings" class="btn btn-primary btn-full" style="margin-top: 20px;">
                    <i class="fas fa-save"></i> حفظ الإعدادات
                </button>
            </form>
        </div>

        <!-- قسم ربط Telegram -->
<div class="card">
    <div class="card-header">
        <i class="fab fa-telegram"></i>
        <h3>ربط Telegram</h3>
    </div>
    
    <?php
    // جلب بيانات Telegram الحالية
    $stmt = $pdo->prepare("SELECT telegram_bot_token, telegram_chat_id, telegram_username, telegram_linked_at, telegram_notifications FROM groups WHERE id = ?");
    $stmt->execute([$group_id]);
    $telegram_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $is_linked = !empty($telegram_data['telegram_bot_token']);
    ?>
    
    <?php if ($is_linked): ?>
        <!-- حالة الربط النشط -->
        <div class="telegram-status" style="background: linear-gradient(135deg, #0088cc, #34b7f1); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <i class="fab fa-telegram fa-2x"></i>
                    <div>
                        <h4 style="margin: 0; font-weight: 600;">✅ تم ربط Telegram</h4>
                        <p style="margin: 5px 0 0 0; opacity: 0.9;">
                            <?php if ($telegram_data['telegram_username']): ?>
                                Username: <?php echo htmlspecialchars($telegram_data['telegram_username']); ?>
                            <?php else: ?>
                                Chat ID: <?php echo htmlspecialchars($telegram_data['telegram_chat_id']); ?>
                            <?php endif; ?>
                        </p>
                        <?php if ($telegram_data['telegram_linked_at']): ?>
                            <small>تم الربط في: <?php echo date('Y-m-d H:i', strtotime($telegram_data['telegram_linked_at'])); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <form method="POST" action="" style="display: inline;" onsubmit="return confirm('هل تريد فصل Telegram عن المجموعة؟');">
                    <button type="submit" name="unlink_telegram" class="btn btn-danger">
                        <i class="fas fa-unlink"></i> فصل الربط
                    </button>
                </form>
            </div>
        </div>
        
        <!-- إعدادات الإشعارات -->
        <form method="POST" action="" class="telegram-settings-form">
            <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <div>
                    <label style="font-weight: 600; color: #333;">تفعيل إشعارات Telegram</label>
                    <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">
                        إرسال إشعارات عند إضافة أعضاء جدد أو أنشطة مهمة
                    </p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="telegram_notifications" 
                           value="1" <?php echo $telegram_data['telegram_notifications'] ? 'checked' : ''; ?> 
                           onchange="this.form.submit()">
                    <span class="slider"></span>
                </label>
                <input type="hidden" name="update_telegram_settings" value="1">
            </div>
        </form>
        
        <!-- أزرار التحكم -->
        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <form method="POST" action="" style="flex: 1;">
                <button type="submit" name="send_test_message" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-paper-plane"></i> إرسال رسالة اختبار
                </button>
            </form>
            
            <button class="btn btn-outline" style="flex: 1;" onclick="showTelegramModal()">
                <i class="fas fa-edit"></i> تعديل إعدادات الربط
            </button>
        </div>
        
    <?php else: ?>
        <!-- حالة غير مرتبط -->
        <div style="text-align: center; padding: 30px 20px;">
            <i class="fab fa-telegram fa-4x" style="color: #0088cc; margin-bottom: 20px;"></i>
            <h4 style="color: #333; margin-bottom: 10px;">لم يتم ربط Telegram بعد</h4>
            <p style="color: #666; margin-bottom: 25px;">
                قم بربط مجموعتك بحساب Telegram لتلقي الإشعارات والإعلانات
            </p>
            <button class="btn btn-primary" onclick="showTelegramModal()" style="padding: 12px 30px;">
                <i class="fas fa-link"></i> ربط Telegram الآن
            </button>
        </div>
    <?php endif; ?>
</div>
        
        <!-- منطقة الخطر -->
        <div class="danger-zone">
            <div class="danger-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>منطقة الخطر</h3>
            </div>
            <div class="danger-content">
                <p>الإجراءات في هذه المنطقة لا يمكن التراجع عنها</p>
                
                <div class="danger-actions">
                    <button class="btn btn-danger btn-full" onclick="showDeleteModal()">
                        <i class="fas fa-trash-alt"></i> حذف المجموعة نهائياً
                    </button>
                    
                    <form method="POST" action="../groups.php" onsubmit="return confirm('هل أنت متأكد من مغادرة المجموعة؟');">
                        <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                        <button type="submit" name="leave_group" class="btn btn-outline btn-full">
                            <i class="fas fa-sign-out-alt"></i> مغادرة المجموعة
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal حذف المجموعة -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>حذف المجموعة</h3>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>تحذير:</strong> هذا الإجراء لا يمكن التراجع عنه
                </div>
                <p>سيتم حذف المجموعة وجميع بياناتها بما في ذلك:</p>
                <ul>
                    <li>جميع الأعضاء سيفقدون عضوية المجموعة</li>
                    <li>جميع السجلات والأنشطة المرتبطة بالمجموعة</li>
                    <li>رابط الانضمام لن يعمل بعد الآن</li>
                </ul>
                <p style="color: var(--danger); font-weight: 600; margin-top: 15px;">
                    هل أنت متأكد من حذف المجموعة "<?php echo htmlspecialchars($group['group_name']); ?>"؟
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="hideDeleteModal()">إلغاء</button>
                <form method="POST" action="../api/delete_group.php" style="display: inline;">
                    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> نعم، حذف المجموعة
                    </button>
                </form>
            </div>
        </div>
    </div>
    

    <!-- مودال ربط Telegram -->
<div class="modal" id="telegramModal">
    <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #0088cc, #34b7f1);">
            <i class="fab fa-telegram"></i>
            <h3>ربط Telegram</h3>
        </div>
        <div class="modal-body">
            <div class="alert alert-info" style="background: #e3f2fd; color: #1565c0; border-right-color: #1565c0; margin-bottom: 20px;">
                <i class="fas fa-info-circle"></i>
                <strong>تعليمات الربط:</strong>
                <ol style="margin: 10px 0 0 20px;">
                    <li>أنشئ بوت جديد عبر <a href="https://t.me/BotFather" target="_blank" style="color: #1565c0; font-weight: 600;">@BotFather</a></li>
                    <li>احصل على Token الخاص بالبوت</li>
                    <li>أضف البوت إلى المجموعة/القناة المراد ربطها</li>
                    <li>اجعل البوت مشرف (Admin) في المجموعة/القناة</li>
                    <li>أدخل البيانات المطلوبة أدناه</li>
                </ol>
            </div>
            
            <form method="POST" action="" id="telegramForm">
                <div class="form-group">
                    <label for="telegram_bot_token">Bot Token *</label>
                    <input type="text" class="form-input" id="telegram_bot_token" name="telegram_bot_token" 
                           value="<?php echo $is_linked ? htmlspecialchars($telegram_data['telegram_bot_token']) : ''; ?>"
                           placeholder="مثال: 1234567890:ABCdefGhIJKlmNoPQRsTUVwxyZ" required>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        احصل عليه من <a href="https://t.me/BotFather" target="_blank" style="color: #0088cc;">@BotFather</a>
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="telegram_chat_identifier">Chat ID أو Username *</label>
                    <div class="input-group">
                        <input type="text" class="form-input" id="telegram_chat_identifier" name="telegram_chat_identifier" 
                               value="<?php echo $is_linked ? ($telegram_data['telegram_username'] ?: $telegram_data['telegram_chat_id']) : ''; ?>"
                               placeholder="@group_username أو -1001234567890" required>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <div class="radio-item" style="flex: 1;">
                            <input type="radio" id="use_username" name="identifier_type" value="username" checked>
                            <label for="use_username" style="color: #666;">استخدم Username</label>
                        </div>
                        <div class="radio-item" style="flex: 1;">
                            <input type="radio" id="use_chatid" name="identifier_type" value="chatid">
                            <label for="use_chatid" style="color: #666;">استخدم Chat ID</label>
                        </div>
                    </div>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        للمجموعات: استخدم @group_username أو Chat ID الرقمي
                    </small>
                </div>
                
                <div class="form-group" style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <h5 style="margin: 0 0 10px 0; color: #333;">🔍 كيف أحصل على Chat ID؟</h5>
                    <ol style="margin: 0 0 0 20px; color: #666;">
                        <li>أرسل رسالة للمجموعة/القناة</li>
                        <li>اذهب لرابط: <code>https://api.telegram.org/botYOUR_TOKEN/getUpdates</code></li>
                        <li>ابحث عن <code>"chat":{"id":-100xxxxxxxxxx}</code></li>
                        <li>الرقم بعد <code>id:</code> هو Chat ID المطلوب</li>
                    </ol>
                </div>
                
                <div class="alert alert-success" style="background: #e8f5e9; color: #2e7d32; border-right-color: #2e7d32; margin-top: 20px;">
                    <i class="fas fa-bell"></i>
                    <span>بعد الربط يمكنك استقبال إشعارات عند:
                        <ul style="margin: 5px 0 0 15px;">
                            <li>انضمام عضو جديد</li>
                            <li>تحديث إعدادات المجموعة</li>
                            <li>أنشطة مهمة أخرى</li>
                        </ul>
                    </span>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="hideTelegramModal()">إلغاء</button>
            <button type="submit" form="telegramForm" name="link_telegram" class="btn btn-primary">
                <i class="fas fa-link"></i> ربط Telegram
            </button>
        </div>
    </div>
</div>


    <script>
        // التحكم في قسم التعديل المخفي
        const toggleBtn = document.getElementById('toggleEditSection');
        const editSection = document.getElementById('editSection');
        const toggleText = document.getElementById('toggleText');
        
        toggleBtn.addEventListener('click', function() {
            editSection.classList.toggle('active');
            if (editSection.classList.contains('active')) {
                toggleText.textContent = 'إخفاء خيارات التعديل';
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i> <span id="toggleText">إخفاء خيارات التعديل</span>';
            } else {
                toggleText.textContent = 'إظهار خيارات التعديل';
                toggleBtn.innerHTML = '<i class="fas fa-edit"></i> <span id="toggleText">إظهار خيارات التعديل</span>';
            }
        });
        
        // تحديث معاينة الرمز التعبيري
        document.getElementById('emoji').addEventListener('input', function() {
            document.getElementById('emojiPreview').textContent = this.value || '👥';
        });
        
        // إدارة المودال
        function showDeleteModal() {
            document.getElementById('deleteModal').classList.add('active');
        }
        
        function hideDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }
        
        // إغلاق المودال عند النقر خارجها
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideDeleteModal();
            }
        });
        
        // إغلاق التنبيهات تلقائياً بعد 5 ثوانٍ
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
        
        // معالجة رفع الصورة
        function handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // عرض معلومات الملف
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const fileType = document.getElementById('fileType');
            
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileType.textContent = file.type;
            fileInfo.style.display = 'block';
            
            // عرض معاينة الصورة
            const preview = document.getElementById('imagePreview');
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            reader.readAsDataURL(file);
            
            // إظهار بار التحميل وإرسال النموذج
            showUploadProgress();
            uploadImage(file);
        }
        
        // تنسيق حجم الملف
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 بايت';
            const k = 1024;
            const sizes = ['بايت', 'كيلوبايت', 'ميجابايت', 'جيجابايت'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // عرض بار التحميل
        function showUploadProgress() {
            const progress = document.getElementById('uploadProgress');
            progress.classList.add('active');
        }
        
        // إخفاء بار التحميل
        function hideUploadProgress() {
            const progress = document.getElementById('uploadProgress');
            progress.classList.remove('active');
        }
        
        // تحديث بار التقدم
        function updateProgress(percent, message) {
            const progressFill = document.getElementById('progressFill');
            const progressPercent = document.getElementById('progressPercent');
            const statusMessage = document.getElementById('statusMessage');
            
            progressFill.style.width = percent + '%';
            progressPercent.textContent = percent + '%';
            statusMessage.textContent = message;
        }
        
        // رفع الصورة
        function uploadImage(file) {
            const formData = new FormData();
            formData.append('group_image', file);
            formData.append('upload_group_image', '1');
            
            // محاكاة تقدم التحميل
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 5;
                if (progress > 90) {
                    clearInterval(progressInterval);
                }
                updateProgress(progress, 'جاري تحميل الصورة...');
            }, 200);
            
            // إرسال الصورة
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(() => {
                clearInterval(progressInterval);
                updateProgress(100, 'تم رفع وتحويل الصورة بنجاح!');
                
                // إعادة تحميل الصفحة بعد تأخير
                setTimeout(() => {
                    location.reload();
                }, 1500);
            })
            .catch(error => {
                clearInterval(progressInterval);
                updateProgress(0, 'حدث خطأ أثناء رفع الصورة: ' + error.message);
                console.error('Error:', error);
                
                // إخفاء بار التحميل بعد تأخير
                setTimeout(() => {
                    hideUploadProgress();
                }, 3000);
            });
        }
        
        // تحسين تجربة المستخدم
        document.addEventListener('DOMContentLoaded', function() {
            // إضافة تأثير للبطاقات
            const memberItems = document.querySelectorAll('.member-item');
            memberItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });
        });


        // التحقق من صحة رابط الانضمام المخصص
        document.getElementById('custom_join_link').addEventListener('input', function(e) {
            const value = e.target.value;
            // السماح فقط بالأحرف الإنجليزية والأرقام والشرطة
            const cleaned = value.replace(/[^a-zA-Z0-9-]/g, '');
            e.target.value = cleaned;
        });

        // تحديث حالات التبديل
        document.querySelectorAll('.switch input').forEach(switchInput => {
            switchInput.addEventListener('change', function() {
                const settingName = this.name;
                const isChecked = this.checked;
                console.log(`Setting ${settingName} changed to: ${isChecked}`);
            });
        });

        // إظهار/إخفاء صورة المجموعة بناءً على الإعداد
        const hideImageSwitch = document.getElementById('hide_group_image');
        if (hideImageSwitch) {
            hideImageSwitch.addEventListener('change', function() {
                const imageContainer = document.querySelector('.group-image-section');
                if (imageContainer) {
                    imageContainer.style.opacity = this.checked ? '0.5' : '1';
                    imageContainer.querySelectorAll('button, input').forEach(el => {
                        el.disabled = this.checked;
                    });
                }
            });
            
            // تطبيق الحالة الأولية
            if (hideImageSwitch.checked) {
                const imageContainer = document.querySelector('.group-image-section');
                if (imageContainer) {
                    imageContainer.style.opacity = '0.5';
                    imageContainer.querySelectorAll('button, input').forEach(el => {
                        el.disabled = true;
                    });
                }
            }
        }







        
    </script>
</body>
</html>