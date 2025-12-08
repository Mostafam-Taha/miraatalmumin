// حفظ آخر صفحة زارها المستخدم
function saveLastPage() {
    const currentPage = window.location.pathname + window.location.search;
    
    // تجنب حفظ صفحات معينة (مثل صفحة الاشتراك)
    const excludePages = ['/pro_subscription.php', '/login.php', '/register.php'];
    const shouldExclude = excludePages.some(page => currentPage.includes(page));
    
    if (!shouldExclude) {
        fetch('save_last_page.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `page=${encodeURIComponent(currentPage)}`
        });
    }
}

// حفظ الصفحة عند تحميلها
document.addEventListener('DOMContentLoaded', saveLastPage);

// حفظ الصفحة عند الانتقال منها
window.addEventListener('beforeunload', saveLastPage);