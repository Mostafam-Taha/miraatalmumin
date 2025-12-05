// تنفيذ عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    console.log('صفحة الصلوات والنوافل جاهزة');
    
    // إضافة تأثيرات تفاعلية للصفوف
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    
    tableRows.forEach(row => {
        // تأثير عند التمرير
        row.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
            this.style.transform = 'scale(1.01)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
        
        // إضافة نقرة للصف (يمكن توسيعها لاحقاً)
        row.addEventListener('click', function() {
            this.classList.toggle('selected');
        });
    });
    
    // تحديث الوقت الحالي
    function updateDateTime() {
        const now = new Date();
        const dateOptions = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        const timeOptions = { 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        };
        
        const dateStr = now.toLocaleDateString('ar-SA', dateOptions);
        const timeStr = now.toLocaleTimeString('ar-SA', timeOptions);
        
        // يمكنك عرض التاريخ والوقت في مكان ما بالصفحة إذا أردت
        console.log('التاريخ:', dateStr, 'الوقت:', timeStr);
    }
    
    // تحديث الوقت كل ثانية
    setInterval(updateDateTime, 1000);
    updateDateTime(); // تشغيل أول مرة
    
    // زر الطباعة (يمكن إضافته لاحقاً)
    const addPrintButton = () => {
        const footer = document.querySelector('footer');
        const printBtn = document.createElement('a');
        printBtn.href = '#';
        printBtn.className = 'btn';
        printBtn.innerHTML = '🖨️ طباعة التقرير';
        printBtn.style.marginLeft = '10px';
        printBtn.style.backgroundColor = '#27ae60';
        
        printBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
        
        footer.insertBefore(printBtn, footer.firstChild);
    };
    
    // إحصائيات بسيطة
    function showStatistics() {
        const prayerRows = document.querySelectorAll('.prayers-section tbody tr');
        const nawafilRows = document.querySelectorAll('.nawafil-section tbody tr');
        
        const stats = {
            totalPrayers: prayerRows.length,
            totalNawafil: nawafilRows.length,
            mosquePrayers: document.querySelectorAll('.prayers-section .status-badge[style*="28a745"]').length,
            missedPrayers: document.querySelectorAll('.prayers-section .status-badge[style*="dc3545"]').length
        };
        
        console.log('إحصائيات:');
        console.log('- إجمالي الصلوات:', stats.totalPrayers);
        console.log('- إجمالي النوافل:', stats.totalNawafil);
        console.log('- الصلوات في المسجد:', stats.mosquePrayers);
        console.log('- الصلوات الفائتة:', stats.missedPrayers);
        
        // يمكن عرض الإحصائيات في واجهة المستخدم إذا أردت
        if (document.querySelector('.filter-buttons')) {
            const statsDiv = document.createElement('div');
            statsDiv.className = 'stats-summary';
            statsDiv.innerHTML = `
                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <h3 style="color: #2c3e50; margin-bottom: 10px;">ملخص إحصائي</h3>
                    <div style="display: flex; justify-content: space-around; flex-wrap: wrap;">
                        <div style="text-align: center; margin: 5px;">
                            <div style="font-size: 1.5rem; color: #3498db;">${stats.totalPrayers}</div>
                            <div>إجمالي الصلوات</div>
                        </div>
                        <div style="text-align: center; margin: 5px;">
                            <div style="font-size: 1.5rem; color: #e74c3c;">${stats.missedPrayers}</div>
                            <div>صلوات فائتة</div>
                        </div>
                        <div style="text-align: center; margin: 5px;">
                            <div style="font-size: 1.5rem; color: #2ecc71;">${stats.mosquePrayers}</div>
                            <div>صلوات في المسجد</div>
                        </div>
                        <div style="text-align: center; margin: 5px;">
                            <div style="font-size: 1.5rem; color: #9b59b6;">${stats.totalNawafil}</div>
                            <div>إجمالي النوافل</div>
                        </div>
                    </div>
                </div>
            `;
            
            document.querySelector('header').appendChild(statsDiv);
        }
    }
    
    // تنفيذ الوظائف
    addPrintButton();
    showStatistics();
    
    // فلترة إضافية من خلال JavaScript
    const filterInput = document.createElement('input');
    filterInput.type = 'text';
    filterInput.placeholder = '🔍 بحث في الصلوات...';
    filterInput.style.cssText = `
        padding: 10px;
        margin: 10px 0;
        width: 100%;
        max-width: 400px;
        border: 2px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
    `;
    
    // إضافة حقل البحث إذا كان هناك أكثر من 5 صفوف
    if (tableRows.length > 5) {
        document.querySelector('.prayers-section h2').parentNode.insertBefore(filterInput, document.querySelector('.table-container'));
        
        filterInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.prayers-section tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
    
    // زر تحديث البيانات
    const refreshBtn = document.createElement('a');
    refreshBtn.href = '#';
    refreshBtn.className = 'btn';
    refreshBtn.innerHTML = '🔄 تحديث';
    refreshBtn.style.marginLeft = '10px';
    refreshBtn.style.backgroundColor = '#f39c12';
    
    refreshBtn.addEventListener('click', function(e) {
        e.preventDefault();
        location.reload();
    });
    
    document.querySelector('footer').insertBefore(refreshBtn, document.querySelector('.back-btn'));
});