<?php
require '../config.php';
require 'auth.admin.php';

// ✅ Admin Guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ✅ เพิ่มสินค้าใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name        = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);

    if ($name && $price > 0) {
        $imageName = null;
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png'];

            if (in_array($file['type'], $allowed)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '.' . $ext;
                $path = __DIR__ . '/../product_images/' . $imageName;
                move_uploaded_file($file['tmp_name'], $path);
            }
        }

        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id, image)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);
        header("Location: products.php");
        exit;
    }
}

// ✅ ลบสินค้า (พร้อมลบรูป)
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $imageName = $stmt->fetchColumn();

    try {
        $conn->beginTransaction();
        $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $del->execute([$product_id]);
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        header("Location: products.php");
        exit;
    }

    if ($imageName) {
        $baseDir = realpath(__DIR__ . '/../product_images');
        $filePath = realpath($baseDir . '/' . $imageName);
        if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
            @unlink($filePath);
        }
    }
    header("Location: products.php");
    exit;
}

// ✅ ดึงข้อมูลสินค้า + หมวดหมู่
$stmt = $conn->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.product_id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #eef2f7;
            font-family: "Prompt", sans-serif;
        }

        .card-custom {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: 0.3s;
        }

        .card-custom:hover {
            transform: translateY(-2px);
        }

        .table th {
            background: linear-gradient(45deg, #4e73df, #1cc88a);
            color: white;
            vertical-align: middle;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #eaf3ff;
            transition: 0.2s;
        }

        .btn-custom {
            border-radius: 10px;
        }

        footer {
            font-size: 14px;
            color: gray;
        }
    </style>
</head>

<body class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color:#2c3e50;">
            <i class="bi bi-box-seam me-2" style="color:#4e73df;"></i> จัดการสินค้า
        </h2>
        <a href="index.php" class="btn btn-secondary btn-custom">
            <i class="bi bi-arrow-left-circle me-1"></i> กลับหน้าผู้ดูแล
        </a>
    </div>

    <!-- 🧩 ฟอร์มเพิ่มสินค้า -->
    <div class="card-custom p-4 mb-4">
        <h5 class="mb-3" style="color:#2c3e50;">เพิ่มสินค้าใหม่</h5>
        <form method="post" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="product_name" class="form-control" placeholder="ชื่อสินค้า" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" name="price" class="form-control" placeholder="ราคา" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="stock" class="form-control" placeholder="จำนวน" required>
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select" required>
                    <option value="">เลือกหมวดหมู่</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>">
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <textarea name="description" class="form-control" placeholder="รายละเอียดสินค้า" rows="2"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">รูปสินค้า (jpg, png)</label>
                <input type="file" name="product_image" class="form-control">
            </div>
            <div class="col-12 text-end">
                <button type="submit" name="add_product" class="btn btn-primary btn-custom px-4">
                    <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้า
                </button>
            </div>
        </form>
    </div>

    <!-- 🧾 ตารางสินค้า -->
    <div class="card-custom p-3">
        <h5 class="mb-3" style="color:#2c3e50;">รายการสินค้า</h5>
        <div class="table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <th>หมวดหมู่</th>
                        <th>ราคา</th>
                        <th>คงเหลือ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['product_name']) ?></td>
                            <td><?= htmlspecialchars($p['category_name']) ?></td>
                            <td><span class="badge bg-primary"><?= number_format($p['price'], 2) ?> บาท</span></td>
                            <td><span class="badge bg-info text-dark"><?= $p['stock'] ?></span></td>
                            <td>
                                <a href="edit_products.php?id=<?= $p['product_id'] ?>"
                                    class="btn btn-sm btn-warning btn-custom me-1">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </a>
                                <a href="products.php?delete=<?= $p['product_id'] ?>"
                                    class="btn btn-sm btn-danger btn-custom"
                                    onclick="return confirm('ยืนยันการลบสินค้านี้?')">
                                    <i class="bi bi-trash"></i> ลบ
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="text-center mt-4 mb-2">
        © 2025 ระบบผู้ดูแล | Mr.Pungon Somnuek
    </footer>

</body>

</html>
