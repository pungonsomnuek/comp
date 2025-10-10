<?php
require '../config.php';
require 'auth.admin.php';

// ตรวจสิทธิ์เฉพาะแอดมิน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ นับข้อมูลสมาชิกในระบบ
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_admins = $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$new_users = $conn->query("SELECT COUNT(*) FROM users WHERE role='member' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

// ✅ ดึงชื่อแอดมินปัจจุบัน
$admin_name = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แดชบอร์ดผู้ดูแลระบบ</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background-color: #f0f2f5;
    font-family: 'Prompt', sans-serif;
}
.sidebar {
    height: 100vh;
    background: #212529;
    color: #fff;
    padding-top: 20px;
    position: fixed;
    width: 230px;
}
.sidebar a {
    color: #ccc;
    display: block;
    padding: 12px 20px;
    text-decoration: none;
    border-radius: 8px;
    margin: 4px 10px;
}
.sidebar a:hover, .sidebar a.active {
    background: #0d6efd;
    color: #fff;
}
.main {
    margin-left: 250px;
    padding: 30px;
}
.card {
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
.card-header {
    background: linear-gradient(90deg, #0d6efd, #6610f2);
    color: white;
    border-radius: 1rem 1rem 0 0;
}
.summary-card {
    border: none;
    border-radius: 1rem;
    background: white;
    text-align: center;
    padding: 25px 10px;
    transition: 0.3s;
}
.summary-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.summary-card h2 {
    color: #0d6efd;
    font-weight: bold;
}
.btn {
    border-radius: 8px;
}
</style>
</head>
<body>

<!-- ✅ Sidebar -->
<div class="sidebar">
    <h4 class="text-center mb-4">⚙️ Admin Panel</h4>
    <a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="user.php"><i class="bi bi-people"></i> จัดการสมาชิก</a>
    <a href="../logout.php" onclick="return confirm('ออกจากระบบ?')"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
</div>

<!-- ✅ Main content -->
<div class="main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 แดชบอร์ดผู้ดูแลระบบ</h2>
        <div class="d-flex align-items-center gap-3">
            <!-- 🔹 ปุ่มกลับหน้าแรกของแอดมิน -->
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> กลับหน้าแรก (Admin)
            </a>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-circle fs-4 text-primary"></i>
                <span><b><?= $admin_name ?></b> (Admin)</span>
            </div>
        </div>
    </div>

    <!-- ✅ สรุปข้อมูลสมาชิก -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="summary-card shadow-sm">
                <i class="bi bi-people-fill fs-1 text-primary"></i>
                <h5 class="mt-2">สมาชิกทั้งหมด</h5>
                <h2><?= $total_users ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card shadow-sm">
                <i class="bi bi-shield-lock-fill fs-1 text-success"></i>
                <h5 class="mt-2">ผู้ดูแลระบบ</h5>
                <h2><?= $total_admins ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card shadow-sm">
                <i class="bi bi-person-plus-fill fs-1 text-warning"></i>
                <h5 class="mt-2">สมาชิกใหม่ (30 วัน)</h5>
                <h2><?= $new_users ?></h2>
            </div>
        </div>
    </div>

    <!-- ✅ การดำเนินการด่วน -->
    <div class="card mt-5">
        <div class="card-header">
            <h5 class="mb-0">🧭 การดำเนินการด่วน</h5>
        </div>
        <div class="card-body text-center">
            <a href="user.php" class="btn btn-primary px-4 me-3">
                <i class="bi bi-people"></i> จัดการสมาชิก
            </a>
            <a href="add_user.php" class="btn btn-success px-4">
                <i class="bi bi-person-plus"></i> เพิ่มสมาชิกใหม่
            </a>
        </div>
    </div>

    <!-- ✅ ปุ่มกลับหน้าแรก (ล่างสุด เผื่อกดสะดวก) -->
    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left"></i> กลับหน้าแรก (Admin)
        </a>
    </div>
</div>

</body>
</html>
