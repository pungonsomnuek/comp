<?php
session_start();
require_once 'config.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงสินค้าที่อยู่ในรายการโปรด
$stmt = $conn->prepare("
    SELECT p.*, c.category_name 
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$stmt->execute([$user_id]);
$wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>รายการโปรด 💖 | My Lovely Shop</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
body {
    background: #fff6fb;
    font-family: 'Segoe UI', sans-serif;
}
.navbar {
    background: linear-gradient(90deg, #ffb6c1, #ffd6e0);
}
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
.btn-wishlist {
    border: none;
    background: none;
    font-size: 1.7rem;
    color: #ff4f70;
    transition: 0.2s;
}
.btn-wishlist:hover {
    color: #999;
    transform: scale(1.2);
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-white" href="index.php">🌸 My Lovely Shop</a>
        <div class="d-flex">
            <a href="index.php" class="btn btn-outline-light btn-sm me-2">
                <i class="bi bi-house-heart"></i> หน้าหลัก
            </a>
            <a href="wishlist.php" class="btn btn-outline-light btn-sm me-2 position-relative">
                <i class="bi bi-heart-fill text-danger"></i> รายการโปรด
                <span id="wishlist-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                </span>
            </a>
            <a href="cart.php" class="btn btn-outline-warning btn-sm me-2">
                <i class="bi bi-cart4"></i> ตะกร้า
            </a>
            <a href="logout.php" class="btn btn-outline-dark btn-sm bg-white text-danger">ออกจากระบบ</a>
        </div>
    </div>
</nav>


<!-- Header -->
<header class="container text-center my-5">
    <h1 class="fw-bold" style="color:#ff4f70;">💖 รายการโปรดของคุณ</h1>
    <p class="text-muted">สินค้าที่คุณกดหัวใจไว้จะอยู่ที่นี่ค่ะ 🌷</p>
</header>

<div class="container">
    <div class="row g-4">
        <?php if (empty($wishlist)): ?>
            <div class="col-12 text-center text-muted">
                <p>ยังไม่มีสินค้าที่คุณชื่นชอบเลย 💔</p>
                <a href="index.php" class="btn btn-pink mt-3" style="background:#ff9ebb;color:white;">กลับไปเลือกสินค้า</a>
            </div>
        <?php else: ?>
            <?php foreach ($wishlist as $p): 
                $img = !empty($p['image']) ? 'product_images/' . rawurlencode($p['image']) : 'product_images/no-image.jpg';
            ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="product-card position-relative">
                    <img src="<?= htmlspecialchars($img) ?>" class="product-thumb w-100" alt="">
                    <button class="btn-wishlist position-absolute top-0 end-0 m-2" 
                            data-id="<?= $p['product_id'] ?>">
                        <i class="bi bi-heart-fill"></i>
                    </button>
                    <div class="p-3 text-center">
                        <div class="fw-bold"><?= htmlspecialchars($p['product_name']) ?></div>
                        <div class="text-muted small mb-2"><?= htmlspecialchars($p['category_name']) ?></div>
                        <div class="text-danger fw-bold"><?= number_format($p['price'], 2) ?> ฿</div>
                        <a href="product_detail.php?id=<?= $p['product_id'] ?>" class="btn btn-outline-primary btn-sm mt-2">ดูรายละเอียด</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<footer class="text-center py-4 mt-5">
    © <?= date('Y') ?> My Lovely Shop 🌷 | Designed with 💕
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    function updateWishlistCount() {
        $.get("wishlist_count.php", function(count) {
            $("#wishlist-count").text(count);
        });
    }

    updateWishlistCount();

    // เมื่อกดหัวใจในหน้า wishlist
    $(".btn-wishlist").click(function(){
        var btn = $(this);
        var productId = btn.data("id");
        $.post("wishlist_action.php", { product_id: productId }, function(res){
            try {
                var data = JSON.parse(res);
                if (data.status === 'removed') {
                    btn.closest(".col-12").fadeOut(300, function(){ $(this).remove(); });
                    updateWishlistCount();
                }
            } catch(e){
                console.error("Invalid JSON:", res);
            }
        });
    });
});
</script>


</body>
</html>
