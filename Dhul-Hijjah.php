<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>صيام العشر الأوائل من ذي الحجة</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(145deg, #f5f2e8 0%, #e8e2d4 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 650px;
            width: 100%;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 48px;
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1), 0 5px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .header {
            background: #1e3a2f;
            color: #f9e7c2;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 1.9rem;
            letter-spacing: -0.5px;
        }

        .header p {
            margin: 10px 0 0;
            opacity: 0.85;
            font-size: 1rem;
        }

        .today-badge {
            background: #d4af37;
            color: #1e3a2f;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 60px;
            display: inline-block;
            margin-top: 15px;
            font-size: 0.9rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .days-list {
            padding: 20px 25px;
        }

        .day-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fefaf0;
            margin: 12px 0;
            padding: 14px 20px;
            border-radius: 60px;
            border-right: 8px solid #d4b87a;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .day-info {
            display: flex;
            flex-direction: column;
        }

        .day-number {
            font-size: 1.4rem;
            font-weight: 800;
            color: #2c4b3e;
        }

        .day-date {
            font-size: 0.75rem;
            color: #8b7a5b;
            margin-top: 4px;
        }

        .fazl {
            font-size: 0.7rem;
            background: #f0e3c6;
            padding: 2px 10px;
            border-radius: 30px;
            color: #8b5e2e;
            width: fit-content;
            margin-top: 5px;
        }

        .check-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-badge {
            font-size: 0.7rem;
            padding: 4px 12px;
            border-radius: 50px;
            background: #eee6d8;
            color: #5a4a2e;
        }

        input[type="checkbox"] {
            width: 24px;
            height: 24px;
            cursor: pointer;
            accent-color: #2c7a4d;
            transform: scale(1.1);
        }

        input[type="checkbox"]:disabled {
            cursor: not-allowed;
            opacity: 0.5;
            accent-color: #aaa;
        }

        .message-box {
            background: #f5e6d3;
            margin: 15px 25px 25px;
            padding: 15px;
            border-radius: 40px;
            text-align: center;
            font-size: 0.85rem;
            color: #7a5a3a;
            border: 1px solid #f0ddc0;
        }

        .footer-note {
            background: #e9e0d0;
            padding: 15px;
            text-align: center;
            font-size: 0.7rem;
            color: #5b4b2e;
        }

        @media (max-width: 500px) {
            .day-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                border-radius: 28px;
            }
            .check-area {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🌟 أيام العشر الأوائل من ذي الحجة</h1>
        <p>صيامها من أفضل الأعمال الصالحة</p>
        <div class="today-badge" id="todayDisplay">جاري تحديد اليوم...</div>
    </div>

    <div class="days-list" id="daysContainer">
        <!-- سيتم عرض الأيام عبر الجافاسكريبت -->
    </div>

    <div class="message-box" id="infoMessage">
        ⚠️ لا يمكنك وضع علامة (✓) على يوم لم يأتي بعد أو يوم قد مضى نهائياً.<br>
        ✅ يمكنك التأشير على اليوم الحالي فقط (لمن لم يصمه بعد).
    </div>
    <div class="footer-note">
        📅 تقويم أم القرى /可根据 الرؤية | تطبيق لنية الصيام
    </div>
</div>

<script>
    // ---------- الإعدادات الأساسية ----------
    // هنا نحدد تاريخ أول يوم من ذو الحجة (يمكنك تعديله حسب بلدك)
    // مثال: لو أول يوم صيام هو 1 ذو الحجة 1446 الموافق لتاريخ معين
    // لنفترض أن 1 ذو الحجة = 28 مايو 2025 (هذا افتراض، أنت غير التاريخ حسب رؤية بلدك)
    // لكن الأفضل جعله ديناميكياً: سأضع التاريخ الذي تريده، غير السطر التالي:
    
    const FIRST_DAY_OF_DHU_AL_HIJJAH = new Date(2025, 4, 28); // ملاحظة: الشهر يبدأ من 0 -> 4 = مايو. (سنة 2025, شهر مايو, اليوم 28)
    // هام: يمكنك تغيير هذه القيمة إلى أول يوم صيام عندكم (1 ذو الحجة).
    
    // الأيام العشرة: من 1 إلى 10 ذو الحجة
    const hijriDays = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    
    // دالة لتنسيق التاريخ بشكل جميل (ميلادي)
    function formatDate(date) {
        const options = { year: 'numeric', month: 'numeric', day: 'numeric' };
        return date.toLocaleDateString('ar-EG', options);
    }
    
    // دالة لإرجاع اليوم الحالي (بدون وقت)
    function getTodayDate() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return today;
    }
    
    // إنشاء قائمة الأيام مع تحديد الحالة: مسموح؟ ممنوع؟
    function buildDays() {
        const today = getTodayDate();
        const daysData = [];
        
        for (let i = 0; i < hijriDays.length; i++) {
            const dayNumber = hijriDays[i];
            // التاريخ الميلادي لهذا اليوم من ذو الحجة
            const currentDate = new Date(FIRST_DAY_OF_DHU_AL_HIJJAH);
            currentDate.setDate(FIRST_DAY_OF_DHU_AL_HIJJAH.getDate() + i);
            currentDate.setHours(0, 0, 0, 0);
            
            let status = "future"; // مستقبلي
            let canCheck = false;
            let statusText = "";
            
            if (currentDate.getTime() === today.getTime()) {
                status = "today";
                canCheck = true;
                statusText = "✅ اليوم (مسموح)";
            } else if (currentDate < today) {
                status = "past";
                canCheck = false;
                statusText = "📆 مضى (لا يمكن)";
            } else if (currentDate > today) {
                status = "future";
                canCheck = false;
                statusText = "⏳ لم يأتِ بعد (ممنوع)";
            }
            
            daysData.push({
                day: dayNumber,
                dateObj: currentDate,
                dateStr: formatDate(currentDate),
                canCheck: canCheck,
                statusText: statusText,
                status: status
            });
        }
        return daysData;
    }
    
    // عرض الأيام في الصفحة
    function renderDays() {
        const container = document.getElementById('daysContainer');
        const todayDisplaySpan = document.getElementById('todayDisplay');
        const days = buildDays();
        
        // عرض تاريخ اليوم الحقيقي
        const todayDate = getTodayDate();
        todayDisplaySpan.innerHTML = `📌 اليوم هو: ${formatDate(todayDate)} (حسب جهازك)`;
        
        let html = '';
        for (let i = 0; i < days.length; i++) {
            const d = days[i];
            // وصف فضل اليوم (خاص بيوم عرفة مثلاً)
            let specialText = '';
            if (d.day === 9) specialText = ' (🏆 يوم عرفة - أفضل يوم للصيام)';
            else if (d.day === 1) specialText = ' (بداية العشر المباركة)';
            else if (d.day === 8) specialText = ' (يوم التروية)';
            
            const disabledAttr = d.canCheck ? '' : 'disabled';
            
            html += `
                <div class="day-card">
                    <div class="day-info">
                        <div class="day-number">اليوم ${d.day} من ذو الحجة ${specialText}</div>
                        <div class="day-date">📅 الموافق: ${d.dateStr}</div>
                        <div class="fazl">✨ صيامه سنة مؤكدة لغير الحاج</div>
                    </div>
                    <div class="check-area">
                        <span class="status-badge">${d.statusText}</span>
                        <input type="checkbox" id="check_day_${d.day}" ${disabledAttr} data-day="${d.day}" data-date="${d.dateStr}">
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html;
        
        // إضافة حدث الاستماع لتخزين ما يختاره المستخدم في localStorage (اختياري حتى لا يضيع السجل عند التحديث)
        // مع مراعاة أن المربعات المفعلة فقط هي التي يسمح بتغييرها (اليوم الحالي فقط)
        for (let i = 0; i < days.length; i++) {
            const d = days[i];
            const chk = document.getElementById(`check_day_${d.day}`);
            if (chk) {
                // تحميل الحالة المخزنة مسبقاً (إذا كان قد وضع علامة اليوم الحالي)
                const saved = localStorage.getItem(`fast_${d.day}_dhul`);
                if (saved === 'true' && d.canCheck) {
                    chk.checked = true;
                } else if (!d.canCheck) {
                    // تأكيد أن المربعات غير المسموحة تكون غير مفعلة ولاغية
                    chk.checked = false;
                }
                
                chk.addEventListener('change', function(e) {
                    if (d.canCheck) {
                        // فقط اليوم الحالي هو الذي يسمح بالتخزين
                        if (this.checked) {
                            localStorage.setItem(`fast_${d.day}_dhul`, 'true');
                            // رسالة صغيرة مؤقتة (اختياري)
                            showTemporaryMessage(`✅ تم تسجيل نية صيام اليوم ${d.day} من ذو الحجة`, 2000);
                        } else {
                            localStorage.removeItem(`fast_${d.day}_dhul`);
                            showTemporaryMessage(`❌ إلغاء تسجيل صيام اليوم ${d.day}`, 1500);
                        }
                    } else {
                        // أمان إضافي: لو حاول أحد تغييرها من dev tools نعيد تعطيلها
                        this.checked = false;
                        alert(`⚠️ لا يمكنك وضع علامة على هذا اليوم (${d.statusText}) لأنك لا تستطيع صيامه إلا في وقته الفعلي.`);
                    }
                });
            }
        }
    }
    
    // رسالة مساعدة صغيرة تظهر وتختفي
    let msgTimeout;
    function showTemporaryMessage(msg, duration) {
        const msgBox = document.getElementById('infoMessage');
        const originalText = msgBox.innerHTML;
        msgBox.innerHTML = `💡 ${msg}`;
        msgBox.style.backgroundColor = "#e0f0e8";
        msgBox.style.transition = "0.2s";
        if (msgTimeout) clearTimeout(msgTimeout);
        msgTimeout = setTimeout(() => {
            msgBox.innerHTML = originalText;
            msgBox.style.backgroundColor = "#f5e6d3";
        }, duration);
    }
    
    // إعادة التحقق عند منتصف الليل (لكي يتم تحديث الحالة تلقائياً لو بقي المستخدم على الصفحة)
    function checkMidnightRefresh() {
        const now = new Date();
        const night = new Date();
        night.setHours(24, 0, 0, 0);
        const msUntilMidnight = night.getTime() - now.getTime();
        setTimeout(() => {
            renderDays(); // إعادة بناء الأيام بعد منتصف الليل
            checkMidnightRefresh(); // إعادة جدولة
        }, msUntilMidnight);
    }
    
    // تحميل الصفحة
    renderDays();
    checkMidnightRefresh();
</script>
</body>
</html>