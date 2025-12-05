// main.js - الملف الرئيسي للجافاسكريبت

// البيانات العالمية
let currentPrayer = '';
let selectedStatus = '';
let naflStatuses = {};
let currentDate = window.selectedDate || new Date().toISOString().split('T')[0];

// عناصر DOM
const menuBtn = document.getElementById('menuBtn');
const closeBtn = document.getElementById('closeBtn');
const sideMenu = document.getElementById('sideMenu');
const menuOverlay = document.getElementById('menuOverlay');
const sessionIndicator = document.getElementById('sessionIndicator');
const prayerModalOverlay = document.getElementById('prayerModalOverlay');

// تهيئة الصفحة
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
    loadPrayersForDate(currentDate);
    setupEventListeners();
    updateSessionTime();
});

// تهيئة الصفحة
function initializePage() {
    // إضافة تأثير عند تحميل الصفحة
    setTimeout(() => {
        const cards = document.querySelectorAll('.prayer-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            }, index * 100);
        });
    }, 500);
}

// إعداد المستمعين للأحداث
function setupEventListeners() {
    // القائمة الجانبية
    if (menuBtn) {
        menuBtn.addEventListener('click', openSideMenu);
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeSideMenu);
    }
    
    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeSideMenu);
    }
    
    // إغلاق النوافذ عند الضغط على زر ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (prayerModalOverlay && prayerModalOverlay.classList.contains('active')) {
                closePrayerModal();
            } else {
                closeSideMenu();
            }
        }
    });
    
    // تحديث وقت الجلسة كل دقيقة
    setInterval(updateSessionTime, 60000);
}

// فتح القائمة الجانبية
function openSideMenu() {
    if (sideMenu) {
        sideMenu.classList.add('open');
        menuOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// إغلاق القائمة الجانبية
function closeSideMenu() {
    if (sideMenu) {
        sideMenu.classList.remove('open');
        menuOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// فتح نافذة الصلاة التفصيلية
function openPrayerModal(prayerKey) {
    currentPrayer = prayerKey;
    selectedStatus = '';
    naflStatuses = {};
    
    // تحديث عنوان النافذة
    const modalTitle = document.getElementById('modalPrayerName');
    if (modalTitle && prayerData[prayerKey]) {
        modalTitle.innerHTML = `
            <i class="fas ${prayerData[prayerKey].icon}" style="color: ${prayerData[prayerKey].color}"></i>
            <span>${prayerData[prayerKey].name}</span>
        `;
    }
    
    // تحديث وقت الصلاة
    const prayerTimeElement = document.getElementById('modalPrayerTime');
    if (prayerTimeElement && prayerData[prayerKey]) {
        prayerTimeElement.textContent = prayerData[prayerKey].time;
    }
    
    // إعادة تعيين خيارات الحالة
    document.querySelectorAll('.status-option').forEach(option => {
        option.classList.remove('active');
    });
    
    // تحميل النوافل
    loadNaflPrayers(prayerKey);
    
    // عرض النافذة
    if (prayerModalOverlay) {
        prayerModalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// إغلاق نافذة الصلاة التفصيلية
function closePrayerModal() {
    if (prayerModalOverlay) {
        prayerModalOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// اختيار حالة الصلاة
function selectStatus(status) {
    selectedStatus = status;
    
    // تحديث الواجهة
    document.querySelectorAll('.status-option').forEach(option => {
        option.classList.remove('active');
    });
    
    const selectedOption = document.querySelector(`.status-option.${status}`);
    if (selectedOption) {
        selectedOption.classList.add('active');
    }
}

// تحميل النوافل
function loadNaflPrayers(prayerKey) {
    const naflList = document.getElementById('naflList');
    if (!naflList) return;
    
    naflList.innerHTML = '';
    
    const nafls = naflData[prayerKey] || [];
    
    if (nafls.length === 0) {
        naflList.innerHTML = `
            <div class="nafl-item">
                <div class="nafl-info">
                    <h4 class="nafl-name">لا توجد نوافل لهذه الصلاة</h4>
                </div>
            </div>
        `;
        return;
    }
    
    nafls.forEach((nafl, index) => {
        const naflId = `nafl_${prayerKey}_${index}`;
        naflStatuses[naflId] = 'completed'; // افتراضياً مكتملة
        
        const naflItem = document.createElement('div');
        naflItem.className = 'nafl-item';
        naflItem.innerHTML = `
            <div class="nafl-info">
                <h4 class="nafl-name">${nafl.type}</h4>
                <p class="nafl-details">${nafl.rakats} ركعات - ${nafl.time}</p>
            </div>
            <div class="nafl-status completed" 
                 onclick="toggleNaflStatus('${naflId}', this)"
                 data-nafl-id="${naflId}"></div>
        `;
        
        naflList.appendChild(naflItem);
    });
}

// تبديل حالة النافلة
function toggleNaflStatus(naflId, element) {
    naflStatuses[naflId] = naflStatuses[naflId] === 'completed' ? 'missed' : 'completed';
    element.classList.toggle('completed');
}

// حفظ حالة الصلاة
async function savePrayerStatus() {
    if (!selectedStatus) {
        showNotification('الرجاء اختيار حالة الصلاة', 'error');
        return;
    }
    
    try {
        // إرسال البيانات إلى الخادم
        const response = await fetch('../api/save_prayer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                prayer: currentPrayer,
                status: selectedStatus,
                naflStatuses: naflStatuses,
                userId: userId,
                date: currentDate
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // تحديث بطاقة الصلاة في الواجهة
            updatePrayerCard();
            
            // تحديث التقويم
            if (window.updateCalendarPrayerCount) {
                window.updateCalendarPrayerCount();
            }
            
            // إغلاق النافذة
            closePrayerModal();
            
            // إظهار رسالة نجاح
            showNotification('تم حفظ حالة الصلاة بنجاح', 'success');
        } else {
            throw new Error(data.message || 'حدث خطأ أثناء الحفظ');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('حدث خطأ أثناء حفظ البيانات', 'error');
    }
}

// تحديث بطاقة الصلاة في الواجهة
function updatePrayerCard() {
    const prayerCard = document.querySelector(`.prayer-card[data-prayer="${currentPrayer}"]`);
    if (!prayerCard) return;
    
    const statusText = {
        'missed': 'لم أصلي',
        'alone': 'صليت لوحدي',
        'mosque': 'صليت في المسجد',
        'delayed': 'متأخر'
    }[selectedStatus];
    
    const statusElement = prayerCard.querySelector('.prayer-status');
    if (statusElement) {
        statusElement.textContent = statusText;
        statusElement.className = 'prayer-status completed';
    }
    
    // إضافة تأثير
    if (prayerData[currentPrayer]) {
        prayerCard.style.borderColor = prayerData[currentPrayer].color;
        prayerCard.style.boxShadow = `0 0 0 3px ${prayerData[currentPrayer].color}40`;
        
        setTimeout(() => {
            prayerCard.style.boxShadow = '0 8px 15px -3px rgba(0, 0, 0, 0.1)';
        }, 500);
    }
    
    playNotificationSound();
}

// تحميل الصلوات للتاريخ المحدد
async function loadPrayersForDate(date) {
    try {
        const response = await fetch(`../api/get_prayers.php?date=${date}`);
        const data = await response.json();
        
        if (data.success) {
            renderPrayers(data.prayers);
            currentDate = date;
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error loading prayers:', error);
        showNotification('حدث خطأ في تحميل الصلوات', 'error');
        // عرض الصلوات الافتراضية
        renderDefaultPrayers();
    }
}

// عرض الصلوات
function renderPrayers(prayerStatusData) {
    const container = document.getElementById('prayersContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    Object.keys(prayerData).forEach(prayerKey => {
        const prayer = prayerData[prayerKey];
        const status = prayerStatusData[prayerKey] || 'pending';
        
        const statusText = {
            'missed': 'لم أصلي',
            'alone': 'صليت لوحدي',
            'mosque': 'صليت في المسجد',
            'delayed': 'متأخر',
            'pending': 'قيد الانتظار'
        }[status];
        
        const statusClass = status === 'pending' ? 'pending' : 'completed';
        
        const prayerCard = document.createElement('div');
        prayerCard.className = 'prayer-card';
        prayerCard.dataset.prayer = prayerKey;
        prayerCard.onclick = () => openPrayerModal(prayerKey);
        
        prayerCard.innerHTML = `
            <div class="prayer-info">
                <div class="prayer-icon" style="color: ${prayer.color}">
                    <i class="fas ${prayer.icon}"></i>
                </div>
                <div class="prayer-details">
                    <h3 class="prayer-name">${prayer.name}</h3>
                    <p class="prayer-time">وقت الصلاة: ${prayer.time}</p>
                </div>
            </div>
            <div class="prayer-status ${statusClass}">
                ${statusText}
            </div>
        `;
        
        container.appendChild(prayerCard);
    });
}

// عرض الصلوات الافتراضية
function renderDefaultPrayers() {
    const container = document.getElementById('prayersContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    Object.keys(prayerData).forEach(prayerKey => {
        const prayer = prayerData[prayerKey];
        const status = prayerStatus[prayerKey] || 'pending';
        
        const statusText = {
            'missed': 'لم أصلي',
            'alone': 'صليت لوحدي',
            'mosque': 'صليت في المسجد',
            'delayed': 'متأخر',
            'pending': 'قيد الانتظار'
        }[status];
        
        const statusClass = status === 'pending' ? 'pending' : 'completed';
        
        const prayerCard = document.createElement('div');
        prayerCard.className = 'prayer-card';
        prayerCard.dataset.prayer = prayerKey;
        prayerCard.onclick = () => openPrayerModal(prayerKey);
        
        prayerCard.innerHTML = `
            <div class="prayer-info">
                <div class="prayer-icon" style="color: ${prayer.color}">
                    <i class="fas ${prayer.icon}"></i>
                </div>
                <div class="prayer-details">
                    <h3 class="prayer-name">${prayer.name}</h3>
                    <p class="prayer-time">وقت الصلاة: ${prayer.time}</p>
                </div>
            </div>
            <div class="prayer-status ${statusClass}">
                ${statusText}
            </div>
        `;
        
        container.appendChild(prayerCard);
    });
}

// إظهار إشعار
function showNotification(message, type) {
    // إنشاء عنصر الإشعار
    const notification = document.createElement('div');
    notification.className = `notification ${type === 'error' ? 'error' : ''}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // إضافة الأنماط
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        animation: slideDown 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    document.body.appendChild(notification);
    
    // إزالة الإشعار بعد 3 ثوانٍ
    setTimeout(() => {
        notification.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// تشغيل صوت الإشعار
function playNotificationSound() {
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    } catch (e) {
        console.log('لا يمكن تشغيل الصوت:', e);
    }
}

// إدارة وقت الجلسة
function updateSessionTime() {
    if (!sessionIndicator) return;
    
    const sessionExpiry = document.body.dataset.sessionExpiry || '';
    if (!sessionExpiry) return;
    
    const expiryDate = new Date(sessionExpiry);
    const now = new Date();
    const diffHours = Math.floor((expiryDate - now) / (1000 * 60 * 60));
    
    if (diffHours < 24) {
        sessionIndicator.innerHTML = `
            <span class="session-dot" style="background: #f59e0b;"></span>
            <span>الجلسة تنتهي خلال ${diffHours} ساعة</span>
        `;
    }
}

// التنقل بين الصفحات
function navigateTo(page) {
    closeSideMenu();
    showNotification(`سيتم التوجه إلى صفحة ${page}`, 'success');
    // يمكن استبدال هذا بالتنقل الفعلي
    // window.location.href = page + '.php';
}

// تسجيل الخروج
function logout() {
    if (confirm('هل أنت متأكد من تسجيل الخروج؟')) {
        window.location.href = 'logout.php';
    }
}

// جعل الدوال متاحة عالمياً
window.openPrayerModal = openPrayerModal;
window.closePrayerModal = closePrayerModal;
window.selectStatus = selectStatus;
window.toggleNaflStatus = toggleNaflStatus;
window.savePrayerStatus = savePrayerStatus;
window.navigateTo = navigateTo;
window.logout = logout;
window.loadPrayersForDate = loadPrayersForDate;