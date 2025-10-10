<?php
require '../config.php';
require 'auth.admin.php';

// ตรวจสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ ลบสมาชิก
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    header("Location: user.php");
    exit;
}

// ✅ ดึงข้อมูลสมาชิก
$stmt = $conn->query("SELECT user_id, username, full_name, email, role FROM users ORDER BY user_id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>จัดการสมาชิก | Admin Panel</title>
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
.table th {
    background-color: #f8f9fa;
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
    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="user.php" class="active"><i class="bi bi-people"></i> จัดการสมาชิก</a>
    <a href="../logout.php" onclick="return confirm('ออกจากระบบ?')"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
</div>

<!-- ✅ Main content -->
<div class="main">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">👥 จัดการสมาชิก</h3>
            <div class="d-flex gap-2">
                <a href="add_user.php" class="btn btn-success"><i class="bi bi-person-plus"></i> เพิ่มสมาชิก</a>
                <!-- ✅ ปุ่ม Back -->
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                    <i class="bi bi-arrow-left-circle"></i> กลับหน้าก่อนหน้า
                </button>
            </div>
        </div>

        <div class="card-body">
            <?php if (count($users) === 0): ?>
                <div class="alert alert-warning text-center">ยังไม่มีสมาชิกในระบบ</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>ชื่อผู้ใช้</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>อีเมล</th>
                                <th>สถานะ</th>
                                <th>การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $i => $user): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($user['role']) ?></span></td>
                                <td>
                                    <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                                    <a href="user.php?delete=<?= $user['user_id'] ?>" class="btn btn-danger btn-sm"
                                       onclick="return confirm('ยืนยันการลบสมาชิกนี้หรือไม่?')"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
