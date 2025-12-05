<?php
function convertToHijri($gregorian_date) {
    $date = new DateTime($gregorian_date);
    $year = (int)$date->format('Y');
    $month = (int)$date->format('m');
    $day = (int)$date->format('d');
    
    // خوارزمية تحويل مبسطة (للاستخدام الحقيقي، استخدم مكتبة مثل Ummalqura)
    $jd = gregoriantojd($month, $day, $year);
    $hijri = jdtogregorian($jd);
    
    // أسماء الأشهر الهجرية
    $hijri_months = [
        'محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني',
        'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان',
        'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'
    ];
    
    // في البيئة الحقيقية، استخدم مكتبة دقيقة للتحويل
    // هذه دالة مبسطة للعرض فقط
    $hijri_month = $hijri_months[($month - 1) % 12];
    $hijri_day = $day;
    $hijri_year = $year - 579;
    
    return "$hijri_day $hijri_month $hijri_year هـ";
}
?>