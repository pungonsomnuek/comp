<?php
session_start();
require 'config.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) { // ตรวจสอบการล็อกอินของผู้ใช้
    header("Location: login.php"); // ถ้ายังไม่ได้ล็อกอินจะไปหน้า login
    exit;
}
$user_id = $_SESSION['user_id']; // ดึง user_id จาก session

// -----------------------------
// ดึงรายการสินค้าจากตะกร้า
// -----------------------------
$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, products.product_name, products.price
                FROM cart
                JOIN products ON cart.product_id = products.product_id
                WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -----------------------------
// เพิ่มสินค้าลงในตะกร้า
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) { // ตรวจสอบการส่งข้อมูล
    $product_id = $_POST['product_id']; // product_id
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    // ตรวจสอบว่าสินค้าตัวนี้อยู่ในตะกร้าแล้วหรือยัง
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // ถ้ามีแล้วให้เพิ่มจำนวน
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $item['cart_id']]);
    } else {
        // ถ้ายังไม่มีให้เพิ่มสินค้าใหม่
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    header("Location: cart.php");
    exit;
}

// -----------------------------
// คำนวณราคาทั้งหมด
// -----------------------------
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price']; // คำนวณราคา
}

// -----------------------------
// ลบสินค้าจากตะกร้า
// -----------------------------
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: cart.php");
    exit;
}

// ดึงรายการสินค้าจากตะกร้า
$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, cart.product_id, products.product_name, products.price
FROM cart
JOIN products ON cart.product_id = products.product_id
WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>ตระกร้าสินค้า</h2>
    <a href="index.php" class="btn btn-secondary mb-3">← กลับไปเลือกสินค้า</a>
    <?php if (count($items) === 0): ?>
        <div class="alert alert-warning">ยังไม่มีสินค้าในตะกร้า</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ชื่อสินค้า</th>
                    <th>จำนวน</th>
                    <th>ราคาต่อหน่วย</th>
                    <th>ราคาทั้งหมด</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['price'], 2) ?></td> <!-- แก้ไขตรงนี้ -->
                        <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        <td>
                            <a href="cart.php?remove=<?= $item['cart_id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('คุณต้องการลบสินค้านี้ออกจากตะกร้าหรือไม่?')">ลบ</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>รวมทั้งหมด:</strong></td>
                    <td colspan="2"><strong><?= number_format($total, 2) ?> บาท</strong></td>
                </tr>
            </tbody>
        </table>
        <a href="checkout.php" class="btn btn-success">สั่งซื้อสินค้า</a>
    <?php endif; ?>
</body>

</html>
