<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// ── Auth Guard ─────────────────────────────────────────────────────────────
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}

// ── DB Config ──────────────────────────────────────────────────────────────
$host     = 'sql207.infinityfree.com';
$dbname   = 'if0_39304815_miraatalmuminif';
$username = 'if0_39304815';
$password = 'NIHOIGYPkLGsq0';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ── Theme ──────────────────────────────────────────────────────────────────
$btn_color  = '#059669';
$sec_color  = '#047857';
$text_color = '#111827';

// ── Logout ─────────────────────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: auth.php');
    exit;
}

// ── AJAX ───────────────────────────────────────────────────────────────────
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    // ── Dashboard Stats ────────────────────────────────────────────────────
    if ($_POST['action'] === 'get_stats') {
        $stats = [
            'users'          => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'pro_users'      => $pdo->query("SELECT COUNT(*) FROM users WHERE Pro = 1")->fetchColumn(),
            'subscriptions'  => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions")->fetchColumn(),
            'pending_subs'   => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status='pending'")->fetchColumn(),
            'verified_subs'  => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status='verified'")->fetchColumn(),
            'rejected_subs'  => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status='rejected'")->fetchColumn(),
            'new_users_today'=> $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
            'revenue_total'  => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM pro_subscriptions WHERE status='verified'")->fetchColumn(),
        ];
        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }

    // ── Get Users ──────────────────────────────────────────────────────────
    if ($_POST['action'] === 'get_users') {
        $page   = max(1, intval($_POST['page'] ?? 1));
        $limit  = 20;
        $offset = ($page - 1) * $limit;
        $search = trim($_POST['search'] ?? '');

        $where = '';
        $params = [];
        if ($search) {
            $where  = "WHERE name LIKE ? OR email LIKE ?";
            $params = ["%$search%", "%$search%"];
        }

        $total = $pdo->prepare("SELECT COUNT(*) FROM users $where");
        $total->execute($params);
        $total = $total->fetchColumn();

        $stmt = $pdo->prepare("SELECT id, name, email, Pro, current_prayer_streak, max_prayer_streak, created_at FROM users $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $users, 'total' => $total, 'page' => $page]);
        exit;
    }

    // ── Get Subscriptions ──────────────────────────────────────────────────
    if ($_POST['action'] === 'get_subscriptions') {
        $filter = $_POST['filter'] ?? 'all';
        $where  = $filter !== 'all' ? "WHERE ps.status = " . $pdo->quote($filter) : '';

        $stmt = $pdo->prepare("SELECT ps.*, u.name, u.email, u.Pro as user_pro_status
            FROM pro_subscriptions ps
            LEFT JOIN users u ON ps.user_id = u.id
            $where ORDER BY ps.created_at DESC LIMIT 50");
        $stmt->execute();
        $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $subs]);
        exit;
    }

    // ── Update Subscription Status ─────────────────────────────────────────
    if ($_POST['action'] === 'update_sub_status') {
        $id     = intval($_POST['id']);
        $status = $_POST['status'];

        if (!in_array($status, ['verified','rejected','pending'])) {
            echo json_encode(['success' => false, 'message' => 'حالة غير صالحة']);
            exit;
        }

        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE pro_subscriptions SET status = ? WHERE id = ?")->execute([$status, $id]);
            if ($status === 'verified') {
                $row = $pdo->prepare("SELECT user_id FROM pro_subscriptions WHERE id = ?");
                $row->execute([$id]);
                $row = $row->fetch(PDO::FETCH_ASSOC);
                if ($row) $pdo->prepare("UPDATE users SET Pro = 1 WHERE id = ?")->execute([$row['user_id']]);
            }
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'تم تحديث الحالة بنجاح']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ── Toggle Pro ─────────────────────────────────────────────────────────
    if ($_POST['action'] === 'toggle_pro') {
        $id  = intval($_POST['id']);
        $val = intval($_POST['val']);
        $pdo->prepare("UPDATE users SET Pro = ? WHERE id = ?")->execute([$val, $id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ── Get single subscription ────────────────────────────────────────────
    if ($_POST['action'] === 'get_subscription') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT ps.*, u.name, u.email, u.profile_picture,
            u.current_prayer_streak, u.max_prayer_streak, u.created_at as user_created_at, u.Pro as user_pro_status
            FROM pro_subscriptions ps LEFT JOIN users u ON ps.user_id = u.id WHERE ps.id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        exit;
    }

    // ── Get Admins ─────────────────────────────────────────────────────────
    if ($_POST['action'] === 'get_admins') {
        $admins = $pdo->query("SELECT id, admin_id, name, email, role, last_login, created_at FROM `admin-stv` ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $admins]);
        exit;
    }

    // ── Delete Admin ───────────────────────────────────────────────────────
    if ($_POST['action'] === 'delete_admin') {
        $id = intval($_POST['id']);
        if ($id == $_SESSION['admin_id']) {
            echo json_encode(['success' => false, 'message' => 'لا يمكنك حذف حسابك الحالي']);
            exit;
        }
        $pdo->prepare("DELETE FROM `admin-stv` WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ── Recent Activity ────────────────────────────────────────────────────
    if ($_POST['action'] === 'get_activity') {
        $activity = $pdo->query("SELECT ps.id, ps.status, ps.created_at, ps.amount, ps.plan_type, u.name, u.email
            FROM pro_subscriptions ps LEFT JOIN users u ON ps.user_id = u.id
            ORDER BY ps.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $activity]);
        exit;
    }
}

// ── Pre-load stats ─────────────────────────────────────────────────────────
$quick_stats = [
    'users'         => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'pro_users'     => $pdo->query("SELECT COUNT(*) FROM users WHERE Pro = 1")->fetchColumn(),
    'pending_subs'  => $pdo->query("SELECT COUNT(*) FROM pro_subscriptions WHERE status='pending'")->fetchColumn(),
    'revenue_total' => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM pro_subscriptions WHERE status='verified'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم — مرآة المؤمن</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --green:  <?php echo $btn_color; ?>;
            --green2: <?php echo $sec_color; ?>;
            --text:   <?php echo $text_color; ?>;
            --sidebar-w: 260px;
        }

        html, body {
            height: 100%;
            font-family: 'Tajawal', sans-serif;
            background: #f1f5f9;
            color: var(--text);
            overflow: hidden;
        }

        /* ── Layout ── */
        .layout { display: flex; height: 100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #064e3b 0%, #065f46 50%, #047857 100%);
            display: flex; flex-direction: column;
            flex-shrink: 0;
            transition: width 0.3s ease;
            overflow: hidden;
            z-index: 100;
            position: relative;
        }

        .sidebar.collapsed { width: 72px; }

        .sidebar-logo {
            padding: 22px 20px 18px;
            display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            min-height: 72px;
        }

        .sidebar-logo .logo-icon {
            width: 40px; height: 40px; flex-shrink: 0;
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: white;
        }

        .logo-text { color: white; transition: opacity 0.2s; }
        .logo-text h2 { font-size: 15px; font-weight: 800; }
        .logo-text p  { font-size: 11px; opacity: 0.6; margin-top: 1px; }
        .sidebar.collapsed .logo-text { opacity: 0; width: 0; overflow: hidden; }

        .sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; scrollbar-width: none; }

        .nav-section {
            padding: 8px 14px 4px;
            font-size: 10px; font-weight: 700;
            color: rgba(255,255,255,0.35);
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s;
        }

        .sidebar.collapsed .nav-section { opacity: 0; }

        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 20px;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            transition: all 0.2s;
            border-right: 3px solid transparent;
            white-space: nowrap;
            position: relative;
        }

        .nav-item i { font-size: 16px; flex-shrink: 0; width: 20px; text-align: center; }
        .nav-item span { font-size: 14px; font-weight: 500; transition: opacity 0.2s; }
        .sidebar.collapsed .nav-item span { opacity: 0; width: 0; overflow: hidden; }

        .nav-item:hover { color: white; background: rgba(255,255,255,0.08); }
        .nav-item.active {
            color: white;
            background: rgba(255,255,255,0.12);
            border-right-color: white;
        }

        .nav-badge {
            margin-right: auto;
            background: #ef4444;
            color: white; font-size: 11px; font-weight: 700;
            padding: 2px 7px; border-radius: 20px;
            transition: opacity 0.2s;
        }

        .sidebar.collapsed .nav-badge { opacity: 0; }

        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .admin-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            background: rgba(255,255,255,0.08);
            border-radius: 12px;
            cursor: pointer;
        }

        .admin-avatar {
            width: 36px; height: 36px; flex-shrink: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 700; color: white;
        }

        .admin-info { overflow: hidden; transition: opacity 0.2s; }
        .admin-info p { font-size: 13px; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .admin-info span { font-size: 11px; color: rgba(255,255,255,0.5); }
        .sidebar.collapsed .admin-info { opacity: 0; width: 0; }

        /* ── Main ── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

        /* ── Top Header ── */
        .topbar {
            background: white;
            height: 64px;
            display: flex; align-items: center;
            padding: 0 24px;
            gap: 16px;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
            box-shadow: 0 1px 8px rgba(0,0,0,0.06);
            z-index: 10;
        }

        .toggle-sidebar {
            width: 36px; height: 36px;
            background: #f8fafc; border: 1px solid #e5e7eb;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #6b7280; font-size: 16px;
            transition: all 0.2s; flex-shrink: 0;
        }

        .toggle-sidebar:hover { background: var(--green); color: white; border-color: var(--green); }

        .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 14px; color: #6b7280; }
        .breadcrumb .current { color: var(--text); font-weight: 600; }
        .breadcrumb i { font-size: 10px; }

        .topbar-right { margin-right: auto; display: flex; align-items: center; gap: 12px; }

        .topbar-search {
            position: relative;
            display: flex; align-items: center;
        }

        .topbar-search i { position: absolute; right: 12px; color: #9ca3af; font-size: 14px; }

        .topbar-search input {
            padding: 8px 38px 8px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'Tajawal', sans-serif;
            font-size: 13px; width: 220px;
            outline: none;
            transition: border-color 0.2s;
        }

        .topbar-search input:focus { border-color: var(--green); }

        .icon-btn {
            width: 38px; height: 38px;
            background: #f8fafc; border: 1px solid #e5e7eb;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #6b7280; font-size: 15px;
            transition: all 0.2s; position: relative;
            text-decoration: none;
        }

        .icon-btn:hover { background: var(--green); color: white; border-color: var(--green); }

        .notif-dot {
            position: absolute; top: 6px; left: 6px;
            width: 8px; height: 8px;
            background: #ef4444; border-radius: 50%;
            border: 2px solid white;
        }

        /* ── Content ── */
        .content {
            flex: 1; overflow-y: auto;
            padding: 24px;
            scrollbar-width: thin;
            scrollbar-color: #e5e7eb transparent;
        }

        /* ── Pages ── */
        .page { display: none; animation: fadeSlide 0.3s ease; }
        .page.active { display: block; }

        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Page Header ── */
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
        }

        .page-title h2 { font-size: 20px; font-weight: 800; }
        .page-title p  { font-size: 13px; color: #6b7280; margin-top: 2px; }

        .page-actions { display: flex; gap: 10px; }

        .btn {
            padding: 10px 20px;
            border: none; border-radius: 10px;
            font-family: 'Tajawal', sans-serif;
            font-size: 13px; font-weight: 600;
            cursor: pointer;
            display: inline-flex; align-items: center; gap: 7px;
            transition: all 0.2s;
        }

        .btn-primary { background: var(--green); color: white; }
        .btn-primary:hover { background: var(--green2); transform: translateY(-1px); }
        .btn-outline { background: white; border: 1.5px solid #e5e7eb; color: #374151; }
        .btn-outline:hover { border-color: var(--green); color: var(--green); }
        .btn-danger  { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 6px 14px; font-size: 12px; }

        /* ── Stats Grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            display: flex; align-items: center; gap: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }

        .stat-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }

        .sc-purple  { background: #ede9fe; color: #7c3aed; }
        .sc-green   { background: #d1fae5; color: var(--green); }
        .sc-orange  { background: #fef3c7; color: #d97706; }
        .sc-blue    { background: #dbeafe; color: #2563eb; }
        .sc-red     { background: #fee2e2; color: #dc2626; }
        .sc-teal    { background: #ccfbf1; color: #0d9488; }

        .stat-info .label   { font-size: 12px; color: #6b7280; }
        .stat-info .number  { font-size: 28px; font-weight: 800; line-height: 1.1; }
        .stat-info .change  { font-size: 11px; margin-top: 2px; }
        .change.up   { color: #059669; }
        .change.down { color: #dc2626; }

        /* ── Card ── */
        .card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-head {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
        }

        .card-head h3 {
            font-size: 15px; font-weight: 700;
            display: flex; align-items: center; gap: 8px;
        }

        .card-head h3 i { color: var(--green); }

        .card-body { padding: 20px; }

        /* ── Table ── */
        .tbl-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }
        th {
            padding: 11px 16px; text-align: right;
            font-size: 12px; font-weight: 600; color: #6b7280;
            background: #f8fafc; border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }
        td { padding: 13px 16px; border-bottom: 1px solid #f8fafc; font-size: 13px; }
        tbody tr { transition: background 0.15s; cursor: pointer; }
        tbody tr:hover { background: #f8fafc; }
        tbody tr:last-child td { border-bottom: none; }

        .user-cell { display: flex; align-items: center; gap: 10px; }
        .avatar {
            width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--green2), var(--green));
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 13px;
        }
        .u-name  { font-weight: 600; font-size: 13px; }
        .u-email { font-size: 11px; color: #9ca3af; }

        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600; white-space: nowrap;
        }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-verified { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-pro      { background: var(--green); color: white; }
        .badge-admin    { background: #ede9fe; color: #5b21b6; }
        .badge-superadmin { background: #fef3c7; color: #92400e; }
        .badge-moderator  { background: #dbeafe; color: #1d4ed8; }

        .amount { font-weight: 700; color: var(--green2); }

        /* ── Filters ── */
        .filters-bar {
            display: flex; align-items: center; gap: 10px;
            flex-wrap: wrap; padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9; background: #fafafa;
        }

        .filter-btn {
            padding: 7px 16px;
            border: 1.5px solid #e5e7eb; border-radius: 30px;
            background: white; cursor: pointer;
            font-size: 13px; font-weight: 500; color: #374151;
            font-family: 'Tajawal', sans-serif;
            transition: all 0.2s;
        }
        .filter-btn:hover  { border-color: var(--green); color: var(--green); }
        .filter-btn.active { background: var(--green); color: white; border-color: var(--green); }

        .search-box {
            margin-right: auto; position: relative;
        }
        .search-box i { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 13px; }
        .search-box input {
            padding: 8px 36px 8px 14px;
            border: 1.5px solid #e5e7eb; border-radius: 10px;
            font-family: 'Tajawal', sans-serif; font-size: 13px;
            width: 240px; outline: none;
            transition: border-color 0.2s;
        }
        .search-box input:focus { border-color: var(--green); }

        /* ── Row count ── */
        .row-count { font-size: 12px; color: #6b7280; background: #f1f5f9; padding: 3px 10px; border-radius: 20px; }

        /* ── Toggle Switch ── */
        .toggle { position: relative; display: inline-block; width: 40px; height: 22px; }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer; inset: 0;
            background: #d1d5db; border-radius: 22px;
            transition: 0.3s;
        }
        .slider::before {
            content: ''; position: absolute;
            width: 16px; height: 16px;
            left: 3px; top: 3px;
            background: white; border-radius: 50%;
            transition: 0.3s;
        }
        .toggle input:checked + .slider { background: var(--green); }
        .toggle input:checked + .slider::before { transform: translateX(18px); }

        /* ── Activity Feed ── */
        .activity-list { padding: 0 20px 4px; }
        .activity-item {
            display: flex; align-items: flex-start; gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid #f8fafc;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-dot {
            width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 14px;
        }
        .activity-dot.pending  { background: #fef3c7; color: #d97706; }
        .activity-dot.verified { background: #d1fae5; color: var(--green); }
        .activity-dot.rejected { background: #fee2e2; color: #dc2626; }
        .activity-text p { font-size: 13px; font-weight: 500; }
        .activity-text span { font-size: 11px; color: #9ca3af; }

        /* ── Chart Placeholder ── */
        .chart-wrap {
            height: 200px; background: #f8fafc;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #9ca3af; font-size: 13px;
        }

        /* ── Revenue Card ── */
        .revenue-highlight {
            background: linear-gradient(135deg, var(--green2), var(--green));
            border-radius: 16px; padding: 24px; color: white;
            display: flex; align-items: center; gap: 20px;
            margin-bottom: 20px;
        }

        .revenue-icon {
            width: 60px; height: 60px; background: rgba(255,255,255,0.2);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center; font-size: 26px;
            flex-shrink: 0;
        }

        .revenue-info p { font-size: 13px; opacity: 0.85; margin-bottom: 4px; }
        .revenue-info h2 { font-size: 32px; font-weight: 800; }
        .revenue-info span { font-size: 12px; opacity: 0.7; }

        /* ── Empty ── */
        .empty { text-align: center; padding: 50px 20px; color: #9ca3af; }
        .empty i { font-size: 42px; margin-bottom: 10px; display: block; }
        .empty p { font-size: 13px; }

        /* ── Loading ── */
        .loading { text-align: center; padding: 40px; color: #9ca3af; }
        .loading i { font-size: 24px; display: block; margin-bottom: 8px; }

        /* ── Modal ── */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 1000;
            background: rgba(0,0,0,0.6);
            display: none; align-items: center; justify-content: center;
            padding: 20px;
            backdrop-filter: blur(4px);
        }

        .modal {
            background: white; border-radius: 20px;
            width: 100%; max-width: 760px; max-height: 90vh;
            overflow-y: auto; scrollbar-width: none;
            box-shadow: 0 25px 60px rgba(0,0,0,0.25);
            animation: slideUp 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }

        @keyframes slideUp {
            from { opacity:0; transform: translateY(30px) scale(0.97); }
            to   { opacity:1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            padding: 20px 24px;
            background: linear-gradient(135deg, var(--green2), var(--green));
            color: white; border-radius: 20px 20px 0 0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-header h2 { font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .close-btn {
            width: 34px; height: 34px;
            background: rgba(255,255,255,0.2); border: none;
            border-radius: 50%; color: white; font-size: 17px;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .close-btn:hover { background: rgba(255,255,255,0.35); }

        .modal-body { padding: 24px; }
        .modal-footer {
            padding: 14px 24px;
            background: #f8fafc; border-top: 1px solid #f1f5f9;
            border-radius: 0 0 20px 20px;
            display: flex; gap: 10px;
        }

        .modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .info-card {
            background: #f8fafc; border-radius: 12px;
            padding: 16px; border: 1px solid #f1f5f9;
        }
        .info-card h3 {
            font-size: 13px; font-weight: 700; color: #374151;
            margin-bottom: 12px; padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center; gap: 6px;
        }
        .info-card h3 i { color: var(--green); }
        .info-row { margin-bottom: 8px; }
        .info-label { font-size: 11px; color: #9ca3af; margin-bottom: 2px; }
        .info-value { font-size: 13px; font-weight: 500; }

        .receipt-img { width: 100%; border-radius: 10px; border: 1px solid #e5e7eb; cursor: pointer; transition: transform 0.2s; }
        .receipt-img:hover { transform: scale(1.01); }

        /* ── Toast ── */
        .toast {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
            padding: 12px 24px; border-radius: 12px;
            color: white; font-weight: 600; font-size: 13px;
            z-index: 2000; display: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            animation: toastIn 0.3s ease;
            white-space: nowrap;
        }
        @keyframes toastIn {
            from { opacity:0; transform: translateX(-50%) translateY(10px); }
            to   { opacity:1; transform: translateX(-50%) translateY(0); }
        }
        .toast.success { background: var(--green); }
        .toast.error   { background: #ef4444; }
        .toast.info    { background: #2563eb; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: repeat(2,1fr); }
            .modal-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .topbar-search { display: none; }
            .stats-grid { grid-template-columns: repeat(2,1fr); }
            .content { padding: 16px; }
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- ─────────── SIDEBAR ─────────── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon"><i class="fas fa-mosque"></i></div>
            <div class="logo-text">
                <h2>مرآة المؤمن</h2>
                <p>لوحة الإدارة</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">الرئيسية</div>
            <div class="nav-item active" onclick="showPage('dashboard',this)">
                <i class="fas fa-chart-pie"></i><span>الداش بورد</span>
            </div>

            <div class="nav-section">إدارة المستخدمين</div>
            <div class="nav-item" onclick="showPage('users',this)">
                <i class="fas fa-users"></i><span>المستخدمون</span>
            </div>
            <div class="nav-item" onclick="showPage('subscriptions',this)">
                <i class="fas fa-crown"></i><span>الاشتراكات</span>
                <?php if ($quick_stats['pending_subs'] > 0): ?>
                <span class="nav-badge"><?php echo $quick_stats['pending_subs']; ?></span>
                <?php endif; ?>
            </div>

            <div class="nav-section">الإعدادات</div>
            <div class="nav-item" onclick="showPage('admins',this)">
                <i class="fas fa-user-shield"></i><span>المديرون</span>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-card" onclick="showPage('admins',null)">
                <div class="admin-avatar"><?php echo mb_substr($_SESSION['admin_name'], 0, 1, 'UTF-8'); ?></div>
                <div class="admin-info">
                    <p><?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                    <span><?php echo htmlspecialchars($_SESSION['admin_uid']); ?></span>
                </div>
            </div>
        </div>
    </aside>

    <!-- ─────────── MAIN ─────────── -->
    <div class="main">

        <!-- Top Bar -->
        <header class="topbar">
            <div class="toggle-sidebar" onclick="toggleSidebar()"><i class="fas fa-bars"></i></div>
            <div class="breadcrumb">
                <i class="fas fa-home"></i>
                <i class="fas fa-angle-left"></i>
                <span class="current" id="breadcrumb">الداش بورد</span>
            </div>
            <div class="topbar-right">
                <div class="topbar-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="بحث سريع...">
                </div>
                <a href="?logout=1" class="icon-btn" title="تسجيل الخروج" onclick="return confirm('هل تريد تسجيل الخروج؟')">
                    <i class="fas fa-right-from-bracket"></i>
                </a>
            </div>
        </header>

        <!-- Content -->
        <div class="content">

            <!-- ──────────────── DASHBOARD PAGE ──────────────── -->
            <div class="page active" id="page-dashboard">
                <div class="page-header">
                    <div class="page-title">
                        <h2>مرحباً، <?php echo htmlspecialchars($_SESSION['admin_name']); ?> 👋</h2>
                        <p>إليك نظرة عامة على المنصة اليوم</p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-outline" onclick="loadDashboard()"><i class="fas fa-rotate-right"></i> تحديث</button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="stats-grid" id="dashStats">
                    <div class="stat-card" onclick="showPage('users',null)">
                        <div class="stat-icon sc-purple"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <div class="label">إجمالي المستخدمين</div>
                            <div class="number"><?php echo $quick_stats['users']; ?></div>
                        </div>
                    </div>
                    <div class="stat-card" onclick="showPage('subscriptions',null)">
                        <div class="stat-icon sc-green"><i class="fas fa-crown"></i></div>
                        <div class="stat-info">
                            <div class="label">مشتركو Pro</div>
                            <div class="number"><?php echo $quick_stats['pro_users']; ?></div>
                        </div>
                    </div>
                    <div class="stat-card" onclick="showPage('subscriptions',null)">
                        <div class="stat-icon sc-orange"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <div class="label">طلبات معلّقة</div>
                            <div class="number" style="color:#d97706"><?php echo $quick_stats['pending_subs']; ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon sc-teal"><i class="fas fa-coins"></i></div>
                        <div class="stat-info">
                            <div class="label">الإيرادات المؤكدة</div>
                            <div class="number" style="color:#0d9488"><?php echo number_format($quick_stats['revenue_total'],0); ?></div>
                            <div class="change up">ر.س</div>
                        </div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-head">
                            <h3><i class="fas fa-bolt"></i> أحدث النشاطات</h3>
                            <button class="btn btn-outline btn-sm" onclick="showPage('subscriptions',null)">عرض الكل</button>
                        </div>
                        <div class="activity-list" id="activityList">
                            <div class="loading"><i class="fas fa-spinner fa-spin"></i>جاري التحميل...</div>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div>
                        <div class="revenue-highlight">
                            <div class="revenue-icon"><i class="fas fa-sack-dollar"></i></div>
                            <div class="revenue-info">
                                <p>إجمالي الإيرادات</p>
                                <h2><?php echo number_format($quick_stats['revenue_total'],0); ?></h2>
                                <span>ريال سعودي — مدفوعات مؤكدة</span>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-head"><h3><i class="fas fa-chart-bar"></i> نسب الاشتراكات</h3></div>
                            <div class="card-body" id="subRatios">
                                <div class="loading"><i class="fas fa-spinner fa-spin"></i>...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ──────────────── USERS PAGE ──────────────── -->
            <div class="page" id="page-users">
                <div class="page-header">
                    <div class="page-title">
                        <h2>إدارة المستخدمين</h2>
                        <p>عرض وإدارة جميع حسابات المستخدمين</p>
                    </div>
                </div>
                <div class="card">
                    <div class="filters-bar">
                        <button class="filter-btn active" data-pro="all">الكل</button>
                        <button class="filter-btn" data-pro="1">Pro فقط</button>
                        <button class="filter-btn" data-pro="0">عادي فقط</button>
                        <div class="search-box" style="margin-right:auto">
                            <i class="fas fa-search"></i>
                            <input type="text" id="userSearch" placeholder="بحث بالاسم أو البريد...">
                        </div>
                        <span class="row-count"><span id="userCount">—</span> مستخدم</span>
                    </div>
                    <div class="tbl-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>المستخدم</th>
                                    <th>حالة Pro</th>
                                    <th>التواصل الحالي</th>
                                    <th>أقصى تواصل</th>
                                    <th>تاريخ التسجيل</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="usersTable">
                                <tr><td colspan="6" class="loading"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ──────────────── SUBSCRIPTIONS PAGE ──────────────── -->
            <div class="page" id="page-subscriptions">
                <div class="page-header">
                    <div class="page-title">
                        <h2>اشتراكات Pro</h2>
                        <p>مراجعة وإدارة جميع طلبات الاشتراكات</p>
                    </div>
                </div>
                <div class="card">
                    <div class="filters-bar">
                        <button class="filter-btn active" data-filter="all" onclick="loadSubs('all',this)">الكل</button>
                        <button class="filter-btn" data-filter="pending" onclick="loadSubs('pending',this)">معلّقة</button>
                        <button class="filter-btn" data-filter="verified" onclick="loadSubs('verified',this)">مفعّلة</button>
                        <button class="filter-btn" data-filter="rejected" onclick="loadSubs('rejected',this)">مرفوضة</button>
                        <span class="row-count" style="margin-right:auto"><span id="subsCount">—</span> طلب</span>
                    </div>
                    <div class="tbl-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>المستخدم</th>
                                    <th>رقم الهاتف</th>
                                    <th>نوع الاشتراك</th>
                                    <th>المبلغ</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody id="subsTable">
                                <tr><td colspan="6" class="loading"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ──────────────── ADMINS PAGE ──────────────── -->
            <div class="page" id="page-admins">
                <div class="page-header">
                    <div class="page-title">
                        <h2>المديرون</h2>
                        <p>إدارة حسابات الإدارة</p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-primary" onclick="window.open('auth.php?page=register','_blank')">
                            <i class="fas fa-user-plus"></i> إضافة مدير
                        </button>
                    </div>
                </div>
                <div class="card">
                    <div class="tbl-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>المدير</th>
                                    <th>معرّف الإدارة</th>
                                    <th>الصلاحية</th>
                                    <th>آخر دخول</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody id="adminsTable">
                                <tr><td colspan="6" class="loading"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /content -->
    </div><!-- /main -->
</div><!-- /layout -->

<!-- ── Subscription Detail Modal ── -->
<div class="modal-overlay" id="subModal">
    <div class="modal">
        <div class="modal-header">
            <h2><i class="fas fa-file-invoice"></i> تفاصيل الاشتراك</h2>
            <button class="close-btn" onclick="closeModal('subModal')">✕</button>
        </div>
        <div class="modal-body" id="subModalBody">
            <div class="loading"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</div>
        </div>
        <div class="modal-footer" id="subModalFooter"></div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
// ── State ──────────────────────────────────────────────────────────────────
let sidebarCollapsed = false;
let currentSubId     = null;
let currentAdminPage = 1;
let adminPro         = 'all';

// ── Sidebar Toggle ─────────────────────────────────────────────────────────
function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    sidebarCollapsed = !sidebarCollapsed;
    sb.classList.toggle('collapsed', sidebarCollapsed);
}

// ── Page Navigation ────────────────────────────────────────────────────────
const pageNames = {
    dashboard: 'الداش بورد',
    users: 'المستخدمون',
    subscriptions: 'الاشتراكات',
    admins: 'المديرون',
};

function showPage(name, navEl) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

    document.getElementById('page-' + name).classList.add('active');
    if (navEl) navEl.classList.add('active');
    document.getElementById('breadcrumb').textContent = pageNames[name] || name;

    if (name === 'dashboard')     loadDashboard();
    if (name === 'users')         loadUsers();
    if (name === 'subscriptions') loadSubs('all', document.querySelector('[data-filter="all"]'));
    if (name === 'admins')        loadAdmins();
}

// ── POST Helper ────────────────────────────────────────────────────────────
async function post(data) {
    const body = Object.entries({ ajax: true, ...data })
        .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
        .join('&');
    const res = await fetch('', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body });
    return res.json();
}

// ── Toast ──────────────────────────────────────────────────────────────────
function toast(msg, type = 'success') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = `toast ${type}`;
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 4000);
}

// ── Modal ──────────────────────────────────────────────────────────────────
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

document.getElementById('subModal').addEventListener('click', e => {
    if (e.target === document.getElementById('subModal')) closeModal('subModal');
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
});

// ── Dashboard ──────────────────────────────────────────────────────────────
async function loadDashboard() {
    // Activity
    try {
        const r = await post({ action: 'get_activity' });
        if (r.success) renderActivity(r.data);
    } catch(e) {}

    // Sub Ratios
    try {
        const r = await post({ action: 'get_stats' });
        if (r.success) renderSubRatios(r.data);
    } catch(e) {}
}

function renderActivity(items) {
    const el = document.getElementById('activityList');
    if (!items.length) { el.innerHTML = '<div class="empty"><i class="fas fa-inbox"></i><p>لا توجد نشاطات</p></div>'; return; }

    const icons = { pending: 'fa-clock', verified: 'fa-check-circle', rejected: 'fa-times-circle' };
    const labels = { pending: 'طلب اشتراك جديد', verified: 'تم تفعيل Pro', rejected: 'رُفض الاشتراك' };

    el.innerHTML = items.map(i => `
        <div class="activity-item">
            <div class="activity-dot ${i.status}"><i class="fas ${icons[i.status]||'fa-info-circle'}"></i></div>
            <div class="activity-text">
                <p>${labels[i.status]||i.status} — <strong>${i.name||'مستخدم'}</strong></p>
                <span>${i.plan_type==='yearly'?'سنوي':'شهري'} — ${parseFloat(i.amount||0).toFixed(2)} ر.س &nbsp;·&nbsp; ${new Date(i.created_at).toLocaleDateString('ar-EG')}</span>
            </div>
        </div>
    `).join('');
}

function renderSubRatios(d) {
    const total = parseInt(d.subscriptions) || 1;
    const vPct  = Math.round((d.verified_subs / total) * 100);
    const pPct  = Math.round((d.pending_subs  / total) * 100);
    const rPct  = Math.round((d.rejected_subs / total) * 100);

    document.getElementById('subRatios').innerHTML = `
        <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:5px"><span>مفعّلة</span><span>${d.verified_subs} (${vPct}%)</span></div>
            <div style="height:8px;background:#f1f5f9;border-radius:10px;overflow:hidden"><div style="height:100%;width:${vPct}%;background:var(--green);border-radius:10px;transition:width 1s"></div></div>
        </div>
        <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:5px"><span>معلّقة</span><span>${d.pending_subs} (${pPct}%)</span></div>
            <div style="height:8px;background:#f1f5f9;border-radius:10px;overflow:hidden"><div style="height:100%;width:${pPct}%;background:#f59e0b;border-radius:10px;transition:width 1s"></div></div>
        </div>
        <div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:5px"><span>مرفوضة</span><span>${d.rejected_subs} (${rPct}%)</span></div>
            <div style="height:8px;background:#f1f5f9;border-radius:10px;overflow:hidden"><div style="height:100%;width:${rPct}%;background:#ef4444;border-radius:10px;transition:width 1s"></div></div>
        </div>
        <hr style="border:none;border-top:1px solid #f1f5f9;margin:14px 0">
        <div style="font-size:12px;color:#6b7280;display:flex;justify-content:space-between">
            <span>مستخدمو Pro</span><strong style="color:var(--green)">${d.pro_users}</strong>
        </div>
        <div style="font-size:12px;color:#6b7280;display:flex;justify-content:space-between;margin-top:6px">
            <span>جدد اليوم</span><strong style="color:#2563eb">${d.new_users_today}</strong>
        </div>
    `;
}

// ── Users ──────────────────────────────────────────────────────────────────
let usersData = [];

async function loadUsers() {
    const search = document.getElementById('userSearch').value.trim();
    document.getElementById('usersTable').innerHTML = '<tr><td colspan="6" class="loading"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</td></tr>';

    try {
        const r = await post({ action: 'get_users', page: 1, search });
        if (r.success) {
            usersData = r.data;
            document.getElementById('userCount').textContent = r.total;
            renderUsers(r.data);
        }
    } catch(e) {}
}

function renderUsers(data) {
    const filter = document.querySelector('[data-pro].active')?.dataset.pro ?? 'all';
    const filtered = filter === 'all' ? data : data.filter(u => String(u.Pro) === filter);

    if (!filtered.length) {
        document.getElementById('usersTable').innerHTML = '<tr><td colspan="6"><div class="empty"><i class="fas fa-users-slash"></i><p>لا يوجد مستخدمون</p></div></td></tr>';
        return;
    }

    document.getElementById('usersTable').innerHTML = filtered.map(u => `
        <tr>
            <td>
                <div class="user-cell">
                    <div class="avatar">${(u.name||'U').charAt(0)}</div>
                    <div><div class="u-name">${u.name||'غير معروف'}</div><div class="u-email">${u.email||''}</div></div>
                </div>
            </td>
            <td>
                <label class="toggle">
                    <input type="checkbox" ${u.Pro==1?'checked':''} onchange="togglePro(${u.id},this.checked?1:0)">
                    <span class="slider"></span>
                </label>
            </td>
            <td>${u.current_prayer_streak||0} 🔥</td>
            <td>${u.max_prayer_streak||0} ⭐</td>
            <td style="color:#6b7280;font-size:12px">${new Date(u.created_at).toLocaleDateString('ar-EG')}</td>
            <td>—</td>
        </tr>
    `).join('');
}

async function togglePro(id, val) {
    try {
        const r = await post({ action: 'toggle_pro', id, val });
        if (r.success) toast(val ? 'تم تفعيل Pro ✓' : 'تم إلغاء Pro', val ? 'success' : 'info');
        else toast('حدث خطأ', 'error');
    } catch(e) { toast('خطأ في الاتصال', 'error'); }
}

document.addEventListener('DOMContentLoaded', () => {
    // User filters
    document.querySelectorAll('[data-pro]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('[data-pro]').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            renderUsers(usersData);
        });
    });

    let userSearchTimer;
    document.getElementById('userSearch').addEventListener('input', () => {
        clearTimeout(userSearchTimer);
        userSearchTimer = setTimeout(loadUsers, 400);
    });
});

// ── Subscriptions ──────────────────────────────────────────────────────────
async function loadSubs(filter, btnEl) {
    if (btnEl) {
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        btnEl.classList.add('active');
    }

    document.getElementById('subsTable').innerHTML = '<tr><td colspan="6" class="loading"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</td></tr>';

    try {
        const r = await post({ action: 'get_subscriptions', filter });
        if (r.success) {
            document.getElementById('subsCount').textContent = r.data.length;
            renderSubs(r.data);
        }
    } catch(e) {}
}

function renderSubs(data) {
    if (!data.length) {
        document.getElementById('subsTable').innerHTML = '<tr><td colspan="6"><div class="empty"><i class="fas fa-inbox"></i><p>لا توجد اشتراكات</p></div></td></tr>';
        return;
    }

    const labels = { pending: 'معلّق', verified: 'مفعّل', rejected: 'مرفوض' };

    document.getElementById('subsTable').innerHTML = data.map(s => `
        <tr onclick="openSubDetail(${s.id})">
            <td>
                <div class="user-cell">
                    <div class="avatar">${(s.name||'U').charAt(0)}</div>
                    <div>
                        <div class="u-name">
                            ${s.user_pro_status==1?'<span class="badge badge-pro" style="font-size:10px;padding:2px 6px">PRO</span> ':''}
                            ${s.name||'غير معروف'}
                        </div>
                        <div class="u-email">${s.email||''}</div>
                    </div>
                </div>
            </td>
            <td>${s.phone||'—'}</td>
            <td>${s.plan_type==='yearly'?'🗓 سنوي':'📅 شهري'}</td>
            <td class="amount">${parseFloat(s.amount||0).toFixed(2)} ر.س</td>
            <td><span class="badge badge-${s.status}">${labels[s.status]||s.status}</span></td>
            <td style="color:#6b7280;font-size:12px">${new Date(s.created_at).toLocaleDateString('ar-EG')}</td>
        </tr>
    `).join('');
}

// ── Sub Detail Modal ───────────────────────────────────────────────────────
async function openSubDetail(id) {
    currentSubId = id;
    openModal('subModal');
    document.getElementById('subModalBody').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</div>';
    document.getElementById('subModalFooter').innerHTML = '';

    try {
        const r = await post({ action: 'get_subscription', id });
        if (r.success && r.data) renderSubModal(r.data);
    } catch(e) {
        document.getElementById('subModalBody').innerHTML = '<p style="color:red;text-align:center;padding:30px">خطأ في التحميل</p>';
    }
}

function renderSubModal(d) {
    const fmt   = s => s ? new Date(s).toLocaleDateString('ar-EG') : '—';
    const fmtDt = s => s ? new Date(s).toLocaleString('ar-EG') : '—';
    const labels = { pending:'معلّق', verified:'مفعّل', rejected:'مرفوض' };

    document.getElementById('subModalBody').innerHTML = `
        <div class="modal-grid">
            <div class="info-card">
                <h3><i class="fas fa-user"></i> معلومات المستخدم</h3>
                <div class="info-row"><div class="info-label">الاسم</div><div class="info-value">${d.name||'—'}</div></div>
                <div class="info-row"><div class="info-label">البريد</div><div class="info-value">${d.email||'—'}</div></div>
                <div class="info-row"><div class="info-label">تاريخ التسجيل</div><div class="info-value">${fmt(d.user_created_at)}</div></div>
                <div class="info-row"><div class="info-label">حالة Pro</div><div class="info-value"><span class="badge ${d.user_pro_status==1?'badge-pro':'badge-rejected'}">${d.user_pro_status==1?'مفعّل':'غير مفعّل'}</span></div></div>
                <div class="info-row"><div class="info-label">التواصل الحالي</div><div class="info-value">${d.current_prayer_streak||0} يوم 🔥</div></div>
                <div class="info-row"><div class="info-label">أقصى تواصل</div><div class="info-value">${d.max_prayer_streak||0} يوم ⭐</div></div>
            </div>
            <div class="info-card">
                <h3><i class="fas fa-receipt"></i> تفاصيل الاشتراك</h3>
                <div class="info-row"><div class="info-label">رقم الهاتف</div><div class="info-value">${d.phone||'—'}</div></div>
                <div class="info-row"><div class="info-label">إنستجرام</div><div class="info-value">${d.insta_user||'—'}</div></div>
                <div class="info-row"><div class="info-label">نوع الاشتراك</div><div class="info-value">${d.plan_type==='yearly'?'سنوي':'شهري'}</div></div>
                <div class="info-row"><div class="info-label">المبلغ</div><div class="info-value amount">${parseFloat(d.amount||0).toFixed(2)} ر.س</div></div>
                <div class="info-row"><div class="info-label">تاريخ الدفع</div><div class="info-value">${fmtDt(d.payment_date)}</div></div>
                <div class="info-row"><div class="info-label">الحالة</div><div class="info-value"><span class="badge badge-${d.status}">${labels[d.status]||d.status}</span></div></div>
            </div>
        </div>
        ${d.receipt_image ? `
        <div class="info-card" style="margin-bottom:14px">
            <h3><i class="fas fa-image"></i> صورة الإيصال</h3>
            <img src="https://miraat-almumin.xo.je/${d.receipt_image}" class="receipt-img" onclick="window.open(this.src,'_blank')">
        </div>` : ''}
        ${d.extracted_data ? `
        <div class="info-card">
            <h3><i class="fas fa-database"></i> البيانات المستخرجة</h3>
            <pre style="background:#f8fafc;padding:12px;border-radius:8px;font-size:12px;overflow:auto;max-height:150px">${d.extracted_data}</pre>
        </div>` : ''}
    `;

    const footer = document.getElementById('subModalFooter');
    if (d.status === 'pending') {
        footer.innerHTML = `
            <button class="btn btn-primary" style="flex:1" onclick="updateSubStatus('verified')"><i class="fas fa-check"></i> موافقة وتفعيل Pro</button>
            <button class="btn btn-danger"  style="flex:1" onclick="updateSubStatus('rejected')"><i class="fas fa-times"></i> رفض</button>
            <button class="btn btn-outline" onclick="closeModal('subModal')">إغلاق</button>`;
    } else {
        footer.innerHTML = `<button class="btn btn-outline" onclick="closeModal('subModal')">إغلاق</button>`;
    }
}

async function updateSubStatus(status) {
    if (!confirm(status === 'verified' ? 'تأكيد الموافقة وتفعيل Pro؟' : 'تأكيد الرفض؟')) return;

    try {
        const r = await post({ action: 'update_sub_status', id: currentSubId, status });
        if (r.success) {
            toast(r.message, 'success');
            closeModal('subModal');
            loadSubs(document.querySelector('[data-filter].active')?.dataset.filter || 'all', null);
        } else {
            toast(r.message || 'حدث خطأ', 'error');
        }
    } catch(e) { toast('خطأ في الاتصال', 'error'); }
}

// ── Admins ─────────────────────────────────────────────────────────────────
async function loadAdmins() {
    document.getElementById('adminsTable').innerHTML = '<tr><td colspan="6" class="loading"><i class="fas fa-spinner fa-spin"></i> جاري التحميل...</td></tr>';

    try {
        const r = await post({ action: 'get_admins' });
        if (r.success) renderAdmins(r.data);
    } catch(e) {}
}

function renderAdmins(data) {
    const currentId = <?php echo intval($_SESSION['admin_id']); ?>;
    const roleLabels = { superadmin: 'مشرف عام', admin: 'مدير', moderator: 'مراقب' };
    const roleBadge  = { superadmin: 'badge-superadmin', admin: 'badge-admin', moderator: 'badge-moderator' };

    document.getElementById('adminsTable').innerHTML = data.map(a => `
        <tr>
            <td>
                <div class="user-cell">
                    <div class="avatar">${(a.name||'A').charAt(0)}</div>
                    <div><div class="u-name">${a.name} ${a.id==currentId?'<span class="badge badge-pro" style="font-size:10px;padding:2px 6px">أنت</span>':''}</div><div class="u-email">${a.email}</div></div>
                </div>
            </td>
            <td style="font-family:monospace;font-size:12px;color:#6b7280">@${a.admin_id}</td>
            <td><span class="badge ${roleBadge[a.role]||'badge-admin'}">${roleLabels[a.role]||a.role}</span></td>
            <td style="font-size:12px;color:#6b7280">${a.last_login ? new Date(a.last_login).toLocaleString('ar-EG') : 'لم يدخل بعد'}</td>
            <td style="font-size:12px;color:#6b7280">${new Date(a.created_at).toLocaleDateString('ar-EG')}</td>
            <td>
                ${a.id != currentId ? `<button class="btn btn-danger btn-sm" onclick="deleteAdmin(${a.id},event)"><i class="fas fa-trash"></i></button>` : '—'}
            </td>
        </tr>
    `).join('');
}

async function deleteAdmin(id, e) {
    e.stopPropagation();
    if (!confirm('هل تريد حذف هذا المدير؟')) return;

    try {
        const r = await post({ action: 'delete_admin', id });
        if (r.success) { toast('تم حذف المدير', 'info'); loadAdmins(); }
        else toast(r.message || 'حدث خطأ', 'error');
    } catch(e2) { toast('خطأ في الاتصال', 'error'); }
}

// ── Init ───────────────────────────────────────────────────────────────────
loadDashboard();
</script>
</body>
</html>