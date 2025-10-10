<?php
session_start();
require_once 'config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? 0;

// ดึงสินค้า
$stmt = $conn->query("
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงสินค้าที่อยู่ในรายการโปรดของสมาชิก
$wishlist_ids = [];
if ($isLoggedIn) {
    $w = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id=?");
    $w->execute([$user_id]);
    $wishlist_ids = array_column($w->fetchAll(PDO::FETCH_ASSOC), 'product_id');
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Lovely Shop | หน้าหลัก</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
body {
    background: #fff6fb;
    font-family: 'Segoe UI', sans-serif;
}

/* Navbar */
.navbar {
    background: linear-gradient(90deg, #ffb6c1, #ffd6e0);
    padding: 0.8rem 1rem;
}
.navbar-brand {
    color: #fff !important;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
    font-weight: bold;
    font-size: 1.4rem;
}
.navbar .btn {
    border-radius: 20px;
    font-weight: 500;
    box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    transition: all 0.2s;
}
.navbar .btn:hover {
    transform: translateY(-2px);
}

/* Product Cards */
.product-card {
    background: #fff;
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    transition: 0.3s;
    box-shadow: 0 6px 12px rgba(255, 182, 193, 0.25);
}
.product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 20px rgba(255, 182, 193, 0.4);
}
.product-thumb {
    height: 230px;
    object-fit: cover;
}
.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #444;
}
.price {
    font-size: 1.2rem;
    color: #ff4f70;
    font-weight: bold;
}

/* Wishlist button */
.btn-wishlist {
    border: none;
    background: none;
    font-size: 1.7rem;
    color: #d6d6d6;
    transition: all 0.25s ease;
}
.btn-wishlist:hover {
    color: #ff7d9a;
    transform: scale(1.2);
}
.btn-wishlist.active {
    color: #ff4f70;
    animation: heartbeat 0.4s ease;
}
@keyframes heartbeat {
    0% { transform: scale(1); }
    25% { transform: scale(1.3); }
    50% { transform: scale(1); }
    75% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Buttons */
.btn-pink {
    background: #ff9ebb;
    color: #fff;
    border: none;
    border-radius: 20px;
    transition: 0.2s;
}
.btn-pink:hover {
    background: #ff7aa1;
}

.btn-outline-primary {
    border-color: #ff9ebb;
    color: #ff7aa1;
    border-radius: 20px;
}
.btn-outline-primary:hover {
    background: #ff9ebb;
    color: white;
}

/* Footer */
footer {
    background: #fff;
    color: #999;
    border-top: 2px solid #ffe3ed;
}
</style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-dark" href="index.php">🌸 My Lovely Shop</a>
        <div class="d-flex">
            <a href="wishlist.php" class="btn btn-outline-danger btn-sm me-2">
                ❤️ รายการโปรด <span id="wishlist-count" class="badge bg-danger ms-1">0</span>
            </a>
            <a href="cart.php" class="btn btn-outline-warning btn-sm me-2">
                🛒 ตะกร้าสินค้า <span id="cart-count" class="badge bg-warning text-dark ms-1">0</span>
            </a>
            <a href="logout.php" class="btn btn-outline-dark btn-sm">ออกจากระบบ</a>
        </div>
    </div>
</nav>



<!-- Header -->
<header class="container text-center my-5">
    <h1 class="fw-bold" style="color:#ff4f70;">🛍️ สินค้าทั้งหมด</h1>
    <p class="text-muted">เลือกของที่คุณรักได้เลย 💖</p>
</header>

<!-- Product Grid -->
<div class="container">
    <div class="row g-4">
        <?php foreach ($products as $p): 
            // ป้องกันชื่อไฟล์ซ้ำ .jpg.jpg และ encode เฉพาะช่องว่าง/อักขระพิเศษ
            $imgFile = !empty($p['image']) ? $p['image'] : 'no-image.jpg';
            // ลบ .jpg.jpg ซ้ำ (ถ้ามี)
            $imgFile = preg_replace('/(\.jpg|\.png){2,}$/i', '$1', $imgFile);
            $img = 'product_images/' . rawurlencode($imgFile);
            $isFav = in_array($p['product_id'], $wishlist_ids);
        ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="product-card position-relative">
                <img src="<?= htmlspecialchars($img) ?>" class="product-thumb w-100" alt="<?= htmlspecialchars($imgFile) ?>"
                     onerror="this.onerror=null;this.src='product_images/no-image.jpg';this.alt='ไม่พบรูป';">
                <!-- debug: <small style='color:red;'><?= htmlspecialchars($img) ?></small> -->
                <button class="btn-wishlist position-absolute top-0 end-0 m-2 <?= $isFav ? 'active' : '' ?>"
                        data-id="<?= $p['product_id'] ?>">
                    <i class="bi bi-heart-fill"></i>
                </button>
                <div class="p-3 text-center">
                    <div class="product-title"><?= htmlspecialchars($p['product_name']) ?></div>
                    <div class="text-muted small mb-2"><?= htmlspecialchars($p['category_name']) ?></div>
                    <div class="price mb-3"><?= number_format($p['price'],2) ?> ฿</div>
                    <a href="product_detail.php?id=<?= $p['product_id'] ?>" class="btn btn-outline-primary btn-sm">ดูรายละเอียด</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Footer -->
<footer class="text-center py-4 mt-5">
    © <?= date('Y') ?> My Lovely Shop 🌷 | Designed with 💕
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $(".btn-wishlist").on("click", function () {
        const btn = $(this);
        const productId = btn.data("id");

        // ส่งข้อมูลไปยัง wishlist_add.php
        $.ajax({
            url: "wishlist_add.php",
            method: "POST",
            data: { id: productId },
            success: function (response) {
                response = response.trim();

                if (response === "added") {
                    btn.addClass("active");
                    btn.attr("title", "ลบออกจากรายการโปรด");
                } else if (response === "removed") {
                    btn.removeClass("active");
                    btn.attr("title", "เพิ่มในรายการโปรด");
                }

                // อัปเดตจำนวนรายการโปรด (นับใหม่)
                $.get("wishlist_count.php", function (count) {
                    $("#wishlist-count").text(count);
                });
            },
            error: function () {
                alert("เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์ 💔");
            }
        });
    });
});
</script>




</body>
</html>
