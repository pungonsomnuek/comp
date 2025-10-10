<?php
session_start();
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
exit;

require '../config.php';
require 'auth.admin.php';

// ตรวจสิทธิ์
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$user_id = (int)$_GET['id'];

// ✅ ดึงข้อมูลผู้ใช้
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("❌ ไม่พบข้อมูลผู้ใช้");
}

// ✅ อัปเดตข้อมูลเมื่อกดบันทึก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $update = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE user_id = ?");
    $update->execute([$full_name, $email, $role, $user_id]);

    header("Location: user.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="card shadow p-4">
        <h3 class="mb-4">✏️ แก้ไขข้อมูลสมาชิก</h3>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">ชื่อ-นามสกุล</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">อีเมล</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">สิทธิ์การใช้งาน</label>
                <select name="role" class="form-select" required>
                    <option value="member" <?= $user['role'] === 'member' ? 'selected' : '' ?>>member</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <a href="user.php" class="btn btn-secondary">⬅️ กลับ</a>
                <button type="submit" class="btn btn-primary">💾 บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</body>
</html>
