// assets/js/theme.js

class ThemeManager {
    constructor() {
        this.currentTheme = 'light';
        this.currentFontSize = 'medium';
        this.init();
    }

    // تهيئة المدير
    init() {
        this.loadUserSettings();
        this.setupEventListeners();
        this.applySavedTheme();
    }

    // تحميل إعدادات المستخدم
    loadUserSettings() {
        fetch('settings/get_settings.php', {
            credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
            if (data.theme) {
                this.currentTheme = data.theme;
                this.applyTheme(this.currentTheme);
            }
            if (data.font_size) {
                this.currentFontSize = data.font_size;
                this.applyFontSize(this.currentFontSize);
            }
        })
        .catch(error => {
            console.error('Error loading theme settings:', error);
            // استخدام الإعدادات الافتراضية
            this.applyTheme('light');
            this.applyFontSize('medium');
        });
    }

    // تطبيق المظهر
    applyTheme(theme) {
        this.currentTheme = theme;
        const actualTheme = theme === 'auto' ? this.getSystemTheme() : theme;
        
        // إزالة كلاس المظاهر السابقة
        document.body.classList.remove('light-theme', 'dark-theme');
        
        // إضافة الكلاس المناسب
        if (actualTheme === 'dark') {
            document.body.classList.add('dark-theme');
        } else {
            document.body.classList.add('light-theme');
        }
        
        // تحديث meta tag للون
        this.updateThemeMeta(actualTheme);
        
        // حفظ في localStorage للسرعة
        localStorage.setItem('theme', theme);
        localStorage.setItem('appliedTheme', actualTheme);
        
        // إرسال حدث للتطبيقات الأخرى
        this.dispatchThemeChangeEvent(actualTheme);
    }

    // تطبيق حجم الخط
    applyFontSize(size) {
        this.currentFontSize = size;
        const sizes = {
            'small': '14px',
            'medium': '16px',
            'large': '18px',
            'xlarge': '20px'
        };
        
        document.documentElement.style.fontSize = sizes[size] || '16px';
        localStorage.setItem('fontSize', size);
        
        // إضافة كلاس حجم الخط
        document.body.classList.remove('font-small', 'font-medium', 'font-large', 'font-xlarge');
        document.body.classList.add(`font-${size}`);
    }

    // الحصول على مظهر النظام
    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    // تحديث meta tag
    updateThemeMeta(theme) {
        let meta = document.querySelector('meta[name="theme-color"]');
        if (!meta) {
            meta = document.createElement('meta');
            meta.name = 'theme-color';
            document.head.appendChild(meta);
        }
        
        const colors = {
            'light': '#ffffff',
            'dark': '#121212'
        };
        
        meta.content = colors[theme] || '#ffffff';
    }

    // إرسال حدث تغيير المظهر
    dispatchThemeChangeEvent(theme) {
        const event = new CustomEvent('themeChanged', {
            detail: { theme }
        });
        document.dispatchEvent(event);
    }

    // إعداد مستمعي الأحداث
    setupEventListeners() {
        // الاستماع لتغير مظهر النظام
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.currentTheme === 'auto') {
                this.applyTheme('auto');
            }
        });

        // الاستماع لأحداث من التطبيقات الأخرى
        document.addEventListener('changeTheme', (e) => {
            if (e.detail && e.detail.theme) {
                this.applyTheme(e.detail.theme);
            }
        });
    }

    // حفظ الإعدادات في الخادم
    saveSettings(setting, value) {
        return fetch('settings/save_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                setting: setting,
                value: value
            })
        });
    }

    // تغيير المظهر
    changeTheme(theme) {
        this.saveSettings('theme', theme)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.applyTheme(theme);
                    this.showNotification('تم تغيير المظهر بنجاح', 'success');
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error changing theme:', error);
                this.showNotification('حدث خطأ في تغيير المظهر', 'error');
            });
    }

    // تغيير حجم الخط
    changeFontSize(size) {
        this.saveSettings('font_size', size)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.applyFontSize(size);
                    this.showNotification('تم تغيير حجم الخط بنجاح', 'success');
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error changing font size:', error);
                this.showNotification('حدث خطأ في تغيير حجم الخط', 'error');
            });
    }

    // عرض الإشعارات
    showNotification(message, type) {
        // يمكنك استخدام مكتبة إشعارات خاصة بك
        if (typeof showNotification === 'function') {
            showNotification(message, type);
        } else {
            alert(message);
        }
    }
}

// إنشاء نسخة عامة
window.themeManager = new ThemeManager();