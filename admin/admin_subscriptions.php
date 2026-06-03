<?php
    
ini_set('display_errors', 1);
error_reporting(E_ALL);
// إعدادات الاتصال بقاعدة البيانات
$host = 'sql207.infinityfree.com';
$dbname = 'if0_39304815_miraatalmuminif';
$username = 'if0_39304815';
$password = 'NIHOIGYPkLGsq0';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$button_color   = '#059669';
$text_color     = '#111827';
$secondary_color = '#047857';
$bg_color       = '#ffffff';

// معالجة AJAX
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'get_subscription') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT
                ps.*,
                u.name,
                u.email,
                u.profile_picture,
                u.current_prayer_streak,
                u.max_prayer_streak,
                u.created_at as user_created_at,
                u.Pro as user_pro_status
            FROM pro_subscriptions ps
            LEFT JOIN users u ON ps.user_id = u.id
            WHERE ps.id = ?");
        $stmt->execute([$id]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $subscription]);
        exit;
    }

    if ($_POST['action'] === 'update_status') {
        $id     = intval($_POST['id']);
        $status = $_POST['status'];

        if (!in_array($status, ['verified', 'rejected', 'pending'])) {
            echo json_encode(['success' => false, 'message' => 'حالة غير صالحة']);
            exit;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE pro_subscriptions SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);

            if ($status === 'verified') {
                $stmt2 = $pdo->prepare("SELECT user_id FROM pro_subscriptions WHERE id = ?");
                $stmt2->execute([$id]);
                $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $stmt3 = $pdo->prepare("UPDATE users SET Pro = 1 WHERE id = ?");
                    $stmt3->execute([$row['user_id']]);
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'تم تحديث الحالة بنجاح']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
        exit;
    }
}

// إحصائيات
$stats = [
    'total'    => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions")->fetchColumn(),
    'pending'  => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status = 'pending'")->fetchColumn(),
    'verified' => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status = 'verified'")->fetchColumn(),
    'rejected' => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status = 'rejected'")->fetchColumn(),
];

// جلب الاشتراكات
$stmt = $pdo->prepare("SELECT
        ps.*,
        u.name,
        u.email,
        u.Pro as user_pro_status
    FROM pro_subscriptions ps
    LEFT JOIN users u ON ps.user_id = u.id
    ORDER BY ps.created_at DESC
    LIMIT 50");
$stmt->execute();
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الاشتراكات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            overflow-y: scroll;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background: #f1f5f9;
            color: <?php echo $text_color; ?>;
            line-height: 1.6;
        }

        .container { max-width: 1400px; margin: 0 auto; padding: 24px; }

        /* Header */
        .header {
            background: linear-gradient(135deg, <?php echo $secondary_color; ?>, <?php echo $button_color; ?>);
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            color: white;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-icon {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
        }

        .header h1 { font-size: 24px; font-weight: 700; }
        .header p  { font-size: 14px; opacity: 0.85; margin-top: 2px; }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-box {
            background: white;
            padding: 20px 24px;
            border-radius: 14px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-box:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .stat-box.total    .stat-icon { background: #ede9fe; color: #7c3aed; }
        .stat-box.pending  .stat-icon { background: #fef3c7; color: #d97706; }
        .stat-box.verified .stat-icon { background: #d1fae5; color: #059669; }
        .stat-box.rejected .stat-icon { background: #fee2e2; color: #dc2626; }

        .stat-info {}
        .stat-label  { font-size: 13px; color: #6b7280; }
        .stat-number { font-size: 28px; font-weight: 800; line-height: 1.1; }

        .stat-box.total    .stat-number { color: #7c3aed; }
        .stat-box.pending  .stat-number { color: #d97706; }
        .stat-box.verified .stat-number { color: #059669; }
        .stat-box.rejected .stat-number { color: #dc2626; }

        /* Filters */
        .filters-bar {
            background: white;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 18px;
            border: 1.5px solid #e5e7eb;
            border-radius: 30px;
            background: white;
            cursor: pointer;
            font-size: 14px;
            font-family: 'Tajawal', sans-serif;
            font-weight: 500;
            color: #374151;
            transition: all 0.2s;
        }

        .filter-btn:hover  { border-color: <?php echo $button_color; ?>; color: <?php echo $button_color; ?>; }
        .filter-btn.active { background: <?php echo $button_color; ?>; color: white; border-color: <?php echo $button_color; ?>; }

        .search-wrap {
            margin-right: auto;
            position: relative;
        }

        .search-wrap i {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
        }

        .search-input {
            padding: 9px 40px 9px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Tajawal', sans-serif;
            width: 280px;
            transition: border-color 0.2s;
            outline: none;
        }

        .search-input:focus { border-color: <?php echo $button_color; ?>; }

        /* Table */
        .table-container {
            background: white;
            border-radius: 14px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .table-header {
            padding: 18px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-header h3 { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .table-header h3 i { color: <?php echo $button_color; ?>; }
        .row-count { font-size: 13px; color: #6b7280; background: #f1f5f9; padding: 4px 12px; border-radius: 20px; }

        table { width: 100%; border-collapse: collapse; }

        th {
            padding: 12px 20px;
            text-align: right;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
        }

        td { padding: 14px 20px; border-bottom: 1px solid #f8fafc; font-size: 14px; }
        tbody tr { transition: background 0.15s; cursor: pointer; }
        tbody tr:hover { background: #f8fafc; }
        tbody tr:last-child td { border-bottom: none; }

        .user-cell { display: flex; align-items: center; gap: 12px; }

        .avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, <?php echo $secondary_color; ?>, <?php echo $button_color; ?>);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 15px;
            flex-shrink: 0;
        }

        .user-name  { font-weight: 600; color: #111827; font-size: 14px; }
        .user-email { font-size: 12px; color: #9ca3af; margin-top: 1px; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-verified { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-pro      { background: <?php echo $button_color; ?>; color: white; font-size: 11px; padding: 2px 8px; }

        .amount { font-weight: 700; color: <?php echo $secondary_color; ?>; }

        .empty-state { text-align: center; padding: 60px 20px; color: #9ca3af; }
        .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; }

        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
            backdrop-filter: blur(4px);
        }

        .modal {
            background: white;
            border-radius: 20px;
            width: 100%; max-width: 760px;
            max-height: 90vh;
            overflow-y: auto;
            scrollbar-width: none;
            box-shadow: 0 25px 60px rgba(0,0,0,0.25);
            animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            padding: 22px 24px;
            background: linear-gradient(135deg, <?php echo $secondary_color; ?>, <?php echo $button_color; ?>);
            color: white;
            border-radius: 20px 20px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h2 { font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px; }

        .close-btn {
            width: 36px; height: 36px;
            background: rgba(255,255,255,0.2);
            border: none; border-radius: 50%;
            color: white; font-size: 18px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }

        .close-btn:hover { background: rgba(255,255,255,0.35); }

        .modal-body { padding: 24px; }

        .modal-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 18px;
            border: 1px solid #f1f5f9;
        }

        .info-card h3 {
            font-size: 14px; font-weight: 700;
            color: #374151;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center; gap: 8px;
        }

        .info-card h3 i { color: <?php echo $button_color; ?>; }

        .info-row { margin-bottom: 10px; }
        .info-label { font-size: 12px; color: #9ca3af; margin-bottom: 2px; }
        .info-value { font-size: 14px; font-weight: 500; color: #111827; }

        .receipt-img {
            width: 100%; border-radius: 10px;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .receipt-img:hover { transform: scale(1.01); }

        .extracted-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-right: 4px solid <?php echo $button_color; ?>;
            border-radius: 8px;
            padding: 14px;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-wrap;
            max-height: 180px;
            overflow-y: auto;
        }

        .rejection-box {
            margin: 0 24px 0;
            background: #fefce8;
            border: 1px solid #fde047;
            border-radius: 12px;
            padding: 16px;
            display: none;
        }

        .rejection-box h4 { font-size: 14px; font-weight: 600; color: #854d0e; margin-bottom: 10px; }

        .rejection-textarea {
            width: 100%;
            min-height: 90px;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            resize: vertical;
            outline: none;
        }

        .rejection-textarea:focus { border-color: <?php echo $button_color; ?>; }

        .modal-footer {
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            border-radius: 0 0 20px 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 11px 22px;
            border: none; border-radius: 10px;
            cursor: pointer;
            font-weight: 600; font-size: 14px;
            font-family: 'Tajawal', sans-serif;
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px;
            flex: 1;
            transition: all 0.2s;
        }

        .btn-verify { background: <?php echo $button_color; ?>; color: white; }
        .btn-verify:hover { background: <?php echo $secondary_color; ?>; transform: translateY(-1px); }

        .btn-reject { background: #ef4444; color: white; }
        .btn-reject:hover { background: #dc2626; transform: translateY(-1px); }

        .btn-close { background: #e5e7eb; color: #374151; flex: none; }
        .btn-close:hover { background: #d1d5db; }

        /* Toast */
        .toast {
            position: fixed; top: 24px; left: 50%; transform: translateX(-50%);
            padding: 12px 24px;
            border-radius: 10px;
            color: white; font-weight: 600; font-size: 14px;
            z-index: 2000;
            display: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            animation: toastIn 0.3s ease;
        }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(-50%) translateY(-10px); }
            to   { opacity: 1; transform: translateX(-50%) translateY(0); }
        }

        .toast.success { background: <?php echo $button_color; ?>; }
        .toast.error   { background: #ef4444; }

        /* Responsive */
        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .modal-grid { grid-template-columns: 1fr; }
            .modal-footer { flex-direction: column; }
            .search-input { width: 100%; }
            .search-wrap { width: 100%; }
            th:nth-child(3), td:nth-child(3),
            th:nth-child(6), td:nth-child(6) { display: none; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="header-icon"><i class="fas fa-crown"></i></div>
        <div>
            <h1>لوحة تحكم اشتراكات Pro</h1>
            <p>إدارة جميع طلبات الاشتراكات المميزة</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-box total">
            <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="stat-info">
                <div class="stat-label">إجمالي الطلبات</div>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
            </div>
        </div>
        <div class="stat-box pending">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-label">قيد الانتظار</div>
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
            </div>
        </div>
        <div class="stat-box verified">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">مفعّلة</div>
                <div class="stat-number"><?php echo $stats['verified']; ?></div>
            </div>
        </div>
        <div class="stat-box rejected">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">مرفوضة</div>
                <div class="stat-number"><?php echo $stats['rejected']; ?></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
        <button class="filter-btn active" data-filter="all">الكل</button>
        <button class="filter-btn" data-filter="pending">قيد الانتظار</button>
        <button class="filter-btn" data-filter="verified">مفعّلة</button>
        <button class="filter-btn" data-filter="rejected">مرفوضة</button>
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" class="search-input" id="searchInput" placeholder="بحث بالاسم أو البريد...">
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> قائمة الاشتراكات</h3>
            <span class="row-count"><span id="rowCount"><?php echo count($subscriptions); ?></span> طلب</span>
        </div>

        <?php if (empty($subscriptions)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>لا توجد طلبات اشتراك حتى الآن</p>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>المستخدم</th>
                    <th>رقم الهاتف</th>
                    <th>نوع الاشتراك</th>
                    <th>المبلغ</th>
                    <th>الحالة</th>
                    <th>تاريخ الطلب</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $statusLabels = ['pending' => 'قيد الانتظار', 'verified' => 'مفعّل', 'rejected' => 'مرفوض'];
            foreach ($subscriptions as $sub):
                $initials = mb_substr($sub['name'] ?? 'U', 0, 1, 'UTF-8');
            ?>
                <tr class="sub-row"
                    data-id="<?php echo intval($sub['id']); ?>"
                    data-status="<?php echo htmlspecialchars($sub['status']); ?>"
                    data-name="<?php echo mb_strtolower(htmlspecialchars($sub['name'] ?? '')); ?>"
                    data-email="<?php echo strtolower(htmlspecialchars($sub['email'] ?? '')); ?>">

                    <td>
                        <div class="user-cell">
                            <div class="avatar"><?php echo $initials; ?></div>
                            <div>
                                <div class="user-name">
                                    <?php if ($sub['user_pro_status'] == 1): ?>
                                        <span class="badge badge-pro">PRO</span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($sub['name'] ?? 'غير معروف'); ?>
                                </div>
                                <div class="user-email"><?php echo htmlspecialchars($sub['email'] ?? ''); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($sub['phone'] ?? '-'); ?></td>
                    <td><?php echo ($sub['plan_type'] === 'yearly') ? '🗓 سنوي' : '📅 شهري'; ?></td>
                    <td class="amount"><?php echo number_format($sub['amount'], 2); ?> ر.س</td>
                    <td>
                        <span class="badge badge-<?php echo $sub['status']; ?>">
                            <?php echo $statusLabels[$sub['status']] ?? $sub['status']; ?>
                        </span>
                    </td>
                    <td style="color:#6b7280;"><?php echo date('Y/m/d', strtotime($sub['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-file-invoice"></i> تفاصيل الاشتراك</h2>
            <button class="close-btn" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body" id="modalBody">
            <p style="text-align:center;color:#9ca3af;padding:30px;">جاري التحميل...</p>
        </div>
        <div class="rejection-box" id="rejectionBox">
            <h4><i class="fas fa-comment-alt"></i> سبب الرفض (اختياري)</h4>
            <textarea class="rejection-textarea" id="rejectionText" placeholder="اكتب سبب الرفض هنا..."></textarea>
        </div>
        <div class="modal-footer" id="modalFooter"></div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const modal        = document.getElementById('modal');
const modalBody    = document.getElementById('modalBody');
const modalFooter  = document.getElementById('modalFooter');
const rejectionBox = document.getElementById('rejectionBox');
const rejectionText= document.getElementById('rejectionText');
const toast        = document.getElementById('toast');
const rowCountEl   = document.getElementById('rowCount');

let currentId = null;

// فتح Modal
document.querySelectorAll('.sub-row').forEach(row => {
    row.addEventListener('click', () => {
        currentId = row.dataset.id;
        openModal(currentId);
    });
});

async function openModal(id) {
    modal.style.display = 'flex';
    modalBody.innerHTML = '<p style="text-align:center;color:#9ca3af;padding:40px;"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</p>';
    modalFooter.innerHTML = '';
    rejectionBox.style.display = 'none';
    rejectionText.value = '';

    try {
        const res    = await fetch('', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`ajax=true&action=get_subscription&id=${id}` });
        const result = await res.json();
        if (result.success && result.data) renderModal(result.data);
        else modalBody.innerHTML = '<p style="color:red;text-align:center;padding:40px;">حدث خطأ في التحميل</p>';
    } catch(e) {
        modalBody.innerHTML = '<p style="color:red;text-align:center;padding:40px;">خطأ في الاتصال</p>';
    }
}

function renderModal(d) {
    const statusColor = { pending:'#d97706', verified:'#059669', rejected:'#dc2626' };
    const statusLabel = { pending:'قيد الانتظار', verified:'مفعّل', rejected:'مرفوض' };
    const planLabel   = d.plan_type === 'yearly' ? 'سنوي' : 'شهري';
    const fmt = s => s ? new Date(s).toLocaleDateString('ar-EG') : '-';
    const fmtDt = s => s ? new Date(s).toLocaleString('ar-EG') : '-';

    modalBody.innerHTML = `
        <div class="modal-grid">
            <div class="info-card">
                <h3><i class="fas fa-user"></i> معلومات المستخدم</h3>
                <div class="info-row"><div class="info-label">الاسم</div><div class="info-value">${d.name || '-'}</div></div>
                <div class="info-row"><div class="info-label">البريد الإلكتروني</div><div class="info-value">${d.email || '-'}</div></div>
                <div class="info-row"><div class="info-label">تاريخ التسجيل</div><div class="info-value">${fmt(d.user_created_at)}</div></div>
                <div class="info-row"><div class="info-label">حالة Pro</div>
                    <div class="info-value">
                        <span class="badge ${d.user_pro_status==1?'badge-pro':'badge-rejected'}">${d.user_pro_status==1?'مفعّل':'غير مفعّل'}</span>
                    </div>
                </div>
                <div class="info-row"><div class="info-label">المتواصل الحالي</div><div class="info-value">${d.current_prayer_streak||0} يوم 🔥</div></div>
                <div class="info-row"><div class="info-label">أقصى متواصل</div><div class="info-value">${d.max_prayer_streak||0} يوم ⭐</div></div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-receipt"></i> تفاصيل الاشتراك</h3>
                <div class="info-row"><div class="info-label">رقم الهاتف</div><div class="info-value">${d.phone||'-'}</div></div>
                <div class="info-row"><div class="info-label">إنستجرام</div><div class="info-value">${d.insta_user||'-'}</div></div>
                <div class="info-row"><div class="info-label">نوع الاشتراك</div><div class="info-value">${planLabel}</div></div>
                <div class="info-row"><div class="info-label">المبلغ</div><div class="info-value amount">${parseFloat(d.amount||0).toFixed(2)} ر.س</div></div>
                <div class="info-row"><div class="info-label">تاريخ الدفع</div><div class="info-value">${fmtDt(d.payment_date)}</div></div>
                <div class="info-row"><div class="info-label">الحالة</div>
                    <div class="info-value">
                        <span class="badge badge-${d.status}">${statusLabel[d.status]||d.status}</span>
                    </div>
                </div>
            </div>
        </div>
        ${d.receipt_image ? `
        <div class="info-card" style="margin-bottom:16px;">
            <h3><i class="fas fa-image"></i> صورة الإيصال</h3>
            <img src="https://miraat-almumin.xo.je/${d.receipt_image}" class="receipt-img" onclick="window.open('${d.receipt_image}','_blank')">
        </div>` : ''}
        ${d.extracted_data ? `
        <div class="info-card">
            <h3><i class="fas fa-database"></i> البيانات المستخرجة</h3>
            <div class="extracted-box">${d.extracted_data}</div>
        </div>` : ''}
    `;

    if (d.status === 'pending') {
        modalFooter.innerHTML = `
            <button class="btn btn-verify" onclick="handleAction('verified')"><i class="fas fa-check"></i> موافقة وتفعيل Pro</button>
            <button class="btn btn-reject" onclick="showRejection()"><i class="fas fa-times"></i> رفض</button>
            <button class="btn btn-close" onclick="closeModal()">إغلاق</button>`;
    } else {
        modalFooter.innerHTML = `<button class="btn btn-close" style="flex:none" onclick="closeModal()">إغلاق</button>`;
    }
}

function showRejection() {
    rejectionBox.style.display = 'block';
    modalFooter.innerHTML = `
        <button class="btn btn-reject" onclick="handleAction('rejected')"><i class="fas fa-times"></i> تأكيد الرفض</button>
        <button class="btn btn-close" onclick="cancelRejection()">إلغاء</button>`;
}

function cancelRejection() {
    rejectionBox.style.display = 'none';
    rejectionText.value = '';
    openModal(currentId);
}

async function handleAction(status) {
    const confirmMsg = status === 'verified'
        ? 'هل تأكد من الموافقة وتفعيل Pro؟'
        : 'هل تأكد من رفض هذا الاشتراك؟';
    if (!confirm(confirmMsg)) return;

    try {
        const body = `ajax=true&action=update_status&id=${currentId}&status=${status}&rejection_reason=${encodeURIComponent(rejectionText.value)}`;
        const res    = await fetch('', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body });
        const result = await res.json();

        if (result.success) {
            showToast(result.message, 'success');

            // تحديث الصف
            const row = document.querySelector(`tr[data-id="${currentId}"]`);
            if (row) {
                row.dataset.status = status;
                const badge = row.querySelector('.badge');
                const labels = { pending:'قيد الانتظار', verified:'مفعّل', rejected:'مرفوض' };
                badge.className = `badge badge-${status}`;
                badge.textContent = labels[status];

                if (status === 'verified') {
                    const nameDiv = row.querySelector('.user-name');
                    if (!nameDiv.querySelector('.badge-pro')) {
                        const b = document.createElement('span');
                        b.className = 'badge badge-pro';
                        b.textContent = 'PRO';
                        nameDiv.prepend(b);
                        nameDiv.prepend(document.createTextNode(' '));
                    }
                }
            }

            setTimeout(closeModal, 1800);
        } else {
            showToast(result.message || 'حدث خطأ', 'error');
        }
    } catch(e) {
        showToast('خطأ في الاتصال', 'error');
    }
}

function closeModal() {
    modal.style.display = 'none';
    rejectionText.value = '';
    rejectionBox.style.display = 'none';
}

// فلترة
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        applyFilters();
    });
});

document.getElementById('searchInput').addEventListener('input', applyFilters);

function applyFilters() {
    const filter = document.querySelector('.filter-btn.active').dataset.filter;
    const term   = document.getElementById('searchInput').value.toLowerCase().trim();
    let count = 0;

    document.querySelectorAll('.sub-row').forEach(row => {
        const statusOk = filter === 'all' || row.dataset.status === filter;
        const searchOk = !term || row.dataset.name.includes(term) || row.dataset.email.includes(term);
        const show = statusOk && searchOk;
        row.style.display = show ? '' : 'none';
        if (show) count++;
    });

    rowCountEl.textContent = count;
}

// Toast
function showToast(msg, type) {
    toast.textContent = msg;
    toast.className = `toast ${type}`;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 4000);
}

// Close on overlay click & ESC
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
</body>
</html>