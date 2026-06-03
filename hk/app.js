// ========== المتغيرات العامة ==========
let currentUserId = null;
let currentUsername = null;
let currentChatPeer = null;
let messagePollingInterval = null;
let users = new Map();
let isLoadingMessages = false;

// ========== تهيئة التطبيق ==========
document.addEventListener('DOMContentLoaded', () => {
    setupEventListeners();
    checkSession();
});

function setupEventListeners() {
    // Auth tabs
    document.querySelectorAll('.auth-tab').forEach(tab => {
        tab.addEventListener('click', () => switchAuthTab(tab.dataset.tab));
    });
    
    // Auth buttons
    document.getElementById('login-btn').addEventListener('click', handleLogin);
    document.getElementById('register-btn').addEventListener('click', handleRegister);
    document.getElementById('logout-btn').addEventListener('click', handleLogout);
    
    // Chat actions
    document.getElementById('send-message-btn').addEventListener('click', sendMessage);
    document.getElementById('message-input').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
    
    // File upload
    document.getElementById('attach-file-btn').addEventListener('click', () => {
        document.getElementById('file-input').click();
    });
    document.getElementById('file-input').addEventListener('change', handleFileUpload);
}

function switchAuthTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.auth-tab[data-tab="${tab}"]`).classList.add('active');
    
    document.getElementById('login-panel').classList.toggle('hidden', tab !== 'login');
    document.getElementById('register-panel').classList.toggle('hidden', tab !== 'register');
}

// ========== المصادقة ==========
async function checkSession() {
    try {
        const response = await fetch('check_session.php');
        const data = await response.json();
        
        if (data.success && data.data.user_id) {
            currentUserId = data.data.user_id;
            currentUsername = data.data.username;
            initializeChat(data.data);
        } else {
            showAuthScreen();
        }
    } catch (error) {
        console.error('Session check failed:', error);
        showAuthScreen();
    }
}

function initializeChat(userData) {
    document.getElementById('current-username').textContent = currentUsername;
    document.getElementById('auth-screen').classList.add('hidden');
    document.getElementById('chat-screen').classList.remove('hidden');
    
    loadUsers();
    startMessagePolling();
    showNotification('Connected successfully', 'success');
}

async function handleLogin() {
    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;
    const errorDiv = document.getElementById('login-error');
    const loginBtn = document.getElementById('login-btn');
    
    errorDiv.textContent = '';
    
    if (!username || !password) {
        errorDiv.textContent = 'Please enter username/email and password';
        return;
    }
    
    loginBtn.disabled = true;
    loginBtn.textContent = 'AUTHENTICATING...';
    
    try {
        const response = await fetch('login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUserId = data.data.user_id;
            currentUsername = data.data.username;
            initializeChat(data.data);
        } else {
            errorDiv.textContent = data.error || 'Login failed';
        }
    } catch (error) {
        errorDiv.textContent = 'Network error. Please try again.';
    } finally {
        loginBtn.disabled = false;
        loginBtn.textContent = 'AUTHENTICATE';
    }
}

async function handleRegister() {
    const username = document.getElementById('reg-username').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const errorDiv = document.getElementById('register-error');
    const registerBtn = document.getElementById('register-btn');
    
    errorDiv.textContent = '';
    
    if (!username || !email || !password) {
        errorDiv.textContent = 'Please fill all fields';
        return;
    }
    
    if (password.length < 8) {
        errorDiv.textContent = 'Password must be at least 8 characters';
        return;
    }
    
    if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
        errorDiv.textContent = 'Username must be 3-20 characters (letters, numbers, underscore)';
        return;
    }
    
    registerBtn.disabled = true;
    registerBtn.textContent = 'CREATING...';
    
    try {
        const response = await fetch('register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUserId = data.data.user_id;
            currentUsername = data.data.username;
            initializeChat(data.data);
        } else {
            errorDiv.textContent = data.error || 'Registration failed';
        }
    } catch (error) {
        errorDiv.textContent = 'Network error. Please try again.';
    } finally {
        registerBtn.disabled = false;
        registerBtn.textContent = 'CREATE ACCOUNT';
    }
}

async function handleLogout() {
    try {
        await fetch('logout.php', { method: 'POST' });
        
        if (messagePollingInterval) {
            clearInterval(messagePollingInterval);
            messagePollingInterval = null;
        }
        
        currentUserId = null;
        currentChatPeer = null;
        users.clear();
        
        document.getElementById('chat-screen').classList.add('hidden');
        document.getElementById('auth-screen').classList.remove('hidden');
        
        // Clear inputs
        document.getElementById('login-username').value = '';
        document.getElementById('login-password').value = '';
        document.getElementById('reg-username').value = '';
        document.getElementById('reg-email').value = '';
        document.getElementById('reg-password').value = '';
        
        // Clear chat UI
        document.getElementById('messages-list').innerHTML = '';
        document.getElementById('users-list').innerHTML = '';
        document.getElementById('chat-active').classList.add('hidden');
        document.getElementById('chat-placeholder').classList.remove('hidden');
        
        showNotification('Logged out successfully', 'success');
    } catch (error) {
        console.error('Logout failed:', error);
    }
}

function showAuthScreen() {
    document.getElementById('auth-screen').classList.remove('hidden');
    document.getElementById('chat-screen').classList.add('hidden');
}

// ========== إدارة المستخدمين ==========
async function loadUsers() {
    try {
        const response = await fetch('messages.php?action=get_users');
        const data = await response.json();
        
        if (data.success) {
            users.clear();
            const usersList = document.getElementById('users-list');
            usersList.innerHTML = '';
            
            for (const user of data.data) {
                users.set(user.id, user);
                
                const li = document.createElement('li');
                li.className = 'user-item';
                li.dataset.userId = user.id;
                li.innerHTML = `
                    <div class="user-item-content">
                        <span class="conv-username">${escapeHtml(user.username)}</span>
                        <span class="conv-last-msg"></span>
                    </div>
                    <span class="unread-badge hidden"></span>
                `;
                li.addEventListener('click', () => selectChatUser(user.id, user.username));
                usersList.appendChild(li);
            }
            
            // بعد تحميل المستخدمين، حمّل عداد الرسائل
            updateUnreadCounts();
        }
    } catch (error) {
        console.error('Failed to load users:', error);
        showNotification('Failed to load contacts', 'error');
    }
}

// تحديث عداد الرسائل الغير مقروءة وآخر رسالة بدون إعادة بناء القائمة
async function updateUnreadCounts() {
    try {
        const response = await fetch('messages.php?action=get_unread_counts');
        const data = await response.json();
        
        if (!data.success) return;
        
        const counts = data.data;
        
        const usersList = document.getElementById('users-list');
        for (const li of usersList.children) {
            const userId = parseInt(li.dataset.userId);
            const info = counts[userId];
            
            const badge = li.querySelector('.unread-badge');
            const lastMsgEl = li.querySelector('.conv-last-msg');
            
            if (info && info.unread_count > 0 && userId !== currentChatPeer) {
                badge.textContent = info.unread_count > 99 ? '99+' : info.unread_count;
                badge.classList.remove('hidden');
                li.classList.add('has-unread');
            } else {
                badge.classList.add('hidden');
                li.classList.remove('has-unread');
            }
            
            if (info && info.last_message) {
                const preview = info.last_has_file ? '\u{1F4CE} Media' : truncateMsg(info.last_message, 28);
                lastMsgEl.textContent = preview;
            } else if (info && info.last_has_file) {
                lastMsgEl.textContent = '\u{1F4CE} Media';
            } else {
                lastMsgEl.textContent = '';
            }
        }
    } catch (error) {
        console.error('Failed to update unread counts:', error);
    }
}

function truncateMsg(text, maxLen) {
    if (!text) return '';
    return text.length > maxLen ? text.substring(0, maxLen) + '\u2026' : text;
}

function selectChatUser(userId, username) {
    // Don't reload if same user
    if (currentChatPeer === userId) return;
    
    currentChatPeer = userId;
    
    // Remove active class from all users, add to selected
    document.querySelectorAll('#users-list .user-item').forEach(li => {
        li.classList.remove('active-chat');
    });
    const selectedLi = document.querySelector(`#users-list .user-item[data-user-id="${userId}"]`);
    if (selectedLi) {
        selectedLi.classList.add('active-chat');
        // إخفاء badge فوراً لما نفتح المحادثة
        const badge = selectedLi.querySelector('.unread-badge');
        if (badge) {
            badge.classList.add('hidden');
            badge.textContent = '';
        }
        selectedLi.classList.remove('has-unread');
    }
    
    document.getElementById('chat-placeholder').classList.add('hidden');
    document.getElementById('chat-active').classList.remove('hidden');
    document.getElementById('chat-peer-name').textContent = username;
    
    // Clear messages list first
    document.getElementById('messages-list').innerHTML = '';
    
    // Load messages
    loadMessages();
    
    // Close sidebar on mobile
    if (window.innerWidth <= 768) {
        closeMobileSidebar();
    }
}

// ========== إدارة الرسائل ==========
async function loadMessages() {
    if (!currentChatPeer || isLoadingMessages) return;
    
    isLoadingMessages = true;
    
    try {
        const response = await fetch(`messages.php?action=get&with_user=${currentChatPeer}`);
        const data = await response.json();
        
        if (data.success) {
            const messagesList = document.getElementById('messages-list');
            const oldScrollHeight = messagesList.scrollHeight;
            const wasScrolledToBottom = messagesList.scrollTop + messagesList.clientHeight >= oldScrollHeight - 50;
            
            // Clear and rebuild
            messagesList.innerHTML = '';
            
            for (const msg of data.data) {
                appendMessageToDOM(msg);
            }
            
            // Initialize lazy loading for images
            initLazyLoading();
            
            // Scroll to bottom if was there or first load
            if (wasScrolledToBottom || messagesList.children.length < 10) {
                messagesList.scrollTop = messagesList.scrollHeight;
            }
        }
    } catch (error) {
        console.error('Failed to load messages:', error);
    } finally {
        isLoadingMessages = false;
    }
}

function appendMessageToDOM(message) {
    const isMine = message.sender_id == currentUserId;
    const messagesList = document.getElementById('messages-list');
    const li = document.createElement('li');
    li.className = `msg-item ${isMine ? 'mine' : 'theirs'}`;
    li.dataset.msgId = message.id;
    li.dataset.isMine = isMine ? '1' : '0';
    
    let content = '';
    
    // Handle files
    if (message.has_file && message.files && message.files.length > 0) {
        for (const file of message.files) {
            content += renderFileContent(file);
        }
    }
    
    // Handle text message
    if (message.message_text && message.message_text !== '[FILE]') {
        const editedBadge = message.is_edited ? `<span class="edited-badge">edited</span>` : '';
        content += `<div class="bubble">${escapeHtml(message.message_text)}${editedBadge}</div>`;
    }
    
    const isTextMsg = message.message_text && message.message_text !== '[FILE]';
    const msgData = JSON.stringify({id: message.id, message_text: message.message_text, is_edited: !!message.is_edited});

    li.innerHTML = `
        ${isMine ? `<div class="msg-actions">
            ${isTextMsg ? `<button class="msg-action-btn" data-action="edit">✏️</button>` : ''}
            <button class="msg-action-btn del" data-action="del">🗑️</button>
        </div>` : ''}
        ${content}
        <div class="msg-time">${formatTime(message.sent_at)}</div>
    `;

    if (isMine) {
        li.querySelectorAll('.msg-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (btn.dataset.action === 'edit') startEditMessage(li, message);
                if (btn.dataset.action === 'del') confirmDeleteMessage(li, message.id);
            });
        });
    }

    messagesList.appendChild(li);
}

// ========== تعديل الرسالة ==========
function startEditMessage(li, message) {
    const bubble = li.querySelector('.bubble');
    if (!bubble) return;

    const originalText = message.message_text;
    bubble.innerHTML = `
        <div class="edit-input-wrap">
            <input type="text" class="edit-input" value="${escapeHtml(originalText)}">
            <div class="edit-actions">
                <button class="edit-save-btn">✔ حفظ</button>
                <button class="edit-cancel-btn">✕</button>
            </div>
        </div>
    `;

    const input = bubble.querySelector('.edit-input');
    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);

    bubble.querySelector('.edit-cancel-btn').addEventListener('mousedown', (e) => e.preventDefault());
    bubble.querySelector('.edit-cancel-btn').addEventListener('click', () => {
        const editedBadge = message.is_edited ? `<span class="edited-badge">edited</span>` : '';
        bubble.innerHTML = escapeHtml(originalText) + editedBadge;
    });

    bubble.querySelector('.edit-save-btn').addEventListener('mousedown', (e) => e.preventDefault());
    bubble.querySelector('.edit-save-btn').addEventListener('click', () => {
        const newText = input.value.trim();
        if (!newText || newText === originalText) {
            const editedBadge = message.is_edited ? `<span class="edited-badge">edited</span>` : '';
            bubble.innerHTML = escapeHtml(originalText) + editedBadge;
            return;
        }
        saveEditMessage(li, message, bubble, newText);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const newText = input.value.trim();
            if (!newText || newText === originalText) {
                const editedBadge = message.is_edited ? `<span class="edited-badge">edited</span>` : '';
                bubble.innerHTML = escapeHtml(originalText) + editedBadge;
                return;
            }
            saveEditMessage(li, message, bubble, newText);
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            const editedBadge = message.is_edited ? `<span class="edited-badge">edited</span>` : '';
            bubble.innerHTML = escapeHtml(originalText) + editedBadge;
        }
    });
}

async function saveEditMessage(li, message, bubble, newText) {
    try {
        const response = await fetch('messages.php?action=edit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message_id: message.id, message_text: newText })
        });
        const data = await response.json();

        if (data.success) {
            message.message_text = newText;
            message.is_edited = true;
            bubble.innerHTML = `${escapeHtml(newText)}<span class="edited-badge">edited</span>`;
            showNotification('تم تعديل الرسالة', 'success');
        } else {
            showNotification(data.error || 'فشل التعديل', 'error');
            bubble.innerHTML = escapeHtml(message.message_text);
        }
    } catch (err) {
        showNotification('خطأ في الاتصال', 'error');
        bubble.innerHTML = escapeHtml(message.message_text);
    }
}

// ========== حذف الرسالة ==========
function confirmDeleteMessage(li, messageId) {
    // Inline confirm inside the message
    const confirmBox = document.createElement('div');
    confirmBox.className = 'delete-confirm-box';
    confirmBox.innerHTML = `
        <span>حذف الرسالة؟</span>
        <button class="del-yes-btn">حذف</button>
        <button class="del-no-btn">لأ</button>
    `;
    li.appendChild(confirmBox);

    confirmBox.querySelector('.del-no-btn').addEventListener('click', () => confirmBox.remove());
    confirmBox.querySelector('.del-yes-btn').addEventListener('click', () => {
        confirmBox.remove();
        doDeleteMessage(li, messageId);
    });
}

async function doDeleteMessage(li, messageId) {
    try {
        const response = await fetch('messages.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message_id: messageId })
        });
        const data = await response.json();

        if (data.success) {
            li.style.transition = 'opacity 0.3s, transform 0.3s';
            li.style.opacity = '0';
            li.style.transform = 'scale(0.8)';
            setTimeout(() => li.remove(), 300);
            showNotification('تم حذف الرسالة', 'success');
        } else {
            showNotification(data.error || 'فشل الحذف', 'error');
        }
    } catch (err) {
        showNotification('خطأ في الاتصال', 'error');
    }
}

function renderFileContent(file) {
    const fileName = escapeHtml(file.original_name);
    const fileSize = formatFileSize(file.file_size);
    const fileUrl = file.path;
    const thumbUrl = file.thumbnail_path || file.path;
    
    switch (file.file_type) {
        case 'image':
            return `
                <div class="file-container image-container">
                    <div class="image-placeholder" style="width: ${Math.min(file.width || 300, 250)}px;">
                        <div class="image-loader"></div>
                        <img 
                            class="chat-image lazy-image"
                            data-src="${thumbUrl}"
                            data-full="${fileUrl}"
                            alt="${fileName}"
                            loading="lazy"
                            onclick="openImageModal('${fileUrl}')"
                        >
                    </div>
                    <div style="display: block;" class="file-info">${fileName} (${fileSize})</div>
                </div>
            `;
        
        case 'video':
            return `
                <div class="file-container">
                    <video controls preload="metadata" class="chat-video">
                        <source src="${fileUrl}" type="${file.mime_type}">
                        Your browser does not support the video tag.
                    </video>
                    <div class="file-info">${fileName} (${fileSize})</div>
                </div>
            `;
        
        default:
            return `
                <div class="file-container">
                    <a href="${fileUrl}" download="${fileName}" class="file-download">
                        <div class="file-icon">📄</div>
                        <div class="file-details">
                            <div class="file-name">${fileName}</div>
                            <div class="file-size">${fileSize}</div>
                        </div>
                    </a>
                </div>
            `;
    }
}

async function sendMessage() {
    const input = document.getElementById('message-input');
    const text = input.value.trim();
    
    if (!text || !currentChatPeer) return;
    
    const sendBtn = document.getElementById('send-message-btn');
    sendBtn.disabled = true;
    sendBtn.textContent = 'SENDING...';
    
    try {
        const response = await fetch('messages.php?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                receiver_id: currentChatPeer,
                message_text: text
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            input.value = '';
            
            // Add message to UI immediately
            const tempMessage = {
                id: data.data.message_id,
                sender_id: currentUserId,
                receiver_id: currentChatPeer,
                message_text: text,
                sent_at: new Date().toISOString(),
                has_file: false,
                files: []
            };
            
            appendMessageToDOM(tempMessage);
            
            // تحديث preview آخر رسالة في القائمة الجانبية فوراً
            updateLastMsgPreview(currentChatPeer, text, false);
            
            // Scroll to bottom
            const messagesList = document.getElementById('messages-list');
            messagesList.scrollTop = messagesList.scrollHeight;
        } else {
            showNotification('Failed to send message', 'error');
        }
    } catch (error) {
        console.error('Send message failed:', error);
        showNotification('Failed to send message', 'error');
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = 'SEND';
    }
}

async function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file || !currentChatPeer) return;
    
    // Validate file size (60MB max)
    if (file.size > 60 * 1024 * 1024) {
        showNotification('File too large. Maximum 60MB', 'error');
        event.target.value = '';
        return;
    }
    
    // Show loading
    showNotification('Uploading file...', 'info');
    
    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('receiver_id', currentChatPeer);
    formData.append('file', file);
    
    try {
        const response = await fetch('upload.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('File uploaded successfully', 'success');
            
            // Add file message to UI immediately
            const tempMessage = {
                id: data.data.message_id,
                sender_id: currentUserId,
                receiver_id: currentChatPeer,
                message_text: '',
                sent_at: new Date().toISOString(),
                has_file: true,
                files: [{
                    id: data.data.file_id,
                    path: data.data.path,
                    thumbnail_path: data.data.thumbnail_path,
                    file_type: data.data.file_type,
                    original_name: data.data.original_name,
                    file_size: data.data.file_size,
                    mime_type: data.data.mime_type,
                    width: data.data.width,
                    height: data.data.height
                }]
            };
            
            appendMessageToDOM(tempMessage);
            
            const messagesList = document.getElementById('messages-list');
            messagesList.scrollTop = messagesList.scrollHeight;
        } else {
            showNotification(data.error || 'File upload failed', 'error');
        }
    } catch (error) {
        console.error('File upload failed:', error);
        showNotification('File upload failed', 'error');
    } finally {
        event.target.value = '';
    }
}

// ========== تحديث الرسائل (Polling without refresh) ==========
function startMessagePolling() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    
    // Poll every 3 seconds for new messages + unread counts
    let pollCount = 0;
    messagePollingInterval = setInterval(async () => {
        pollCount++;
        
        // تحديث عداد الرسائل كل 3 ثوان دائماً
        updateUnreadCounts();
        
        // تحديث الرسائل فقط لو في محادثة مفتوحة
        if (!currentChatPeer) return;
        
        try {
            const response = await fetch(`messages.php?action=get&with_user=${currentChatPeer}`);
            const data = await response.json();
            
            if (data.success && data.data.length > 0) {
                const messagesList = document.getElementById('messages-list');
                const currentMsgIds = new Set();
                
                // Get current message IDs
                for (const li of messagesList.children) {
                    if (li.dataset.msgId) {
                        currentMsgIds.add(parseInt(li.dataset.msgId));
                    }
                }
                
                // Find and add only new messages
                let hasNewMessages = false;
                const wasAtBottom = 
                    messagesList.scrollTop + messagesList.clientHeight >= messagesList.scrollHeight - 100;

                const serverMsgIds = new Set(data.data.map(m => m.id));

                // Remove deleted messages from DOM
                for (const li of [...messagesList.children]) {
                    const msgId = parseInt(li.dataset.msgId);
                    if (msgId && !serverMsgIds.has(msgId)) {
                        li.style.transition = 'opacity 0.3s';
                        li.style.opacity = '0';
                        setTimeout(() => li.remove(), 300);
                    }
                }

                for (const msg of data.data) {
                    if (!currentMsgIds.has(msg.id)) {
                        appendMessageToDOM(msg);
                        hasNewMessages = true;
                    } else {
                        // Update edited messages
                        const existingLi = messagesList.querySelector(`[data-msg-id="${msg.id}"]`);
                        if (existingLi && msg.message_text && msg.message_text !== '[FILE]') {
                            const bubble = existingLi.querySelector('.bubble');
                            if (bubble) {
                                const currentText = bubble.textContent.replace('edited', '').trim();
                                if (currentText !== msg.message_text) {
                                    const editedBadge = msg.is_edited ? `<span class="edited-badge">edited</span>` : '';
                                    bubble.innerHTML = escapeHtml(msg.message_text) + editedBadge;
                                }
                            }
                        }
                    }
                }
                
                // Scroll only if was at bottom
                if (hasNewMessages && wasAtBottom) {
                    messagesList.scrollTop = messagesList.scrollHeight;
                }
                
                // Re-init lazy loading for new images
                if (hasNewMessages) {
                    initLazyLoading();
                }
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 3000);
}

// ========== Lazy Loading للصور ==========
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('.lazy-image:not(.lazy-initialized)');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.dataset.src;
                    
                    if (src) {
                        img.src = src;
                        img.classList.add('loaded');
                        img.classList.add('lazy-initialized');
                        
                        // Preload full image in background
                        const fullSrc = img.dataset.full;
                        if (fullSrc) {
                            const fullImg = new Image();
                            fullImg.src = fullSrc;
                        }
                        
                        imageObserver.unobserve(img);
                    }
                }
            }
        }, {
            rootMargin: '50px',
            threshold: 0.01
        });
        
        lazyImages.forEach(img => {
            imageObserver.observe(img);
            img.classList.add('lazy-initialized');
        });
    } else {
        // Fallback
        lazyImages.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.classList.add('loaded');
                img.classList.add('lazy-initialized');
            }
        });
    }
}

// ========== صورة بتكبير ==========
function openImageModal(imageUrl) {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="image-modal-content">
            <span class="image-modal-close">&times;</span>
            <img src="${imageUrl}" alt="Full size image">
        </div>
    `;
    document.body.appendChild(modal);
    
    modal.querySelector('.image-modal-close').onclick = () => modal.remove();
    modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
}

// ========== دوال مساعدة ==========
function formatTime(isoString) {
    const date = new Date(isoString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification ${type} visible`;
    
    setTimeout(() => {
        notification.classList.remove('visible');
    }, 3000);
}

// ========== Mobile Sidebar ==========
function closeMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('mobile-overlay');
    if (sidebar) sidebar.classList.remove('mobile-open');
    if (overlay) overlay.classList.remove('active');
    document.body.classList.remove('sidebar-open');
}

function openMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('mobile-overlay');
    if (sidebar) sidebar.classList.add('mobile-open');
    if (overlay) overlay.classList.add('active');
    document.body.classList.add('sidebar-open');
}

// Mobile sidebar initialization
(function initMobileSidebar() {
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const closeBtn = document.getElementById('mobile-close-sidebar');
    const overlay = document.getElementById('mobile-overlay');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', openMobileSidebar);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', closeMobileSidebar);
    }
    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }
    
    // Close on resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            closeMobileSidebar();
        }
    });
})();




// ========== Custom Video Player Class ==========
class CustomVideoPlayer {
    constructor(videoElement, container) {
        this.video = videoElement;
        this.container = container;
        this.controls = null;
        this.isPlaying = false;
        this.isMuted = false;
        this.volume = 1;
        
        this.init();
    }
    
    init() {
        // Create controls if not exists
        if (!this.container.querySelector('.video-controls')) {
            this.createControls();
        }
        
        this.controls = this.container.querySelector('.video-controls');
        this.setupEventListeners();
        this.setupVideoEvents();
    }
    
    createControls() {
        const controlsHTML = `
            <div class="video-controls">
                <div class="controls-row">
                    <button class="video-btn play-pause-btn">▶</button>
                    
                    <div class="volume-control">
                        <button class="video-btn volume-btn">🔊</button>
                        <div class="volume-slider">
                            <input type="range" class="volume-range" min="0" max="100" value="100">
                        </div>
                    </div>
                    
                    <div class="time-display">
                        <span class="current-time">00:00</span> / 
                        <span class="total-time">00:00</span>
                    </div>
                    
                    <button class="video-btn fullscreen-btn">⤢</button>
                </div>
                
                <div class="progress-bar">
                    <div class="progress-filled"></div>
                </div>
            </div>
            
            <div class="video-loader"></div>
            <div class="big-play-btn">▶</div>
            <div class="video-info">
                <span class="video-quality">HD</span> • 
                <span class="video-size"></span>
            </div>
        `;
        
        this.container.insertAdjacentHTML('beforeend', controlsHTML);
        
        // Store file size if available
        const fileSizeElem = this.container.querySelector('.video-size');
        if (this.video.dataset.fileSize) {
            fileSizeElem.textContent = this.formatFileSize(parseInt(this.video.dataset.fileSize));
        } else {
            fileSizeElem.parentElement.style.display = 'none';
        }
    }
    
    setupEventListeners() {
        // Play/Pause button
        const playPauseBtn = this.controls.querySelector('.play-pause-btn');
        playPauseBtn.addEventListener('click', () => this.togglePlay());
        
        // Big play button
        const bigPlayBtn = this.container.querySelector('.big-play-btn');
        bigPlayBtn.addEventListener('click', () => this.togglePlay());
        
        // Volume control
        const volumeBtn = this.controls.querySelector('.volume-btn');
        const volumeRange = this.controls.querySelector('.volume-range');
        
        volumeBtn.addEventListener('click', () => this.toggleMute());
        volumeRange.addEventListener('input', (e) => this.setVolume(e.target.value / 100));
        
        // Progress bar
        const progressBar = this.controls.querySelector('.progress-bar');
        progressBar.addEventListener('click', (e) => this.seek(e));
        
        // Fullscreen
        const fullscreenBtn = this.controls.querySelector('.fullscreen-btn');
        fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
        
        // Show/hide controls on container hover
        this.container.addEventListener('mouseenter', () => this.showControls());
        this.container.addEventListener('mouseleave', () => this.hideControls());
        
        // Touch devices: tap to show controls
        this.container.addEventListener('click', (e) => {
            if (e.target === this.video || e.target.classList.contains('big-play-btn')) {
                this.togglePlay();
            }
            this.showControls();
            setTimeout(() => this.hideControls(), 2500);
        });
    }
    
    setupVideoEvents() {
        // Update time display
        this.video.addEventListener('timeupdate', () => this.updateTimeDisplay());
        
        // Update progress bar
        this.video.addEventListener('timeupdate', () => this.updateProgress());
        
        // Update play/pause button
        this.video.addEventListener('play', () => this.updatePlayPauseButton(true));
        this.video.addEventListener('pause', () => this.updatePlayPauseButton(false));
        
        // Loading events
        this.video.addEventListener('waiting', () => this.showLoading());
        this.video.addEventListener('playing', () => this.hideLoading());
        
        // Metadata loaded
        this.video.addEventListener('loadedmetadata', () => {
            this.updateTotalTime();
        });
        
        // End of video
        this.video.addEventListener('ended', () => {
            this.updatePlayPauseButton(false);
            this.video.currentTime = 0;
        });
        
        // Volume change
        this.video.addEventListener('volumechange', () => this.updateVolumeButton());
    }
    
    togglePlay() {
        if (this.video.paused) {
            this.video.play();
        } else {
            this.video.pause();
        }
    }
    
    updatePlayPauseButton(isPlaying) {
        const playPauseBtn = this.controls.querySelector('.play-pause-btn');
        const bigPlayBtn = this.container.querySelector('.big-play-btn');
        
        if (isPlaying) {
            playPauseBtn.textContent = '⏸';
            bigPlayBtn.style.opacity = '0';
            bigPlayBtn.classList.remove('visible');
        } else {
            playPauseBtn.textContent = '▶';
            if (this.video.currentTime === 0 || this.video.ended) {
                bigPlayBtn.style.opacity = '1';
                bigPlayBtn.classList.add('visible');
            }
        }
    }
    
    updateTimeDisplay() {
        const currentTimeElem = this.controls.querySelector('.current-time');
        currentTimeElem.textContent = this.formatTime(this.video.currentTime);
    }
    
    updateTotalTime() {
        const totalTimeElem = this.controls.querySelector('.total-time');
        totalTimeElem.textContent = this.formatTime(this.video.duration);
    }
    
    updateProgress() {
        if (this.video.duration) {
            const percent = (this.video.currentTime / this.video.duration) * 100;
            this.controls.querySelector('.progress-filled').style.width = `${percent}%`;
        }
    }
    
    seek(e) {
        const progressBar = this.controls.querySelector('.progress-bar');
        const rect = progressBar.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const percent = x / rect.width;
        this.video.currentTime = percent * this.video.duration;
    }
    
    setVolume(volume) {
        this.volume = volume;
        this.video.volume = volume;
        this.isMuted = volume === 0;
        this.updateVolumeButton();
    }
    
    toggleMute() {
        if (this.isMuted) {
            this.video.volume = this.volume || 1;
            this.isMuted = false;
        } else {
            this.volume = this.video.volume;
            this.video.volume = 0;
            this.isMuted = true;
        }
        this.updateVolumeButton();
    }
    
    updateVolumeButton() {
        const volumeBtn = this.controls.querySelector('.volume-btn');
        const volumeRange = this.controls.querySelector('.volume-range');
        
        if (this.isMuted || this.video.volume === 0) {
            volumeBtn.textContent = '🔇';
            volumeRange.value = 0;
        } else if (this.video.volume < 0.5) {
            volumeBtn.textContent = '🔉';
            volumeRange.value = this.video.volume * 100;
        } else {
            volumeBtn.textContent = '🔊';
            volumeRange.value = this.video.volume * 100;
        }
    }
    
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            this.container.requestFullscreen();
            this.controls.querySelector('.fullscreen-btn').textContent = '✕';
        } else {
            document.exitFullscreen();
            this.controls.querySelector('.fullscreen-btn').textContent = '⤢';
        }
    }
    
    showControls() {
        if (this.controls) {
            this.controls.style.opacity = '1';
            this.container.classList.add('controls-visible');
        }
    }
    
    hideControls() {
        if (this.controls && this.video && !this.video.paused) {
            this.controls.style.opacity = '0';
            this.container.classList.remove('controls-visible');
        }
    }
    
    showLoading() {
        this.container.classList.add('loading');
    }
    
    hideLoading() {
        this.container.classList.remove('loading');
    }
    
    formatTime(seconds) {
        if (isNaN(seconds)) return '00:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    
    formatFileSize(bytes) {
        if (bytes < 1024) return bytes + 'B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(0) + 'KB';
        return (bytes / 1048576).toFixed(1) + 'MB';
    }
}

// ========== تحديث دالة renderFileContent للفيديو ==========
function renderFileContent(file) {
    const fileName = escapeHtml(file.original_name);
    const fileSize = formatFileSize(file.file_size);
    const fileUrl = file.path;
    const thumbUrl = file.thumbnail_path || file.path;
    
    switch (file.file_type) {
        case 'image':
            return `
                <div class="file-container image-container">
                    <div class="image-placeholder" style="width: ${Math.min(file.width || 300, 250)}px;">
                        <div class="image-loader"></div>
                        <img 
                            class="chat-image lazy-image"
                            data-src="${thumbUrl}"
                            data-full="${fileUrl}"
                            alt="${fileName}"
                            loading="lazy"
                            onclick="openImageModal('${fileUrl}')"
                        >
                    </div>
                    <div class="file-info">${fileName} (${fileSize})</div>
                </div>
            `;
        
        case 'video':
            // Return custom video player HTML
            return `
                <div class="custom-video-container" data-filesize="${file.file_size}">
                    <video 
                        class="custom-video" 
                        preload="metadata"
                        data-file-size="${file.file_size}"
                        poster="${thumbUrl !== fileUrl ? thumbUrl : ''}"
                    >
                        <source src="${fileUrl}" type="${file.mime_type || 'video/mp4'}">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="file-info">${fileName} (${fileSize})</div>
            `;
        
        default:
            return `
                <div class="file-container">
                    <a href="${fileUrl}" download="${fileName}" class="file-download">
                        <div class="file-icon">📄</div>
                        <div class="file-details">
                            <div class="file-name">${fileName}</div>
                            <div class="file-size">${fileSize}</div>
                        </div>
                    </a>
                </div>
            `;
    }
}

// ========== تحديث دالة appendMessageToDOM لإضافة مشغلات الفيديو ==========
// appendMessageToDOM - moved to top of file

// ========== تهيئة جميع مشغلات الفيديو ==========
function initVideoPlayers() {
    const videoContainers = document.querySelectorAll('.custom-video-container:not(.initialized)');
    
    videoContainers.forEach(container => {
        const video = container.querySelector('.custom-video');
        if (video) {
            // Mark as initialized
            container.classList.add('initialized');
            
            // Wait for video to be ready
            if (video.readyState >= 1) {
                new CustomVideoPlayer(video, container);
            } else {
                video.addEventListener('loadedmetadata', () => {
                    new CustomVideoPlayer(video, container);
                }, { once: true });
            }
        }
    });
}

// ========== تحديث loadMessages ==========
async function loadMessages() {
    if (!currentChatPeer || isLoadingMessages) return;
    
    isLoadingMessages = true;
    
    try {
        const response = await fetch(`messages.php?action=get&with_user=${currentChatPeer}`);
        const data = await response.json();
        
        if (data.success) {
            const messagesList = document.getElementById('messages-list');
            const oldScrollHeight = messagesList.scrollHeight;
            const wasScrolledToBottom = messagesList.scrollTop + messagesList.clientHeight >= oldScrollHeight - 50;
            
            // Clear and rebuild
            messagesList.innerHTML = '';
            
            for (const msg of data.data) {
                appendMessageToDOM(msg);
            }
            
            // Initialize lazy loading for images
            initLazyLoading();
            
            // Initialize video players
            initVideoPlayers();
            
            // Scroll to bottom if was there or first load
            if (wasScrolledToBottom || messagesList.children.length < 10) {
                messagesList.scrollTop = messagesList.scrollHeight;
            }
        }
    } catch (error) {
        console.error('Failed to load messages:', error);
    } finally {
        isLoadingMessages = false;
    }
}
// تحديث preview آخر رسالة في القائمة الجانبية مباشرةً بعد الإرسال
function updateLastMsgPreview(userId, msgText, isFile) {
    const li = document.querySelector(`#users-list .user-item[data-user-id="${userId}"]`);
    if (!li) return;
    const lastMsgEl = li.querySelector('.conv-last-msg');
    if (!lastMsgEl) return;
    const preview = isFile ? '📎 Media' : truncateMsg(msgText, 28);
    lastMsgEl.textContent = preview;
}