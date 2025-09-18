<?php
session_start();
require_once 'config.php';

// ตรวจสอบการเข้าสู่ระบบ
$isLoggedIn = isset($_SESSION['user_id']);  // ★ เปลี่ยนให้ใช้ชื่อตัวแปรเดียว

// ดึงสินค้า
$stmt = $conn->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- ★ Icons -->

    <style>
    .product-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: .5rem;
    }

    .product-thumb {
        height: 180px;
        object-fit: cover;
        border-radius: .5rem;
    }

    .product-meta {
        font-size: .75rem;
        letter-spacing: .05em;
        color: #8a8f98;
        text-transform: uppercase;
    }

    .product-title {
        font-size: 1rem;
        margin: .25rem 0 .5rem;
        font-weight: 600;
        color: #222;
    }

    .price {
        font-weight: 700;
    }

    .rating i {
        color: #ffc107;
    }

    /* ดาวสีทอง */
    .wishlist {
        color: #b9bfc6;
    }

    .wishlist:hover {
        color: #ff5b5b;
    }

    .badge-top-left {
        position: absolute;
        top: .5rem;
        left: .5rem;
        z-index: 2;
        border-radius: .375rem;
    }
    </style>
</head>

<body style="background:#f8f9fa;font-family:'Segoe UI',sans-serif;">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">My Shop</a>
            <div class="d-flex">
                <?php if ($isLoggedIn): ?>
                <span class="navbar-text text-white me-3">
                    👋 ยินดีต้อนรับ <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)
                </span>
                <a href="profile.php" class="btn btn-sm btn-info me-2">ข้อมูลส่วนตัว</a>
                <a href="cart.php" class="btn btn-sm btn-warning me-2">ตะกร้าสินค้า</a>
                <a href="logout.php" class="btn btn-sm btn-danger">ออกจากระบบ</a>
                <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-success me-2">เข้าสู่ระบบ</a>
                <a href="register.php" class="btn btn-sm btn-primary">สมัครสมาชิก</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="container text-center my-5">
        <h1 class="fw-bold" style="color:#333;">🛍️ รายการสินค้า</h1>
        <p class="text-muted">เลือกซื้อสินค้าที่คุณชื่นชอบได้เลย</p>
    </header>

    <div class="container">
        <div class="row g-4">
            <?php foreach ($products as $p): ?>
            <?php
        $img = !empty($p['image'])
            ? 'product_images/' . rawurlencode($p['image'])
            : 'product_images/no-image.jpg';

        $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7*24*3600);
        $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 5;

        $rating = isset($p['rating']) ? (float)$p['rating'] : 4.5;
        $full   = floor($rating);
        $half   = ($rating - $full) >= 0.5 ? 1 : 0;
        ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card product-card h-100 position-relative">
                    <?php if ($isNew): ?>
                    <span class="badge bg-success badge-top-left">NEW</span>
                    <?php elseif ($isHot): ?>
                    <span class="badge bg-danger badge-top-left">HOT</span>
                    <?php endif; ?>

                    <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="p-3 d-block">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>"
                            class="img-fluid w-100 product-thumb">
                    </a>

                    <div class="px-3 pb-3 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="product-meta">
                                <?= htmlspecialchars($p['category_name'] ?? 'Category') ?>
                            </div>
                            <button class="btn btn-link p-0 wishlist" title="Add to wishlist" type="button">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>

                        <a class="text-decoration-none" href="product_detail.php?id=<?= (int)$p['product_id'] ?>">
                            <div class="product-title">
                                <?= htmlspecialchars($p['product_name']) ?>
                            </div>
                        </a>

                        <div class="rating mb-2">
                            <?php for ($i=0; $i<$full; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                            <?php if ($half): ?><i class="bi bi-star-half"></i><?php endif; ?>
                            <?php for ($i=0; $i<5-$full-$half; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                        </div>

                        <div class="price mb-3">
                            <?= number_format((float)$p['price'], 2) ?> บาท
                        </div>

                        <div class="mt-auto d-flex gap-2">
                            <?php if ($isLoggedIn): ?>
                            <form action="cart.php" method="post" class="d-inline-flex gap-2">
                                <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-success">เพิ่มในตะกร้า</button>
                            </form>
                            <?php else: ?>
                            <small class="text-muted">เข้าสู่ระบบเพื่อสั่งซื้อ</small>
                            <?php endif; ?>
                            <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>"
                                class="btn btn-sm btn-outline-primary ms-auto">ดูรายละเอียด</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (count($products) === 0): ?>
            <div class="col-12">
                <div class="alert alert-warning text-center shadow-sm">ยังไม่มีสินค้าในระบบ</div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-white text-center text-muted py-3 mt-5 shadow-sm">
        &copy; <?= date("Y") ?> My Shop | Nawapath
    </footer>

</body>

</html>