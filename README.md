# miraatalmumin
# 🕌 مرآة المؤمن — Miraat Al-Mu'min

> **Arabic:** تطبيق ويب إسلامي متكامل لمتابعة الصلوات، النوافل، الصيام، والعبادات اليومية  
> **English:** A comprehensive Islamic web app for tracking prayers, Nawafil, fasting, and daily worship

---

## 📋 About the Project | نبذة عن المشروع

**Miraat Al-Mu'min (Mirror of the Believer)** is a PHP-based web application that helps Muslims track their daily acts of worship and build stronger spiritual habits. It features a fully Arabic RTL interface with Hijri calendar support, live prayer times, and detailed statistics to measure consistency and commitment.

**مرآة المؤمن** هو تطبيق ويب مبني بـ PHP يساعد المسلم على متابعة عباداته اليومية وتطوير عاداته الروحية. يوفر التطبيق واجهة عربية كاملة مع دعم التقويم الهجري، مواقيت الصلاة الحية، وإحصائيات تفصيلية لقياس المداومة والالتزام.

---

## ✨ Key Features | المميزات الرئيسية

### 🕋 Prayers | الصلوات
- Log the five daily prayers with performance type (Mosque / Alone / Missed / Delayed)
- Live prayer times based on user location via [Aladhan API](https://aladhan.com/prayer-times-api)
- Record Nawafil (voluntary prayers) before and after each prayer with rak'a count
- Track and make up missed prayers (`makeup_prayers.php`)
- Full prayer history with filtering options (`history.php`)

---

- تسجيل الصلوات الخمس يومياً مع تحديد نوع الأداء (في المسجد / منفرد / فائتة / متأخرة)
- عرض مواقيت الصلاة الحقيقية بناءً على موقع المستخدم عبر [Aladhan API](https://aladhan.com/prayer-times-api)
- تسجيل النوافل القبلية والبعدية وعدد الركعات
- متابعة قضاء الصلوات الفائتة (`makeup_prayers.php`)
- سجل تاريخي كامل للصلوات مع إمكانية الفلترة (`history.php`)

---

### 📿 Additional Worship | العبادات الإضافية
- Track optional Nawafil (Duha, Qiyam Al-Layl)
- Log fasting by type
- Manage custom additional acts of worship

---

- تتبع النوافل الاختيارية (الضحى، قيام الليل)
- تسجيل الصيام بأنواعه
- إدارة العبادات الإضافية المخصصة

---

### 📊 Statistics & Reports | الإحصائيات والتقارير
- Detailed statistics dashboard (`statistics.php`)
- Prayer streak tracking
- Weekly and monthly performance charts

---

- لوحة إحصاءات تفصيلية (`statistics.php`)
- حساب مداومة الصلاة (Prayer Streak)
- رسوم بيانية للأداء الأسبوعي والشهري

---

### 👥 Groups | المجموعات
- Create groups for collective motivation among friends and family (`groups.php`)
- إنشاء مجموعات للتحفيز الجماعي بين الأصدقاء والعائلة (`groups.php`)

### 💎 Pro Subscription | الاشتراك المميز
- Monthly and yearly plans (`pro_subscription.php`)
- Extra features for Pro subscribers (`welcome_pro.php`)

---

- خطة شهرية وسنوية (`pro_subscription.php`)
- مميزات إضافية للمشتركين (`welcome_pro.php`)

---

### 👤 Account Management | إدارة الحساب
- Secure login with multi-device session support
- Log out from a single device or all devices at once
- Profile page and settings (`profile.php`, `settings.php`)
- Privacy policy (`privacy.php`)

---

- تسجيل دخول آمن مع دعم جلسات متعددة الأجهزة
- تسجيل خروج من جهاز واحد أو جميع الأجهزة
- صفحة الملف الشخصي والإعدادات (`profile.php`, `settings.php`)
- سياسة الخصوصية (`privacy.php`)

---

### 🔌 Internal API
| File | Function |
|---|---|
| `api/get_habits.php` | Fetch user's daily habits |
| `api/log_habit.php` | Log / update a habit entry |
| `get_prayer_status.php` | Query the status of a specific prayer |

---

## 🛠️ Tech Stack | التقنيات المستخدمة

| Technology | Usage |
|---|---|
| **PHP 8+** | Backend & core logic |
| **MySQL** | Database |
| **PDO** | Secure database access |
| **HTML5 / CSS3** | User interface |
| **JavaScript (Vanilla)** | Client-side interactivity |
| **Font Awesome 6** | Icons |
| **Google Fonts (Tajawal / Cairo)** | Arabic typography |
| **Aladhan API** | Prayer times |
| **Bootstrap Icons** | Additional icons |

---

## 📁 File Structure | هيكل الملفات

```
miraat-almunin/
│
├── index.php               # Home – daily prayer logging
├── login.php               # Login & registration
├── logout.php              # Logout (single device or all)
├── force_logout.php        # Forced logout page
│
├── profile.php             # User profile & device management
├── settings.php            # Account settings
├── history.php             # Prayer & Nawafil history
├── statistics.php          # Statistics & reports
├── statiy.php              # Extended statistics
│
├── makeup_prayers.php      # Missed prayer makeup
├── worship.php             # Additional worship acts
├── Ibadat.php              # Worship dashboard
├── groups.php              # Groups & collective challenges
│
├── pro_subscription.php    # Pro subscription page
├── welcome_pro.php         # Pro subscriber welcome
├── privacy.php             # Privacy policy
│
├── get_prayer_status.php   # API: prayer status
│
├── api/
│   ├── get_habits.php      # API: fetch habits
│   ├── log_habit.php       # API: log habits
│   └── hijri_date.php      # Hijri date helper
│
├── includes/
│   ├── config.php          # Database configuration
│   └── session_config.php  # Session configuration
│
├── assets/
│   ├── css/                # Stylesheets
│   └── js/                 # JavaScript files
│
└── .htaccess               # Apache configuration
```

---

## ⚙️ Requirements | متطلبات التشغيل

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache with `mod_rewrite` enabled
- PHP extensions: `PDO`, `pdo_mysql`, `curl`

---

## 🚀 Installation | خطوات التثبيت

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/miraat-almunin.git
   cd miraat-almunin
   ```

2. **Set up the database:**
   - Create a new MySQL database
   - Import `database.sql` if available, or create tables manually

3. **Configure the database connection:**

   Open `includes/config.php` and update:
   ```php
   $host     = 'localhost';
   $dbname   = 'your_database_name';
   $username = 'your_db_username';
   $password = 'your_db_password';
   ```

4. **Deploy to a server** or run locally using XAMPP / Laragon.

5. **Open your browser** and navigate to:
   ```
   http://localhost/miraat-almunin/login.php
   ```

---

## 🗃️ Main Database Tables | الجداول الرئيسية

| Table | Description |
|---|---|
| `users` | User accounts |
| `user_sessions` | Multi-device sessions |
| `prayer_records` | Five daily prayer logs |
| `nawafil_records` | Nawafil logs |
| `additional_prayers` | Custom additional prayers |
| `fasting_records` | Fasting logs |
| `habits` | Habit definitions |
| `user_habits` | Per-user habit settings |
| `habit_logs` | Daily habit completion log |
| `pro_subscriptions` | Pro subscription records |
| `device_activity_log` | Device activity audit log |

---

## 🔒 Security | الأمان

- All database queries use **Prepared Statements** via PDO
- XSS protection with `htmlspecialchars()`
- Authentication check on every protected page
- Multi-device session management with activity logging

---

## 🌐 Interface | الواجهة

- Text direction: **RTL** (Right-to-Left)
- Language: **Arabic** (fully localized)
- Responsive design for mobile and desktop

---

## ⚠️ Before Pushing to GitHub | قبل الرفع على GitHub

> **Important:** Remove or replace any hardcoded database credentials before pushing.  
> Use environment variables or a `.env` file and add it to `.gitignore`.

```bash
# .gitignore
.env
includes/config.php
```

---

## 📄 License | الترخيص

This project is open-source for personal and educational use.  
هذا المشروع مفتوح المصدر للاستخدام الشخصي والتعليمي.

---

## 🤝 Contributing | المساهمة

Contributions are welcome!

1. Fork the project
2. Create a new branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -m 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Open a Pull Request

---

> **"Indeed, prayer has been decreed upon the believers a decree of specified times."**  
> **"إِنَّ الصَّلَاةَ كَانَتْ عَلَى الْمُؤْمِنِينَ كِتَابًا مَّوْقُوتًا"** — Quran 4:103
