<?php
    session_start(); // เริ่ม session
require_once 'config.php'; // เชื่อมต่อฐานข้อมูล
if (!isset($_GET['id'])) { // ตรวจสอบว่ามีการส่ง ID ของสินค้าเข้ามาหรือไม่
header("Location: index.php");
exit;
}
$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT p.*, c.category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
echo "<h3>ไม่พบสินค้าที่คุณต้องการ</h3>";
exit;
}
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>รายละเอียดสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: #bacdf4ff;
    }
    .card {
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .card-title {
        font-weight: bold;
        color: #e195bdff;
    }
    .btn {
        border-radius: 25px;
        padding: 8px 20px;
    }
    .product-info p {
        font-size: 1.1rem;
        margin-bottom: 8px;
    }
</style>
</head>
<?php
// สร้างตัวแปรสี container จาก PHP
$containerColor = "linear-gradient(135deg, #ffffff, #f3e5f5)"; // ค่า default

if (!empty($product['category_name'])) {
    switch ($product['category_name']) {
        case "เสื้อผ้า":
            $containerColor = "linear-gradient(135deg, #fce4ec, #f8bbd0)";
            break;
        case "อิเล็กทรอนิกส์":
            $containerColor = "linear-gradient(135deg, #e3f2fd, #bbdefb)";
            break;
        case "อาหาร":
            $containerColor = "linear-gradient(135deg, #fff9c4, #ffe082)";
            break;
        default:
            $containerColor = "linear-gradient(135deg, #ffffff, #f3e5f5)";
    }
}
?>

<body class="container mt-4">
<a href="index.php" class="btn btn-secondary mb-3">← กลับหน้ารายการสินค้า</a>
<div class="card">
    <div class="card-body">
        <h3 class="card-title mb-3"><?= htmlspecialchars($product['product_name']) ?></h3>
        <h6 class="text-muted mb-3">📂 หมวดหมู่: <?= htmlspecialchars($product['category_name']) ?></h6>
        <p class="card-text mt-3"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        
        <div class="product-info mt-3">
            <p><strong>💰 ราคา:</strong> <?= number_format($product['price'], 2) ?> บาท</p>
            <p><strong>📦 คงเหลือ:</strong> <?= $product['stock'] ?> ชิ้น</p>
        </div>

        <?php if ($isLoggedIn): ?>
        <form action="cart.php" method="post" class="mt-3">
            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
            <label for="quantity" class="form-label">จำนวน:</label>
            <input type="number" name="quantity" id="quantity" class="form-control w-25 d-inline-block"
                   value="1" min="1" max="<?= $product['stock'] ?>" required>
            <button type="submit" class="btn btn-success ms-2">🛒 เพิ่มในตะกร้า</button>
        </form>
        <?php else: ?>
        <div class="alert alert-info mt-3 text-center">🔑 กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
