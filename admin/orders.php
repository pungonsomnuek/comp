<?php
require '../config.php';
require 'auth.admin.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ดึงคำสั่งซื้อทั้งหมด
$stmt = $conn->query("
    SELECT o.*, u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

require '../function.php'; // ฟังก์ชัน getOrderItems() และ getShippingInfo()

// อัปเดตสถานะคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        header("Location: orders.php");
        exit;
    }
    if (isset($_POST['update_shipping'])) {
        $stmt = $conn->prepare("UPDATE shipping SET shipping_status = ? WHERE shipping_id = ?");
        $stmt->execute([$_POST['shipping_status'], $_POST['shipping_id']]);
        header("Location: orders.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: "Prompt", sans-serif;
        }

        .container-main {
            max-width: 1100px;
            margin: 50px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.08);
            padding: 40px 50px;
        }

        h2 {
            font-weight: 600;
            color: #2c3e50;
        }

        .accordion-button {
            background: linear-gradient(45deg, #4e73df, #1cc88a);
            color: white;
            font-weight: 500;
        }

        .accordion-button:not(.collapsed) {
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .125);
        }

        .accordion-body {
            background-color: #f9fafc;
            border-radius: 10px;
        }

        .list-group-item {
            border: none;
            background: #fff;
            border-radius: 10px;
            margin-bottom: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .card-header {
            background-color: #4e73df;
            color: #fff;
            border-radius: 10px 10px 0 0;
        }

        .btn-custom {
            border-radius: 10px;
            font-weight: 500;
        }

        .badge {
            font-size: 0.9rem;
        }

        footer {
            text-align: center;
            color: #888;
            font-size: 14px;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <div class="container-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-clipboard-check me-2" style="color:#4e73df;"></i>คำสั่งซื้อทั้งหมด</h2>
            <a href="index.php" class="btn btn-secondary btn-custom">
                <i class="bi bi-arrow-left-circle me-1"></i> กลับหน้าผู้ดูแล
            </a>
        </div>

        <div class="accordion" id="ordersAccordion">
            <?php foreach ($orders as $index => $order): ?>
                <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
                <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="heading<?= $index ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse<?= $index ?>" aria-expanded="false"
                            aria-controls="collapse<?= $index ?>">
                            <i class="bi bi-box-seam me-2"></i>
                            คำสั่งซื้อ #<?= $order['order_id'] ?> |
                            <?= htmlspecialchars($order['username']) ?> |
                            <?= $order['order_date'] ?> |
                            <span class="badge bg-info text-dark ms-2"><?= ucfirst($order['status']) ?></span>
                        </button>
                    </h2>
                    <div id="collapse<?= $index ?>" class="accordion-collapse collapse"
                        aria-labelledby="heading<?= $index ?>" data-bs-parent="#ordersAccordion">
                        <div class="accordion-body">
                            <!-- รายการสินค้า -->
                            <h5 class="mb-3"><i class="bi bi-cart-check me-1 text-primary"></i> รายการสินค้า</h5>
                            <ul class="list-group mb-3">
                                <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?>
                                        <span><?= number_format($item['quantity'] * $item['price'], 2) ?> บาท</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <p class="fw-bold text-end">
                                ยอดรวม: <span class="text-success"><?= number_format($order['total_amount'], 2) ?> บาท</span>
                            </p>

                            <!-- อัปเดตสถานะคำสั่งซื้อ -->
                            <div class="card p-3 mb-3 shadow-sm border-0">
                                <h6 class="text-primary fw-bold mb-3">อัปเดตสถานะคำสั่งซื้อ</h6>
                                <form method="post" class="row g-2 align-items-center">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <div class="col-md-4">
                                        <select name="status" class="form-select">
                                            <?php
                                            $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                                            foreach ($statuses as $status) {
                                                $selected = ($order['status'] === $status) ? 'selected' : '';
                                                echo "<option value=\"$status\" $selected>$status</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" name="update_status" class="btn btn-primary btn-custom">
                                            <i class="bi bi-arrow-repeat me-1"></i> อัปเดต
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- ข้อมูลการจัดส่ง -->
                            <?php if ($shipping): ?>
                                <div class="card p-3 border-0 shadow-sm">
                                    <h6 class="text-success fw-bold mb-2">ข้อมูลจัดส่ง</h6>
                                    <p><strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>,
                                        <?= htmlspecialchars($shipping['city']) ?>
                                        <?= $shipping['postal_code'] ?></p>
                                    <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>

                                    <form method="post" class="row g-2 align-items-center">
                                        <input type="hidden" name="shipping_id" value="<?= $shipping['shipping_id'] ?>">
                                        <div class="col-md-4">
                                            <select name="shipping_status" class="form-select">
                                                <?php
                                                $s_statuses = ['not_shipped', 'shipped', 'delivered'];
                                                foreach ($s_statuses as $s) {
                                                    $selected = ($shipping['shipping_status'] === $s) ? 'selected' : '';
                                                    echo "<option value=\"$s\" $selected>$s</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" name="update_shipping"
                                                class="btn btn-success btn-custom">
                                                <i class="bi bi-truck me-1"></i> อัปเดตจัดส่ง
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <footer>© 2025 ระบบผู้ดูแล | Mr.Pungon Somnuek</footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
