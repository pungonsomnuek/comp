<?php
require '../config.php';
require 'auth.admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = 'member';

    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $full_name, $email, $role]);

    header("Location: user.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เพิ่มสมาชิกใหม่</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-lg rounded-4">
        <div class="card-header bg-success text-white text-center">
            <h3>➕ เพิ่มสมาชิกใหม่</h3>
        </div>
        <div class="card-body p-4">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">ชื่อผู้ใช้</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">รหัสผ่าน</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">อีเมล</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success px-4">บันทึก</button>
                    <a href="user.php" class="btn btn-secondary px-4">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
