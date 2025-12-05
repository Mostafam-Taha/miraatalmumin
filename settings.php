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
    </script>
</body>
</html>