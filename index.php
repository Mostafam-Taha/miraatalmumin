<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/config.php';
require_once 'api/hijri_date.php';

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// اليوم يبدأ من 00:00 (12:00 AM)
$selected_date = isset($_GET['date']) ? $_GET['date'] : $today;

// التحقق إذا كان التاريخ مستقبلياً
$today_timestamp = strtotime($today);
$selected_timestamp = strtotime($selected_date);
$is_future_date = ($selected_timestamp > $today_timestamp);

if ($is_future_date) {
    $prayer_records = [];
    $nawafil_records = [];
} else {
    $stmt = $pdo->prepare("SELECT prayer_name, status FROM prayer_records WHERE user_id = :user_id AND date = :date");
    $stmt->execute(['user_id' => $user_id, 'date' => $selected_date]);
    $prayer_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT prayer_name, nawafil_type, rakat_count FROM nawafil_records WHERE user_id = :user_id AND date = :date");
    $stmt->execute(['user_id' => $user_id, 'date' => $selected_date]);
    $nawafil_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$hijri_date = convertToHijri($selected_date);

$prayer_status = [];
foreach ($prayer_records as $record) {
    $prayer_status[$record['prayer_name']] = $record['status'];
}

$prayer_nawafil = [];
foreach ($nawafil_records as $record) {
    $prayer_nawafil[$record['prayer_name']][$record['nawafil_type']] = $record['rakat_count'];
}

$prayer_times = [
    'الفجر' => '05:30',
    'الظهر' => '12:30',
    'العصر' => '15:45',
    'المغرب' => '18:15',
    'العشاء' => '19:45'
];

$nawafil_info = [
    'الفجر' => ['قبل' => 2, 'بعد' => 0],
    'الظهر' => ['قبل' => 4, 'بعد' => 2],
    'العصر' => ['قبل' => 0, 'بعد' => 0],
    'المغرب' => ['قبل' => 0, 'بعد' => 2],
    'العشاء' => ['قبل' => 0, 'بعد' => 2]
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/prayer.css">
    <title>الرئيسية | مرآة المؤمن</title>
    <style>
        
        .future-date {
            opacity: 0.6;
            background-color: #f3f4f6 !important;
            border: 1px dashed #d1d5db !important;
            cursor: not-allowed;
        }
    </style>
    <script>
        // تعريف الدوال هنا في البداية
        function changeDate(days) {
            const date = new Date(selectedDate);
            date.setDate(date.getDate() + days);
            
            // التحقق إذا كان التاريخ الجديد مستقبلياً
            const newDateString = date.toISOString().split('T')[0];
            const newDateObj = new Date(newDateString);
            const todayObj = new Date(today);
            
            if (newDateObj > todayObj) {
                alert('لا يمكن تسجيل الصلوات لتاريخ مستقبلي. يمكنك فقط عرض الصلوات.');
                return;
            }
            
            selectDate(newDateString);
        }
        
        function selectDate(date) {
            // تحديث الصفحة بالتاريخ الجديد
            window.location.href = `index.php?date=${date}`;
        }
        
        function goToToday() {
            // العودة إلى اليوم الحالي
            window.location.href = `index.php?date=${today}`;
        }
    </script>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <ul class="nav-list">
                <li><a href="setting.php" class="bi bi-gear-fill"></a></li>
                <li><a href="statistics.php" class="bi bi-list"></a></li>
            </ul>
            <ul class="nav-list">
                <li><span style="font-size: 16px;">مرحباً <?php echo $_SESSION['user_name']; ?></span></li>
            </ul>
        </nav>
    </header>

    <div class="date-header" style="display: none;">
        <h2>اليوم: <?php echo date('Y-m-d', strtotime($selected_date)); ?></h2>
        <?php if ($hijri_date): ?>
        <div class="hijri"><?php echo $hijri_date; ?></div>
        <?php endif; ?>
    </div>

    <div class="date-navigation">
        <div class="date-controls">
            <button class="nav-btn" onclick="changeDate(-1)"><i class="bi bi-chevron-right"></i></button>
            <button class="nav-btn nt-one" onclick="goToToday()">اليوم</button>
            <button class="nav-btn" onclick="changeDate(1)"><i class="bi bi-chevron-left"></i></button>
        </div>
        
        <div class="date-selector">
            <div class="date-grid" id="dateGrid">
                <!-- سيتم ملؤه بالجافاسكريبت -->
            </div>
        </div>
    </div>

    <section class="content" style="padding-bottom: 0px;">
        <?php foreach ($prayer_times as $prayer => $time): ?>
        <div class="prayer-list">
            <div class="card-py" data-prayer="<?php echo $prayer; ?>">
                <div class="lift">
                    <h3 class="title-py"><?php echo $prayer; ?></h3>
                    <p class="time-py"><?php echo $time; ?></p>
                    <?php if(isset($prayer_status[$prayer])): ?>
                        <span class="status-badge" style="font-size: 12px; color: #666;">
                            <?php 
                            $status_text = [
                                'prayed_in_mosque' => 'في المسجد',
                                'prayed_alone' => 'منفرد',
                                'not_prayed' => 'لم أصل',
                                'delayed' => 'متأخر'
                            ];
                            echo $status_text[$prayer_status[$prayer]] ?? '';
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="right">
                    <?php if(isset($prayer_nawafil[$prayer])): ?>
                        <div class="nawafil-badge">
                            <?php 
                            $nawafil_text = [];
                            foreach ($prayer_nawafil[$prayer] as $type => $rakat) {
                                $nawafil_text[] = "$rakat $type";
                            }
                            echo implode(' + ', $nawafil_text);
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="check <?php echo isset($prayer_status[$prayer]) ? 'prayed' : ''; ?>" 
                         id="check-<?php echo $prayer; ?>"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </section>

    <!-- Modal لتسجيل الصلاة -->
    <div class="modal" id="prayerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalPrayerName">الصلاة</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <div class="status-options">
                    <label class="status-option">
                        <input type="radio" name="prayerStatus" value="prayed_in_mosque">
                        <span>صليت في المسجد</span>
                    </label>
                    <label class="status-option">
                        <input type="radio" name="prayerStatus" value="prayed_alone">
                        <span>صليت منفرد</span>
                    </label>
                    <label class="status-option">
                        <input type="radio" name="prayerStatus" value="not_prayed">
                        <span>لم أصل</span>
                    </label>
                    <label class="status-option">
                        <input type="radio" name="prayerStatus" value="delayed">
                        <span>متأخر</span>
                    </label>
                </div>
                
                <div class="nawafil-section">
                    <h4 class="section-title">النوافل</h4>
                    <div class="nawafil-options" id="nawafilOptions">
                        <!-- سيتم ملؤه بالجافاسكريبت -->
                    </div>
                </div>
            </div>
            
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal()">إلغاء</button>
                <button class="btn btn-primary" onclick="savePrayerRecord()">حفظ</button>
            </div>
        </div>
    </div>

    <section class="content" style="padding: 8px 10px 70px 10px;">
        <!-- بطاقات الصلاة تبقى كما هي -->
        <?php foreach ($prayer_times as $prayer => $time): ?>
        <div class="prayer-list">
            <!-- ... -->
        </div>
        <?php endforeach; ?>
        
        <!-- زر التغذية الراجعة في المنتصف -->
        <div class="feedback-link-container">
            <button class="feedback-link" onclick="openFeedbackModal()">
                <i class="bi bi-chat-left-text"></i>
                تقييم واقتراحات
            </button>
        </div>
    </section>

    <footer class="footer">
        <nav class="nav-foot">
            <ul class="list-foot">
                <li class="item-foot"><a href="index.php" class="bi bi-house-fill active"></a></li>
                <li class="item-foot"><a href="statistics.php" class="bi bi-bar-chart-fill"></a></li>
                <li class="item-foot"><a href="reminder.php" class="bi bi-bell-fill"></a></li>
                <li class="item-foot"><a href="profile.php" class="bi bi-person-fill"></a></li>
            </ul>
        </nav>
    </footer>


        <!-- نافذة التغذية الراجعة -->
    <div class="feedback-modal" id="feedbackModal" >
        <div class="feedback-content">
            <div class="feedback-header">
                <button class="feedback-close" onclick="closeFeedbackModal()">&times;</button>
                <h3 id="feedbackTitle">تقييم واقتراحات</h3>
                <p id="feedbackSubtitle">شاركنا رأيك لنساعدك بشكل أفضل</p>
            </div>
            
            <div class="feedback-body" id="feedbackForm">
                <!-- رسالة المعلومات -->
                <div class="message-info">
                    <i class="bi bi-info-circle"></i>
                    <span>نقدر ملاحظاتك ونعدك بالتحسين المستمر</span>
                </div>
                
                <!-- اختيار نوع التغذية الراجعة -->
                <div class="feedback-type-selector">
                    <button type="button" class="feedback-type-btn" onclick="selectFeedbackType('suggestion')">
                        <i class="bi bi-lightbulb"></i>
                        <span>اقتراح</span>
                    </button>
                    <button type="button" class="feedback-type-btn" onclick="selectFeedbackType('complaint')">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>شكوى</span>
                    </button>
                    <button type="button" class="feedback-type-btn" onclick="selectFeedbackType('bug')">
                        <i class="bi bi-bug"></i>
                        <span>خطأ</span>
                    </button>
                    <button type="button" class="feedback-type-btn" onclick="selectFeedbackType('thanks')">
                        <i class="bi bi-heart"></i>
                        <span>شكر</span>
                    </button>
                </div>
                
                <!-- حقل الرسالة -->
                <div class="feedback-message-container">
                    <textarea 
                        class="feedback-textarea" 
                        id="feedbackMessage" 
                        placeholder="اكتب رسالتك هنا... (اختياري)"
                        maxlength="1000"></textarea>
                    <div style="text-align: left; margin-top: 5px; font-size: 12px; color: #6b7280;">
                        <span id="charCount">0</span>/1000 حرف
                    </div>
                </div>
                
                <!-- أزرار الإجراء -->
                <div class="feedback-actions">
                    <button class="feedback-cancel-btn" onclick="closeFeedbackModal()">
                        إلغاء
                    </button>
                    <button class="feedback-submit-btn" id="submitFeedbackBtn" onclick="submitFeedback()" disabled>
                        <i class="bi bi-send"></i>
                        إرسال
                    </button>
                </div>
            </div>
            
            <!-- عرض رسالة النجاح -->
            <div class="feedback-success" id="feedbackSuccess" style="display: none;">
                <i class="bi bi-check-circle"></i>
                <h4>تم الإرسال بنجاح!</h4>
                <p>شكراً لك على مشاركة رأيك. سنعمل على تحسين التطبيق بناءً على ملاحظاتك.
                    سيتم التواصل معك على البريد الإلكتروني الخاص بك.
                </p>
                <button class="feedback-cancel-btn" onclick="closeFeedbackModal()" style="margin-top: 20px;">
                    إغلاق
                </button>
            </div>
        </div>
    </div>


    <script>
        // تعريف المتغيرات العالمية
        let currentPrayer = '';
        let selectedDate = '<?php echo $selected_date; ?>';
        let today = '<?php echo $today; ?>';
        let selectedNawafil = [];
        let nawafilInfo = <?php echo json_encode($nawafil_info); ?>;
        
        // توليد التواريخ للشريط
        function generateDateGrid() {
            const grid = document.getElementById('dateGrid');
            grid.innerHTML = '';
            
            const days = ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'];
            const todayObj = new Date(today);
            const selectedDateObj = new Date(selectedDate);
            
            // إنشاء 15 يوماً (7 أيام قبل، اليوم الحالي، 7 أيام بعد)
            for (let i = -7; i <= 7; i++) {
                const date = new Date(selectedDate);
                date.setDate(date.getDate() + i);
                
                const dateItem = document.createElement('div');
                dateItem.className = 'date-item';
                
                // تحديد إذا كان هذا التاريخ هو المحدد
                const dateString = date.toISOString().split('T')[0];
                if (dateString === selectedDate) {
                    dateItem.classList.add('selected');
                }
                
                // تحديد إذا كان هذا التاريخ هو اليوم الحالي
                if (dateString === today) {
                    dateItem.classList.add('today-marker');
                }
                
                // تحديد إذا كان هذا التاريخ مستقبلياً
                const dateTimestamp = date.getTime();
                const todayTimestamp = todayObj.getTime();
                if (dateTimestamp > todayTimestamp) {
                    dateItem.classList.add('future-date');
                }
                
                const dayName = days[date.getDay()];
                const dateNumber = date.getDate();
                const monthNames = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                                   'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                const monthName = monthNames[date.getMonth()];
                
                dateItem.innerHTML = `
                    <div class="date-day">${dayName}</div>
                    <div class="date-number">${dateNumber}</div>
                    <div class="date-month">${monthName.substring(0, 7)}</div>
                `;
                
                dateItem.onclick = function() {
                    if (this.classList.contains('future-date')) {
                        alert('لا يمكن تسجيل الصلوات لتاريخ مستقبلي. يمكنك فقط عرض الصلوات.');
                        return;
                    }
                    selectDate(dateString);
                };
                
                grid.appendChild(dateItem);
            }
            
            // التمرير إلى التاريخ المحدد
            const selectedItem = grid.querySelector('.selected');
            if (selectedItem) {
                selectedItem.scrollIntoView({ behavior: 'smooth', inline: 'center' });
            }
        }
        
        // إضافة حدث النقر على بطاقات الصلاة
        document.querySelectorAll('.card-py').forEach(card => {
            card.addEventListener('click', function() {
                const prayer = this.dataset.prayer;
                
                // التحقق إذا كان التاريخ مستقبلياً
                const selectedDate = '<?php echo $selected_date; ?>';
                const today = '<?php echo $today; ?>';
                
                if (selectedDate > today) {
                    alert('لا يمكن تسجيل الصلوات لتاريخ مستقبلي. يمكنك فقط عرض الصلوات.');
                    return;
                }
                
                openPrayerModal(prayer);
            });
        });

        
        function openPrayerModal(prayer) {
            currentPrayer = prayer;
            document.getElementById('modalPrayerName').textContent = prayer;
            
            // إعداد خيارات النوافل
            const nawafilDiv = document.getElementById('nawafilOptions');
            nawafilDiv.innerHTML = '';
            selectedNawafil = [];
            
            const nawafil = nawafilInfo[prayer];
            
            if (nawafil['قبل'] > 0) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'nawafil-btn';
                btn.textContent = `${nawafil['قبل']} ركعات قبل`;
                btn.dataset.type = 'قبل';
                btn.dataset.rakat = nawafil['قبل'];
                btn.onclick = function() {
                    this.classList.toggle('selected');
                    toggleNawafil(this.dataset.type, this.dataset.rakat);
                };
                nawafilDiv.appendChild(btn);
            }
            
            if (nawafil['بعد'] > 0) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'nawafil-btn';
                btn.textContent = `${nawafil['بعد']} ركعات بعد`;
                btn.dataset.type = 'بعد';
                btn.dataset.rakat = nawafil['بعد'];
                btn.onclick = function() {
                    this.classList.toggle('selected');
                    toggleNawafil(this.dataset.type, this.dataset.rakat);
                };
                nawafilDiv.appendChild(btn);
            }
            
            // تحديد الحالة الحالية إذا كانت موجودة
            fetch(`api/get_prayer_status.php?date=${selectedDate}&prayer=${prayer}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.record) {
                        const statusRadio = document.querySelector(`input[name="prayerStatus"][value="${data.record.status}"]`);
                        if (statusRadio) {
                            statusRadio.checked = true;
                        }
                        
                        // تحديد النوافل المحفوظة
                        if (data.record.nawafil) {
                            data.record.nawafil.forEach(nawafil => {
                                const btn = nawafilDiv.querySelector(`[data-type="${nawafil.nawafil_type}"]`);
                                if (btn) {
                                    btn.classList.add('selected');
                                    toggleNawafil(nawafil.nawafil_type, nawafil.rakat_count);
                                }
                            });
                        }
                    }
                });
            
            document.getElementById('prayerModal').style.display = 'flex';
        }
        
        function toggleNawafil(type, rakat) {
            const index = selectedNawafil.findIndex(n => n.type === type);
            if (index > -1) {
                selectedNawafil.splice(index, 1);
            } else {
                selectedNawafil.push({ type, rakat: parseInt(rakat) });
            }
        }
        
        function closeModal() {
            document.getElementById('prayerModal').style.display = 'none';
            document.querySelectorAll('input[name="prayerStatus"]').forEach(radio => {
                radio.checked = false;
            });
            document.querySelectorAll('.nawafil-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            selectedNawafil = [];
        }
        
        function savePrayerRecord() {
            const statusRadio = document.querySelector('input[name="prayerStatus"]:checked');
            if (!statusRadio) {
                alert('الرجاء اختيار حالة الصلاة');
                return;
            }
            
            const status = statusRadio.value;
            
            // إرسال البيانات إلى الخادم
            fetch('api/save_prayer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    prayer_name: currentPrayer,
                    date: selectedDate,
                    status: status,
                    nawafil: selectedNawafil
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // إعادة تحميل الصفحة لعرض التحديثات
                    window.location.reload();
                } else {
                    alert('خطأ: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء الحفظ');
            });
        }
        
        // توليد شريط التواريخ عند تحميل الصفحة
        window.addEventListener('DOMContentLoaded', generateDateGrid);
        
        // تحديث اليوم الحالي كل ساعة
        setInterval(() => {
            const now = new Date();
            const currentDate = now.toISOString().split('T')[0];
            
            if (currentDate !== today) {
                // إذا تغير اليوم، نعيد تحميل الصفحة
                window.location.reload();
            }
        }, 3600000); // كل ساعة


        // تعريف المتغيرات للتغذية الراجعة
        let selectedFeedbackType = '';
        let selectedFeedbackIcon = '';
        
        // دالة فتح نافذة التغذية الراجعة
        function openFeedbackModal() {
            // إعادة تعيين النموذج
            resetFeedbackForm();
            document.getElementById('feedbackModal').style.display = 'flex';
        }
        
        // دالة إغلاق نافذة التغذية الراجعة
        function closeFeedbackModal() {
            document.getElementById('feedbackModal').style.display = 'none';
            setTimeout(resetFeedbackForm, 300);
        }
        
        // دالة إعادة تعيين النموذج
        function resetFeedbackForm() {
            selectedFeedbackType = '';
            selectedFeedbackIcon = '';
            
            // إلغاء تحديد جميع الأزرار
            document.querySelectorAll('.feedback-type-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // إعادة تعيين الرسالة
            document.getElementById('feedbackMessage').value = '';
            document.getElementById('charCount').textContent = '0';
            
            // إعادة تعيين العنوان
            document.getElementById('feedbackTitle').textContent = 'تقييم واقتراحات';
            document.getElementById('feedbackSubtitle').textContent = 'شاركنا رأيك لنساعدك بشكل أفضل';
            
            // إعادة تعيين زر الإرسال
            document.getElementById('submitFeedbackBtn').disabled = true;
            document.getElementById('submitFeedbackBtn').innerHTML = '<i class="bi bi-send"></i> إرسال';
            
            // إظهار النموذج وإخفاء رسالة النجاح
            document.getElementById('feedbackForm').style.display = 'block';
            document.getElementById('feedbackSuccess').style.display = 'none';
        }
        
        // دالة اختيار نوع التغذية الراجعة
        function selectFeedbackType(type) {
            // إلغاء تحديد جميع الأزرار
            document.querySelectorAll('.feedback-type-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            
            // تحديد الزر المختار
            const selectedBtn = document.querySelector(`.feedback-type-btn:nth-child(${getTypeIndex(type)})`);
            selectedBtn.classList.add('selected');
            
            selectedFeedbackType = type;
            
            // تحديث العنوان بناءً على النوع المختار
            const titles = {
                'suggestion': { title: 'اقتراح', subtitle: 'شاركنا أفكارك لتحسين التطبيق' },
                'complaint': { title: 'شكوى', subtitle: 'نعتذر عن أي إزعاج، كيف يمكننا المساعدة؟' },
                'bug': { title: 'تبليغ عن خطأ', subtitle: 'ساعدنا في تحسين التطبيق بالإبلاغ عن المشكلة' },
                'thanks': { title: 'شكر وتقدير', subtitle: 'نشكرك على ثقتك ودعمك لنا' }
            };
            
            document.getElementById('feedbackTitle').textContent = titles[type].title;
            document.getElementById('feedbackSubtitle').textContent = titles[type].subtitle;
            
            // تفعيل زر الإرسال
            document.getElementById('submitFeedbackBtn').disabled = false;
        }
        
        // دالة للحصول على ترتيب الزر بناءً على النوع
        function getTypeIndex(type) {
            const types = ['suggestion', 'complaint', 'bug', 'thanks'];
            return types.indexOf(type) + 1;
        }
        
        // تحديث عداد الأحرف
        document.getElementById('feedbackMessage').addEventListener('input', function() {
            const charCount = this.value.length;
            document.getElementById('charCount').textContent = charCount;
            
            // تغيير اللون إذا تجاوز الحد
            if (charCount > 1000) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = '#059669';
            }
        });
        
        // دالة إرسال التغذية الراجعة
        async function submitFeedback() {
            const message = document.getElementById('feedbackMessage').value;
            
            // التحقق من أن النوع قد تم اختياره
            if (!selectedFeedbackType) {
                alert('الرجاء اختيار نوع الرسالة');
                return;
            }
            
            // تعطيل زر الإرسال أثناء المعالجة
            const submitBtn = document.getElementById('submitFeedbackBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> جاري الإرسال...';
            
            try {
                // إرسال البيانات إلى الخادم
                const response = await fetch('api/submit_feedback.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        type: selectedFeedbackType,
                        message: message,
                        user_id: <?php echo $_SESSION['user_id']; ?>,
                        user_name: '<?php echo $_SESSION['user_name']; ?>',
                        user_email: '<?php echo $_SESSION['user_email']; ?>'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // عرض رسالة النجاح
                    document.getElementById('feedbackForm').style.display = 'none';
                    document.getElementById('feedbackSuccess').style.display = 'block';
                    
                    // إغلاق النافذة تلقائياً بعد 3 ثواني
                    setTimeout(() => {
                        closeFeedbackModal();
                    }, 3000);
                } else {
                    alert('حدث خطأ أثناء الإرسال: ' + (data.message || 'يرجى المحاولة مرة أخرى'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-send"></i> إرسال';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال بالخادم');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send"></i> إرسال';
            }
        }
        
        // إغلاق النافذة بالضغط على ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeFeedbackModal();
            }
        });
        
        // إغلاق النافذة بالضغط خارجها
        document.getElementById('feedbackModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeFeedbackModal();
            }
        });
        
    </script>
</body>
</html>