<?php
    require '../config.php'; // ✅ เชื่อมต่อฐานข้อมูลด้วย PDO
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
            // แก้ไขแล้ว: ตัด NOW() ออก ให้คอลัมน์กับค่าตรงกัน (5 ต่อ 5)
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

    // ✅ ลบสินค้า
    // if (isset($_GET['delete'])) {
    //     $product_id = $_GET['delete'];

    //     $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    //     $stmt->execute([$product_id]);

    //     header("Location: products.php");
    //     exit;
    // }
    // ลบสนิ คำ้ (ลบไฟลร์ปู ดว้ย)
    if (isset($_GET['delete'])) {
        $product_id = (int)$_GET['delete']; // แคสต์เป็น int
        
        // 1) ดึงไฟล์รูปจาก DB ก่อน
        $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $imageName = $stmt->fetchColumn(); // null ถ้าไม่มีรูป

        // 2) ลบใน DB ด้วย Transaction
        try {
            $conn->beginTransaction();
            $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
            $del->execute([$product_id]);
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            // ใส่ flash message หรือ log ได้ตามต้องกำร
            header("Location: products.php");
        exit;
        }
        // 3) ลบไฟล์รูปหลัง DB ลบสำเร็จ
        if ($imageName) {
            $baseDir = realpath(__DIR__ . '/../product_images'); // โฟลเดอร์เก็บรูป
            $filePath = realpath($baseDir . '/' . $imageName);

            // กัน path traversal: ต ้องอยู่ใต้ $baseDir จริง ๆ
            if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
                @unlink($filePath); // ใช ้@ กัน warning ถำ้ลบไมส่ ำเร็จ
            }
        }

        header("Location: products.php");
        exit;
    }


    // ✅ ดึงรายการสินค้า (join categories)
    $stmt = $conn->query("
        SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        ORDER BY p.product_id DESC
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ ดึงหมวดหมู่ทั้งหมด
    $categories = $conn->query("
        SELECT * FROM categories ORDER BY category_name ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="container mt-4" style="background:#f8f9fa;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color:#2c3e50;">
            <i class="bi bi-box-seam me-2" style="color:#6c63ff;"></i> จัดการสินค้า
        </h2>
        <a href="index.php" class="btn btn-secondary" style="border-radius:8px;">
            <i class="bi bi-arrow-left-circle me-1"></i> กลับหน้าผู้ดูแล
        </a>
    </div>

    <!-- ✅ ฟอร์มเพิ่มสินค้าใหม่ -->
    <form method="post" enctype="multipart/form-data" class="row g-3 mb-4 shadow-sm p-3"
        style="border-radius:8px;background:#fff;">
        <h5 class="mb-3" style="color:#2c3e50;">เพิ่มสินค้าใหม่</h5>
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
            <label class="form-label">รูปสินคัา (jpg, png)</label>
            <input type="file" name="product_image" class="form-control">
        </div>

        <div class="col-12 text-end">
            <button type="submit" name="add_product" class="btn btn-primary" style="border-radius:8px;">
                <i class="bi bi-plus-circle me-1"></i> เพิ่มสินค้า
            </button>
        </div>
    </form>

    <!-- ✅ แสดงรายการสินค้า -->
    <h5 class="mb-3" style="color:#2c3e50;">รายการสินค้า</h5>
    <div class="table-responsive shadow-sm" style="border-radius:8px; overflow:hidden;">
        <table class="table table-bordered table-hover m-0 text-center align-middle">
            <thead class="table-dark">
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
                    <td><?= number_format($p['price'], 2) ?> บาท </td>
                    <td><?= $p['stock'] ?></td>
                    <td>
                        <a href="edit_products.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-warning me-1"
                            style="border-radius:8px;">
                            <i class="bi bi-pencil-square"></i> แก้ไข </a>
                        <a href="products.php?delete=<?= $p['product_id'] ?>" class="btn btn-sm btn-danger"
                            style="border-radius:8px;" onclick="return confirm('ยืนยันการลบสินค้านี้?')">
                            <i class="bi bi-trash"></i> ลบ </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <footer class="text-center mt-4 mb-2" style="color:gray; font-size:14px;">
        © 2025 ระบบผู้ดูแล | Nawapath
    </footer>

</body>

</html>