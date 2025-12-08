<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/config.php';

$user_id = $_SESSION['user_id'];

// التحقق إذا كان المستخدم مسؤولاً (يمكنك تعديل هذا الشرط حسب احتياجاتك)
// يمكنك إضافة حقل is_admin في جدول users للتحكم في الصلاحيات
$is_admin = true; // مؤقتاً

if (!$is_admin) {
    header('Location: ../index.php');
    exit();
}

// البحث والتصفية
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'desc';

// بناء استعلام البحث
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

// إضافة الترتيب
$allowed_sort = ['name', 'email', 'created_at'];
$allowed_order = ['asc', 'desc'];
$sort_by = in_array($sort_by, $allowed_sort) ? $sort_by : 'created_at';
$sort_order = in_array($sort_order, $allowed_order) ? $sort_order : 'desc';
$sql .= " ORDER BY $sort_by $sort_order";

// جلب المستخدمين
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب عدد المستخدمين
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// جلب عدد المستخدمين الجدد اليوم
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) as today_count FROM users WHERE DATE(created_at) = :today");
$stmt->execute([':today' => $today]);
$today_users = $stmt->fetch(PDO::FETCH_ASSOC)['today_count'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>المستخدمين | مرآة المؤمن</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        body {
            background: #f8fafc;
            color: #111827;
            min-height: 100vh;
            padding-bottom: 80px;
        }

        .admin-header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-title h1 {
            font-size: 24px;
            font-weight: 700;
        }

        .back-button {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-5px);
        }

        .stats-cards {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.15);
            border-color: #059669;
        }

        .stat-icon {
            font-size: 40px;
            color: #059669;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .filters-section {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 2px solid #e5e7eb;
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .filters-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 18px;
        }

        .sort-options {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .sort-btn {
            padding: 8px 16px;
            background: #f8fafc;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            color: #666;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .sort-btn:hover {
            background: #059669;
            color: white;
            border-color: #059669;
        }

        .sort-btn.active {
            background: #059669;
            color: white;
            border-color: #059669;
        }

        .users-table-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 2px solid #e5e7eb;
            overflow-x: auto;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            background: #f8fafc;
            padding: 15px;
            text-align: right;
            font-weight: 700;
            color: #111827;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }

        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .users-table tr:hover {
            background: #f0f9ff;
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #e5e7eb;
            overflow: hidden;
            margin-left: 15px;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-avatar.default {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 700;
            color: #111827;
            margin-bottom: 5px;
        }

        .user-email {
            color: #666;
            font-size: 14px;
            direction: ltr;
            text-align: right;
        }

        .user-date {
            color: #666;
            font-size: 14px;
        }

        .user-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-view {
            background: #f0f9ff;
            color: #059669;
            border: 1px solid #059669;
        }

        .action-view:hover {
            background: #059669;
            color: white;
        }

        .action-delete {
            background: #fee;
            color: #dc2626;
            border: 1px solid #dc2626;
        }

        .action-delete:hover {
            background: #dc2626;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-icon {
            font-size: 60px;
            color: #e5e7eb;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #111827;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding: 20px;
        }

        .pagination-btn {
            padding: 8px 16px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pagination-btn:hover:not(:disabled) {
            background: #059669;
            color: white;
            border-color: #059669;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-numbers {
            display: flex;
            gap: 5px;
        }

        .page-number {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .page-number:hover {
            background: #f0f9ff;
            color: #059669;
        }

        .page-number.active {
            background: #059669;
            color: white;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .filters-header {
                flex-direction: column;
                gap: 15px;
            }

            .search-box {
                max-width: 100%;
            }

            .sort-options {
                justify-content: center;
            }

            .users-table {
                min-width: 800px;
            }

            .user-actions {
                flex-direction: column;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e5e7eb;
            border-top-color: #059669;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .export-section {
            margin-top: 20px;
            text-align: left;
        }

        .export-btn {
            background: #059669;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            background: #047857;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            padding-top: 80px;
            overflow: scroll;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            max-width: 500px;
            width: 100%;
            animation: modalIn 0.3s ease;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .modal-body {
            padding: 20px;
        }

        .user-detail-item {
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 600;
            color: #111827;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #666;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .modal-actions {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="header-content">
            <div class="header-title">
                <i class="bi bi-people-fill"></i>
                <h1>إدارة المستخدمين</h1>
            </div>
            <a href="../index.php" class="back-button">
                <i class="bi bi-arrow-right"></i>
                العودة للرئيسية
            </a>
        </div>
    </header>

    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-number"><?php echo number_format($total_users); ?></div>
            <div class="stat-label">إجمالي المستخدمين</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <div class="stat-number"><?php echo number_format($today_users); ?></div>
            <div class="stat-label">مستخدم جديد اليوم</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="bi bi-calendar-week-fill"></i>
            </div>
            <div class="stat-number"><?php echo date('Y/m/d'); ?></div>
            <div class="stat-label">التاريخ الحالي</div>
        </div>
    </div>

    <div class="filters-section">
        <div class="filters-header">
            <h2>البحث والتصفية</h2>
            <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" 
                       class="search-input" 
                       placeholder="ابحث بالاسم أو البريد الإلكتروني..."
                       value="<?php echo htmlspecialchars($search); ?>"
                       onkeyup="if(event.keyCode === 13) searchUsers()"
                       id="searchInput">
            </div>
        </div>
        
        <div class="sort-options">
            <button class="sort-btn <?php echo $sort_by == 'name' ? 'active' : ''; ?>" 
                    onclick="sortUsers('name')">
                <i class="bi bi-sort-alpha-down"></i>
                بالاسم
            </button>
            <button class="sort-btn <?php echo $sort_by == 'created_at' ? 'active' : ''; ?>" 
                    onclick="sortUsers('created_at')">
                <i class="bi bi-calendar"></i>
                بتاريخ التسجيل
            </button>
            <button class="sort-btn" onclick="toggleSortOrder()">
                <i class="bi bi-sort-<?php echo $sort_order == 'desc' ? 'down' : 'up'; ?>"></i>
                <?php echo $sort_order == 'desc' ? 'تنازلي' : 'تصاعدي'; ?>
            </button>
            
            <div class="export-section">
                <button class="export-btn" onclick="exportUsers()">
                    <i class="bi bi-download"></i>
                    تصدير البيانات
                </button>
            </div>
        </div>
    </div>

    <div class="users-table-container">
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="bi bi-people"></i>
                </div>
                <h3>لا يوجد مستخدمين</h3>
                <p>لم يتم العثور على مستخدمين مطابقين لبحثك</p>
            </div>
        <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>البريد الإلكتروني</th>
                        <th>تاريخ التسجيل</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar <?php echo empty($user['profile_picture']) ? 'default' : ''; ?>">
                                        <?php if (!empty($user['profile_picture'])): ?>
                                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                                        <?php else: ?>
                                            <?php echo mb_substr($user['name'], 0, 1, 'UTF-8'); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                                        <div class="user-id">ID: <?php echo $user['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            </td>
                            <td>
                                <div class="user-date">
                                    <i class="bi bi-calendar"></i>
                                    <?php echo date('Y/m/d', strtotime($user['created_at'])); ?>
                                    <br>
                                    <small style="color: #999;">
                                        <?php echo date('H:i', strtotime($user['created_at'])); ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="user-actions">
                                    <button class="action-btn action-view" onclick="viewUser(<?php echo $user['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                        عرض
                                    </button>
                                    <button class="action-btn action-delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                        <i class="bi bi-trash"></i>
                                        حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- يمكن إضافة ترقيم الصفحات هنا إذا كان عدد المستخدمين كبيراً -->
        <?php endif; ?>
    </div>

    <!-- Modal لعرض تفاصيل المستخدم -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">تفاصيل المستخدم</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="userDetails">
                <!-- سيتم ملؤها بالجافاسكريبت -->
            </div>
            <div class="modal-actions">
                <button class="action-btn action-view" onclick="closeModal()">
                    <i class="bi bi-x"></i>
                    إغلاق
                </button>
            </div>
        </div>
    </div>

    <!-- Modal لتأكيد الحذف -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">تأكيد الحذف</h3>
                <button class="close-modal" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">هل أنت متأكد من حذف هذا المستخدم؟</p>
            </div>
            <div class="modal-actions">
                <button class="action-btn" onclick="closeDeleteModal()" style="background: #f8fafc; color: #666;">
                    <i class="bi bi-x"></i>
                    إلغاء
                </button>
                <button class="action-btn action-delete" id="confirmDeleteBtn">
                    <i class="bi bi-trash"></i>
                    حذف
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentSort = '<?php echo $sort_by; ?>';
        let currentOrder = '<?php echo $sort_order; ?>';
        let currentSearch = '<?php echo htmlspecialchars($search); ?>';
        let userToDelete = null;

        function searchUsers() {
            const search = document.getElementById('searchInput').value;
            const url = new URL(window.location.href);
            url.searchParams.set('search', search);
            window.location.href = url.toString();
        }

        function sortUsers(sortBy) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', sortBy);
            url.searchParams.set('order', currentOrder);
            window.location.href = url.toString();
        }

        function toggleSortOrder() {
            const newOrder = currentOrder === 'desc' ? 'asc' : 'desc';
            const url = new URL(window.location.href);
            url.searchParams.set('sort', currentSort);
            url.searchParams.set('order', newOrder);
            window.location.href = url.toString();
        }

        function viewUser(userId) {
            // عرض مؤشر التحميل
            document.getElementById('userDetails').innerHTML = `
                <div class="loading">
                    <div class="loading-spinner"></div>
                    <p>جاري تحميل البيانات...</p>
                </div>
            `;
            
            // عرض الـ Modal
            document.getElementById('userModal').style.display = 'flex';
            
            // جلب بيانات المستخدم
            fetch(`../api/get_user.php?id=${userId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        const createdDate = new Date(user.created_at).toLocaleDateString('ar-SA');
                        const createdTime = new Date(user.created_at).toLocaleTimeString('ar-SA');
                        
                        let html = `
                            <div class="user-detail-item">
                                <div class="detail-label">الصورة الشخصية</div>
                                <div style="text-align:center;">
                                    ${user.profile_picture ? 
                                        `<img src="${user.profile_picture}" alt="${user.name}" style="width:100px;height:100px;border-radius:50%;border:3px solid #059669;">` : 
                                        `<div style="width:100px;height:100px;background:linear-gradient(135deg, #059669, #047857);color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:700;margin:0 auto;">
                                            ${user.name.charAt(0)}
                                        </div>`
                                    }
                                </div>
                            </div>
                            
                            <div class="user-detail-item">
                                <div class="detail-label">الاسم</div>
                                <div class="detail-value">${user.name}</div>
                            </div>
                            
                            <div class="user-detail-item">
                                <div class="detail-label">البريد الإلكتروني</div>
                                <div class="detail-value" dir="ltr" style="text-align:right;">${user.email}</div>
                            </div>
                            
                            <div class="user-detail-item">
                                <div class="detail-label">معرف المستخدم</div>
                                <div class="detail-value">${user.id}</div>
                            </div>
                            
                            <div class="user-detail-item">
                                <div class="detail-label">معرف جوجل</div>
                                <div class="detail-value">${user.google_id || 'غير متوفر'}</div>
                            </div>
                            
                            <div class="user-detail-item">
                                <div class="detail-label">تاريخ التسجيل</div>
                                <div class="detail-value">
                                    <i class="bi bi-calendar"></i> ${createdDate}
                                    <br>
                                    <i class="bi bi-clock"></i> ${createdTime}
                                </div>
                            </div>
                        `;
                        
                        document.getElementById('userDetails').innerHTML = html;
                    } else {
                        document.getElementById('userDetails').innerHTML = `
                            <div style="text-align:center;color:#dc2626;">
                                <i class="bi bi-exclamation-triangle" style="font-size:48px;"></i>
                                <p>حدث خطأ في جلب البيانات</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('userDetails').innerHTML = `
                        <div style="text-align:center;color:#dc2626;">
                            <i class="bi bi-exclamation-triangle" style="font-size:48px;"></i>
                            <p>حدث خطأ في الاتصال بالخادم</p>
                        </div>
                    `;
                });
        }

        function deleteUser(userId, userName) {
            userToDelete = userId;
            document.getElementById('deleteMessage').innerHTML = 
                `هل أنت متأكد من حذف المستخدم <strong>${userName}</strong>؟ هذا الإجراء لا يمكن التراجع عنه.`;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            userToDelete = null;
        }

        // تأكيد الحذف
        document.getElementById('confirmDeleteBtn').onclick = function() {
            if (!userToDelete) return;
            
            fetch('../api/delete_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userToDelete
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // إعادة تحميل الصفحة
                    window.location.reload();
                } else {
                    alert('خطأ: ' + data.message);
                    closeDeleteModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء الحذف');
                closeDeleteModal();
            });
        };

        function exportUsers() {
            // يمكنك إضافة وظيفة التصدير هنا (CSV, Excel, etc.)
            alert('سيتم إضافة وظيفة التصدير قريباً');
        }

        // إغلاق الـ Modal عند النقر خارج المحتوى
        window.onclick = function(event) {
            const userModal = document.getElementById('userModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === userModal) {
                closeModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>