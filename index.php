<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'includes/config.php';
require_once 'api/hijri_date.php';

$user_id = $_SESSION['user_id'];
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$today = date('Y-m-d');

$is_future_date = ($selected_date > $today);

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

    <div class="date-navigation">
        <div class="date-controls">
            <button class="nav-btn" onclick="changeDate(-1)"><i class="bi bi-chevron-right"></i></button>
            <div class="current-date">
                <div class="gregorian-date" id="currentDate"><?php echo date('Y-m-d', strtotime($selected_date)); ?></div>
            </div>
            <button class="nav-btn" onclick="changeDate(1)"><i class="bi bi-chevron-left"></i></button>
        </div>
        
        <div class="date-selector">
            <div class="date-grid" id="dateGrid">
                <!-- سيتم ملؤه بالجافاسكريبت -->
            </div>
        </div>
    </div>

    <section class="content">
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

    <script>
        let currentPrayer = '';
        let selectedDate = '<?php echo $selected_date; ?>';
        let selectedNawafil = [];
        let nawafilInfo = <?php echo json_encode($nawafil_info); ?>;
        
        // توليد التواريخ للشريط
        function generateDateGrid() {
            const grid = document.getElementById('dateGrid');
            grid.innerHTML = '';
            
            const days = ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت'];
            
            // إنشاء 15 يوماً (7 أيام قبل، اليوم الحالي، 7 أيام بعد)
            for (let i = -7; i <= 7; i++) {
                const date = new Date(selectedDate);
                date.setDate(date.getDate() + i);
                
                const dateItem = document.createElement('div');
                dateItem.className = 'date-item';
                if (i === 0) {
                    dateItem.classList.add('selected');
                }
                
                const dayName = days[date.getDay()];
                const dateNumber = date.getDate();
                const monthNames = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                                   'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                const monthName = monthNames[date.getMonth()];
                const dateString = date.toISOString().split('T')[0];
                
                dateItem.innerHTML = `
                    <div class="date-day">${dayName}</div>
                    <div class="date-number">${dateNumber}</div>
                    <div class="date-month">${monthName.substring(0, 7)}</div>
                `;
                
                dateItem.onclick = function() {
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
        
        // وفي دالة selectDate أيضاً، يمكنك إضافة تنبيه
        function selectDate(date) {
            const today = new Date().toISOString().split('T')[0];
            
            if (date > today) {
                // alert('لا يمكن تسجيل الصلوات لتاريخ مستقبلي. يمكنك فقط عرض الصلوات السابقة أو الحالية.');
                // يمكنك إما:
                // 1. عدم السماح بالتنقل (return)
                // return;
                
                // أو 2. السماح بالتنقل ولكن مع تنبيه
                // window.location.href = `index.php?date=${date}`;
                
                // أو 3. السماح بالتنقل فقط للعرض دون التعديل
                window.location.href = `index.php?date=${date}&view=only`;
                return;
            }
            
            // تحديث الصفحة بالتاريخ الجديد
            window.location.href = `index.php?date=${date}`;
        }
        
        function changeDate(days) {
            const date = new Date(selectedDate);
            date.setDate(date.getDate() + days);
            selectDate(date.toISOString().split('T')[0]);
        }
        
        // events.js (أو ضمن الـ script في الصفحة)
        document.querySelectorAll('.card-py').forEach(card => {
            card.addEventListener('click', function() {
                const prayer = this.dataset.prayer;
                
                // التحقق إذا كان التاريخ مستقبلياً
                const selectedDate = '<?php echo $selected_date; ?>';
                const today = new Date().toISOString().split('T')[0]; // التاريخ الحالي بصيغة YYYY-MM-DD
                
                if (selectedDate > today) {
                    alert('لا يمكن تسجيل الصلوات لتاريخ مستقبلي');
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
    </script>
</body>
</html>