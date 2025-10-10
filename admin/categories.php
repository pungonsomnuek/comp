<?php
require '../config.php';
require 'auth.admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสิทธิ์แอดมิน
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ตั้งค่า PDO ให้แสดง error
if ($conn instanceof PDO) {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// ป้องกัน CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

// --- การทำงานหลัก ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
            throw new Exception('CSRF token ไม่ถูกต้อง');
        }

        // เพิ่มหมวดหมู่
        if (isset($_POST['add_category'])) {
            $name = trim($_POST['category_name']);
            if ($name === '') throw new Exception('กรุณากรอกชื่อหมวดหมู่');
            $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (:n)");
            $stmt->execute([':n' => $name]);
            $_SESSION['success'] = 'เพิ่มหมวดหมู่สำเร็จ ✅';
        }

        // แก้ไข
        if (isset($_POST['update_category'])) {
            $id = (int)$_POST['category_id'];
            $new = trim($_POST['new_name']);
            if ($new === '') throw new Exception('กรุณากรอกชื่อใหม่');
            $stmt = $conn->prepare("UPDATE categories SET category_name = :n WHERE category_id = :id");
            $stmt->execute([':n' => $new, ':id' => $id]);
            $_SESSION['success'] = 'อัปเดตหมวดหมู่เรียบร้อย ✏️';
        }

        // ลบ
        if (isset($_POST['delete_category'])) {
            $id = (int)$_POST['category_id'];
            $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = 'ไม่สามารถลบได้: มีสินค้าผูกกับหมวดหมู่นี้';
            } else {
                $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = :id");
                $stmt->execute([':id' => $id]);
                $_SESSION['success'] = 'ลบหมวดหมู่เรียบร้อย 🗑️';
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    header("Location: categories.php");
    exit;
}

$stmt = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_id ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการหมวดหมู่สินค้า | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f8fafc;
        }

        .container-box {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-weight: 600;
            color: #333;
        }

        .btn {
            border-radius: 8px;
        }

        .table thead {
            background-color: #0d6efd;
            color: white;
        }

        .table td {
            vertical-align: middle;
        }

        .form-control {
            border-radius: 8px;
        }

        .alert {
            border-radius: 8px;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            color: #777;
            font-size: 14px;
        }

        .back-btn {
            text-decoration: none;
            color: #555;
            font-size: 16px;
        }

        .back-btn:hover {
            color: #0d6efd;
        }
    </style>
</head>

<body>
    <div class="container-box">
        <div class="page-header">
            <h2><i class="bi bi-tags"></i> จัดการหมวดหมู่สินค้า</h2>
            <a href="dashboard.php" class="back-btn"><i class="bi bi-arrow-left-circle"></i> กลับหน้าหลัก</a>
        </div>

        <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); endif; ?>

        <!-- ฟอร์มเพิ่มหมวดหมู่ -->
        <form method="post" class="row g-3 mb-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <div class="col-md-8">
                <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่ใหม่..." required>
            </div>
            <div class="col-md-4 d-grid">
                <button type="submit" name="add_category" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> เพิ่มหมวดหมู่
                </button>
            </div>
        </form>

        <!-- ตารางรายการหมวดหมู่ -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="text-center">
                        <th style="width:10%">#</th>
                        <th style="width:45%">ชื่อหมวดหมู่</th>
                        <th style="width:30%">แก้ไขชื่อ</th>
                        <th style="width:15%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">ยังไม่มีหมวดหมู่</td>
                    </tr>
                    <?php else: foreach ($categories as $i => $cat): ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($cat['category_name']) ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="category_id" value="<?= (int)$cat['category_id'] ?>">
                                <input type="text" name="new_name" value="<?= htmlspecialchars($cat['category_name']) ?>"
                                    class="form-control" required>
                                <button type="submit" name="update_category" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <form method="post" onsubmit="return confirm('ต้องการลบหมวดหมู่นี้หรือไม่?');">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="category_id" value="<?= (int)$cat['category_id'] ?>">
                                <button type="submit" name="delete_category" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>© <?= date('Y') ?> ระบบจัดการหมวดหมู่สินค้า</footer>

</body>

</html>
