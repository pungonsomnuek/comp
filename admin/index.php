<?php
require '../config.php';
require 'auth.admin.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: "Prompt", sans-serif;
        }

        .admin-container {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 40px 50px;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .admin-header h2 {
            font-weight: 600;
            color: #2c3e50;
        }

        .admin-header p {
            color: #666;
        }

        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .admin-card {
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 500;
            color: #fff;
            text-decoration: none;
            transition: 0.3s;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-products {
            background: #3498db;
        }

        .btn-orders {
            background: #2ecc71;
        }

        .btn-users {
            background: #f39c12;
        }

        .btn-categories {
            background: #34495e;
        }

        .logout-btn {
            display: block;
            margin: 40px auto 0;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="admin-header">
            <h2>แผงควบคุมผู้ดูแลระบบ</h2>
            <p>ยินดีต้อนรับ, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        </div>

        <div class="admin-menu">
            <a href="products.php" class="admin-card btn-products">
                🛒 จัดการสินค้า
            </a>
            <a href="orders.php" class="admin-card btn-orders">
                📦 จัดการคำสั่งซื้อ
            </a>
            <a href="user.php" class="admin-card btn-users">
                👥 จัดการสมาชิก
            </a>
            <a href="categories.php" class="admin-card btn-categories">
                🗂 จัดการหมวดหมู่
            </a>
        </div>

        <div class="logout-btn">
            <a href="../logout.php" class="btn btn-secondary btn-lg">ออกจากระบบ</a>
        </div>
    </div>
</body>

</html>
