<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/config.php';

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم من قاعدة البيانات
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// التحقق إذا كان المستخدم قد زار صفحة الترحيب من قبل
$has_seen_welcome = isset($_SESSION['has_seen_welcome']) ? $_SESSION['has_seen_welcome'] : false;


// وضع علامة أن المستخدم قد شاهد صفحة الترحيب
$_SESSION['has_seen_welcome'] = true;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>مرحباً بك | مرآة المؤمن</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 100%);
            color: #111827;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .welcome-container {
            max-width: 800px;
            width: 100%;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(5, 150, 105, 0.1);
            border: 2px solid #e5e7eb;
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .welcome-header {
            margin-bottom: 30px;
        }

        .pro-badge {
            display: inline-block;
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);
        }

        .welcome-title {
            color: #111827;
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            color: #666;
            font-size: 18px;
            margin-bottom: 40px;
        }

        .user-info-card {
            background: #f8fafc;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 40px;
            border: 2px solid #e5e7eb;
            text-align: right;
            transition: all 0.3s ease;
        }

        .user-info-card:hover {
            border-color: #059669;
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.1);
            transform: translateY(-5px);
        }

        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 4px solid #059669;
            padding: 3px;
            background: white;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-name {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }

        .user-email {
            color: #666;
            font-size: 16px;
            direction: ltr;
        }

        .features-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 50%;
            transform: translateX(50%);
            width: 100px;
            height: 4px;
            background: #059669;
            border-radius: 2px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: #f8fafc;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card:hover {
            border-color: #059669;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(5, 150, 105, 0.15);
        }

        .feature-card:before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #047857, #059669);
        }

        .feature-icon {
            font-size: 40px;
            color: #059669;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.2);
        }

        .feature-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }

        .feature-desc {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .highlight-badge {
            display: inline-block;
            background: rgba(5, 150, 105, 0.1);
            color: #047857;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
            border: 1px solid rgba(5, 150, 105, 0.3);
        }

        .thank-you-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e6f7f1 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
            border: 2px solid #d1fae5;
            text-align: right;
        }

        .thank-you-title {
            color: #111827;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .thank-you-text {
            color: #666;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 10px;
        }

        .feedback-section {
            margin-bottom: 40px;
        }

        .feedback-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
        }

        .feedback-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .feedback-btn {
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .feedback-btn-positive {
            background: #059669;
            color: white;
            border-color: #059669;
        }

        .feedback-btn-positive:hover {
            background: #047857;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.3);
        }

        .feedback-btn-neutral {
            background: white;
            color: #666;
            border-color: #e5e7eb;
        }

        .feedback-btn-neutral:hover {
            background: #f8fafc;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .main-action-btn {
            padding: 16px 40px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 200px;
            justify-content: center;
        }

        .btn-start {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
        }

        .btn-start:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(5, 150, 105, 0.4);
        }

        .btn-feedback {
            background: white;
            color: #059669;
            border: 2px solid #059669;
        }

        .btn-feedback:hover {
            background: #f0f9ff;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.2);
        }

        .prayer-reminder {
            margin-top: 30px;
            padding: 20px;
            background: rgba(5, 150, 105, 0.05);
            border-radius: 12px;
            border-right: 4px solid #059669;
            text-align: right;
        }

        .prayer-text {
            color: #047857;
            font-size: 16px;
            font-weight: 500;
            line-height: 1.8;
        }

        @media (max-width: 768px) {
            .welcome-container {
                padding: 25px;
            }

            .welcome-title {
                font-size: 26px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .feedback-buttons,
            .action-buttons {
                flex-direction: column;
                width: 100%;
            }

            .feedback-btn,
            .main-action-btn {
                width: 100%;
            }

            .user-info-card {
                padding: 20px;
            }

            .user-avatar {
                width: 80px;
                height: 80px;
            }
        }

        @media (max-width: 480px) {
            .welcome-container {
                padding: 20px;
            }

            .welcome-title {
                font-size: 22px;
            }

            .user-name {
                font-size: 20px;
            }

            .section-title {
                font-size: 20px;
            }
        }

        .footer-note {
            margin-top: 30px;
            color: #666;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-header">
            <div class="pro-badge">
                <i class="bi bi-star-fill"></i> النسخة PRO
            </div>
            <h1 class="welcome-title">مبروك! حصلت على النسخة Pro مجاناً</h1>
            <p class="welcome-subtitle">هدية من فريق مرآة المؤمن تقديراً لثقتك بنا</p>
        </div>

        <div class="user-info-card">
            <div class="user-avatar">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="صورة الملف الشخصي">
                <?php else: ?>
                    <div style="width:100%;height:100%;background:#059669;color:white;display:flex;align-items:center;justify-content:center;font-size:36px;border-radius:50%;">
                        <?php echo mb_substr($user['name'], 0, 1, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
            </div>
            <h2 class="user-name">مرحباً <?php echo htmlspecialchars($user['name']); ?>!</h2>
            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
            <p style="color:#059669;margin-top:10px;font-weight:600;">
                <i class="bi bi-calendar-check"></i> عضو منذ: <?php echo date('Y/m/d', strtotime($user['created_at'])); ?>
            </p>
        </div>

        <div class="features-section">
            <h2 class="section-title">المزايا التي حصلت عليها</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </div>
                    <h3 class="feature-title">إحصائيات غير محدودة</h3>
                    <p class="feature-desc">احصل على إحصائيات مفصلة ومفتوحة لصلواتك وأعمالك الصالحة</p>
                    <span class="highlight-badge">مدى الحياة</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <h3 class="feature-title">عبادات غير محدودة</h3>
                    <p class="feature-desc">أضف وتتبع جميع عباداتك وأذكارك دون أي قيود</p>
                    <span class="highlight-badge">ما لا نهاية</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h3 class="feature-title">دعم فني سريع</h3>
                    <p class="feature-desc">تواصل مع فريق الدعم بسرعة وسهولة لأي استفسار</p>
                    <span class="highlight-badge">أولوية</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="feature-title">مجموعات غير محدودة</h3>
                    <p class="feature-desc">أنشئ وانضم إلى مجموعات الصلاة والعمل الصالح</p>
                    <span class="highlight-badge">مفتوح</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <h3 class="feature-title">محادثات محلية</h3>
                    <p class="feature-desc">تواصل مع المؤمنين في منطقتك وتشاركوا في الأعمال الصالحة</p>
                    <span class="highlight-badge">جديد</span>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="feature-title">أمان وسرية تامة</h3>
                    <p class="feature-desc">بياناتك محمية ومشفرة بأعلى معايير الأمان</p>
                    <span class="highlight-badge">مضمون</span>
                </div>
            </div>
        </div>

        <div class="thank-you-section">
            <h3 class="thank-you-title">شكراً للتواصل مع فريق مرآة المؤمن</h3>
            <p class="thank-you-text">نحن نقدر ثقتك الغالية بنا ونسعى دائماً لتقديم الأفضل لمساعدتك في رحلتك الروحية.</p>
            <p class="thank-you-text" style="color:#047857;font-weight:600;">
                <i class="bi bi-heart-fill"></i> لا تبخل علينا بملاحظاتك وتقييمك
            </p>
            <p class="thank-you-text">
                نتمنى أن تكون تجربتك مفيدة لك، ولا تنسَ تجديد النية مع الله والدعاء لنا بالتوفيق
            </p>
        </div>

        <!-- <div class="feedback-section">
            <h3 class="feedback-title">كيف تجربتك حتى الآن؟</h3>
            <div class="feedback-buttons">
                <button class="feedback-btn feedback-btn-positive" onclick="submitFeedback('positive')">
                    <i class="bi bi-emoji-smile-fill"></i> رائعة!
                </button>
                <button class="feedback-btn feedback-btn-neutral" onclick="submitFeedback('neutral')">
                    <i class="bi bi-emoji-neutral-fill"></i> جيدة
                </button>
                <button class="feedback-btn feedback-btn-neutral" onclick="submitFeedback('suggestions')">
                    <i class="bi bi-lightbulb-fill"></i> لدي اقتراحات
                </button>
            </div>
        </div> -->

        <div class="prayer-reminder">
            <p class="prayer-text">
                <i class="bi bi-quote"></i>
                "اللهم اجعلنا من المقبولين ولا تجعلنا من المحرومين، وارزقنا الإخلاص في القول والعمل"
                <i class="bi bi-quote"></i>
            </p>
        </div>

        <div class="action-buttons">
            <button class="main-action-btn btn-start">
                <i class="bi bi-play-fill"></i>خلاص بنشطب 
            </button>
            <!-- <button class="main-action-btn btn-feedback" onclick="openFeedbackForm()">
                <i class="bi bi-pencil-fill"></i> أكتب ملاحظاتك
            </button> -->
        </div>

        <div class="footer-note">
            <p>© 2025 مرآة المؤمن | جميع الحقوق محفوظة</p>
            <p style="font-size:12px;margin-top:5px;color:#999;">تم تفعيل النسخة Pro بتاريخ <?php echo date('Y/m/d'); ?></p>
        </div>
    </div>

    <script>

        function openFeedbackForm() {
            // يمكنك إضافة رابط نموذج الملاحظات هنا
            window.open('feedback.php', '_blank');
        }

        function submitFeedback(type) {
            // إرسال التقييم إلى الخادم
            fetch('api/submit_feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    feedback_type: type,
                    user_id: <?php echo $user_id; ?>
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let message = '';
                    switch(type) {
                        case 'positive':
                            message = 'شكراً لك! نسعد بتقييمك الإيجابي';
                            break;
                        case 'neutral':
                            message = 'شكراً لملاحظاتك، سنعمل على التحسين';
                            break;
                        case 'suggestions':
                            message = 'شكراً! سنقوم بمراجعة اقتراحاتك';
                            break;
                    }
                    
                    // عرض رسالة تأكيد
                    const feedbackSection = document.querySelector('.feedback-section');
                    feedbackSection.innerHTML = `
                        <div style="text-align:center;padding:20px;">
                            <i class="bi bi-check-circle-fill" style="color:#059669;font-size:40px;margin-bottom:15px;"></i>
                            <h3 style="color:#047857;margin-bottom:10px;">${message}</h3>
                            <p style="color:#666;">نقدر لك مشاركتك في تحسين تجربتك</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // إضافة تأثيرات عند التمرير
        window.addEventListener('DOMContentLoaded', function() {
            const featureCards = document.querySelectorAll('.feature-card');
            
            featureCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });
        });
    </script>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .feature-card.fade-in {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }
    </style>
</body>
</html>