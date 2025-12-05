// دالة لفتح النافذة المنبثقة
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

// دالة لإغلاق النافذة المنبثقة
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// إغلاق النافذة المنبثقة عند النقر خارجها
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// إغلاق النافذة المنبثقة بمفتاح ESC
document.onkeydown = function(event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
        document.body.style.overflow = 'auto';
    }
}

// تحديث معاينة الرمز التعبيري
const emojiInput = document.getElementById('emoji');
if (emojiInput) {
    emojiInput.addEventListener('input', function() {
        const preview = document.getElementById('emojiPreview');
        if (preview) {
            preview.textContent = this.value || '👥';
        }
    });
}

// تعيين رابط المشاركة
function setShareLink(link) {
    const shareInput = document.getElementById('shareLinkInput');
    if (shareInput) {
        shareInput.value = link;
    }
}

// نسخ رابط المشاركة
function copyShareLink() {
    const shareInput = document.getElementById('shareLinkInput');
    if (!shareInput) return;
    
    shareInput.select();
    shareInput.setSelectionRange(0, 99999);
    
    try {
        navigator.clipboard.writeText(shareInput.value).then(function() {
            alert('تم نسخ الرابط إلى الحافظة');
        }).catch(function(err) {
            document.execCommand('copy');
            alert('تم نسخ الرابط إلى الحافظة');
        });
    } catch (err) {
        document.execCommand('copy');
        alert('تم نسخ الرابط إلى الحافظة');
    }
}

// تأكيد مغادرة المجموعة
document.addEventListener('DOMContentLoaded', function() {
    const leaveForms = document.querySelectorAll('.leave-form');
    
    leaveForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const confirmed = confirm('هل أنت متأكد من مغادرة المجموعة؟');
            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
});

// تحسين عرض النافذة المنبثقة للموبايل
function adjustModalForMobile() {
    const modals = document.querySelectorAll('.modal-content');
    const isMobile = window.innerWidth <= 768;
    
    modals.forEach(modal => {
        if (isMobile) {
            modal.style.margin = '10px auto';
            modal.style.width = '95%';
            modal.style.maxHeight = '90vh';
            modal.style.overflowY = 'auto';
        } else {
            modal.style.margin = '50px auto';
            modal.style.width = '90%';
            modal.style.maxHeight = '';
        }
    });
}

// استدعاء عند تغيير حجم النافذة
window.addEventListener('resize', adjustModalForMobile);

// تنفيذ عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    adjustModalForMobile();
});