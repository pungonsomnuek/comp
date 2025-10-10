<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../config.php';
require 'auth.admin.php';

// ✅ ตรวจสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ ตรวจสอบว่ามี id ถูกส่งมาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$user_id = (int)$_GET['id'];

// ✅ ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: user.php");
    exit;
}

// ✅ ถ้ามีการส่งฟอร์มแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];

    // ✅ อัปเดตข้อมูลสมาชิก
    $update = $conn->prepare("
        UPDATE users 
        SET full_name = ?, email = ?, username = ?, role = ? 
        WHERE user_id = ?
    ");
    $update->execute([$full_name, $email, $username, $role, $user_id]);

    header("Location: user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .card {
            max-width: 600px;
            margin: 40px auto;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 1rem 1rem 0 0;
        }
        .btn {
            border-radius: 8px;
        }
    </style>
</head>
<body class="container mt-5">

    <div class="card">
        <div class="card-header text-center">
            <h3>✏️ แก้ไขข้อมูลสมาชิก</h3>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">ชื่อผู้ใช้</label>
                    <input type="text" name="username" class="form-control" 
                           value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" name="full_name" class="form-control" 
                           value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">อีเมล</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">สิทธิ์การใช้งาน</label>
                    <select name="role" class="form-select">
                        <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>>สมาชิก</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>แอดมิน</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="user.php" class="btn btn-secondary">⬅️ กลับ</a>
                    <button type="submit" class="btn btn-primary">💾 บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
