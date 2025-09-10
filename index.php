<?php
session_start(); // เริ่ม session

require_once 'config.php'; // เชื่อมต่อฐานข้อมูล
$isLoggedIn = isset($_SESSION['user_id']);// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่

$stmt = $conn->query("SELECT p.*,c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f4d8ffff;
        }

        h1 {
            font-weight: bold;
            color: #000000ff;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn {
            border-radius: 25px;
        }

        .navbar-box {
            background: #fff;
            border-radius: 15px;
            padding: 15px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-weight: bold;
            color: #212529;
        }

        .card-subtitle {
            font-style: italic;
        }
    </style>
</head>

<body class="container mt-4">

    <!-- ส่วนหัว -->
    <div class="d-flex justify-content-between align-items-center mb-4 navbar-box">
        <h1>🛒 รายการสินค้า</h1>

        <div>
            <?php if ($isLoggedIn): ?>
                <span class="me-3 text-dark fw-bold">
                    👋 ยินดีต้อนรับ, <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)
                </span>
                <a href="profile.php" class="btn btn-info btn-sm">ข้อมูลส่วนตัว</a>
                <a href="cart.php" class="btn btn-warning btn-sm">ดูตะกร้าสินค้า</a>
                <a href="logout.php" class="btn btn-danger btn-sm">ออกจากระบบ</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-success btn-sm">เข้าสู่ระบบ</a>
                <a href="register.php" class="btn btn-primary btn-sm">สมัครสมาชิก</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- รายการสินค้า -->
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            <?= htmlspecialchars($product['category_name']) ?>
                        </h6>
                        <p class="card-text flex-grow-1">
                            <?= nl2br(htmlspecialchars($product['description'])) ?>
                        </p>
                        <p><strong>💰 ราคา:</strong> <?= number_format($product['price'], 2) ?> บาท</p>

                        <div class="mt-auto">
                            <?php if ($isLoggedIn): ?>
                                <form action="cart.php" method="post" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?= $รหัสสินค้า ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-success">➕ เพิ่มในตะกร้า</button>
                                </form>
                            <?php else: ?>
                                <small class="text-muted">🔑 กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า</small>
                            <?php endif; ?>

                            <a href="product_detail.php?id=<?= $product['product_id'] ?>"
                                class="btn btn-sm btn-outline-primary float-end">📖 ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>
