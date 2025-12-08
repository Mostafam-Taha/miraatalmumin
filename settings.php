<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة الإعدادات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Tajawal", sans-serif;
        }

        html, body {
            overflow-y: scroll;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
        }

        :root {
            --button-color: #059669;
            --text-color: #111827;
            --bg-color: #047857;
            --light-bg: #f9fafb;
            --border-color: #e5e7eb;
            --hover-color: #047857;
        }

        body {
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .header h1 {
            color: var(--text-color);
            font-size: 28px;
            font-weight: 700;
        }

        .settings-container {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .settings-section {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .section-header {
            background-color: var(--light-bg);
            padding: 18px 25px;
            border-bottom: 1px solid var(--border-color);
        }

        .section-header h2 {
            color: var(--text-color);
            font-size: 20px;
            font-weight: 600;
        }

        .settings-items {
            padding: 10px 0;
        }

        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 25px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .setting-item:hover {
            background-color: var(--light-bg);
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .item-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: rgba(5, 150, 105, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--button-color);
            font-size: 18px;
        }

        .item-text h3 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .item-text p {
            font-size: 14px;
            color: #6b7280;
        }

        .item-arrow {
            color: #9ca3af;
            font-size: 14px;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal {
            background-color: white;
            border-radius: 16px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px 30px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--light-bg);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-header h2 {
            color: var(--text-color);
            font-size: 24px;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .close-modal:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--text-color);
        }

        .modal-content {
            padding: 30px;
        }

        .modal-content p {
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .modal-section {
            margin-bottom: 30px;
        }

        .modal-section h3 {
            color: var(--text-color);
            font-size: 18px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--button-color);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .checkbox-group input {
            margin-left: 10px;
            width: 18px;
            height: 18px;
            accent-color: var(--button-color);
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: var(--button-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--hover-color);
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        .btn-danger {
            background-color: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        /* Dark mode toggle */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            right: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background-color: var(--button-color);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(-30px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .section-header h2 {
                font-size: 18px;
            }
            
            .modal-header {
                padding: 20px;
            }
            
            .modal-content {
                padding: 20px;
            }
            
            .modal-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid var(--border-color);
        }

                /* Telegram specific styles */
        .telegram-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .status-connected {
            background-color: #05966920;
            color: #059669;
        }
        
        .status-disconnected {
            background-color: #dc262620;
            color: #dc2626;
        }
        
        .test-btn {
            background-color: #0ea5e9;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .test-btn:hover {
            background-color: #0284c7;
        }
        
        .token-display {
            background-color: #f3f4f6;
            padding: 10px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
            word-break: break-all;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-cog"></i> الإعدادات</h1>
        </div>

        <div class="settings-container">
            <!-- المظهر -->
            <div class="settings-section">
                <div class="section-header">
                    <h2>المظهر</h2>
                </div>
                <div class="settings-items">
                    <div class="setting-item" onclick="openModal('appearance')">
                        <div class="item-info">
                            <div class="item-icon">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="item-text">
                                <h3>الوضع</h3>
                                <p>اختر بين الوضع الفاتح أو الداكن</p>
                            </div>
                        </div>
                        <div class="item-arrow">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- الخصوصية -->
            <div class="settings-section">
                <div class="section-header">
                    <h2>الخصوصية والأمان</h2>
                </div>
                <div class="settings-items">
                    <div class="setting-item" onclick="openModal('privacy')">
                        <div class="item-info">
                            <div class="item-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="item-text">
                                <h3>إقون الحماية</h3>
                                <p>إدارة إعدادات الخصوصية والأمان</p>
                            </div>
                        </div>
                        <div class="item-arrow">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                    </div>
                    <div class="setting-item" onclick="openModal('data-delete')">
                        <div class="item-info">
                            <div class="item-icon">
                                <i class="fas fa-trash-alt"></i>
                            </div>
                            <div class="item-text">
                                <h3>حذف البيانات</h3>
                                <p>حذف جميع بيانات الحساب</p>
                            </div>
                        </div>
                        <div class="item-arrow">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- المجموعة -->
            <div class="settings-section">
                <div class="section-header">
                    <h2>المجموعة</h2>
                </div>
                <div class="settings-items">
                    <div class="setting-item" onclick="openModal('group')">
                        <div class="item-info">
                            <div class="item-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="item-text">
                                <h3>إدارة المجموعة</h3>
                                <p>إعدادات المجموعة والأعضاء</p>
                            </div>
                        </div>
                        <div class="item-arrow">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                    </div>
                    
                    <!-- إضافة خيار منع الانضمام للمجموعات -->
                    <div class="setting-item" id="block-groups-item">
                        <div class="item-info">
                            <div class="item-icon">
                                <i class="fas fa-user-slash"></i>
                            </div>
                            <div class="item-text">
                                <h3>عدم الدخول في أي مجموعة</h3>
                                <p>منع الانضمام التلقائي لأي مجموعة (خاص بالنساء)</p>
                            </div>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" id="block-all-groups-toggle" 
                                onchange="toggleBlockGroups(this.checked)">
                            <span class="toggle-slider"></span>
                        </div>
                    </div>
                </div>
            </div>


                        <!-- ربط Telegram -->
            <div class="settings-section">
                <div class="section-header">
                    <h2>ربط حسابك على مرآة المؤمن</h2>
                </div>
                <div class="settings-items">
                    <div class="setting-item" onclick="openModal('telegram')">
                        <div class="item-info">
                            <div class="item-icon">
                                <i class="fab fa-telegram"></i>
                            </div>
                            <div class="item-text">
                                <h3>ربط حساب Telegram</h3>
                                <p>Token البوت و Chat ID</p>
                            </div>
                        </div>
                        <div class="item-arrow">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                    </div>
                </div>
            </div>

        <div class="footer">
            <p>جميع الحقوق محفوظة &copy; 2023</p>
        </div>
    </div>

    <!-- Modal for Appearance -->
    <div id="appearance-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>إعدادات المظهر</h2>
                <button class="close-modal" onclick="closeModal('appearance')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <div class="modal-section">
                    <h3>الوضع</h3>
                    <p>اختر الوضع المناسب لك:</p>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <label for="light-mode">الوضع الفاتح</label>
                            <input type="radio" id="light-mode" name="theme" value="light" checked>
                        </div>
                        
                        <div class="checkbox-group">
                            <label for="dark-mode">الوضع الداكن</label>
                            <input type="radio" id="dark-mode" name="theme" value="dark">
                        </div>
                        
                        <div class="checkbox-group">
                            <label for="auto-mode">تلقائي (يتناسب مع إعدادات الجهاز)</label>
                            <input type="radio" id="auto-mode" name="theme" value="auto">
                        </div>
                    </div>
                </div>
                
                <div class="modal-section">
                    <h3>حجم الخط</h3>
                    <p>اختر حجم الخط المناسب:</p>
                    
                    <div class="form-group">
                        <select class="form-control" id="font-size">
                            <option value="small">صغير</option>
                            <option value="medium" selected>متوسط</option>
                            <option value="large">كبير</option>
                            <option value="xlarge">كبير جداً</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal('appearance')">إلغاء</button>
                    <button class="btn btn-primary" onclick="saveAppearanceSettings()">حفظ التغييرات</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Block Groups -->
    <div id="block-groups-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>منع الانضمام للمجموعات</h2>
                <button class="close-modal" onclick="closeModal('block-groups')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <div class="modal-section">
                    <h3>ميزة خاصة للنساء</h3>
                    <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-right: 4px solid #ffc107;">
                        <p><i class="fas fa-female" style="color: #d63384; margin-left: 5px;"></i> 
                        <strong>هذه الميزة موجهة للنساء بشكل خاص</strong> للحفاظ على خصوصيتهن ومنع الانضمام التلقائي للمجموعات.</p>
                    </div>
                    
                    <p>عند تفعيل هذه الميزة:</p>
                    <ul style="padding-right: 20px; margin-bottom: 20px;">
                        <li style="margin-bottom: 10px;"><i class="fas fa-ban" style="color: #dc3545; margin-left: 5px;"></i> سيتم حظر أي محاولة للانضمام التلقائي لأي مجموعة</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-link" style="color: #6f42c1; margin-left: 5px;"></i> عند الضغط على أي رابط انضمام لمجموعة، سيتم عرض رسالة تحذيرية</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-exclamation-triangle" style="color: #fd7e14; margin-left: 5px;"></i> يمكنك إلغاء التفعيل في أي وقت ترغبين فيه بالانضمام لمجموعة</li>
                        <li style="margin-bottom: 10px;"><i class="fas fa-user-shield" style="color: #20c997; margin-left: 5px;"></i> تبقى لديكِ القدرة على الانضمام للمجموعات يدوياً من خلال صفحة المجموعات</li>
                    </ul>
                    
                    <p><strong>الغرض من هذه الميزة:</strong> حماية خصوصية النساء ومنع الانضمام غير المرغوب فيه للمجموعات التي قد تحتوي على محتوى غير مناسب أو أشخاص غير معروفين.</p>
                    
                    <div class="form-group" style="margin-top: 25px;">
                        <div class="checkbox-group">
                            <label for="confirm-block-groups">أفهم أن تفعيل هذه الميزة سيمنع الانضمام التلقائي لأي مجموعة</label>
                            <input type="checkbox" id="confirm-block-groups" onchange="updateBlockToggleState()">
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal('block-groups')">إلغاء</button>
                    <button class="btn btn-primary" onclick="confirmBlockGroups()" id="confirm-block-btn" disabled>تفعيل الميزة</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Privacy -->
    <div id="privacy-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>الخصوصية والأمان</h2>
                <button class="close-modal" onclick="closeModal('privacy')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <div class="modal-section">
                    <h3>إعدادات الخصوصية</h3>
                    <p>يمكنك من خلال هذه الصفحة التحكم في إعدادات الخصوصية والأمان لحسابك.</p>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <label for="private-account">جعل الحساب خاصاً</label>
                            <input type="checkbox" id="private-account">
                        </div>
                        
                        <div class="checkbox-group">
                            <label for="show-activity">إظهار حالة النشاط</label>
                            <input type="checkbox" id="show-activity" checked>
                        </div>
                        
                        <div class="checkbox-group">
                            <label for="two-factor">تفعيل المصادقة الثنائية</label>
                            <input type="checkbox" id="two-factor">
                        </div>
                    </div>
                </div>
                
                <div class="modal-section">
                    <h3>صلاحية الوصول</h3>
                    <p>تحكم في من يمكنه رؤية معلوماتك والتواصل معك:</p>
                    
                    <div class="form-group">
                        <label for="who-can-see">من يمكنه رؤية معلوماتك الشخصية</label>
                        <select class="form-control" id="who-can-see">
                            <option value="everyone">الجميع</option>
                            <option value="friends" selected>الأصدقاء فقط</option>
                            <option value="only-me">أنا فقط</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message-who">من يمكنه إرسال رسائل لك</label>
                        <select class="form-control" id="message-who">
                            <option value="everyone">الجميع</option>
                            <option value="friends" selected>الأصدقاء فقط</option>
                            <option value="none">لا أحد</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal('privacy')">إلغاء</button>
                    <button class="btn btn-primary" onclick="savePrivacySettings()">حفظ التغييرات</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Data Delete -->
    <div id="data-delete-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>حذف البيانات</h2>
                <button class="close-modal" onclick="closeModal('data-delete')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <div class="modal-section">
                    <h3>تحذير مهم</h3>
                    <p>عملية حذف البيانات نهائية ولا يمكن التراجع عنها. سيتم حذف جميع المعلومات المرتبطة بحسابك بما في ذلك:</p>
                    <ul style="padding-right: 20px; margin-bottom: 20px;">
                        <li style="margin-bottom: 10px;">جميع البيانات الشخصية</li>
                        <li style="margin-bottom: 10px;">سجل النشاط والعمليات</li>
                        <li style="margin-bottom: 10px;">المحتوى الذي أنشأته</li>
                        <li style="margin-bottom: 10px;">الإعدادات والتخصيصات</li>
                    </ul>
                    <p>قد تستغرق عملية الحذف الكامل حتى 30 يومًا من جميع أنظمتنا.</p>
                </div>
                
                <div class="modal-section">
                    <h3>تأكيد الحذف</h3>
                    <p>إذا كنت تريد المضي قدماً في عملية الحذف، يرجى كتابة "<strong>أؤكد الحذف</strong>" في المربع أدناه:</p>
                    
                    <div class="form-group">
                        <input type="text" class="form-control" id="confirm-delete-text" placeholder="اكتب 'أؤكد الحذف' هنا">
                    </div>
                    
                    <div class="checkbox-group">
                        <label for="understand-delete">أفهم أن هذه العملية نهائية ولا يمكن التراجع عنها</label>
                        <input type="checkbox" id="understand-delete">
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal('data-delete')">إلغاء</button>
                    <button class="btn btn-danger" onclick="confirmDeleteData()" id="delete-btn" disabled>حذف البيانات</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Group -->
    <div id="group-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>إعدادات المجموعة</h2>
                <button class="close-modal" onclick="closeModal('group')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <div class="modal-section">
                    <h3>إدارة المجموعة</h3>
                    <p>من هنا يمكنك إدارة إعدادات المجموعة والأعضاء:</p>
                    
                    <div class="form-group">
                        <label for="group-name">اسم المجموعة</label>
                        <input type="text" class="form-control" id="group-name" value="مجموعة العمل الرئيسية">
                    </div>
                    
                    <div class="form-group">
                        <label for="group-description">وصف المجموعة</label>
                        <textarea class="form-control" id="group-description" rows="3">مجموعة للعمل على مشروع تطوير النظام الجديد</textarea>
                    </div>
                </div>
                
                <div class="modal-section">
                    <h3>صلاحيات الأعضاء</h3>
                    
                    <div class="checkbox-group">
                        <label for="allow-invite">السماح للأعضاء بدعوة آخرين</label>
                        <input type="checkbox" id="allow-invite" checked>
                    </div>
                    
                    <div class="checkbox-group">
                        <label for="allow-post">السماح للأعضاء بنشر محتوى</label>
                        <input type="checkbox" id="allow-post" checked>
                    </div>
                    
                    <div class="checkbox-group">
                        <label for="approve-new">الموافقة على الأعضاء الجدد يدوياً</label>
                        <input type="checkbox" id="approve-new">
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal('group')">إلغاء</button>
                    <button class="btn btn-primary" onclick="saveGroupSettings()">حفظ التغييرات</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Modal for Telegram -->
    <div id="telegram-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fab fa-telegram"></i> ربط حساب Telegram</h2>
                <button class="close-modal" onclick="closeModal('telegram')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <div class="modal-section">
                    <h3>إعدادات ربط Telegram</h3>
                    <div style="background-color: #e0f2fe; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-right: 4px solid #0ea5e9;">
                        <p><strong>كيفية الحصول على Token:</strong></p>
                        <ol style="padding-right: 20px;">
                            <li style="margin-bottom: 8px;">ابحث عن <strong>@BotFather</strong> في Telegram</li>
                            <li style="margin-bottom: 8px;">أرسل الأمر <code>/newbot</code></li>
                            <li style="margin-bottom: 8px;">اختر اسمًا للبوت</li>
                            <li style="margin-bottom: 8px;">انسخ الـ Token الذي سيعطيه لك BotFather</li>
                        </ol>
                    </div>
                    
                    <div class="form-group">
                        <label for="telegram-token">Token البوت <span style="color: #dc2626;">*</span></label>
                        <input type="password" class="form-control" id="telegram-token" placeholder="أدخل token البوت هنا">
                        <small style="color: #6b7280; display: block; margin-top: 5px;">مثال: 1234567890:ABCdefGHIjklMnOpQRstUVwxyz</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="bot-name">اسم البوت (اختياري)</label>
                        <input type="text" class="form-control" id="bot-name" placeholder="اسم البوت">
                    </div>
                    
                    <div class="form-group">
                        <label for="chat-id">Chat ID</label>
                        <div class="token-display" id="chat-id-display">لم يتم التعرف بعد</div>
                        <button type="button" class="test-btn" onclick="detectChatId()">
                            <i class="fas fa-search"></i> التعرف التلقائي على Chat ID
                        </button>
                        <small style="color: #6b7280; display: block; margin-top: 5px;">
                            Chat ID سيتم تعبئته تلقائيًا بعد إدخال Token الصحيح
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <label for="enable-notifications">تفعيل الإشعارات</label>
                            <input type="checkbox" id="enable-notifications" checked>
                        </div>
                        
                        <div class="checkbox-group">
                            <label for="enable-backups">تفعيل النسخ الاحتياطي عبر Telegram</label>
                            <input type="checkbox" id="enable-backups">
                        </div>
                    </div>
                    
                    <div class="form-group" id="connection-status" style="display: none;">
                        <div style="padding: 10px; border-radius: 6px; background-color: #f3f4f6; text-align: center;">
                            <span id="status-icon"></span>
                            <span id="status-text"></span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal('telegram')">إلغاء</button>
                    <button class="btn btn-primary" onclick="saveTelegramSettings()" id="save-telegram-btn">
                        <i class="fas fa-save"></i> حفظ الإعدادات
                    </button>
                    <button class="btn" onclick="testTelegramConnection()" id="test-telegram-btn" style="background-color: #0ea5e9; color: white;">
                        <i class="fas fa-paper-plane"></i> اختبار الاتصال
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // وظيفة لفتح النافذة المنبثقة
        function openModal(modalType) {
            const modal = document.getElementById(`${modalType}-modal`);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        // وظيفة لإغلاق النافذة المنبثقة
        function closeModal(modalType) {
            const modal = document.getElementById(`${modalType}-modal`);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // إغلاق النافذة المنبثقة عند النقر خارجها
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // وظائف لحفظ الإعدادات
        function saveAppearanceSettings() {
            const theme = document.querySelector('input[name="theme"]:checked').value;
            const fontSize = document.getElementById('font-size').value;
            
            alert(`تم حفظ إعدادات المظهر:\nالوضع: ${theme}\nحجم الخط: ${fontSize}`);
            closeModal('appearance');
        }

        function savePrivacySettings() {
            const privateAccount = document.getElementById('private-account').checked;
            const twoFactor = document.getElementById('two-factor').checked;
            
            alert(`تم حفظ إعدادات الخصوصية:\nالحساب الخاص: ${privateAccount ? 'نعم' : 'لا'}\nالمصادقة الثنائية: ${twoFactor ? 'مفعلة' : 'غير مفعلة'}`);
            closeModal('privacy');
        }

        function saveGroupSettings() {
            const groupName = document.getElementById('group-name').value;
            const allowInvite = document.getElementById('allow-invite').checked;
            
            alert(`تم حفظ إعدادات المجموعة:\nاسم المجموعة: ${groupName}\nالسماح بالدعوة: ${allowInvite ? 'نعم' : 'لا'}`);
            closeModal('group');
        }

        // التحقق من تأكيد حذف البيانات
        document.getElementById('confirm-delete-text').addEventListener('input', function() {
            const confirmText = this.value;
            const understandCheckbox = document.getElementById('understand-delete').checked;
            const deleteBtn = document.getElementById('delete-btn');
            
            if (confirmText === 'أؤكد الحذف' && understandCheckbox) {
                deleteBtn.disabled = false;
            } else {
                deleteBtn.disabled = true;
            }
        });

        document.getElementById('understand-delete').addEventListener('change', function() {
            const confirmText = document.getElementById('confirm-delete-text').value;
            const deleteBtn = document.getElementById('delete-btn');
            
            if (confirmText === 'أؤكد الحذف' && this.checked) {
                deleteBtn.disabled = false;
            } else {
                deleteBtn.disabled = true;
            }
        });

        function confirmDeleteData() {
            if (confirm('هل أنت متأكد من حذف جميع البيانات؟ لا يمكن التراجع عن هذه العملية.')) {
                alert('تم بدء عملية حذف البيانات. ستتلقى تأكيداً بالبريد الإلكتروني عند اكتمال العملية.');
                closeModal('data-delete');
                
                // إعادة تعيين الحقول
                document.getElementById('confirm-delete-text').value = '';
                document.getElementById('understand-delete').checked = false;
                document.getElementById('delete-btn').disabled = true;
            }
        }

        // إضافة تأثير عند التمرير
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if (window.scrollY > 50) {
                header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.boxShadow = 'none';
            }
        });




        // إضافة هذه الوظائف في قسم JavaScript

        // فتح نافذة شرح ميزة منع المجموعات
        document.getElementById('block-groups-item').addEventListener('click', function(e) {
            if (!e.target.closest('.toggle-switch')) {
                openModal('block-groups');
            }
        });

        // تحديث حالة زر التأكيد
        function updateBlockToggleState() {
            const confirmCheckbox = document.getElementById('confirm-block-groups');
            const confirmBtn = document.getElementById('confirm-block-btn');
            confirmBtn.disabled = !confirmCheckbox.checked;
        }

        // تفعيل/تعطيل ميزة منع المجموعات
        function toggleBlockGroups(isChecked) {
            if (isChecked) {
                openModal('block-groups');
                // إعادة تعطيل التبديل حتى يتم التأكيد
                document.getElementById('block-all-groups-toggle').checked = false;
            } else {
                // تعطيل الميزة مباشرة
                disableBlockGroups();
            }
        }

        // تأكيد تفعيل ميزة منع المجموعات
        function confirmBlockGroups() {
            const confirmCheckbox = document.getElementById('confirm-block-groups');
            
            if (confirmCheckbox.checked) {
                enableBlockGroups();
                closeModal('block-groups');
                
                // إعادة تعيين خانة التأكيد
                confirmCheckbox.checked = false;
                updateBlockToggleState();
            }
        }

        // تفعيل ميزة منع المجموعات (AJAX)
        function enableBlockGroups() {
            // تحديث واجهة المستخدم
            document.getElementById('block-all-groups-toggle').checked = true;
            
            // إضافة مؤشر مرئي على العنصر
            const item = document.getElementById('block-groups-item');
            item.style.backgroundColor = 'rgba(220, 53, 69, 0.05)';
            item.style.borderRight = '4px solid #dc3545';
            
            // إرسال طلب AJAX لحفظ الإعداد
            fetch('settings/save_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    setting: 'block_all_groups',
                    value: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('تم تفعيل ميزة منع الانضمام للمجموعات بنجاح', 'success');
                } else {
                    showNotification('حدث خطأ في حفظ الإعدادات', 'error');
                    // التراجع عن التغيير في واجهة المستخدم
                    document.getElementById('block-all-groups-toggle').checked = false;
                    item.style.backgroundColor = '';
                    item.style.borderRight = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ في الاتصال بالخادم', 'error');
                document.getElementById('block-all-groups-toggle').checked = false;
                item.style.backgroundColor = '';
                item.style.borderRight = '';
            });
        }

        // تعطيل ميزة منع المجموعات (AJAX)
        function disableBlockGroups() {
            // تحديث واجهة المستخدم
            document.getElementById('block-all-groups-toggle').checked = false;
            
            // إزالة المؤشر المرئي
            const item = document.getElementById('block-groups-item');
            item.style.backgroundColor = '';
            item.style.borderRight = '';
            
            // إرسال طلب AJAX لحفظ الإعداد
            fetch('settings/save_settings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    setting: 'block_all_groups',
                    value: 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('تم تعطيل ميزة منع الانضمام للمجموعات', 'success');
                } else {
                    showNotification('حدث خطأ في حفظ الإعدادات', 'error');
                    // إعادة التفعيل في واجهة المستخدم
                    document.getElementById('block-all-groups-toggle').checked = true;
                    item.style.backgroundColor = 'rgba(220, 53, 69, 0.05)';
                    item.style.borderRight = '4px solid #dc3545';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ في الاتصال بالخادم', 'error');
                document.getElementById('block-all-groups-toggle').checked = true;
                item.style.backgroundColor = 'rgba(220, 53, 69, 0.05)';
                item.style.borderRight = '4px solid #dc3545';
            });
        }

        // تحميل إعدادات المستخدم عند فتح الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            loadUserSettings();
        });

        // تحميل إعدادات المستخدم
        function loadUserSettings() {
            fetch('settings/get_settings.php')
            .then(response => response.json())
            .then(data => {
                if (data.block_all_groups === 1) {
                    document.getElementById('block-all-groups-toggle').checked = true;
                    const item = document.getElementById('block-groups-item');
                    item.style.backgroundColor = 'rgba(220, 53, 69, 0.05)';
                    item.style.borderRight = '4px solid #dc3545';
                }
            })
            .catch(error => {
                console.error('Error loading settings:', error);
            });
        }

        // دالة لعرض الإشعارات
        function showNotification(message, type) {
            // إنصراف إشعار مؤقت
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                left: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: bold;
                z-index: 9999;
                animation: slideIn 0.3s ease;
            `;
            
            if (type === 'success') {
                notification.style.backgroundColor = '#28a745';
            } else if (type === 'error') {
                notification.style.backgroundColor = '#dc3545';
            } else {
                notification.style.backgroundColor = '#17a2b8';
            }
            
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // إضافة أنيميشن للإشعارات
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(-100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(-100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);




                // Telegram Settings Functions
        let telegramSettings = {
            token: '',
            botName: '',
            chatId: '',
            enabled: false
        };

        // تحميل إعدادات Telegram عند فتح الصفحة
        async function loadTelegramSettings() {
            try {
                const response = await fetch('settings/get_telegram_settings.php');
                const data = await response.json();
                
                if (data.success && data.settings) {
                    telegramSettings = data.settings;
                    
                    // تحديث واجهة المستخدم إذا كان Modal مفتوحًا
                    if (document.getElementById('telegram-modal').style.display === 'flex') {
                        updateTelegramModal();
                    }
                    
                    // تحديث حالة الربط في القائمة الرئيسية
                    updateTelegramStatus();
                }
            } catch (error) {
                console.error('Error loading Telegram settings:', error);
            }
        }

        // تحديث واجهة Telegram Modal
        function updateTelegramModal() {
            document.getElementById('telegram-token').value = telegramSettings.token || '';
            document.getElementById('bot-name').value = telegramSettings.botName || '';
            
            if (telegramSettings.chatId) {
                document.getElementById('chat-id-display').textContent = telegramSettings.chatId;
            }
            
            document.getElementById('enable-notifications').checked = telegramSettings.enableNotifications || true;
            document.getElementById('enable-backups').checked = telegramSettings.enableBackups || false;
            
            updateConnectionStatus();
        }

        // تحديث حالة الربط
        function updateTelegramStatus() {
            const telegramItem = document.querySelector('.setting-item[onclick="openModal(\'telegram\')"]');
            if (!telegramItem) return;
            
            let statusElement = telegramItem.querySelector('.telegram-status');
            if (!statusElement) {
                statusElement = document.createElement('span');
                statusElement.className = 'telegram-status';
                telegramItem.querySelector('.item-info').appendChild(statusElement);
            }
            
            if (telegramSettings.token && telegramSettings.chatId) {
                statusElement.textContent = '✓ متصل';
                statusElement.className = 'telegram-status status-connected';
            } else {
                statusElement.textContent = '✗ غير متصل';
                statusElement.className = 'telegram-status status-disconnected';
            }
        }

        // حفظ إعدادات Telegram
        async function saveTelegramSettings() {
            const token = document.getElementById('telegram-token').value.trim();
            const botName = document.getElementById('bot-name').value.trim();
            
            if (!token) {
                showNotification('يرجى إدخال Token البوت', 'error');
                return;
            }
            
            const saveBtn = document.getElementById('save-telegram-btn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...';
            saveBtn.disabled = true;
            
            try {
                const response = await fetch('settings/save_telegram_settings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        token: token,
                        bot_name: botName,
                        chat_id: telegramSettings.chatId || '',
                        enable_notifications: document.getElementById('enable-notifications').checked,
                        enable_backups: document.getElementById('enable-backups').checked
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    telegramSettings = data.settings;
                    showNotification('تم حفظ إعدادات Telegram بنجاح', 'success');
                    updateTelegramStatus();
                } else {
                    showNotification(data.message || 'حدث خطأ في حفظ الإعدادات', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('حدث خطأ في الاتصال بالخادم', 'error');
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }

        // التعرف التلقائي على Chat ID
        async function detectChatId() {
            const token = document.getElementById('telegram-token').value.trim();
            
            if (!token) {
                showNotification('يرجى إدخال Token البوت أولاً', 'error');
                return;
            }
            
            const detectBtn = document.querySelector('.test-btn[onclick="detectChatId()"]');
            const originalText = detectBtn.innerHTML;
            detectBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التعرف...';
            detectBtn.disabled = true;
            
            try {
                // أولاً، الحصول على معلومات البوت
                const botInfoResponse = await fetch(`https://api.telegram.org/bot${token}/getMe`);
                const botInfo = await botInfoResponse.json();
                
                if (!botInfo.ok) {
                    throw new Error('Token غير صحيح');
                }
                
                // حفظ اسم البوت تلقائيًا
                if (botInfo.result.username) {
                    document.getElementById('bot-name').value = botInfo.result.username;
                }
                
                // الحصول على آخر تحديثات البوت للعثور على Chat ID
                const updatesResponse = await fetch(`https://api.telegram.org/bot${token}/getUpdates`);
                const updates = await updatesResponse.json();
                
                if (updates.ok && updates.result.length > 0) {
                    // أخذ Chat ID من أول رسالة
                    const chatId = updates.result[0].message.chat.id;
                    telegramSettings.chatId = chatId;
                    telegramSettings.token = token;
                    
                    document.getElementById('chat-id-display').textContent = chatId;
                    showNotification(`تم التعرف على Chat ID: ${chatId}`, 'success');
                    updateConnectionStatus(true, 'تم الاتصال بنجاح!');
                } else {
                    // إذا لم تكن هناك رسائل، نطلب من المستخدم إرسال رسالة
                    showNotification('أرسل رسالة إلى البوت ثم اضغط على الزر مرة أخرى', 'info');
                    document.getElementById('chat-id-display').textContent = 'يرجى إرسال رسالة إلى البوت أولاً';
                }
            } catch (error) {
                console.error('Error detecting Chat ID:', error);
                showNotification('فشل التعرف على Chat ID. تأكد من صحة Token', 'error');
                updateConnectionStatus(false, 'فشل الاتصال');
            } finally {
                detectBtn.innerHTML = originalText;
                detectBtn.disabled = false;
            }
        }

        // اختبار الاتصال وإرسال رسالة
        async function testTelegramConnection() {
            const token = telegramSettings.token || document.getElementById('telegram-token').value.trim();
            const chatId = telegramSettings.chatId;
            
            if (!token) {
                showNotification('يرجى إدخال Token البوت أولاً', 'error');
                return;
            }
            
            if (!chatId) {
                showNotification('يرجى التعرف على Chat ID أولاً', 'error');
                return;
            }
            
            const testBtn = document.getElementById('test-telegram-btn');
            const originalText = testBtn.innerHTML;
            testBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الاختبار...';
            testBtn.disabled = true;
            
            try {
                // إرسال رسالة اختبارية
                const message = encodeURIComponent('✅ تم اختبار الاتصال بنجاح من تطبيق مرآة المؤمن!');
                const response = await fetch(`https://api.telegram.org/bot${token}/sendMessage?chat_id=${chatId}&text=${message}`);
                const result = await response.json();
                
                if (result.ok) {
                    showNotification('تم إرسال رسالة الاختبار بنجاح!', 'success');
                    updateConnectionStatus(true, 'تم إرسال الرسالة بنجاح');
                    
                    // حفظ الإعدادات تلقائيًا بعد الاختبار الناجح
                    if (!telegramSettings.token || telegramSettings.token !== token) {
                        await saveTelegramSettings();
                    }
                } else {
                    throw new Error(result.description || 'فشل إرسال الرسالة');
                }
            } catch (error) {
                console.error('Error testing connection:', error);
                showNotification(`فشل إرسال الرسالة: ${error.message}`, 'error');
                updateConnectionStatus(false, 'فشل إرسال الرسالة');
            } finally {
                testBtn.innerHTML = originalText;
                testBtn.disabled = false;
            }
        }

        // تحديث حالة الاتصال
        function updateConnectionStatus(success = null, message = '') {
            const statusDiv = document.getElementById('connection-status');
            const statusIcon = document.getElementById('status-icon');
            const statusText = document.getElementById('status-text');
            
            statusDiv.style.display = 'block';
            
            if (success === true) {
                statusDiv.style.backgroundColor = '#05966920';
                statusIcon.innerHTML = '<i class="fas fa-check-circle" style="color: #059669; margin-left: 5px;"></i>';
                statusText.textContent = message || 'الاتصال ناجح';
                statusText.style.color = '#059669';
            } else if (success === false) {
                statusDiv.style.backgroundColor = '#dc262620';
                statusIcon.innerHTML = '<i class="fas fa-times-circle" style="color: #dc2626; margin-left: 5px;"></i>';
                statusText.textContent = message || 'فشل الاتصال';
                statusText.style.color = '#dc2626';
            } else {
                statusDiv.style.backgroundColor = '#f3f4f6';
                statusIcon.innerHTML = '<i class="fas fa-info-circle" style="color: #6b7280; margin-left: 5px;"></i>';
                statusText.textContent = 'لم يتم اختبار الاتصال بعد';
                statusText.style.color = '#6b7280';
            }
        }

        // تحديث استدعاء loadUserSettings ليشمل Telegram
        function loadUserSettings() {
            loadTelegramSettings();
            
            // الكود القديم...
            fetch('settings/get_settings.php')
            .then(response => response.json())
            .then(data => {
                if (data.block_all_groups === 1) {
                    document.getElementById('block-all-groups-toggle').checked = true;
                    const item = document.getElementById('block-groups-item');
                    item.style.backgroundColor = 'rgba(220, 53, 69, 0.05)';
                    item.style.borderRight = '4px solid #dc3545';
                }
                
                // تحميل إعدادات Telegram أيضًا
                if (data.telegram_settings) {
                    telegramSettings = JSON.parse(data.telegram_settings);
                    updateTelegramStatus();
                }
            })
            .catch(error => {
                console.error('Error loading settings:', error);
            });
        }

        // تحديث openModal لتحميل إعدادات Telegram عند فتح النافذة
        function openModal(modalType) {
            const modal = document.getElementById(`${modalType}-modal`);
            if (modal) {
                if (modalType === 'telegram') {
                    updateTelegramModal();
                }
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
    </script>
</body>
</html>