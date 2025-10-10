<?php
require_once 'config.php';
session_start();

// ตรวจสอบสิทธิ์ (เฉพาะแอดมิน)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $category = trim($_POST['category']);
    $stock = trim($_POST['stock']);
    $desc = trim($_POST['description']);
    $imageName = null;

    // ถ้ามีไฟล์รูปถูกอัปโหลด
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "product_images/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $uploadDir . $imageName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
    }

    $stmt = $conn->prepare("INSERT INTO products (product_name, price, category_id, stock, description, image, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $price, $category, $stock, $desc, $imageName]);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มสินค้า</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-sm p-4">
        <h3 class="mb-3 text-center">เพิ่มสินค้าใหม่</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">ราคา</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">หมวดหมู่</label>
                <select name="category" class="form-select" required>
                    <option value="">-- เลือกหมวดหมู่ --</option>
                    <?php
                    $cats = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($cats as $c) {
                        echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">จำนวนสินค้าในสต็อก</label>
                <input type="number" name="stock" class="form-control" min="0" required>
            </div>
            <div class="mb-3">
                <label class="form-label">รายละเอียดสินค้า</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">เลือกรูปภาพสินค้า</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary w-100">บันทึกสินค้า</button>
        </form>
    </div>
</div>
</body>
</html>
