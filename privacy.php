<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>سياسة الخصوصية وشروط الاستخدام - مرآة المؤمن</title>
    <style>
        :root {
            --primary-color: #059669;
            --primary-light: #d1fae5;
            --primary-dark: #047857;
            --text-color: #111827;
            --text-light: #6b7280;
            --light-bg: #f9fafb;
            --border-color: #e5e7eb;
            --danger-color: #dc2626;
            --warning-color: #d97706;
            --info-color: #3b82f6;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--text-color);
            font-family: 'Tajawal', sans-serif;
            line-height: 1.8;
            padding: 0;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.3;
        }

        .header-content {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .site-name {
            font-size: 42px;
            font-weight: 900;
            color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .header-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .header-subtitle {
            font-size: 18px;
            opacity: 0.9;
            max-width: 800px;
            margin: 0 auto;
        }

        .content-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 40px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 15px 30px;
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-color);
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tab-btn:hover {
            background: var(--light-bg);
            border-color: var(--primary-color);
        }

        .tab-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .tab-btn i {
            font-size: 18px;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .document-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 40px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            margin-bottom: 40px;
        }

        .document-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary-light);
        }

        .document-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-light) 0%, white 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 24px;
            border: 2px solid var(--primary-light);
        }

        .document-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary-dark);
            flex: 1;
        }

        .document-date {
            color: var(--text-light);
            font-size: 14px;
            background: var(--light-bg);
            padding: 5px 15px;
            border-radius: var(--radius-sm);
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 30px 0 20px 0;
            padding-right: 15px;
            border-right: 4px solid var(--primary-color);
        }

        .clause {
            margin-bottom: 25px;
            padding: 20px;
            background: var(--light-bg);
            border-radius: var(--radius-md);
            border-right: 3px solid var(--border-color);
            transition: var(--transition);
        }

        .clause:hover {
            border-right-color: var(--primary-color);
            background: white;
            box-shadow: var(--shadow-sm);
        }

        .clause-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .clause-title i {
            color: var(--primary-color);
        }

        .clause-content {
            color: var(--text-color);
            line-height: 1.8;
        }

        .clause-content ul {
            padding-right: 25px;
            margin: 15px 0;
        }

        .clause-content li {
            margin-bottom: 10px;
            position: relative;
        }

        .clause-content li:before {
            content: "•";
            color: var(--primary-color);
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: 1em;
        }

        .highlight-box {
            background: linear-gradient(135deg, var(--primary-light) 0%, white 100%);
            border-radius: var(--radius-md);
            padding: 25px;
            margin: 25px 0;
            border-right: 4px solid var(--primary-color);
        }

        .highlight-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-info {
            background: white;
            border-radius: var(--radius-lg);
            padding: 40px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            text-align: center;
            margin-top: 60px;
        }

        .contact-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 30px;
        }

        .contact-items {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: var(--light-bg);
            border-radius: var(--radius-md);
            min-width: 250px;
            transition: var(--transition);
        }

        .contact-item:hover {
            background: var(--primary-light);
            transform: translateY(-5px);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .contact-text {
            text-align: right;
            flex: 1;
        }

        .contact-label {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .contact-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-color);
        }

        .footer {
            background: var(--primary-dark);
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-top: 60px;
        }

        .footer-content {
            max-width: 1000px;
            margin: 0 auto;
        }

        .copyright {
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .footer-link {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-link:hover {
            opacity: 1;
            color: var(--primary-light);
        }

        .back-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            z-index: 100;
            border: none;
        }

        .back-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-5px);
        }

        @media (max-width: 768px) {
            .header {
                padding: 40px 20px;
            }
            
            .site-name {
                font-size: 32px;
            }
            
            .header-title {
                font-size: 28px;
            }
            
            .logo-icon {
                width: 60px;
                height: 60px;
                font-size: 28px;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab-btn {
                width: 100%;
                justify-content: center;
            }
            
            .document-section {
                padding: 30px 20px;
            }
            
            .document-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .contact-items {
                flex-direction: column;
                align-items: center;
            }
            
            .contact-item {
                width: 100%;
                max-width: 400px;
            }
            
            .back-btn {
                bottom: 20px;
                left: 20px;
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .header-title {
                font-size: 24px;
            }
            
            .site-name {
                font-size: 28px;
            }
            
            .document-title {
                font-size: 24px;
            }
            
            .section-title {
                font-size: 20px;
            }
        }

        .important-note {
            background: linear-gradient(135deg, #fff3cd 0%, #fde68a 100%);
            border-radius: var(--radius-md);
            padding: 20px;
            margin: 30px 0;
            border-right: 4px solid var(--warning-color);
        }

        .important-title {
            color: #856404;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <!-- زر العودة للأعلى -->
    <button class="back-btn" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- الهيدر -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-mosque"></i>
                </div>
                <h1 class="site-name">مرآة المؤمن</h1>
            </div>
            <h2 class="header-title">سياسة الخصوصية وشروط الاستخدام</h2>
            <p class="header-subtitle">
                نرحب بكم في منصة "مرآة المؤمن" - تطبيق متابعة الصلوات والممارسات الدينية. نلتزم بحماية خصوصيتكم وتوفير تجربة آمنة وموثوقة لجميع المستخدمين.
            </p>
        </div>
    </header>

    <!-- التبويبات -->
    <div class="content-container">
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('privacy')">
                <i class="fas fa-shield-alt"></i>
                سياسة الخصوصية
            </button>
            <button class="tab-btn" onclick="showTab('terms')">
                <i class="fas fa-file-contract"></i>
                شروط الاستخدام
            </button>
            <button class="tab-btn" onclick="showTab('cookies')">
                <i class="fas fa-cookie-bite"></i>
                سياسة ملفات تعريف الارتباط
            </button>
        </div>

        <!-- سياسة الخصوصية -->
        <div id="privacy" class="tab-content active">
            <div class="document-section">
                <div class="document-header">
                    <div class="document-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h2 class="document-title">سياسة الخصوصية</h2>
                    <div class="document-date">آخر تحديث: ١٤٤٧/٠٦/١٤ هـ</div>
                </div>

                <div class="important-note">
                    <h3 class="important-title">
                        <i class="fas fa-exclamation-circle"></i>
                        ملاحظة هامة
                    </h3>
                    <p>نحن في "مرآة المؤمن" ندرك أهمية خصوصية بياناتكم الشخصية ونلتزم بحمايتها وفقًا لأحكام الشريعة الإسلامية وأفضل الممارسات التقنية.</p>
                </div>

                <h3 class="section-title">١. المعلومات التي نجمعها</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-user-circle"></i>
                        ١.١ المعلومات الشخصية الأساسية
                    </h4>
                    <div class="clause-content">
                        <ul>
                            <li>الاسم الكامل (للعرض فقط)</li>
                            <li>البريد الإلكتروني (لأغراض التواصل واستعادة الحساب)</li>
                            <li>الصورة الشخصية من حساب Google (اختيارية)</li>
                            <li>معرّف Google الفريد (للتسجيل الآمن)</li>
                        </ul>
                    </div>
                </div>

                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-pray"></i>
                        ١.٢ بيانات الصلوات والعبادات
                    </h4>
                    <div class="clause-content">
                        <p>نجمع ونخزن البيانات المتعلقة بـ:</p>
                        <ul>
                            <li>سجل الصلوات اليومية (الأوقات والتواريخ)</li>
                            <li>الإنجازات والعلامات التقديرية</li>
                            <li>الأهداف الروحية والتقدم المحرز</li>
                            <li>المشاركة في المجموعات الدينية (إن وجدت)</li>
                        </ul>
                    </div>
                </div>

                <h3 class="section-title">٢. كيفية استخدام المعلومات</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-cogs"></i>
                        ٢.١ الأغراض الأساسية
                    </h4>
                    <div class="clause-content">
                        <ul>
                            <li>توفير وتطوير خدمات متابعة الصلوات</li>
                            <li>حساب الإحصائيات والتقارير الشخصية</li>
                            <li>تحسين تجربة المستخدم وتخصيص المحتوى</li>
                            <li>إرسال تنبيهات وتذكيرات بالصلوات (حسب التفضيلات)</li>
                        </ul>
                    </div>
                </div>

                <div class="highlight-box">
                    <h4 class="highlight-title">
                        <i class="fas fa-handshake"></i>
                        مبادئنا في التعامل مع بياناتكم
                    </h4>
                    <p>نلتزم بالمبادئ التالية في تعاملنا مع بياناتكم الشخصية:</p>
                    <ul>
                        <li>الشفافية: نوضح دائمًا كيف نستخدم بياناتكم</li>
                        <li>الحد الأدنى: نجمع فقط ما نحتاجه لأداء الخدمة</li>
                        <li>الأمان: نحمي بياناتكم بأفضل التقنيات المتاحة</li>
                        <li>التحكم: تملكون الحق في التحكم ببياناتكم دائمًا</li>
                    </ul>
                </div>

                <h3 class="section-title">٣. مشاركة المعلومات</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-share-alt"></i>
                        ٣.١ عدم مشاركة البيانات الشخصية
                    </h4>
                    <div class="clause-content">
                        <p>نؤكد أننا <strong>لا نبيع ولا نؤجر ولا نشارك</strong> بياناتكم الشخصية مع أي أطراف ثالثة لأغراض تجارية.</p>
                        <p>الاستثناءات الوحيدة هي:</p>
                        <ul>
                            <li>عندما يكون ذلك مطلوبًا بموجب القانون السعودي</li>
                            <li>لحماية حقوق أو ممتلكات "مرآة المؤمن"</li>
                            <li>لمنع أو معالجة المشكلات الفنية أو الأمنية</li>
                        </ul>
                    </div>
                </div>

                <h3 class="section-title">٤. أمان البيانات</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-lock"></i>
                        ٤.١ تدابير الحماية
                    </h4>
                    <div class="clause-content">
                        <p>نستخدم أحدث التقنيات لحماية بياناتكم:</p>
                        <ul>
                            <li>تشفير البيانات أثناء النقل والتخزين</li>
                            <li>جدران حماية وبرامج مكافحة الاختراق</li>
                            <li>مراجعات أمنية دورية للأنظمة</li>
                            <li>تدريب فريق العمل على أفضل ممارسات الأمن السيبراني</li>
                        </ul>
                    </div>
                </div>

                <h3 class="section-title">٥. حقوق المستخدم</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-user-check"></i>
                        ٥.١ حقوقكم في بياناتكم
                    </h4>
                    <div class="clause-content">
                        <p>لديكم الحق في:</p>
                        <ul>
                            <li>الوصول إلى بياناتكم الشخصية</li>
                            <li>تصحيح البيانات غير الدقيقة</li>
                            <li>حذف حسابكم وبياناتكم الشخصية</li>
                            <li>تصدير بياناتكم في صيغة قابلة للقراءة</li>
                            <li>إلغاء الاشتراك في الرسائل الإخبارية</li>
                            <li>تحديث تفضيلات الخصوصية في أي وقت</li>
                        </ul>
                    </div>
                </div>

                <h3 class="section-title">٦. التحديثات على السياسة</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-sync-alt"></i>
                        ٦.١ إشعار التغييرات
                    </h4>
                    <div class="clause-content">
                        <p>قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر. وسنخطركم بأي تغييرات جوهرية عن طريق:</p>
                        <ul>
                            <li>إشعار في التطبيق</li>
                            <li>البريد الإلكتروني</li>
                            <li>نشر نسخة محدثة على هذه الصفحة</li>
                        </ul>
                        <p>نشجعكم على مراجعة هذه السياسة بشكل دوري للاطلاع على أي تغييرات.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- شروط الاستخدام -->
        <div id="terms" class="tab-content">
            <div class="document-section">
                <div class="document-header">
                    <div class="document-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h2 class="document-title">شروط وأحكام الاستخدام</h2>
                    <div class="document-date">آخر تحديث: ١٤٤٧/٠٦/١٤ هـ</div>
                </div>

                <div class="important-note">
                    <h3 class="important-title">
                        <i class="fas fa-gavel"></i>
                        بيان عام
                    </h3>
                    <p>باستخدامكم تطبيق "مرآة المؤمن"، فإنكم توافقون على الالتزام بهذه الشروط والأحكام. يرجى قراءتها بعناية قبل استخدام التطبيق.</p>
                </div>

                <h3 class="section-title">١. القبول بالشروط</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-check-circle"></i>
                        ١.١ الموافقة الإلزامية
                    </h4>
                    <div class="clause-content">
                        <p>بإنشاء حساب في "مرآة المؤمن"، فإنكم:</p>
                        <ul>
                            <li>تقرون بأنكم قد قرأتم وفهمتم هذه الشروط</li>
                            <li>توافقون على الالتزام بجميع الأحكام الواردة فيها</li>
                            <li>تقرون بأنكم مؤهلون قانونيًا لعقد هذا الاتفاق</li>
                            <li>توافقون على أن استخدامكم للتطبيق للأغراض الشخصية فقط</li>
                        </ul>
                    </div>
                </div>

                <h3 class="section-title">٢. الالتزامات الدينية والأخلاقية</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-star-and-crescent"></i>
                        ٢.١ الغرض من التطبيق
                    </h4>
                    <div class="clause-content">
                        <p>"مرآة المؤمن" هو تطبيق ديني يهدف إلى:</p>
                        <ul>
                            <li>مساعدة المسلمين على متابعة صلواتهم</li>
                            <li>تشجيع الالتزام بالعبادات اليومية</li>
                            <li>توفير بيئة إيجابية للنمو الروحي</li>
                            <li>تعزيز القيم الإسلامية في الاستخدام الرقمي</li>
                        </ul>
                    </div>
                </div>

                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-ban"></i>
                        ٢.٢ الممنوعات
                    </h4>
                    <div class="clause-content">
                        <p>يمنع منعًا باتًا:</p>
                        <ul>
                            <li>استخدام التطبيق لنشر محتوى مخالف للشريعة الإسلامية</li>
                            <li>الانتحال أو التزوير في البيانات الدينية</li>
                            <li>إساءة استخدام المجموعات الدينية</li>
                            <li>التصرفات التي تخل بالأخلاق الإسلامية</li>
                            <li>التجسس على حسابات الآخرين</li>
                        </ul>
                    </div>
                </div>

                <h3 class="section-title">٣. الحسابات والمستخدمين</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-user-lock"></i>
                        ٣.١ مسؤولية الحساب
                    </h4>
                    <div class="clause-content">
                        <p>أنت المسؤول الوحيد عن:</p>
                        <ul>
                            <li>الحفاظ على سرية بيانات تسجيل الدخول</li>
                            <li>جميع الأنشطة التي تتم تحت حسابك</li>
                            <li>أي ضرر ينتج عن إهمالك في حماية حسابك</li>
                            <li>الإبلاغ الفوري عن أي استخدام غير مصرح به</li>
                        </ul>
                    </div>
                </div>

                <div class="highlight-box">
                    <h4 class="highlight-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        تحذير هام
                    </h4>
                    <p>يحق لإدارة "مرآة المؤمن" تعليق أو إنهاء أي حساب في الحالات التالية:</p>
                    <ul>
                        <li>انتهاك شروط الاستخدام</li>
                        <li>السلوك غير الأخلاقي أو المخالف للشريعة</li>
                        <li>محاولة التلاعب بنتائج الصلوات أو الإحصائيات</li>
                        <li>استخدام التطبيق لأغراض غير مشروعة</li>
                    </ul>
                </div>

                <h3 class="section-title">٤. الملكية الفكرية</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-copyright"></i>
                        ٤.١ حقوق النشر
                    </h4>
                    <div class="clause-content">
                        <p>جميع حقوق الملكية الفكرية في "مرآة المؤمن" محفوظة وتشمل:</p>
                        <ul>
                            <li>الشعارات والعلامات التجارية</li>
                            <li>تصميم وواجهة التطبيق</li>
                            <li>الكود البرمجي والخوارزميات</li>
                            <li>المحتوى الديني المقدم (مع احترام المصادر)</li>
                        </ul>
                    </div>
                </div>

                <h3 class="section-title">٥. حدود المسؤولية</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-info-circle"></i>
                        ٥.١ إخلاء المسؤولية
                    </h4>
                    <div class="clause-content">
                        <p>"مرآة المؤمن" يقدم خدماته "كما هي" دون أي ضمانات صريحة أو ضمنية.</p>
                        <p>لا نتحمل مسؤولية:</p>
                        <ul>
                            <li>أي أخطاء في توقيت الصلوات (نعتمد على مصادر موثوقة)</li>
                            <li>انقطاع الخدمة لأسباب تقنية خارجة عن إرادتنا</li>
                            <li>أي استخدام خاطئ من قبل المستخدمين</li>
                            <li>النتائج الروحية الفردية (تذكير فقط وليس ضمان)</li>
                        </ul>
                    </div>
                </div>

                <h3 class="section-title">٦. القانون الواجب التطبيق</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-scale-balanced"></i>
                        ٦.١ الاختصاص القضائي
                    </h4>
                    <div class="clause-content">
                        <p>تخضع هذه الشروط والأحكام وتفسر وفقًا للقوانين والأنظمة المعمول بها في المملكة العربية السعودية.</p>
                        <p>أي نزاعات تنشأ عن استخدام هذا التطبيق تحال إلى المحاكم المختصة في مدينة الرياض.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- سياسة ملفات تعريف الارتباط -->
        <div id="cookies" class="tab-content">
            <div class="document-section">
                <div class="document-header">
                    <div class="document-icon">
                        <i class="fas fa-cookie"></i>
                    </div>
                    <h2 class="document-title">سياسة ملفات تعريف الارتباط</h2>
                    <div class="document-date">آخر تحديث: ١٤٤٧/٠٦/١٤ هـ</div>
                </div>

                <div class="important-note">
                    <h3 class="important-title">
                        <i class="fas fa-info-circle"></i>
                        ما هي ملفات تعريف الارتباط؟
                    </h3>
                    <p>ملفات تعريف الارتباط (Cookies) هي ملفات نصية صغيرة يتم تخزينها على جهازك لتحسين تجربة استخدامك للتطبيق.</p>
                </div>

                <h3 class="section-title">١. أنواع الملفات التي نستخدمها</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-cog"></i>
                        ١.١ ملفات أساسية (ضرورية)
                    </h4>
                    <div class="clause-content">
                        <p>هذه الملفات ضرورية لعمل التطبيق ولا يمكن إيقافها:</p>
                        <ul>
                            <li>ملفات تسجيل الدخول والحفاظ على الجلسات</li>
                            <li>ملفات الأمان ومنع الاحتيال</li>
                            <li>ملفات تذكر تفضيلات اللغة والتوقيت</li>
                            <li>ملفات تفعيل الميزات الأساسية</li>
                        </ul>
                    </div>
                </div>

                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-chart-line"></i>
                        ١.٢ ملفات تحليلية
                    </h4>
                    <div class="clause-content">
                        <p>تساعدنا على فهم كيفية استخدام التطبيق:</p>
                        <ul>
                            <li>عدد المستخدمين النشطين</li>
                            <li>الصفحات الأكثر زيارة</li>
                            <li>معدلات التفاعل مع الميزات</li>
                            <li>تحسينات الأداء والتجربة</li>
                        </ul>
                        <p>ملاحظة: هذه البيانات مجهولة الهوية ولا ترتبط بأي معلومات شخصية.</p>
                    </div>
                </div>

                <h3 class="section-title">٢. إدارة ملفات تعريف الارتباط</h3>
                
                <div class="clause">
                    <h4 class="clause-title">
                        <i class="fas fa-sliders-h"></i>
                        ٢.١ التحكم في الملفات
                    </h4>
                    <div class="clause-content">
                        <p>يمكنكم التحكم في ملفات تعريف الارتباط من خلال:</p>
                        <ul>
                            <li>إعدادات الخصوصية في متصفحكم</li>
                            <li>أدوات إدارة ملفات تعريف الارتباط المدمجة</li>
                            <li>إعدادات التطبيق (للملفات غير الأساسية)</li>
                        </ul>
                        <p><strong>تحذير:</strong> تعطيل الملفات الأساسية قد يؤثر على أداء التطبيق.</p>
                    </div>
                </div>

                <div class="highlight-box">
                    <h4 class="highlight-title">
                        <i class="fas fa-shield-alt"></i>
                        التزامنا بالخصوصية
                    </h4>
                    <p>نحن في "مرآة المؤمن" نلتزم باستخدام ملفات تعريف الارتباط بطريقة أخلاقية وشفافة:</p>
                    <ul>
                        <li>لا نستخدم ملفات تتبع لأغراض تسويقية</li>
                        <li>لا نشارك بيانات التتبع مع أطراف ثالثة</li>
                        <li>نحترم إعدادات "عدم التتبع" في المتصفحات</li>
                        <li>نزودكم بخيارات واضحة للتحكم</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- معلومات التواصل -->
        <div class="contact-info">
            <h3 class="contact-title">
                <i class="fas fa-headset"></i>
                للاستفسارات والشكاوى
            </h3>
            <div class="contact-items">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-text">
                        <div class="contact-label">البريد الإلكتروني</div>
                        <div class="contact-value">miraatalmumin@gmail.com</div>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-text">
                        <div class="contact-label">أوقات الدعم</div>
                        <div class="contact-value">الأحد - الخميس: ٩ صباحًا - ٥ مساءً</div>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-text">
                        <div class="contact-label">المقر</div>
                        <div class="contact-value">الجيزة - جمهورية مصر</div>
                    </div>
                </div>
            </div>
            <p style="margin-top: 30px; color: var(--text-light);">
                <i class="fas fa-history"></i>
                سيتم الرد على استفساراتكم في غضون 16 ساعة عمل
            </p>
        </div>
    </div>

    <!-- الفوتر -->
    <footer class="footer">
        <div class="footer-content">
            <p class="copyright">
                <i class="fas fa-copyright"></i>
                جميع الحقوق محفوظة لـ "مرآة المؤمن" © ١٤٤٧ هـ - ٢٠٢٥ م
            </p>
            <p style="margin-bottom: 20px; opacity: 0.8;">
                نسعى لجعل التقنية وسيلة لتعزيز العبادات وتقوية الصلة بالله تعالى
            </p>
            <div class="footer-links">
                <a href="index.php" class="footer-link">
                    <i class="fas fa-home"></i>
                    الصفحة الرئيسية
                </a>
                <a href="login.php" class="footer-link">
                    <i class="fas fa-sign-in-alt"></i>
                    تسجيل الدخول
                </a>
                <a href="#" onclick="showTab('privacy')" class="footer-link">
                    <i class="fas fa-shield-alt"></i>
                    سياسة الخصوصية
                </a>
                <a href="#" onclick="showTab('terms')" class="footer-link">
                    <i class="fas fa-file-contract"></i>
                    شروط الاستخدام
                </a>
            </div>
        </div>
    </footer>

    <script>
        // وظيفة عرض التبويب المحدد
        function showTab(tabName) {
            // إخفاء جميع المحتويات
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // إلغاء تنشيط جميع الأزرار
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });
            
            // عرض المحتوى المحدد
            document.getElementById(tabName).classList.add('active');
            
            // تنشيط الزر المحدد
            event.currentTarget.classList.add('active');
            
            // التمرير إلى الأعلى
            scrollToTop();
        }

        // وظيفة التمرير إلى الأعلى
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // إظهار زر العودة عند التمرير
        window.addEventListener('scroll', function() {
            const backBtn = document.querySelector('.back-btn');
            if (window.scrollY > 300) {
                backBtn.style.opacity = '1';
                backBtn.style.visibility = 'visible';
            } else {
                backBtn.style.opacity = '0';
                backBtn.style.visibility = 'hidden';
            }
        });

        // تفعيل تأثيرات عند التمرير
        document.addEventListener('DOMContentLoaded', function() {
            const clauses = document.querySelectorAll('.clause');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, {
                threshold: 0.1
            });
            
            clauses.forEach(clause => {
                clause.style.opacity = '0';
                clause.style.transform = 'translateY(20px)';
                clause.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(clause);
            });
        });

        // حفظ التبويب النشط في التخزين المحلي
        document.addEventListener('DOMContentLoaded', function() {
            const activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                showTab(activeTab);
            }
        });

        // تحديث التبويب النشط عند التغيير
        const tabButtons = document.querySelectorAll('.tab-btn');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('onclick').match(/'([^']+)'/)[1];
                localStorage.setItem('activeTab', tabName);
            });
        });
    </script>
</body>
</html>