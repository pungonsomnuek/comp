<?php
session_start();
require_once 'config.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = (int) ($_SESSION['user_id'] ?? 0);

// ดึงรายการสินค้าในตะกร้า
$stmt = $conn->prepare("
    SELECT cart.cart_id, cart.quantity, cart.product_id, products.product_name, products.price
    FROM cart
    JOIN products ON cart.product_id = products.product_id
    WHERE cart.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -----------------------------
// คำนวณราคารวม
// -----------------------------
$total = 0.0;
foreach ($items as $item) {
    $qty = (int) ($item['quantity'] ?? 0);
    $price = (float) ($item['price'] ?? 0);
    $total += $qty * $price;
}

$errors = [];

// เมื่อผู้ใช้กดยืนยันการสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($address === '' || $city === '' || $postal_code === '' || $phone === '') {
        $errors[] = "กรุณากรอกข้อมูลให้ครบถ้วน 💕";
    }

    if (empty($items)) {
        $errors[] = "ตะกร้าของคุณว่าง ไม่สามารถสั่งซื้อได้ 😢";
    }

    if (empty($errors)) {
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'pending', NOW())");
            $stmt->execute([$user_id, $total]);
            $order_id = $conn->lastInsertId();

            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtItem->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            }

            $stmt = $conn->prepare("INSERT INTO shipping (order_id, address, city, postal_code, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $address, $city, $postal_code, $phone]);

            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);

            $conn->commit();
            header("Location: orders.php?success=1");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>🛍️ ยืนยันการสั่งซื้อ - My Lovely Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffe6f2;
            font-family: "Prompt", sans-serif;
        }

        .checkout-container {
            max-width: 950px;
            margin: 50px auto;
            background-color: #fff0f6;
            border-radius: 25px;
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.2);
            padding: 40px;
        }

        h2 {
            text-align: center;
            color: #ff66a3;
            font-weight: 600;
            margin-bottom: 25px;
        }

        h5 {
            color: #ff80aa;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .list-group-item {
            background-color: #fff;
            border: none;
            border-bottom: 1px dashed #ffcce0;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .summary {
            background-color: #ffe6f2;
            border-radius: 15px;
            padding: 15px;
            text-align: right;
            font-weight: 600;
            color: #ff4f88;
        }

        .form-control {
            border-radius: 15px;
            border: 1px solid #ffd1e1;
        }

        .btn-success {
            background-color: #ff85a2;
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
        }

        .btn-success:hover {
            background-color: #ff6f91;
        }

        .btn-secondary {
            background-color: #ff99c8;
            border-radius: 25px;
            border: none;
            padding: 10px 25px;
        }

        .btn-secondary:hover {
            background-color: #ff7bac;
        }

        .alert {
            border-radius: 15px;
            background-color: #ffe6ef;
            border: none;
            color: #ff4f7b;
        }

        footer {
            text-align: center;
            color: #ff80aa;
            font-size: 14px;
            margin-top: 40px;
        }

        .divider {
            height: 2px;
            background-color: #ffd1e1;
            margin: 25px 0;
            border-radius: 2px;
        }
    </style>
</head>

<body>
    <div class="checkout-container">
        <h2>🌸 ยืนยันการสั่งซื้อสินค้า 🌸</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="divider"></div>

        <!-- รายการสินค้า -->
        <h5>🛒 รายการสินค้าในตะกร้า</h5>
        <ul class="list-group mb-4">
            <?php if (empty($items)): ?>
                <li class="list-group-item text-center text-muted">ตะกร้าว่างค่ะ 💕</li>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                            <small class="text-muted">
                                ราคา <?= number_format($item['price'], 2) ?> ฿ × <?= $item['quantity'] ?>
                            </small>
                        </div>
                        <span><strong><?= number_format($item['price'] * $item['quantity'], 2) ?> ฿</strong></span>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item summary">
                    รวมทั้งหมด : <?= number_format($total, 2) ?> บาท
                </li>
            <?php endif; ?>
        </ul>

        <div class="divider"></div>

        <!-- ฟอร์มกรอกข้อมูล -->
        <h5>📦 ข้อมูลการจัดส่ง</h5>
        <form method="post" class="row g-3 mt-2">
            <div class="col-md-6">
                <label for="address" class="form-label">ที่อยู่จัดส่ง</label>
                <input type="text" name="address" id="address" class="form-control" required
                    value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="city" class="form-label">จังหวัด</label>
                <input type="text" name="city" id="city" class="form-control" required
                    value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="postal_code" class="form-label">รหัสไปรษณีย์</label>
                <input type="text" name="postal_code" id="postal_code" class="form-control" required
                    value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" name="phone" id="phone" class="form-control" required
                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>

            <div class="col-12 text-center mt-4">
                <button type="submit" class="btn btn-success" <?= empty($items) ? 'disabled' : '' ?>>✅ ยืนยันการสั่งซื้อ</button>
                <a href="cart.php" class="btn btn-secondary ms-2">← กลับไปตะกร้า</a>
            </div>
        </form>
    </div>

    <footer>
        © 2025 My Lovely Shop 💕 | Designed with love 🌷
    </footer>
</body>
</html>
