<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إرسال رسائل تيليجرام</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0088cc;
            --secondary-color: #f8f9fa;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --dark-color: #343a40;
        }
        
        * {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px 0;
            color: #333;
        }
        
        .header {
            background: linear-gradient(to right, var(--primary-color), #26a5e4);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0, 136, 204, 0.2);
            text-align: center;
        }
        
        .header h1 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            font-size: 1.2rem;
            padding: 15px 20px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 136, 204, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #0077b3;
            border-color: #0077b3;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
            transform: translateY(-2px);
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            font-weight: 500;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-radius: 10px 0 0 10px;
            border: 1px solid #ddd;
        }
        
        .message-preview {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            border-left: 4px solid var(--primary-color);
        }
        
        .telegram-icon {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-right: 10px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e9ecef;
            z-index: 1;
        }
        
        .step {
            position: relative;
            z-index: 2;
            background-color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #adb5bd;
            border: 2px solid #e9ecef;
        }
        
        .step.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .step.completed {
            background-color: var(--success-color);
            color: white;
            border-color: var(--success-color);
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .hidden {
            display: none;
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 20px 15px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- العنوان الرئيسي -->
        <div class="header">
            <h1><i class="fab fa-telegram telegram-icon"></i> إرسال رسائل تيليجرام</h1>
            <p>أدخل بيانات بوت تيليجرام الخاص بك لإرسال رسائل إلى المجموعات</p>
        </div>
        
        <!-- مؤشر الخطوات -->
        <div class="step-indicator">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
            <div class="step">4</div>
        </div>
        
        <!-- نموذج إدخال البيانات -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-cogs me-2"></i>إعدادات بوت تيليجرام
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="botToken" class="form-label">رمز الوصول للبوت (Bot Token)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="text" class="form-control" id="botToken" placeholder="أدخل الرمز السري للبوت الذي تحصلت عليه من BotFather">
                    </div>
                    <div class="form-text">مثال: 1234567890:ABCdefGhIJKlmNoPQRsTUVwxyZ</div>
                </div>
                
                <div class="mb-3">
                    <label for="botUsername" class="form-label">اسم المستخدم للبوت (Bot Username)</label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input type="text" class="form-control" id="botUsername" placeholder="اسم البوت بدون @">
                    </div>
                    <div class="form-text">مثال: MyTelegramBot</div>
                </div>
                
                <div class="mb-4">
                    <label for="chatId" class="form-label">معرف المجموعة أو المستخدم (Chat ID)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                        <input type="text" class="form-control" id="chatId" placeholder="معرف المجموعة أو المستخدم المستهدف">
                    </div>
                    <div class="form-text">يمكن الحصول على المعرف عن طريق إرسال رسالة إلى @RawDataBot في تيليجرام</div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="button" class="btn btn-primary" id="openModalBtn">
                        <i class="fas fa-paper-plane me-2"></i>إرسال رسالة
                    </button>
                </div>
            </div>
        </div>
        
        <!-- رسالة المعاينة -->
        <div class="card hidden" id="previewCard">
            <div class="card-header">
                <i class="fas fa-eye me-2"></i>معاينة الرسالة
            </div>
            <div class="card-body">
                <div id="messagePreview" class="message-preview">
                    سيتم عرض محتوى الرسالة هنا بعد كتابتها...
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-success" id="sendMessageBtn">
                        <i class="fas fa-paper-plane me-2"></i>إرسال الرسالة الآن
                    </button>
                </div>
            </div>
        </div>
        
        <!-- نتائج الإرسال -->
        <div class="card hidden" id="resultCard">
            <div class="card-header">
                <i class="fas fa-check-circle me-2"></i>نتيجة الإرسال
            </div>
            <div class="card-body">
                <div id="resultMessage" class="alert alert-info">
                    تظهر هنا نتيجة عملية الإرسال...
                </div>
            </div>
        </div>
        
        <!-- تذييل الصفحة -->
        <div class="footer">
            <p>© 2023 أداة إرسال رسائل تيليجرام | تم التطوير باستخدام HTML, CSS, JavaScript, Bootstrap و jQuery</p>
        </div>
    </div>
    
    <!-- نافذة الرسالة -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel"><i class="fas fa-edit me-2"></i>كتابة الرسالة</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="messageForm">
                        <div class="mb-3">
                            <label for="messageText" class="form-label">نص الرسالة</label>
                            <textarea class="form-control" id="messageText" rows="6" placeholder="اكتب محتوى الرسالة التي تريد إرسالها..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">تنسيق الرسالة</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="parseMode" id="plainText" value="plain" checked>
                                <label class="form-check-label" for="plainText">نص عادي</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="parseMode" id="htmlText" value="HTML">
                                <label class="form-check-label" for="htmlText">HTML</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="parseMode" id="markdownText" value="Markdown">
                                <label class="form-check-label" for="markdownText">Markdown</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">خيارات إضافية</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disableNotification">
                                <label class="form-check-label" for="disableNotification">تعطيل الإشعارات</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disableWebPreview">
                                <label class="form-check-label" for="disableWebPreview">تعطيل معاينة الروابط</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" id="previewMessageBtn">معاينة الرسالة</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Axios for HTTP requests -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        // متغيرات عامة
        let botToken = '';
        let botUsername = '';
        let chatId = '';
        
        $(document).ready(function() {
            // فتح نافذة كتابة الرسالة
            $('#openModalBtn').click(function() {
                // التحقق من البيانات المدخلة
                botToken = $('#botToken').val().trim();
                botUsername = $('#botUsername').val().trim();
                chatId = $('#chatId').val().trim();
                
                if (!botToken) {
                    showAlert('الرجاء إدخال رمز الوصول للبوت', 'danger');
                    $('#botToken').focus();
                    return;
                }
                
                if (!botUsername) {
                    showAlert('الرجاء إدخال اسم المستخدم للبوت', 'danger');
                    $('#botUsername').focus();
                    return;
                }
                
                if (!chatId) {
                    showAlert('الرجاء إدخال معرف المجموعة أو المستخدم', 'danger');
                    $('#chatId').focus();
                    return;
                }
                
                // تحديث مؤشر الخطوات
                updateStepIndicator(2);
                
                // إظهار نافذة الرسالة
                $('#messageModal').modal('show');
            });
            
            // معاينة الرسالة
            $('#previewMessageBtn').click(function() {
                const messageText = $('#messageText').val().trim();
                
                if (!messageText) {
                    showAlert('الرجاء كتابة نص الرسالة', 'danger');
                    $('#messageText').focus();
                    return;
                }
                
                // تحديث مؤشر الخطوات
                updateStepIndicator(3);
                
                // إغلاق النافذة
                $('#messageModal').modal('hide');
                
                // عرض معاينة الرسالة
                const parseMode = $('input[name="parseMode"]:checked').val();
                let previewText = messageText;
                
                // تنسيق المعاينة بناءً على نوع التنسيق
                if (parseMode === 'HTML') {
                    previewText = `<div><strong>تنسيق HTML:</strong><br>${messageText}</div>`;
                } else if (parseMode === 'Markdown') {
                    previewText = `<div><strong>تنسيق Markdown:</strong><br>${messageText}</div>`;
                }
                
                $('#messagePreview').html(previewText);
                $('#previewCard').removeClass('hidden');
                
                // تمرير البيانات إلى زر الإرسال
                $('#sendMessageBtn').data('message', {
                    text: messageText,
                    parseMode: parseMode,
                    disableNotification: $('#disableNotification').is(':checked'),
                    disableWebPreview: $('#disableWebPreview').is(':checked')
                });
            });
            
            // إرسال الرسالة
            $('#sendMessageBtn').click(function() {
                const messageData = $(this).data('message');
                
                if (!messageData) {
                    showAlert('الرجاء كتابة رسالة أولاً', 'danger');
                    return;
                }
                
                // تحديث مؤشر الخطوات
                updateStepIndicator(4);
                
                // إظهار رسالة التحميل
                $('#resultCard').removeClass('hidden');
                $('#resultMessage').removeClass('alert-info alert-success alert-danger')
                    .addClass('alert-info')
                    .html('<i class="fas fa-spinner fa-spin me-2"></i>جاري إرسال الرسالة...');
                
                // إعداد بيانات الإرسال
                const requestData = {
                    chat_id: chatId,
                    text: messageData.text,
                    parse_mode: messageData.parseMode === 'plain' ? '' : messageData.parseMode,
                    disable_notification: messageData.disableNotification,
                    disable_web_page_preview: messageData.disableWebPreview
                };
                
                // إرسال الرسالة عبر API تيليجرام
                const apiUrl = `https://api.telegram.org/bot${botToken}/sendMessage`;
                
                axios.post(apiUrl, requestData)
                    .then(function(response) {
                        if (response.data.ok) {
                            // نجاح الإرسال
                            $('#resultMessage').removeClass('alert-info alert-danger')
                                .addClass('alert-success')
                                .html(`<i class="fas fa-check-circle me-2"></i>تم إرسال الرسالة بنجاح إلى ${botUsername}!<br>
                                <small>معرف الرسالة: ${response.data.result.message_id}</small>`);
                        } else {
                            // فشل الإرسال
                            $('#resultMessage').removeClass('alert-info alert-success')
                                .addClass('alert-danger')
                                .html(`<i class="fas fa-exclamation-circle me-2"></i>فشل إرسال الرسالة: ${response.data.description}`);
                        }
                    })
                    .catch(function(error) {
                        // خطأ في الاتصال
                        let errorMessage = 'حدث خطأ أثناء إرسال الرسالة';
                        
                        if (error.response && error.response.data && error.response.data.description) {
                            errorMessage += `: ${error.response.data.description}`;
                        } else if (error.message) {
                            errorMessage += `: ${error.message}`;
                        }
                        
                        $('#resultMessage').removeClass('alert-info alert-success')
                            .addClass('alert-danger')
                            .html(`<i class="fas fa-exclamation-circle me-2"></i>${errorMessage}`);
                    });
            });
            
            // زر إعادة التعيين
            $(document).on('click', '#resetBtn', function() {
                // إعادة تعيين الحقول
                $('#botToken, #botUsername, #chatId, #messageText').val('');
                $('#plainText').prop('checked', true);
                $('#disableNotification, #disableWebPreview').prop('checked', false);
                
                // إخفاء البطاقات
                $('#previewCard, #resultCard').addClass('hidden');
                
                // إعادة مؤشر الخطوات
                updateStepIndicator(1);
                
                // إظهار رسالة نجاح
                showAlert('تم إعادة تعيين النموذج بنجاح', 'success');
            });
            
            // إضافة زر إعادة التعيين
            $('#resultCard .card-body').append(`
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary" id="resetBtn">
                        <i class="fas fa-redo me-2"></i>إرسال رسالة جديدة
                    </button>
                </div>
            `);
        });
        
        // دالة لتحديث مؤشر الخطوات
        function updateStepIndicator(step) {
            $('.step').each(function(index) {
                const stepNumber = index + 1;
                $(this).removeClass('active completed');
                
                if (stepNumber < step) {
                    $(this).addClass('completed');
                } else if (stepNumber === step) {
                    $(this).addClass('active');
                }
            });
        }
        
        // دالة لعرض التنبيهات
        function showAlert(message, type) {
            // إزالة أي تنبيه سابق
            $('.alert-dismissible').remove();
            
            // إنشاء التنبيه الجديد
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // إضافة التنبيه أعلى الصفحة
            $('.header').after(alertHtml);
        }
        
        // بيانات تجريبية لتسهيل الاختبار
        function loadTestData() {
            $('#botToken').val('6898971699:AAEJuQsk78Ye5knm7pmqTir3xN4AAdGhX58');
            $('#botUsername').val('sahatalllmbot');
            $('#chatId').val('-1003345522134');
            $('#messageText').val('مرحباً! هذه رسالة تجريبية من موقع إرسال رسائل تيليجرام.\n\nتم الإرسال بنجاح ✅');
            
            showAlert('تم تحميل بيانات تجريبية. يمكنك تعديلها قبل الإرسال.', 'info');
        }
        
        // زر لتحميل بيانات تجريبية (لأغراض التطوير فقط)
        $('#openModalBtn').after(`
            <button type="button" class="btn btn-outline-secondary me-2" id="loadTestDataBtn">
                <i class="fas fa-flask me-2"></i>تحميل بيانات تجريبية
            </button>
        `);
        
        $('#loadTestDataBtn').click(loadTestData);
    </script>
</body>
</html>