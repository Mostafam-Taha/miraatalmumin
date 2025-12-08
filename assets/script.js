// العناصر الرئيسية في الصفحة
const botUsernameInput = document.getElementById('botUsername');
const botTokenInput = document.getElementById('botToken');
const groupLinkInput = document.getElementById('groupLink');
const chatIdInput = document.getElementById('chatId');
const verifyBtn = document.getElementById('verifyBtn');
const getChatIdBtn = document.getElementById('getChatIdBtn');
const verificationResult = document.getElementById('verificationResult');

const statusDot = document.getElementById('statusDot');
const statusText = document.getElementById('statusText');
const messageText = document.getElementById('messageText');
const parseModeCheckbox = document.getElementById('parseMode');
const disablePreviewCheckbox = document.getElementById('disablePreview');
const sendBtn = document.getElementById('sendBtn');
const sendResult = document.getElementById('sendResult');

const recipientUsernameInput = document.getElementById('recipientUsername');
const personalMessageInput = document.getElementById('personalMessage');
const personalParseModeCheckbox = document.getElementById('personalParseMode');
const sendPersonalBtn = document.getElementById('sendPersonalBtn');
const personalResult = document.getElementById('personalResult');

const messageHistory = document.getElementById('messageHistory');
const clearHistoryBtn = document.getElementById('clearHistoryBtn');

const loadingModal = document.getElementById('loadingModal');
const loadingText = document.getElementById('loadingText');

// بيانات التطبيق
let appState = {
    isVerified: false,
    botToken: '',
    chatId: '',
    botUsername: '',
    groupLink: '',
    messageHistory: []
};

// عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // تحميل البيانات المحفوظة
    loadSavedData();
    
    // تحديث واجهة الحالة
    updateConnectionStatus();
    
    // تحميل سجل الرسائل
    loadMessageHistory();
    
    // عرض المستخدمين المتكررين
    displayFrequentUsers();
    
    // إضافة مستمعي الأحداث
    setupEventListeners();
    
    // عند تغيير أي حقل في قسم الإعدادات
    const setupInputs = [botUsernameInput, botTokenInput, groupLinkInput, chatIdInput];
    setupInputs.forEach(input => {
        input.addEventListener('input', () => {
            appState.isVerified = false;
            updateConnectionStatus();
            hideResult(verificationResult);
        });
    });
    
    // عند تغيير نص الرسالة
    messageText.addEventListener('input', function() {
        sendBtn.disabled = this.value.trim() === '' || !appState.isVerified;
    });
});

// إعداد مستمعي الأحداث
function setupEventListeners() {
    // زر التحقق
    verifyBtn.addEventListener('click', verifyBotData);
    
    // زر الحصول على Chat ID
    getChatIdBtn.addEventListener('click', getChatIdFromBot);
    
    // زر إرسال الرسالة إلى المجموعة
    sendBtn.addEventListener('click', sendMessageToTelegram);
    
    // زر إرسال الرسالة الشخصية
    sendPersonalBtn.addEventListener('click', sendPersonalMessage);
    
    // زر مسح السجل
    clearHistoryBtn.addEventListener('click', clearMessageHistory);
    
    // عند تغيير اسم المستخدم أو نص الرسالة الشخصية
    recipientUsernameInput.addEventListener('input', updatePersonalSendButton);
    personalMessageInput.addEventListener('input', updatePersonalSendButton);
    
    // عند تغيير حالة التحقق
    verifyBtn.addEventListener('click', function() {
        setTimeout(updatePersonalSendButton, 500);
    });
}

// تحديث زر إرسال الرسالة الشخصية
function updatePersonalSendButton() {
    const username = recipientUsernameInput.value.trim();
    const message = personalMessageInput.value.trim();
    sendPersonalBtn.disabled = !appState.isVerified || !username || !message;
}

// تحميل البيانات المحفوظة
function loadSavedData() {
    const savedState = localStorage.getItem('telegramBotState');
    if (savedState) {
        try {
            const parsed = JSON.parse(savedState);
            appState = { ...appState, ...parsed };
            
            // تعبئة الحقول
            if (appState.botUsername) botUsernameInput.value = appState.botUsername;
            if (appState.botToken) botTokenInput.value = appState.botToken;
            if (appState.groupLink) groupLinkInput.value = appState.groupLink;
            if (appState.chatId) chatIdInput.value = appState.chatId;
        } catch (e) {
            console.error('Error loading saved data:', e);
        }
    }
}

// حفظ البيانات
function saveAppState() {
    localStorage.setItem('telegramBotState', JSON.stringify({
        botUsername: appState.botUsername,
        botToken: appState.botToken,
        groupLink: appState.groupLink,
        chatId: appState.chatId,
        isVerified: appState.isVerified
    }));
}

// تحميل سجل الرسائل
function loadMessageHistory() {
    const savedHistory = localStorage.getItem('telegramMessageHistory');
    if (savedHistory) {
        try {
            appState.messageHistory = JSON.parse(savedHistory);
            displayMessageHistory();
        } catch (e) {
            console.error('Error loading message history:', e);
        }
    }
}

// حفظ سجل الرسائل
function saveMessageHistory() {
    localStorage.setItem('telegramMessageHistory', JSON.stringify(appState.messageHistory));
}

// عرض سجل الرسائل
function displayMessageHistory() {
    if (appState.messageHistory.length === 0) {
        messageHistory.innerHTML = `
            <div class="empty-history">
                <i class="fas fa-inbox"></i>
                <p>لا توجد رسائل مرسلة بعد</p>
            </div>
        `;
        return;
    }
    
    let historyHTML = '';
    appState.messageHistory.slice().reverse().forEach((msg, index) => {
        const shortMessage = msg.message.length > 80 ? msg.message.substring(0, 80) + '...' : msg.message;
        const time = new Date(msg.timestamp).toLocaleString('ar-SA');
        const originalIndex = appState.messageHistory.length - 1 - index;
        
        // تنسيق مختلف للرسائل الشخصية
        if (msg.type === 'personal') {
            historyHTML += `
                <div class="message-item personal-message">
                    <div class="message-content">
                        <div class="message-header">
                            <i class="fas fa-user" style="color: #17a2b8; margin-left: 5px;"></i>
                            <strong>رسالة شخصية إلى ${msg.recipient}</strong>
                        </div>
                        <p>${shortMessage}</p>
                        <div class="message-meta">
                            <span><i class="far fa-clock"></i> ${time}</span>
                            <span class="message-status ${msg.success ? 'status-sent' : 'status-failed'}">
                                ${msg.success ? 'تم الإرسال' : 'فشل الإرسال'}
                            </span>
                            ${msg.chatId ? `<span><i class="fas fa-id-card"></i> ${msg.chatId}</span>` : ''}
                        </div>
                        ${msg.note ? `<div class="message-note"><i class="fas fa-info-circle"></i> ${msg.note}</div>` : ''}
                    </div>
                    <div class="message-actions">
                        <button class="btn-icon retry-btn" data-index="${originalIndex}" title="إعادة الإرسال">
                            <i class="fas fa-redo-alt"></i>
                        </button>
                        <button class="btn-icon copy-btn" data-message="${msg.message}" title="نسخ الرسالة">
                            <i class="far fa-copy"></i>
                        </button>
                        ${msg.recipient ? `<button class="btn-icon use-user-btn" data-username="${msg.recipient}" title="استخدام هذا المستخدم">
                            <i class="fas fa-user-plus"></i>
                        </button>` : ''}
                    </div>
                </div>
            `;
        } else {
            // الرسائل العادية إلى المجموعة
            historyHTML += `
                <div class="message-item">
                    <div class="message-content">
                        <div class="message-header">
                            <i class="fas fa-users" style="color: var(--primary-color); margin-left: 5px;"></i>
                            <strong>رسالة إلى المجموعة</strong>
                        </div>
                        <p>${shortMessage}</p>
                        <div class="message-meta">
                            <span><i class="far fa-clock"></i> ${time}</span>
                            <span class="message-status ${msg.success ? 'status-sent' : 'status-failed'}">
                                ${msg.success ? 'تم الإرسال' : 'فشل الإرسال'}
                            </span>
                        </div>
                    </div>
                    <div class="message-actions">
                        <button class="btn-icon retry-btn" data-index="${originalIndex}" title="إعادة الإرسال">
                            <i class="fas fa-redo-alt"></i>
                        </button>
                        <button class="btn-icon copy-btn" data-message="${msg.message}" title="نسخ الرسالة">
                            <i class="far fa-copy"></i>
                        </button>
                    </div>
                </div>
            `;
        }
    });
    
    messageHistory.innerHTML = historyHTML;
    
    // إضافة مستمعي الأحداث للأزرار الجديدة
    document.querySelectorAll('.retry-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            retryMessage(index);
        });
    });
    
    // إضافة مستمعي الأحداث لأزرار النسخ
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const message = this.getAttribute('data-message');
            navigator.clipboard.writeText(message).then(() => {
                // إظهار تنبيه صغير
                const originalTitle = this.getAttribute('title');
                this.setAttribute('title', 'تم النسخ!');
                this.innerHTML = '<i class="fas fa-check"></i>';
                
                setTimeout(() => {
                    this.setAttribute('title', originalTitle);
                    this.innerHTML = '<i class="far fa-copy"></i>';
                }, 2000);
            });
        });
    });
    
    // إضافة مستمعي الأحداث لأزرار استخدام المستخدم
    document.querySelectorAll('.use-user-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const username = this.getAttribute('data-username');
            recipientUsernameInput.value = username;
            updatePersonalSendButton();
            
            // التمرير إلى قسم الرسائل الشخصية
            document.querySelector('.personal-section').scrollIntoView({ behavior: 'smooth' });
            
            // إظهار رسالة تأكيد
            showTempMessage(`تم تعيين المستخدم: ${username}`, 'success');
        });
    });
}

// مسح سجل الرسائل
function clearMessageHistory() {
    if (confirm('هل أنت متأكد من مسح سجل الرسائل؟')) {
        appState.messageHistory = [];
        saveMessageHistory();
        displayMessageHistory();
        showTempMessage('تم مسح سجل الرسائل', 'success');
    }
}

// إعادة إرسال رسالة
function retryMessage(index) {
    const messageObj = appState.messageHistory[index];
    
    if (messageObj.type === 'personal') {
        // إعادة إرسال رسالة شخصية
        recipientUsernameInput.value = messageObj.recipient;
        personalMessageInput.value = messageObj.message;
        personalParseModeCheckbox.checked = messageObj.parseMode === 'HTML';
        sendPersonalBtn.disabled = false;
        
        // التمرير إلى قسم الرسائل الشخصية
        document.querySelector('.personal-section').scrollIntoView({ behavior: 'smooth' });
        
        showTempMessage(`تم تحميل رسالة إلى ${messageObj.recipient}`, 'info');
    } else {
        // إعادة إرسال رسالة عادية
        messageText.value = messageObj.message;
        parseModeCheckbox.checked = messageObj.parseMode === 'HTML';
        sendBtn.disabled = !appState.isVerified;
        
        // التمرير إلى قسم الرسائل العادية
        document.querySelector('.message-section').scrollIntoView({ behavior: 'smooth' });
        
        showTempMessage('تم تحميل رسالة المجموعة', 'info');
    }
}

// تحديث حالة الاتصال
function updateConnectionStatus() {
    if (appState.isVerified) {
        statusDot.className = 'status-dot connected';
        statusText.textContent = `متصل - ${appState.botUsername} → ${appState.groupLink}`;
        sendBtn.disabled = messageText.value.trim() === '';
        sendPersonalBtn.disabled = recipientUsernameInput.value.trim() === '' || personalMessageInput.value.trim() === '';
    } else {
        statusDot.className = 'status-dot disconnected';
        statusText.textContent = 'غير متصل - يرجى التحقق من البيانات أولاً';
        sendBtn.disabled = true;
        sendPersonalBtn.disabled = true;
    }
}

// إظهار نتيجة العملية
function showResult(element, type, message) {
    element.className = `result-box ${type}`;
    
    let icon = '';
    if (type === 'success') {
        icon = '<i class="fas fa-check-circle"></i>';
    } else if (type === 'error') {
        icon = '<i class="fas fa-times-circle"></i>';
    } else if (type === 'warning') {
        icon = '<i class="fas fa-exclamation-triangle"></i>';
    } else if (type === 'info') {
        icon = '<i class="fas fa-info-circle"></i>';
    }
    
    element.innerHTML = `
        <div class="result-icon">${icon}</div>
        <div class="result-text">${message}</div>
    `;
    
    element.classList.remove('hidden');
}

// إخفاء نتيجة العملية
function hideResult(element) {
    element.classList.add('hidden');
}

// إظهار رسالة مؤقتة
function showTempMessage(message, type = 'info') {
    const tempMsg = document.createElement('div');
    tempMsg.className = `temp-message ${type}`;
    tempMsg.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(tempMsg);
    
    // إظهار الرسالة
    setTimeout(() => {
        tempMsg.classList.add('show');
    }, 10);
    
    // إخفاء الرسالة بعد 3 ثوان
    setTimeout(() => {
        tempMsg.classList.remove('show');
        setTimeout(() => {
            if (tempMsg.parentNode) {
                tempMsg.parentNode.removeChild(tempMsg);
            }
        }, 300);
    }, 3000);
}

// إظهار نافذة التحميل
function showLoading(text = 'جاري المعالجة...') {
    loadingText.textContent = text;
    loadingModal.classList.remove('hidden');
}

// إخفاء نافذة التحميل
function hideLoading() {
    loadingModal.classList.add('hidden');
}

// التحقق من بيانات البوت
async function verifyBotData() {
    const botToken = botTokenInput.value.trim();
    const chatId = chatIdInput.value.trim();
    
    if (!botToken) {
        showResult(verificationResult, 'error', 'الرجاء إدخال رمز البوت');
        return;
    }
    
    showLoading('جاري التحقق من بيانات البوت...');
    
    try {
        // التحقق من صحة البوت عن طريق استدعاء getMe
        const response = await fetch(`https://api.telegram.org/bot${botToken}/getMe`);
        const data = await response.json();
        
        if (data.ok) {
            appState.botUsername = data.result.username;
            appState.botToken = botToken;
            
            // إذا كان هناك Chat ID محدد، التحقق منه أيضًا
            if (chatId) {
                const chatResponse = await fetch(`https://api.telegram.org/bot${botToken}/getChat?chat_id=${chatId}`);
                const chatData = await chatResponse.json();
                
                if (chatData.ok) {
                    appState.chatId = chatId;
                    appState.groupLink = groupLinkInput.value.trim() || `https://t.me/${chatData.result.username || 'group'}`;
                    appState.isVerified = true;
                    
                    showResult(verificationResult, 'success', `✅ تم التحقق بنجاح!<br>البوت: @${appState.botUsername}<br>المجموعة: ${appState.groupLink}`);
                } else {
                    showResult(verificationResult, 'warning', `✅ البوت صالح ولكن Chat ID غير صحيح<br>البوت: @${appState.botUsername}<br>الخطأ: ${chatData.description}`);
                    appState.isVerified = false;
                }
            } else {
                appState.isVerified = true;
                showResult(verificationResult, 'success', `✅ البوت صالح!<br>اسم البوت: @${appState.botUsername}<br>الآن يمكنك الحصول على Chat ID أو إدخاله يدويًا`);
            }
            
            // حفظ البيانات
            saveAppState();
            updateConnectionStatus();
            
            showTempMessage('تم التحقق من البوت بنجاح', 'success');
        } else {
            showResult(verificationResult, 'error', `❌ رمز البوت غير صحيح<br>الخطأ: ${data.description}`);
            appState.isVerified = false;
        }
    } catch (error) {
        console.error('Verification error:', error);
        showResult(verificationResult, 'error', '❌ فشل الاتصال بالخادم. تأكد من اتصالك بالإنترنت');
        appState.isVerified = false;
    } finally {
        hideLoading();
    }
}

// الحصول على Chat ID من البوت
async function getChatIdFromBot() {
    const botToken = botTokenInput.value.trim();
    
    if (!botToken) {
        showResult(verificationResult, 'error', 'الرجاء إدخال رمز البوت أولاً');
        return;
    }
    
    showLoading('جاري الحصول على آخر التحديثات من البوت...');
    
    try {
        // الحصول على آخر التحديثات من البوت
        const response = await fetch(`https://api.telegram.org/bot${botToken}/getUpdates`);
        const data = await response.json();
        
        if (data.ok && data.result.length > 0) {
            // عرض آخر 5 محادثات
            let message = '🔍 تم العثور على المحادثات التالية:<br><br>';
            const uniqueChats = [];
            
            data.result.slice(-5).forEach((update, index) => {
                const chat = update.message?.chat || update.channel_post?.chat;
                if (chat && !uniqueChats.some(c => c.id === chat.id)) {
                    uniqueChats.push(chat);
                    const type = chat.type === 'private' ? 'خاصة' : 
                                 chat.type === 'group' ? 'مجموعة' : 
                                 chat.type === 'supergroup' ? 'مجموعة خارقة' : 
                                 chat.type === 'channel' ? 'قناة' : 'غير معروف';
                    
                    message += `${index + 1}. ${chat.title || chat.first_name} (${type})<br>`;
                    message += `   Chat ID: <code>${chat.id}</code><br>`;
                    
                    if (chat.username) {
                        message += `   الرابط: https://t.me/${chat.username}<br>`;
                    }
                    
                    // زر نسخ Chat ID
                    message += `   <button class="btn-copy" onclick="copyToClipboard('${chat.id}')" style="margin: 2px 0;">نسخ Chat ID</button>`;
                    message += `<br>`;
                }
            });
            
            message += '<br>انسخ Chat ID وألصقه في الحقل المخصص';
            showResult(verificationResult, 'success', message);
        } else {
            showResult(verificationResult, 'warning', '⚠️ لم يتم العثور على أي محادثات. أرسل رسالة إلى البوت أولاً، ثم اضغط على زر "الحصول على Chat ID" مرة أخرى');
        }
    } catch (error) {
        console.error('Error getting chat ID:', error);
        showResult(verificationResult, 'error', '❌ فشل الحصول على Chat ID. تأكد من صحة رمز البوت');
    } finally {
        hideLoading();
    }
}

// دالة مساعدة للنسخ إلى الحافظة
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showTempMessage(`تم نسخ: ${text}`, 'success');
    });
}

// إرسال رسالة إلى Telegram (المجموعة)
async function sendMessageToTelegram() {
    if (!appState.isVerified) {
        showResult(sendResult, 'error', '❌ يرجى التحقق من بيانات البوت أولاً');
        return;
    }
    
    const message = messageText.value.trim();
    if (!message) {
        showResult(sendResult, 'error', '❌ الرجاء إدخال نص الرسالة');
        return;
    }
    
    showLoading('جاري إرسال الرسالة...');
    
    const parseMode = parseModeCheckbox.checked ? 'HTML' : '';
    const disableWebPagePreview = disablePreviewCheckbox.checked;
    
    try {
        // إعداد بيانات الطلب
        const requestBody = {
            chat_id: appState.chatId,
            text: message,
            parse_mode: parseMode,
            disable_web_page_preview: disableWebPagePreview
        };
        
        // إرسال الرسالة إلى Telegram API
        const response = await fetch(`https://api.telegram.org/bot${appState.botToken}/sendMessage`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestBody)
        });
        
        const data = await response.json();
        
        if (data.ok) {
            // إضافة الرسالة إلى السجل
            const messageObj = {
                type: 'group',
                message: message,
                timestamp: new Date().toISOString(),
                success: true,
                parseMode: parseMode
            };
            
            appState.messageHistory.push(messageObj);
            if (appState.messageHistory.length > 50) {
                appState.messageHistory = appState.messageHistory.slice(-50);
            }
            
            saveMessageHistory();
            displayMessageHistory();
            
            // إظهار رسالة النجاح
            showResult(sendResult, 'success', '✅ تم إرسال الرسالة بنجاح!');
            
            // مسح حقل الرسالة
            messageText.value = '';
            sendBtn.disabled = true;
            
            showTempMessage('تم إرسال الرسالة إلى المجموعة', 'success');
        } else {
            showResult(sendResult, 'error', `❌ فشل إرسال الرسالة<br>الخطأ: ${data.description}`);
            
            // إضافة محاولة فاشلة إلى السجل
            const messageObj = {
                type: 'group',
                message: message,
                timestamp: new Date().toISOString(),
                success: false,
                parseMode: parseMode,
                error: data.description
            };
            
            appState.messageHistory.push(messageObj);
            saveMessageHistory();
            displayMessageHistory();
            
            showTempMessage('فشل إرسال الرسالة', 'error');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        showResult(sendResult, 'error', '❌ فشل الاتصال بالخادم. تأكد من اتصالك بالإنترنت');
        showTempMessage('فشل الاتصال بالخادم', 'error');
    } finally {
        hideLoading();
        
        // إخفاء رسالة النتيجة بعد 5 ثوان
        setTimeout(() => {
            hideResult(sendResult);
        }, 5000);
    }
}

// إرسال رسالة شخصية باستخدام username
async function sendPersonalMessage() {
    if (!appState.isVerified) {
        showResult(personalResult, 'error', '❌ يرجى التحقق من بيانات البوت أولاً');
        return;
    }
    
    let username = recipientUsernameInput.value.trim();
    const message = personalMessageInput.value.trim();
    
    // إزالة @ من البداية إذا وجدت
    if (username.startsWith('@')) {
        username = username.substring(1);
    }
    
    if (!username) {
        showResult(personalResult, 'error', '❌ الرجاء إدخال اسم المستخدم');
        return;
    }
    
    if (!message) {
        showResult(personalResult, 'error', '❌ الرجاء إدخال نص الرسالة');
        return;
    }
    
    showLoading('جاري إرسال الرسالة الشخصية...');
    
    const parseMode = personalParseModeCheckbox.checked ? 'HTML' : '';
    
    try {
        // أولاً: محاولة الإرسال باستخدام username مباشرة
        let success = false;
        let chatId = null;
        let note = '';
        
        try {
            const requestBody = {
                chat_id: `@${username}`,
                text: message,
                parse_mode: parseMode
            };
            
            const response = await fetch(`https://api.telegram.org/bot${appState.botToken}/sendMessage`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestBody)
            });
            
            const data = await response.json();
            
            if (data.ok) {
                success = true;
                note = 'تم الإرسال باستخدام @username مباشرة';
            } else {
                // إذا فشل، جرب الحصول على chat_id أولاً
                const chatIdResult = await getChatIdByUsername(username);
                if (chatIdResult.success) {
                    chatId = chatIdResult.chatId;
                    
                    // إرسال الرسالة باستخدام chat_id
                    const sendResponse = await fetch(`https://api.telegram.org/bot${appState.botToken}/sendMessage`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            chat_id: chatId,
                            text: message,
                            parse_mode: parseMode
                        })
                    });
                    
                    const sendData = await sendResponse.json();
                    
                    if (sendData.ok) {
                        success = true;
                        note = `تم الإرسال باستخدام chat_id: ${chatId}`;
                    } else {
                        throw new Error(sendData.description || 'فشل الإرسال');
                    }
                } else {
                    throw new Error(chatIdResult.error || 'لا يمكن العثور على المستخدم');
                }
            }
        } catch (sendError) {
            success = false;
            note = sendError.message;
        }
        
        // إضافة الرسالة إلى السجل
        const messageObj = {
            type: 'personal',
            recipient: `@${username}`,
            chatId: chatId,
            message: message,
            timestamp: new Date().toISOString(),
            success: success,
            parseMode: parseMode,
            note: note
        };
        
        appState.messageHistory.push(messageObj);
        if (appState.messageHistory.length > 50) {
            appState.messageHistory = appState.messageHistory.slice(-50);
        }
        
        saveMessageHistory();
        displayMessageHistory();
        
        // إضافة المستخدم إلى القائمة المتكررة
        addFrequentUser(`@${username}`);
        
        // عرض النتيجة
        if (success) {
            showResult(personalResult, 'success', `✅ تم إرسال الرسالة بنجاح إلى @${username}!${chatId ? `<br>chat_id: ${chatId}` : ''}`);
            
            // مسح الحقول
            personalMessageInput.value = '';
            sendPersonalBtn.disabled = true;
            
            showTempMessage(`تم إرسال الرسالة إلى @${username}`, 'success');
        } else {
            showResult(personalResult, 'error', `❌ فشل إرسال الرسالة إلى @${username}<br>السبب: ${note}`);
            showTempMessage(`فشل إرسال الرسالة إلى @${username}`, 'error');
        }
    } catch (error) {
        console.error('Error sending personal message:', error);
        showResult(personalResult, 'error', '❌ فشل الاتصال بالخادم. تأكد من اتصالك بالإنترنت');
        showTempMessage('فشل الاتصال بالخادم', 'error');
    } finally {
        hideLoading();
        
        // إخفاء رسالة النتيجة بعد 5 ثوان
        setTimeout(() => {
            hideResult(personalResult);
        }, 5000);
    }
}

// الحصول على chat_id باستخدام username
async function getChatIdByUsername(username) {
    try {
        // محاولة الحصول على معلومات الدردشة
        const response = await fetch(`https://api.telegram.org/bot${appState.botToken}/getChat?chat_id=@${username}`);
        const data = await response.json();
        
        if (data.ok) {
            return {
                success: true,
                chatId: data.result.id,
                chatInfo: data.result
            };
        } else {
            return {
                success: false,
                error: data.description || 'لا يمكن العثور على المستخدم'
            };
        }
    } catch (error) {
        return {
            success: false,
            error: 'فشل الاتصال بالخادم'
        };
    }
}

// قائمة المستخدمين المتكررين
function addFrequentUser(username) {
    let frequentUsers = JSON.parse(localStorage.getItem('frequentUsers') || '[]');
    
    // تنظيف القائمة من المستخدم الموجود مسبقاً
    frequentUsers = frequentUsers.filter(user => user !== username);
    
    // إضافة المستخدم في البداية
    frequentUsers.unshift(username);
    
    // الحفاظ على 10 مستخدمين فقط
    if (frequentUsers.length > 10) {
        frequentUsers = frequentUsers.slice(0, 10);
    }
    
    localStorage.setItem('frequentUsers', JSON.stringify(frequentUsers));
    displayFrequentUsers();
}

// عرض المستخدمين المتكررين
function displayFrequentUsers() {
    const frequentUsers = JSON.parse(localStorage.getItem('frequentUsers') || '[]');
    
    if (frequentUsers.length > 0) {
        // البحث عن عنصر المستخدمين المتكررين أو إنشائه
        let frequentUsersContainer = document.getElementById('frequentUsersContainer');
        
        if (!frequentUsersContainer) {
            frequentUsersContainer = document.createElement('div');
            frequentUsersContainer.id = 'frequentUsersContainer';
            frequentUsersContainer.className = 'frequent-users';
            
            const personalSection = document.querySelector('.personal-section');
            const firstFormGroup = personalSection.querySelector('.form-group');
            personalSection.insertBefore(frequentUsersContainer, firstFormGroup);
        }
        
        frequentUsersContainer.innerHTML = `
            <div class="section-note">
                <i class="fas fa-history"></i>
                <strong>المستخدمون المتكررون:</strong>
                ${frequentUsers.map(user => `
                    <button class="quick-user-btn" data-username="${user}">
                        ${user}
                    </button>
                `).join('')}
                ${frequentUsers.length > 0 ? `<button class="btn-clear-users" title="مسح القائمة">
                    <i class="fas fa-times"></i>
                </button>` : ''}
            </div>
        `;
        
        // إضافة مستمعي الأحداث للأزرار
        document.querySelectorAll('.quick-user-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const username = this.getAttribute('data-username');
                recipientUsernameInput.value = username;
                updatePersonalSendButton();
                showTempMessage(`تم تعيين المستخدم: ${username}`, 'success');
            });
        });
        
        // زر مسح القائمة
        const clearUsersBtn = document.querySelector('.btn-clear-users');
        if (clearUsersBtn) {
            clearUsersBtn.addEventListener('click', function() {
                if (confirm('هل تريد مسح قائمة المستخدمين المتكررين؟')) {
                    localStorage.removeItem('frequentUsers');
                    displayFrequentUsers();
                    showTempMessage('تم مسح قائمة المستخدمين المتكررين', 'success');
                }
            });
        }
    } else {
        // إزالة العنصر إذا لم يكن هناك مستخدمين
        const frequentUsersContainer = document.getElementById('frequentUsersContainer');
        if (frequentUsersContainer) {
            frequentUsersContainer.remove();
        }
    }
}

// إضافة أنماط CSS ديناميكية للرسائل المؤقتة
const tempMessageStyles = document.createElement('style');
tempMessageStyles.textContent = `
    .temp-message {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #333;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 10000;
        transform: translateX(150%);
        transition: transform 0.3s ease;
        max-width: 400px;
    }
    
    .temp-message.show {
        transform: translateX(0);
    }
    
    .temp-message.success {
        background-color: #28a745;
    }
    
    .temp-message.error {
        background-color: #dc3545;
    }
    
    .temp-message.info {
        background-color: #17a2b8;
    }
    
    .temp-message.warning {
        background-color: #ffc107;
        color: #212529;
    }
    
    .temp-message i {
        font-size: 1.2rem;
    }
    
    .btn-clear-users {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        padding: 5px 10px;
        margin-right: 10px;
        border-radius: 4px;
        transition: all 0.3s;
    }
    
    .btn-clear-users:hover {
        background-color: rgba(220, 53, 69, 0.1);
    }
`;

document.head.appendChild(tempMessageStyles);

// إضافة دالة لاختبار الاتصال
async function testConnection() {
    if (!appState.botToken) {
        showTempMessage('الرجاء إدخال رمز البوت أولاً', 'warning');
        return;
    }
    
    showLoading('جاري اختبار الاتصال...');
    
    try {
        const response = await fetch(`https://api.telegram.org/bot${appState.botToken}/getMe`);
        const data = await response.json();
        
        if (data.ok) {
            showTempMessage(`✅ الاتصال ناجح! البوت: @${data.result.username}`, 'success');
        } else {
            showTempMessage(`❌ فشل الاتصال: ${data.description}`, 'error');
        }
    } catch (error) {
        showTempMessage('❌ فشل الاتصال بالخادم', 'error');
    } finally {
        hideLoading();
    }
}

// إضافة زر اختبار الاتصال ديناميكياً
const testConnectionBtn = document.createElement('button');
testConnectionBtn.innerHTML = '<i class="fas fa-wifi"></i> اختبار الاتصال';
testConnectionBtn.className = 'btn btn-secondary';
testConnectionBtn.style.marginTop = '10px';
testConnectionBtn.addEventListener('click', testConnection);

// إضافة الزر إلى قسم الإعدادات
const setupSection = document.querySelector('.setup-section');
setupSection.querySelector('.button-group').appendChild(testConnectionBtn);

// تهيئة الأزرار عند التحميل
updateConnectionStatus();
updatePersonalSendButton();