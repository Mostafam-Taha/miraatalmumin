document.addEventListener('DOMContentLoaded', function() {
    // ========== تعريف المتغيرات العالمية ==========
    let selectedPlan = '';
    let selectedAmount = 0;
    let extractedInfo = {};
    let originalImageData = null;
    
    // ========== عناصر DOM ==========
    const selectPlanBtns = document.querySelectorAll('.select-plan-btn');
    const paymentFormContainer = document.getElementById('paymentFormContainer');
    const receiptContainer = document.getElementById('receiptContainer');
    const successContainer = document.getElementById('successContainer');
    const selectedPlanText = document.getElementById('selectedPlanText');
    const planTypeInput = document.getElementById('planType');
    const amountInput = document.getElementById('amount');
    const nextStepBtn = document.getElementById('nextStepBtn');
    const backToPaymentBtn = document.getElementById('backToPaymentBtn');
    const uploadArea = document.getElementById('uploadArea');
    const receiptImageInput = document.getElementById('receiptImage');
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const processingStatus = document.getElementById('processingStatus');
    const extractedData = document.getElementById('extractedData');
    const confirmUploadBtn = document.getElementById('confirmUploadBtn');
    const confirmationModal = document.getElementById('confirmationModal');
    const modalCancel = document.getElementById('modalCancel');
    const modalConfirm = document.getElementById('modalConfirm');
    
    // ========== تهيئة العناصر ==========
    initializeElements();
    
    // ========== وظائف التهيئة ==========
    function initializeElements() {
        // التحقق من وجود العناصر قبل إضافة الأحداث
        if (selectPlanBtns) {
            setupPlanSelection();
        }
        
        if (nextStepBtn) {
            setupNextButton();
        }
        
        if (backToPaymentBtn) {
            setupBackButton();
        }
        
        if (uploadArea && receiptImageInput) {
            setupImageUpload();
        }
        
        if (removeImageBtn) {
            setupRemoveImage();
        }
        
        if (confirmUploadBtn) {
            setupConfirmButton();
        }
        
        if (modalCancel && modalConfirm) {
            setupModalButtons();
        }
    }
    
    // ========== اختيار الخطة ==========
    function setupPlanSelection() {
        selectPlanBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                selectedPlan = this.dataset.plan;
                selectedAmount = this.dataset.amount;
                
                // تحديث النص المعروض
                if (selectedPlanText) {
                    selectedPlanText.textContent = `الخطة: ${selectedPlan === 'monthly' ? 'الشهرية' : 'السنوية'} - ${selectedAmount} جنية`;
                }
                
                // تحديث حقول النموذج المخفية
                if (planTypeInput) planTypeInput.value = selectedPlan;
                if (amountInput) amountInput.value = selectedAmount;
                
                // إظهار نموذج الدفع
                if (paymentFormContainer) {
                    paymentFormContainer.style.display = 'block';
                }
                
                // إخفاء حاوية الخطط
                const plansContainer = document.querySelector('.plans-container');
                if (plansContainer) {
                    plansContainer.style.display = 'none';
                }
                
                // التمرير إلى الأعلى
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    }
    
    // ========== زر التالي ==========
    function setupNextButton() {
        nextStepBtn.addEventListener('click', function() {
            const phone = document.getElementById('phone');
            
            if (!phone || !phone.value) {
                alert('يرجى إدخال رقم الهاتف');
                return;
            }
            
            // الانتقال إلى خطوة رفع الإيصال
            if (paymentFormContainer) {
                paymentFormContainer.style.display = 'none';
            }
            
            if (receiptContainer) {
                receiptContainer.style.display = 'block';
            }
        });
    }
    
    // ========== زر العودة ==========
    function setupBackButton() {
        backToPaymentBtn.addEventListener('click', function() {
            if (receiptContainer) {
                receiptContainer.style.display = 'none';
            }
            
            if (paymentFormContainer) {
                paymentFormContainer.style.display = 'block';
            }
        });
    }
    
    // ========== رفع الصور ==========
    function setupImageUpload() {
        // النقر على منطقة الرفع
        uploadArea.addEventListener('click', function() {
            receiptImageInput.click();
        });
        
        // تغيير ملف الصورة
        receiptImageInput.addEventListener('change', handleImageUpload);
        
        // سحب وإفلات
        setupDragAndDrop();
    }
    
    function handleImageUpload(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            
            // التحقق من نوع الملف
            if (!file.type.match('image.*')) {
                alert('يرجى اختيار ملف صورة فقط');
                return;
            }
            
            // التحقق من حجم الملف (5MB كحد أقصى)
            if (file.size > 5 * 1024 * 1024) {
                alert('حجم الملف كبير جدًا. الحد الأقصى هو 5 ميجابايت');
                return;
            }
            
            // حفظ البيانات الأصلية للصورة
            const reader = new FileReader();
            reader.onload = function(event) {
                originalImageData = event.target.result;
                
                // عرض معاينة الصورة بعد القص
                cropAndPreviewImage(originalImageData);
            };
            reader.readAsDataURL(file);
        }
    }
    
    function setupDragAndDrop() {
        // سحب فوق المنطقة
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#047857';
            this.style.background = 'rgba(5, 150, 105, 0.05)';
        });
        
        // مغادرة المنطقة
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#059669';
            this.style.background = '';
        });
        
        // إفلات الملف
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#059669';
            this.style.background = '';
            
            if (e.dataTransfer.files.length > 0) {
                receiptImageInput.files = e.dataTransfer.files;
                const changeEvent = new Event('change');
                receiptImageInput.dispatchEvent(changeEvent);
            }
        });
    }
    
    // ========== قص الصورة ==========
    function cropAndPreviewImage(imageData) {
        const img = new Image();
        img.onload = function() {
            // إنشاء canvas لقص الصورة
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // حساب الأبعاد الجديدة بعد القص
            const cropTop = 20;
            const cropBottom = 20;
            
            // التحقق من أن الصورة كبيرة بما يكفي للقص
            if (img.height <= (cropTop + cropBottom + 100)) {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
            } else {
                canvas.width = img.width;
                canvas.height = img.height - cropTop - cropBottom;
                
                ctx.drawImage(
                    img, 
                    0, cropTop,
                    img.width, img.height - cropTop - cropBottom,
                    0, 0,
                    canvas.width, canvas.height
                );
            }
            
            // عرض الصورة المقطوعة
            if (imagePreview) {
                imagePreview.src = canvas.toDataURL('image/jpeg', 0.9);
            }
            
            if (previewContainer) {
                previewContainer.style.display = 'block';
            }
            
            if (uploadArea) {
                uploadArea.style.display = 'none';
            }
            
            // معالجة الصورة المقطوعة
            canvas.toBlob(function(blob) {
                processImage(blob);
            }, 'image/jpeg', 0.9);
        };
        img.src = imageData;
    }
    
    // ========== إزالة الصورة ==========
    function setupRemoveImage() {
        removeImageBtn.addEventListener('click', function() {
            if (receiptImageInput) {
                receiptImageInput.value = '';
            }
            
            if (previewContainer) {
                previewContainer.style.display = 'none';
            }
            
            if (uploadArea) {
                uploadArea.style.display = 'block';
            }
            
            if (extractedData) {
                extractedData.style.display = 'none';
            }
            
            if (processingStatus) {
                processingStatus.style.display = 'block';
            }
            
            originalImageData = null;
        });
    }
    
    // ========== معالجة الصورة ==========
    async function processImage(blob) {
        try {
            // إظهار حالة المعالجة
            if (processingStatus) {
                processingStatus.style.display = 'block';
            }
            
            if (extractedData) {
                extractedData.style.display = 'none';
            }
            
            // تحديث شريط التقدم
            updateProgressBar(0, 'جاري بدء المعالجة...');
            
            // استخدام Tesseract.js
            const result = await Tesseract.recognize(
                blob,
                'ara+eng',
                {
                    logger: function(m) {
                        console.log('تقدم المعالجة:', m);
                        
                        if (m.status === 'recognizing text') {
                            const progress = Math.round((m.progress || 0) * 100);
                            
                            if (progress < 30) {
                                updateProgressBar(progress, `${progress}% - جاري تحليل الصورة`);
                            } else if (progress < 70) {
                                updateProgressBar(progress, `${progress}% - جاري التعرف على النص`);
                            } else {
                                updateProgressBar(progress, `${progress}% - جاري معالجة البيانات`);
                            }
                        }
                    }
                }
            );
            
            console.log('النص المستخرج:', result.data.text);
            
            // تحليل النص المستخرج
            extractedInfo = extractReceiptInfo(result.data.text);
            console.log('البيانات المستخرجة:', extractedInfo);
            
            // إكمال شريط التقدم
            updateProgressBar(100, '100% - اكتملت المعالجة');
            
            // إخفاء حالة المعالجة وإظهار البيانات
            setTimeout(() => {
                if (processingStatus) {
                    processingStatus.style.display = 'none';
                }
                
                displayExtractedData(extractedInfo);
            }, 1000);
            
        } catch (error) {
            console.error('Error processing image:', error);
            
            // عرض رسالة خطأ مع خيار الإدخال اليدوي
            showProcessingError();
        }
    }
    
    function updateProgressBar(progress, message) {
        const qualityFill = document.getElementById('qualityFill');
        const progressText = document.getElementById('progressText');
        
        if (qualityFill) {
            qualityFill.style.width = `${progress}%`;
        }
        
        if (progressText) {
            progressText.textContent = message;
        }
    }
    
    function showProcessingError() {
        if (!processingStatus) return;
        
        processingStatus.innerHTML = `
            <div style="text-align: center; padding: 30px;">
                <div style="color: #f87171; font-size: 3rem; margin-bottom: 15px;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4 style="color: #f87171; margin-bottom: 10px;">حدث خطأ في معالجة الصورة</h4>
                <p style="color: #94a3b8; margin-bottom: 20px;">
                    يمكنك المتابعة وملء البيانات يدويًا
                </p>
                <button id="manualEntryBtn" style="
                    background: #059669;
                    color: white;
                    border: none;
                    padding: 12px 25px;
                    border-radius: 8px;
                    font-size: 1rem;
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                ">
                    <i class="fas fa-keyboard"></i> إدخال البيانات يدويًا
                </button>
            </div>
        `;
        
        // إضافة حدث لزر الإدخال اليدوي
        setTimeout(() => {
            const manualBtn = document.getElementById('manualEntryBtn');
            if (manualBtn) {
                manualBtn.addEventListener('click', showManualDataEntry);
            }
        }, 100);
    }
    
    // ========== استخراج البيانات من النص ==========
    function extractReceiptInfo(text) {
        const info = {
            amount: '',
            date: '',
            reference: '',
            sender: '',
            transactionType: ''
        };
        
        if (!text || text.trim() === '') {
            return info;
        }
        
        // تنظيف النص
        const cleanText = text
            .replace(/[©¥~©vW&©]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        
        console.log('النص بعد التنظيف:', cleanText);
        
        // استخراج المبلغ
        info.amount = extractAmountFromText(cleanText);
        
        // استخراج التاريخ
        info.date = extractDateFromText(cleanText);
        
        // استخراج رقم المرجع
        info.reference = extractReferenceFromText(cleanText);
        
        // استخراج اسم المرسل
        info.sender = extractSenderFromText(cleanText);
        
        // استخراج نوع العملية
        info.transactionType = extractTransactionTypeFromText(cleanText);
        
        return info;
    }
    
    function extractAmountFromText(text) {
        // البحث عن المبالغ
        const amountPatterns = [
            /(\d+)\s*\.\s*(\d+)/,              // 50 .59
            /(\d+[.,]\d+)/,                    // 50.59 أو 50,59
            /\b(\d{2,3}\.?\d{0,2})\b/,         // أرقام من 2-3 أرقام مع أو بدون كسور
            /(مبلغ|قيمة|المبلغ)\s*[:]?\s*(\d+[.,]?\d*)/i
        ];
        
        for (const pattern of amountPatterns) {
            const match = text.match(pattern);
            if (match) {
                let amount = '';
                
                if (match[1] && match[2] && !match[1].includes('.') && !match[1].includes(',')) {
                    amount = `${match[1]}.${match[2]}`;
                } else if (match[1]) {
                    amount = match[1];
                } else if (match[2]) {
                    amount = match[2];
                }
                
                // تنظيف المبلغ
                amount = amount.replace(',', '.');
                
                // التحقق من أن المبلغ منطقي
                const numericAmount = parseFloat(amount);
                if (!isNaN(numericAmount) && numericAmount >= 1 && numericAmount <= 10000) {
                    return amount;
                }
            }
        }
        
        return '';
    }
    
    function extractDateFromText(text) {
        // البحث عن التواريخ
        const datePatterns = [
            /(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}/i, // Nov 2025
            /\d{1,2}:\d{2}\s*(AM|PM)/i,                                         // 01:20 AM
            /\d{4}[-/]\d{1,2}[-/]\d{1,2}/,                                      // 2025-11-22
            /\d{1,2}[-/]\d{1,2}[-/]\d{4}/,                                      // 22-11-2025
            /\d{1,2}\s+(يناير|فبراير|مارس|أبريل|مايو|يونيو|يوليو|أغسطس|سبتمبر|أكتوبر|نوفمبر|ديسمبر)\s+\d{4}/i
        ];
        
        for (const pattern of datePatterns) {
            const match = text.match(pattern);
            if (match) {
                return match[0];
            }
        }
        
        return '';
    }
    
    function extractReferenceFromText(text) {
        // البحث عن أرقام المراجع
        const refPatterns = [
            /\b(\d{10,})\b/, // أرقام من 10 أرقام أو أكثر
            /(رقم|كود|رقم العمليه|رقم العملية)\s*[:]?\s*(\d+)/i,
            /(Reference|Ref|Transaction)\s*[:]?\s*(\d+)/i
        ];
        
        for (const pattern of refPatterns) {
            const match = text.match(pattern);
            if (match) {
                const ref = match[1] || match[2];
                if (ref && !ref.startsWith('01')) { // استبعاد أرقام الهواتف المصرية
                    return ref;
                }
            }
        }
        
        return '';
    }
    
    function extractSenderFromText(text) {
        // البحث عن الأسماء
        const namePatterns = [
            /\b([A-Z]{2,}(?:\s+[A-Z]{2,}){1,3})\b/, // MOSTAFA MOHAMED TAHA
            /([\u0621-\u064A]{2,}(?:\s+[\u0621-\u064A]{2,}){1,3})/, // أسماء عربية
            /(من|المرسل|اسم المرسل)\s*[:]?\s*([^\n\r\d]{3,})/i
        ];
        
        for (const pattern of namePatterns) {
            const match = text.match(pattern);
            if (match) {
                let sender = match[1] || match[2] || match[0];
                
                // تنظيف الاسم
                sender = sender
                    .replace(/[^A-Za-z\u0621-\u064A\s]/g, '')
                    .trim();
                
                // تحويل الإنجليزية الكبيرة إلى حالة عادية
                if (/^[A-Z\s]+$/.test(sender)) {
                    sender = sender.toLowerCase()
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                }
                
                if (sender.length >= 3) {
                    return sender;
                }
            }
        }
        
        return '';
    }
    
    function extractTransactionTypeFromText(text) {
        // البحث عن نوع العملية
        const typePatterns = [
            /(ارسال نقود|إرسال نقود|تحويل|إرسال أموال)/i,
            /(نفقات المعيشة|مصاريف|فواتير)/i,
            /(شراء|بيع|دفع|سحب|إيداع)/i
        ];
        
        for (const pattern of typePatterns) {
            const match = text.match(pattern);
            if (match) {
                return match[0];
            }
        }
        
        return '';
    }
    
    // ========== عرض البيانات المستخرجة ==========
    function displayExtractedData(info) {
        // التأكد من وجود عنصر extractedData
        if (!extractedData) {
            console.error('عنصر extractedData غير موجود');
            return;
        }
        
        // إنشاء هيكل HTML للبيانات
        extractedData.innerHTML = `
            <h4><i class="fas fa-database"></i> البيانات المستخرجة:</h4>
            <div class="data-fields">
                <div class="data-field ${info.amount ? 'highlighted' : ''}">
                    <label>المبلغ:</label>
                    <span id="extractedAmount">${info.amount ? info.amount + ' جنية' : 'لم يتم التعرف'}</span>
                </div>
                <div class="data-field ${info.date ? 'highlighted' : ''}">
                    <label>التاريخ:</label>
                    <span id="extractedDate">${info.date || 'لم يتم التعرف'}</span>
                </div>
                <div class="data-field ${info.reference ? 'highlighted' : ''}">
                    <label>رقم المرجع:</label>
                    <span id="extractedReference">${info.reference || 'لم يتم التعرف'}</span>
                </div>
                <div class="data-field ${info.sender ? 'highlighted' : ''}">
                    <label>اسم المرسل:</label>
                    <span id="extractedSender">${info.sender || 'لم يتم التعرف'}</span>
                </div>
                <div class="data-field ${info.transactionType ? 'highlighted' : ''}">
                    <label>نوع العملية:</label>
                    <span id="extractedType">${info.transactionType || 'غير محدد'}</span>
                </div>
            </div>
            <div class="confidence-indicator">
                <i class="fas fa-chart-line"></i>
                <span>ثقة التعرف: <span id="confidenceLevel" style="color: #059669; font-weight: bold;">${calculateConfidence(info)}</span></span>
            </div>
            <div class="data-actions">
                <button id="editDataBtn" class="edit-data-btn">
                    <i class="fas fa-edit"></i> تعديل البيانات
                </button>
            </div>
            <button type="button" id="newConfirmUploadBtn" class="confirm-btn">
                تأكيد الرفع <i class="fas fa-check"></i>
            </button>
        `;
        
        // إظهار العنصر
        extractedData.style.display = 'block';
        
        // إضافة الأحداث للأزرار الجديدة
        setupDynamicButtons(info);
    }
    
    function calculateConfidence(info) {
        let score = 0;
        const fields = ['amount', 'date', 'reference', 'sender'];
        
        fields.forEach(field => {
            if (info[field] && info[field] !== '') {
                score++;
            }
        });
        
        const percentage = (score / fields.length) * 100;
        
        if (percentage >= 75) return 'عالية';
        if (percentage >= 50) return 'متوسطة';
        if (percentage >= 25) return 'منخفضة';
        return 'ضعيفة';
    }
    
    function setupDynamicButtons(info) {
        // زر التعديل
        const editBtn = document.getElementById('editDataBtn');
        if (editBtn) {
            editBtn.addEventListener('click', function() {
                showEditModal(info);
            });
        }
        
        // زر تأكيد الرفع الجديد
        const newConfirmBtn = document.getElementById('newConfirmUploadBtn');
        if (newConfirmBtn) {
            newConfirmBtn.addEventListener('click', function() {
                // التحقق من وجود صورة
                if (!receiptImageInput || !receiptImageInput.files[0]) {
                    alert('يرجى تحميل إيصال الدفع أولاً');
                    return;
                }
                
                // إظهار نافذة التأكيد
                if (confirmationModal) {
                    confirmationModal.style.display = 'flex';
                }
            });
        }
    }
    
    // ========== الإدخال اليدوي للبيانات ==========
    function showManualDataEntry() {
        if (!processingStatus) return;
        
        processingStatus.innerHTML = `
            <div class="manual-data-entry">
                <h4><i class="fas fa-keyboard"></i> إدخال البيانات يدويًا</h4>
                <div class="form-group">
                    <label>المبلغ (جنيه):</label>
                    <input type="text" id="manualAmount" placeholder="أدخل المبلغ">
                </div>
                <div class="form-group">
                    <label>تاريخ التحويل:</label>
                    <input type="date" id="manualDate">
                </div>
                <div class="form-group">
                    <label>رقم المرجع:</label>
                    <input type="text" id="manualReference" placeholder="رقم المرجع">
                </div>
                <div class="form-group">
                    <label>اسم المرسل:</label>
                    <input type="text" id="manualSender" placeholder="اسم المرسل">
                </div>
                <div class="form-group">
                    <label>نوع العملية:</label>
                    <input type="text" id="manualType" placeholder="نوع العملية">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button id="cancelManualBtn" class="back-btn" style="flex: 1;">
                        إلغاء
                    </button>
                    <button id="saveManualBtn" class="confirm-btn" style="flex: 1;">
                        حفظ البيانات
                    </button>
                </div>
            </div>
        `;
        
        // إضافة الأحداث للأزرار
        setTimeout(() => {
            const cancelBtn = document.getElementById('cancelManualBtn');
            const saveBtn = document.getElementById('saveManualBtn');
            
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    if (processingStatus) {
                        processingStatus.style.display = 'none';
                    }
                });
            }
            
            if (saveBtn) {
                saveBtn.addEventListener('click', function() {
                    saveManualData();
                });
            }
        }, 100);
    }
    
    function saveManualData() {
        extractedInfo = {
            amount: document.getElementById('manualAmount')?.value || '',
            date: document.getElementById('manualDate')?.value || '',
            reference: document.getElementById('manualReference')?.value || '',
            sender: document.getElementById('manualSender')?.value || '',
            transactionType: document.getElementById('manualType')?.value || ''
        };
        
        // إخفاء نموذج الإدخال اليدوي
        if (processingStatus) {
            processingStatus.style.display = 'none';
        }
        
        // عرض البيانات المدخلة
        displayExtractedData(extractedInfo);
    }
    
    // ========== نافذة تعديل البيانات ==========
    function showEditModal(info) {
        const modalHTML = `
            <div class="edit-modal" id="editModal">
                <div class="edit-modal-content">
                    <h3><i class="fas fa-edit"></i> تعديل البيانات</h3>
                    <div class="edit-field">
                        <label>المبلغ (جنيه):</label>
                        <input type="text" id="editAmount" value="${info.amount || ''}">
                    </div>
                    <div class="edit-field">
                        <label>التاريخ:</label>
                        <input type="text" id="editDate" value="${info.date || ''}" placeholder="YYYY-MM-DD">
                    </div>
                    <div class="edit-field">
                        <label>رقم المرجع:</label>
                        <input type="text" id="editReference" value="${info.reference || ''}">
                    </div>
                    <div class="edit-field">
                        <label>اسم المرسل:</label>
                        <input type="text" id="editSender" value="${info.sender || ''}">
                    </div>
                    <div class="edit-field">
                        <label>نوع العملية:</label>
                        <input type="text" id="editType" value="${info.transactionType || ''}">
                    </div>
                    <div class="edit-modal-actions">
                        <button class="edit-cancel" id="editCancelBtn">إلغاء</button>
                        <button class="edit-save" id="editSaveBtn">حفظ</button>
                    </div>
                </div>
            </div>
        `;
        
        // إضافة النافذة إلى الجسم
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // إضافة الأحداث للأزرار
        setTimeout(() => {
            const cancelBtn = document.getElementById('editCancelBtn');
            const saveBtn = document.getElementById('editSaveBtn');
            
            if (cancelBtn) {
                cancelBtn.addEventListener('click', closeEditModal);
            }
            
            if (saveBtn) {
                saveBtn.addEventListener('click', function() {
                    saveEditedData();
                });
            }
        }, 100);
    }
    
    function closeEditModal() {
        const modal = document.getElementById('editModal');
        if (modal) {
            modal.remove();
        }
    }
    
    function saveEditedData() {
        extractedInfo = {
            amount: document.getElementById('editAmount')?.value || '',
            date: document.getElementById('editDate')?.value || '',
            reference: document.getElementById('editReference')?.value || '',
            sender: document.getElementById('editSender')?.value || '',
            transactionType: document.getElementById('editType')?.value || ''
        };
        
        // إغلاق النافذة
        closeEditModal();
        
        // تحديث عرض البيانات
        displayExtractedData(extractedInfo);
    }
    
    // ========== تأكيد الرفع ==========
    function setupConfirmButton() {
        if (confirmUploadBtn) {
            confirmUploadBtn.addEventListener('click', function() {
                // التحقق من وجود صورة
                if (!receiptImageInput || !receiptImageInput.files[0]) {
                    alert('يرجى تحميل إيصال الدفع أولاً');
                    return;
                }
                
                // إظهار نافذة التأكيد
                if (confirmationModal) {
                    confirmationModal.style.display = 'flex';
                }
            });
        }
    }
    
    // ========== النافذة المنبثقة للتأكيد ==========
    function setupModalButtons() {
        // إلغاء
        modalCancel.addEventListener('click', function() {
            if (confirmationModal) {
                confirmationModal.style.display = 'none';
            }
        });
        
        // تأكيد
        modalConfirm.addEventListener('click', submitPaymentData);
        
        // إغلاق بالنقر خارج النافذة
        confirmationModal.addEventListener('click', function(e) {
            if (e.target === confirmationModal) {
                confirmationModal.style.display = 'none';
            }
        });
    }
    
    // ========== إرسال البيانات إلى الخادم ==========
    async function submitPaymentData() {
        if (confirmationModal) {
            confirmationModal.style.display = 'none';
        }
        
        try {
            // إعداد البيانات للإرسال
            const formData = new FormData();
            
            // إضافة بيانات النموذج
            const phone = document.getElementById('phone');
            const instaUser = document.getElementById('instaUser');
            
            if (planTypeInput) formData.append('planType', planTypeInput.value);
            if (amountInput) formData.append('amount', amountInput.value);
            if (phone) formData.append('phone', phone.value);
            if (instaUser) formData.append('instaUser', instaUser.value);
            
            // إضافة الصورة
            if (receiptImageInput && receiptImageInput.files[0]) {
                formData.append('receiptImage', receiptImageInput.files[0]);
            }
            
            // إضافة البيانات المستخرجة
            formData.append('extractedData', JSON.stringify(extractedInfo));
            
            // تحديث واجهة المستخدم
            if (confirmUploadBtn) {
                confirmUploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';
                confirmUploadBtn.disabled = true;
            }
            
            // إرسال البيانات
            const response = await fetch('pro_subscription.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // إظهار رسالة النجاح
                if (receiptContainer) {
                    receiptContainer.style.display = 'none';
                }
                
                if (successContainer) {
                    successContainer.style.display = 'block';
                    
                    // عرض تفاصيل الطلب
                    const orderIdElement = document.getElementById('orderId');
                    const orderDateElement = document.getElementById('orderDate');
                    
                    if (orderIdElement) {
                        orderIdElement.textContent = result.orderId || 'N/A';
                    }
                    
                    if (orderDateElement) {
                        orderDateElement.textContent = new Date().toLocaleDateString('ar-EG');
                    }
                }
                
                // التمرير إلى الأعلى
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
            } else {
                alert('حدث خطأ: ' + (result.message || 'يرجى المحاولة مرة أخرى'));
                resetConfirmButton();
            }
            
        } catch (error) {
            console.error('Error submitting form:', error);
            alert('حدث خطأ في الإرسال. يرجى المحاولة مرة أخرى');
            resetConfirmButton();
        }
    }
    
    function resetConfirmButton() {
        if (confirmUploadBtn) {
            confirmUploadBtn.innerHTML = 'تأكيد الرفع <i class="fas fa-check"></i>';
            confirmUploadBtn.disabled = false;
        }
        
        // إعادة تعيين الزر الجديد أيضًا
        const newConfirmBtn = document.getElementById('newConfirmUploadBtn');
        if (newConfirmBtn) {
            newConfirmBtn.innerHTML = 'تأكيد الرفع <i class="fas fa-check"></i>';
            newConfirmBtn.disabled = false;
        }
    }
    
    // ========== تحسينات إضافية ==========
    function setupAdditionalFeatures() {
        // تكبير الصورة عند النقر عليها
        if (imagePreview) {
            imagePreview.addEventListener('click', function() {
                this.classList.toggle('zoomed');
            });
        }
        
        // إضافة تأثيرات للخطوط
        const planCards = document.querySelectorAll('.plan-card');
        planCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    }
    
    // تشغيل التحسينات الإضافية
    setupAdditionalFeatures();
    
    // ========== تهيئة الحقول عند التحميل ==========
    function initializeFields() {
        // تعيين تاريخ اليوم كتاريخ افتراضي للحقول اليدوية
        const today = new Date().toISOString().split('T')[0];
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            if (!input.value) {
                input.value = today;
            }
        });
    }
    
    // تشغيل التهيئة
    initializeFields();
});