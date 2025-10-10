<?php
session_start();
require 'config.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $item['cart_id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    header("Location: cart.php");
    exit;
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

// -----------------------------
// คำนวณราคารวม
// -----------------------------
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price'];
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>🛍️ ตะกร้าสินค้า - My Lovely Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffe6f2;
            font-family: "Prompt", sans-serif;
        }

        .cart-container {
            background-color: #fff0f6;
            border-radius: 20px;
            padding: 30px;
            max-width: 900px;
            margin: 40px auto;
            box-shadow: 0 4px 10px rgba(255, 105, 180, 0.2);
        }

        h2 {
            text-align: center;
            color: #ff66a3;
            font-weight: 600;
            margin-bottom: 25px;
        }

        table {
            background-color: #ffffff;
            border-radius: 15px;
            overflow: hidden;
        }

        thead {
            background-color: #ffb6d9;
            color: white;
        }

        .btn-secondary {
            background-color: #ff99c8;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #ff7bac;
        }

        .btn-danger {
            background-color: #ff6f91;
            border: none;
        }

        .btn-danger:hover {
            background-color: #ff4f7b;
        }

        .btn-success {
            background-color: #ff85a2;
            border: none;
        }

        .btn-success:hover {
            background-color: #ff6f91;
        }

        .alert {
            background-color: #ffe6ef;
            color: #ff4f7b;
            border: none;
            border-radius: 10px;
            text-align: center;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            color: #ff80aa;
            font-size: 14px;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 15px;
            color: #fff;
            background-color: #ff99c8;
            padding: 8px 18px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
        }

        .back-btn:hover {
            background-color: #ff7bac;
        }
    </style>
</head>

<body>
    <div class="cart-container">
        <h2>🛒 ตะกร้าสินค้าของคุณ</h2>
        <div class="text-center mb-3">
            <a href="index.php" class="back-btn">← กลับไปเลือกสินค้า</a>
        </div>

        <?php if (count($items) === 0): ?>
            <div class="alert alert-warning">ยังไม่มีสินค้าในตะกร้าเลยค่ะ 💕</div>
        <?php else: ?>
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวน</th>
                        <th>ราคาต่อหน่วย</th>
                        <th>รวม</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'], 2) ?> ฿</td>
                            <td><?= number_format($item['price'] * $item['quantity'], 2) ?> ฿</td>
                            <td>
                                <a href="cart.php?remove=<?= $item['cart_id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('คุณต้องการลบสินค้านี้ออกจากตะกร้าหรือไม่คะ? 💔')">ลบ</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>ยอดรวมทั้งหมด:</strong></td>
                        <td colspan="2"><strong><?= number_format($total, 2) ?> ฿</strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="text-center">
                <a href="checkout.php" class="btn btn-success px-4 py-2 rounded-pill">🛍️ ดำเนินการสั่งซื้อ</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        © 2025 My Lovely Shop 🌸 | Designed with 💕
    </footer>
</body>
</html>
