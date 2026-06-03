<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// إعدادات السكرت لتحسين الأمان
define('WEBHOOK_SECRET', 'your_secret_key_here_change_me');

class TelegramWebhook {
    private $pdo;
    private $token;
    private $chat_id;
    private $user_id;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->handleRequest();
    }
    
    private function handleRequest() {
        // الحصول على بيانات الويب هوك من Telegram
        $input = file_get_contents('php://input');
        
        // التحقق من وجود البيانات
        if (empty($input)) {
            $this->sendResponse(['error' => 'No data received']);
            return;
        }
        
        // تحويل JSON إلى مصفوفة
        $update = json_decode($input, true);
        
        // التحقق من صحة البيانات
        if (!$update) {
            $this->sendResponse(['error' => 'Invalid JSON']);
            return;
        }
        
        // معالجة الرسالة
        $this->processUpdate($update);
    }
    
    private function processUpdate($update) {
        // التحقق من نوع الرسالة
        if (isset($update['message'])) {
            $this->processMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->processCallbackQuery($update['callback_query']);
        } elseif (isset($update['inline_query'])) {
            $this->processInlineQuery($update['inline_query']);
        } else {
            error_log('Unknown update type: ' . json_encode($update));
        }
    }
    
    private function processMessage($message) {
        $chat_id = $message['chat']['id'];
        $text = isset($message['text']) ? $message['text'] : '';
        $from = $message['from'];
        
        // البحث عن المستخدم المرتبط بهذا Chat ID
        $user = $this->findUserByChatId($chat_id);
        
        if (!$user && $text === '/start') {
            $this->handleStartCommand($chat_id, $from);
            return;
        }
        
        if ($user && $text) {
            $this->handleUserMessage($user, $chat_id, $text, $message);
        }
    }
    
    private function handleStartCommand($chat_id, $from) {
        $user_id = $this->findOrCreateUser($from, $chat_id);
        
        if ($user_id) {
            $welcome_message = "🎉 مرحباً " . htmlspecialchars($from['first_name']) . "!\n\n";
            $welcome_message .= "✅ تم ربط حسابك بنجاح مع تطبيق تتبع الصلاة.\n\n";
            $welcome_message .= "📱 يمكنك الآن:\n";
            $welcome_message .= "• تلقي إشعارات مواقيت الصلاة\n";
            $welcome_message .= "• تسجيل صلواتك\n";
            $welcome_message .= "• استعراض تقارير صلواتك\n\n";
            $welcome_message .= "استخدم /help لرؤية الأوامر المتاحة.";
            
            $this->sendMessage($chat_id, $welcome_message);
            
            // تحديث Chat ID في قاعدة البيانات إذا لزم الأمر
            $this->updateUserChatId($user_id, $chat_id);
        } else {
            $this->sendMessage($chat_id, "❌ حدث خطأ في ربط حسابك. يرجى المحاولة مرة أخرى.");
        }
    }
    
    private function handleUserMessage($user, $chat_id, $text, $message) {
        $commands = [
            '/start' => 'بدء الاستخدام',
            '/help' => 'عرض المساعدة',
            '/prayers' => 'مواقيت الصلاة',
            '/record' => 'تسجيل صلاة',
            '/today' => 'تقرير اليوم',
            '/week' => 'تقرير الأسبوع',
            '/settings' => 'الإعدادات',
            '/link' => 'ربط حساب آخر'
        ];
        
        switch ($text) {
            case '/start':
                $this->sendStartMessage($chat_id, $user['first_name']);
                break;
                
            case '/help':
                $this->sendHelpMessage($chat_id);
                break;
                
            case '/prayers':
                $this->sendPrayerTimes($chat_id, $user['id']);
                break;
                
            case '/record':
                $this->showRecordPrayerMenu($chat_id, $user['id']);
                break;
                
            case '/today':
                $this->sendTodayReport($chat_id, $user['id']);
                break;
                
            case '/week':
                $this->sendWeekReport($chat_id, $user['id']);
                break;
                
            case '/settings':
                $this->showSettingsMenu($chat_id, $user['id']);
                break;
                
            case '/link':
                $this->sendLinkInstructions($chat_id, $user['id']);
                break;
                
            default:
                // معالجة الرسائل النصية العادية
                if (strpos($text, '/record_') === 0) {
                    $this->recordPrayer($chat_id, $user['id'], $text);
                } elseif (strpos($text, '/set_') === 0) {
                    $this->handleSetting($chat_id, $user['id'], $text);
                } else {
                    $this->sendMessage($chat_id, "🤔 لم أفهم طلبك. استخدم /help لرؤية الأوامر المتاحة.");
                }
        }
    }
    
    private function sendStartMessage($chat_id, $name) {
        $message = "👋 أهلاً وسهلاً بك مرة أخرى " . htmlspecialchars($name) . "!\n\n";
        $message .= "أنت متصل بالفعل مع تطبيق تتبع الصلاة.\n";
        $message .= "استخدم /help لرؤية جميع الأوامر المتاحة.";
        
        $this->sendMessage($chat_id, $message);
    }
    
    private function sendHelpMessage($chat_id) {
        $message = "📚 **قائمة الأوامر المتاحة:**\n\n";
        $message .= "• /start - بدء استخدام البوت\n";
        $message .= "• /help - عرض هذه الرسالة\n";
        $message .= "• /prayers - عرض مواقيت الصلاة لليوم\n";
        $message .= "• /record - تسجيل صلاة\n";
        $message .= "• /today - عرض تقرير صلوات اليوم\n";
        $message .= "• /week - عرض تقرير صلوات الأسبوع\n";
        $message .= "• /settings - عرض إعدادات الإشعارات\n";
        $message .= "• /link - تعليمات ربط حساب آخر\n\n";
        $message .= "📱 **لتسجيل صلاة:**\n";
        $message .= "استخدم /record ثم اختر الصلاة من القائمة";
        
        $this->sendMessage($chat_id, $message);
    }
    
    private function sendPrayerTimes($chat_id, $user_id) {
        // الحصول على مواقيت الصلاة للمستخدم
        $prayer_times = $this->getUserPrayerTimes($user_id);
        
        if (!$prayer_times) {
            $this->sendMessage($chat_id, "⚠️ لم يتم تعيين مواقيت الصلاة بعد.\nيرجى تعيين موقعك في الإعدادات.");
            return;
        }
        
        $date = date('Y-m-d');
        $message = "🕌 **مواقيت الصلاة لليوم**\n";
        $message .= "📅 " . $this->formatArabicDate($date) . "\n\n";
        
        $prayers = [
            'fajr' => 'الفجر',
            'sunrise' => 'الشروق',
            'dhuhr' => 'الظهر',
            'asr' => 'العصر',
            'maghrib' => 'المغرب',
            'isha' => 'العشاء'
        ];
        
        foreach ($prayers as $key => $name) {
            if (isset($prayer_times[$key])) {
                $time = $prayer_times[$key];
                $message .= "• {$name}: {$time}\n";
            }
        }
        
        $message .= "\n📝 لتسجيل صلاة استخدم /record";
        
        $this->sendMessage($chat_id, $message);
    }
    
    private function showRecordPrayerMenu($chat_id, $user_id) {
        $prayers = [
            ['text' => '☀️ الفجر', 'callback_data' => 'record_fajr'],
            ['text' => '☀️ الظهر', 'callback_data' => 'record_dhuhr'],
            ['text' => '☁️ العصر', 'callback_data' => 'record_asr'],
            ['text' => '🌙 المغرب', 'callback_data' => 'record_maghrib'],
            ['text' => '🌙 العشاء', 'callback_data' => 'record_isha']
        ];
        
        $keyboard = [
            'inline_keyboard' => array_chunk($prayers, 2)
        ];
        
        $this->sendMessage($chat_id, "📝 **اختر الصلاة التي تريد تسجيلها:**", $keyboard);
    }
    
    private function recordPrayer($chat_id, $user_id, $command) {
        $prayer_map = [
            'record_fajr' => ['name' => 'الفجر', 'key' => 'fajr'],
            'record_dhuhr' => ['name' => 'الظهر', 'key' => 'dhuhr'],
            'record_asr' => ['name' => 'العصر', 'key' => 'asr'],
            'record_maghrib' => ['name' => 'المغرب', 'key' => 'maghrib'],
            'record_isha' => ['name' => 'العشاء', 'key' => 'isha']
        ];
        
        $prayer_key = str_replace('/record_', '', $command);
        
        if (!isset($prayer_map[$prayer_key])) {
            $this->sendMessage($chat_id, "❌ نوع الصلاة غير صحيح");
            return;
        }
        
        $prayer_info = $prayer_map[$prayer_key];
        $today = date('Y-m-d');
        $now = date('H:i');
        
        try {
            // التحقق مما إذا كانت الصلاة مسجلة مسبقاً
            $stmt = $this->pdo->prepare("
                SELECT id FROM prayer_records 
                WHERE user_id = ? AND prayer_type = ? AND DATE(prayer_date) = ?
            ");
            $stmt->execute([$user_id, $prayer_info['key'], $today]);
            
            if ($stmt->fetch()) {
                $this->sendMessage($chat_id, "✅ صلاة {$prayer_info['name']} مسجلة مسبقاً لليوم.");
                return;
            }
            
            // تسجيل الصلاة
            $stmt = $this->pdo->prepare("
                INSERT INTO prayer_records 
                (user_id, prayer_type, prayer_time, prayer_date, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$user_id, $prayer_info['key'], $now, $today]);
            
            // إرسال تأكيد
            $message = "✅ **تم تسجيل صلاة {$prayer_info['name']} بنجاح!**\n";
            $message .= "⏰ الوقت: {$now}\n";
            $message .= "📅 التاريخ: " . $this->formatArabicDate($today) . "\n\n";
            $message .= "📊 استخدم /today لرؤية تقرير اليوم";
            
            $this->sendMessage($chat_id, $message);
            
        } catch (PDOException $e) {
            error_log("Error recording prayer: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ حدث خطأ أثناء تسجيل الصلاة. يرجى المحاولة مرة أخرى.");
        }
    }
    
    private function sendTodayReport($chat_id, $user_id) {
        $today = date('Y-m-d');
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT prayer_type, prayer_time 
                FROM prayer_records 
                WHERE user_id = ? AND DATE(prayer_date) = ?
                ORDER BY FIELD(prayer_type, 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha')
            ");
            $stmt->execute([$user_id, $today]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $prayer_names = [
                'fajr' => 'الفجر',
                'dhuhr' => 'الظهر',
                'asr' => 'العصر',
                'maghrib' => 'المغرب',
                'isha' => 'العشاء'
            ];
            
            $total_prayers = 5;
            $completed = count($records);
            $percentage = round(($completed / $total_prayers) * 100);
            
            $message = "📊 **تقرير صلوات اليوم**\n";
            $message .= "📅 " . $this->formatArabicDate($today) . "\n\n";
            
            if ($completed > 0) {
                $message .= "✅ **الصلوات المسجلة:**\n";
                foreach ($records as $record) {
                    $prayer_name = $prayer_names[$record['prayer_type']] ?? $record['prayer_type'];
                    $message .= "• {$prayer_name}: {$record['prayer_time']}\n";
                }
            } else {
                $message .= "⚠️ **لم تسجل أي صلاة اليوم**\n";
            }
            
            $message .= "\n📈 **الإحصائية:**\n";
            $message .= "• المكتملة: {$completed}/{$total_prayers}\n";
            $message .= "• النسبة: {$percentage}%\n\n";
            
            if ($completed < $total_prayers) {
                $message .= "📝 استخدم /record لتسجيل الصلوات المتبقية";
            } else {
                $message .= "🎉 **مبروك! أكملت جميع صلوات اليوم**";
            }
            
            $this->sendMessage($chat_id, $message);
            
        } catch (PDOException $e) {
            error_log("Error fetching today's report: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ حدث خطأ في جلب التقرير.");
        }
    }
    
    private function sendWeekReport($chat_id, $user_id) {
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $today = date('Y-m-d');
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    DATE(prayer_date) as date,
                    COUNT(*) as count,
                    GROUP_CONCAT(prayer_type) as prayers
                FROM prayer_records 
                WHERE user_id = ? 
                AND prayer_date BETWEEN ? AND ?
                GROUP BY DATE(prayer_date)
                ORDER BY date
            ");
            $stmt->execute([$user_id, $week_start, $today]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $message = "📊 **تقرير صلوات الأسبوع**\n";
            $message .= "🗓️ من " . $this->formatArabicDate($week_start) . " إلى " . $this->formatArabicDate($today) . "\n\n";
            
            $total_days = 0;
            $total_prayers = 0;
            $max_prayers_per_day = 5;
            
            foreach ($records as $record) {
                $total_days++;
                $total_prayers += $record['count'];
                $date_formatted = $this->formatArabicDate($record['date']);
                $percentage = round(($record['count'] / $max_prayers_per_day) * 100);
                $message .= "• {$date_formatted}: {$record['count']}/5 ({$percentage}%)\n";
            }
            
            $message .= "\n📈 **الإحصائية العامة:**\n";
            $message .= "• أيام الصلاة: {$total_days}\n";
            $message .= "• إجمالي الصلوات: {$total_prayers}\n";
            
            $average_per_day = $total_days > 0 ? round($total_prayers / $total_days, 1) : 0;
            $message .= "• المتوسط اليومي: {$average_per_day}\n\n";
            
            $message .= "💪 استمر في المحافظة على الصلاة!";
            
            $this->sendMessage($chat_id, $message);
            
        } catch (PDOException $e) {
            error_log("Error fetching week report: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ حدث خطأ في جلب التقرير.");
        }
    }
    
    private function showSettingsMenu($chat_id, $user_id) {
        // الحصول على إعدادات المستخدم
        $settings = $this->getUserSettings($user_id);
        
        $notifications_enabled = isset($settings['notifications_enabled']) && $settings['notifications_enabled'] == 1;
        $advance_notice = isset($settings['advance_notice']) ? $settings['advance_notice'] : 5;
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => $notifications_enabled ? '🔔 الإشعارات: مفعلة' : '🔕 الإشعارات: معطلة',
                        'callback_data' => 'toggle_notifications'
                    ]
                ],
                [
                    [
                        'text' => '⏰ وقت التنبيه: ' . $advance_notice . ' دقائق',
                        'callback_data' => 'change_advance'
                    ]
                ],
                [
                    [
                        'text' => '📍 تغيير الموقع',
                        'callback_data' => 'change_location'
                    ]
                ]
            ]
        ];
        
        $message = "⚙️ **إعدادات الإشعارات**\n\n";
        $message .= "• حالة الإشعارات: " . ($notifications_enabled ? '✅ مفعلة' : '❌ معطلة') . "\n";
        $message .= "• وقت التنبيه: {$advance_notice} دقيقة قبل الصلاة\n\n";
        $message .= "اختر الإعداد الذي تريد تعديله:";
        
        $this->sendMessage($chat_id, $message, $keyboard);
    }
    
    private function processCallbackQuery($callback_query) {
        $chat_id = $callback_query['message']['chat']['id'];
        $data = $callback_query['data'];
        $user_id = $callback_query['from']['id'];
        
        // البحث عن المستخدم
        $user = $this->findUserByChatId($chat_id);
        
        if (!$user) {
            $this->answerCallbackQuery($callback_query['id'], "❌ حسابك غير مرتبط");
            return;
        }
        
        switch ($data) {
            case 'toggle_notifications':
                $this->toggleNotifications($chat_id, $user['id'], $callback_query['id']);
                break;
                
            case 'change_advance':
                $this->showAdvanceTimeMenu($chat_id, $user['id'], $callback_query['id']);
                break;
                
            case 'change_location':
                $this->requestLocation($chat_id, $callback_query['id']);
                break;
                
            default:
                if (strpos($data, 'advance_') === 0) {
                    $minutes = str_replace('advance_', '', $data);
                    $this->setAdvanceTime($chat_id, $user['id'], $minutes, $callback_query['id']);
                }
        }
    }
    
    private function toggleNotifications($chat_id, $user_id, $callback_id) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE telegram_settings 
                SET notifications_enabled = NOT notifications_enabled,
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            
            $stmt = $this->pdo->prepare("
                SELECT notifications_enabled FROM telegram_settings WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $enabled = $result && $result['notifications_enabled'] == 1;
            
            $this->answerCallbackQuery($callback_id, 
                $enabled ? "✅ تم تفعيل الإشعارات" : "✅ تم تعطيل الإشعارات"
            );
            
            // تحديث الرسالة
            $this->showSettingsMenu($chat_id, $user_id);
            
        } catch (PDOException $e) {
            error_log("Error toggling notifications: " . $e->getMessage());
            $this->answerCallbackQuery($callback_id, "❌ حدث خطأ");
        }
    }
    
    private function showAdvanceTimeMenu($chat_id, $user_id, $callback_id) {
        $times = [1, 5, 10, 15, 30];
        
        $keyboard = [
            'inline_keyboard' => []
        ];
        
        foreach ($times as $time) {
            $keyboard['inline_keyboard'][] = [
                [
                    'text' => $time . ' دقائق',
                    'callback_data' => 'advance_' . $time
                ]
            ];
        }
        
        $keyboard['inline_keyboard'][] = [
            [
                'text' => '↩️ رجوع',
                'callback_data' => 'back_to_settings'
            ]
        ];
        
        $message = "⏰ **اختر وقت التنبيه قبل الصلاة:**\n\n";
        $message .= "سيتم إرسال إشعار قبل الصلاة بالوقت الذي تختاره.";
        
        $this->editMessageText($chat_id, $callback_id, $message, $keyboard);
        $this->answerCallbackQuery($callback_id);
    }
    
    private function setAdvanceTime($chat_id, $user_id, $minutes, $callback_id) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE telegram_settings 
                SET advance_notice = ?,
                    updated_at = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$minutes, $user_id]);
            
            $this->answerCallbackQuery($callback_id, "✅ تم تعيين وقت التنبيه إلى {$minutes} دقائق");
            $this->showSettingsMenu($chat_id, $user_id);
            
        } catch (PDOException $e) {
            error_log("Error setting advance time: " . $e->getMessage());
            $this->answerCallbackQuery($callback_id, "❌ حدث خطأ");
        }
    }
    
    private function requestLocation($chat_id, $callback_id) {
        $keyboard = [
            'keyboard' => [
                [
                    [
                        'text' => '📍 مشاركة الموقع',
                        'request_location' => true
                    ]
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];
        
        $message = "📍 **يرجى مشاركة موقعك:**\n\n";
        $message .= "اضغط على الزر أدناه لمشاركة موقعك الحالي.\n";
        $message .= "هذا ضروري لحساب مواقيت الصلاة بدقة.";
        
        $this->sendMessage($chat_id, $message, $keyboard);
        $this->answerCallbackQuery($callback_id);
    }
    
    private function sendLinkInstructions($chat_id, $user_id) {
        $token = $this->generateLinkToken($user_id);
        
        $message = "🔗 **تعليمات ربط حساب آخر:**\n\n";
        $message .= "لربط حساب Telegram آخر:\n\n";
        $message .= "1. اذهب إلى تطبيق تتبع الصلاة على الويب\n";
        $message .= "2. انتقل إلى صفحة الإعدادات\n";
        $message .= "3. اضغط على 'ربط حساب جديد'\n";
        $message .= "4. استخدم الكود التالي:\n\n";
        $message .= "`" . $token . "`\n\n";
        $message .= "⏰ هذا الكود صالح لمدة 10 دقائق فقط.";
        
        $this->sendMessage($chat_id, $message);
    }
    
    private function generateLinkToken($user_id) {
        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO telegram_link_tokens 
                (user_id, token, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                token = VALUES(token),
                expires_at = VALUES(expires_at),
                created_at = NOW()
            ");
            $stmt->execute([$user_id, $token, $expires]);
            
            return $token;
            
        } catch (PDOException $e) {
            error_log("Error generating link token: " . $e->getMessage());
            return null;
        }
    }
    
    // الدوال المساعدة
    private function findUserByChatId($chat_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.first_name, ts.chat_id 
                FROM users u
                JOIN telegram_settings ts ON u.id = ts.user_id
                WHERE ts.chat_id = ?
            ");
            $stmt->execute([$chat_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding user: " . $e->getMessage());
            return null;
        }
    }
    
    private function findOrCreateUser($telegram_user, $chat_id) {
        try {
            // البحث عن المستخدم برقم Telegram ID
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE telegram_id = ?");
            $stmt->execute([$telegram_user['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                return $user['id'];
            }
            
            // إنشاء مستخدم جديد
            $stmt = $this->pdo->prepare("
                INSERT INTO users 
                (telegram_id, first_name, last_name, username, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $telegram_user['id'],
                $telegram_user['first_name'],
                $telegram_user['last_name'] ?? '',
                $telegram_user['username'] ?? ''
            ]);
            
            return $this->pdo->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return null;
        }
    }
    
    private function updateUserChatId($user_id, $chat_id) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE telegram_settings 
                SET chat_id = ?, 
                    updated_at = NOW() 
                WHERE user_id = ?
            ");
            $stmt->execute([$chat_id, $user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error updating chat ID: " . $e->getMessage());
            return false;
        }
    }
    
    private function getUserPrayerTimes($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT prayer_times 
                FROM user_settings 
                WHERE user_id = ? 
                AND DATE(date) = DATE(NOW())
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['prayer_times']) {
                return json_decode($result['prayer_times'], true);
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log("Error getting prayer times: " . $e->getMessage());
            return null;
        }
    }
    
    private function getUserSettings($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT notifications_enabled, advance_notice 
                FROM telegram_settings 
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log("Error getting user settings: " . $e->getMessage());
            return [];
        }
    }
    
    private function sendMessage($chat_id, $text, $reply_markup = null) {
        // سيكون هذا الجزء للاتصال بـ Telegram API
        // في الواقع، سيتم التعامل معه من خلال الـ webhook نفسه
        error_log("Message to {$chat_id}: " . substr($text, 0, 100));
        
        // في تطبيق حقيقي، هنا ستقوم بإرسال الرسالة عبر Telegram API
        // لكن في webhook نحن نرد على الطلب، لذا سنقوم بإرسال الرد بشكل مختلف
    }
    
    private function answerCallbackQuery($callback_id, $text = null) {
        // إجابة على callback query
        $response = [
            'method' => 'answerCallbackQuery',
            'callback_query_id' => $callback_id
        ];
        
        if ($text) {
            $response['text'] = $text;
        }
        
        $this->sendResponse($response);
    }
    
    private function editMessageText($chat_id, $message_id, $text, $reply_markup = null) {
        $response = [
            'method' => 'editMessageText',
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ];
        
        if ($reply_markup) {
            $response['reply_markup'] = $reply_markup;
        }
        
        $this->sendResponse($response);
    }
    
    private function sendResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    private function formatArabicDate($date) {
        $months = [
            'January' => 'يناير',
            'February' => 'فبراير',
            'March' => 'مارس',
            'April' => 'أبريل',
            'May' => 'مايو',
            'June' => 'يونيو',
            'July' => 'يوليو',
            'August' => 'أغسطس',
            'September' => 'سبتمبر',
            'October' => 'أكتوبر',
            'November' => 'نوفمبر',
            'December' => 'ديسمبر'
        ];
        
        $english_date = date('d F Y', strtotime($date));
        $arabic_date = str_replace(
            array_keys($months),
            array_values($months),
            $english_date
        );
        
        return $arabic_date;
    }
}

// بدء تشغيل الـ webhook
try {
    // التحقق من السكرت للأمان (اختياري)
    if (isset($_GET['secret']) && $_GET['secret'] === WEBHOOK_SECRET) {
        new TelegramWebhook($pdo);
    } else {
        // بدء مباشر (للتطوير فقط)
        new TelegramWebhook($pdo);
    }
} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal server error']);
}
?>