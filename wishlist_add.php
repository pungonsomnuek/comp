<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "not_logged_in";
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = (int)($_POST['id'] ?? 0);

if ($product_id <= 0) {
    echo "invalid";
    exit;
}

// ตรวจสอบว่ามีอยู่ในรายการโปรดแล้วหรือยัง
$stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);

if ($stmt->fetch()) {
    // ลบออก
    $del = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $del->execute([$user_id, $product_id]);
    echo "removed";
} else {
    // เพิ่มใหม่
    $add = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $add->execute([$user_id, $product_id]);
    echo "added";
}
?>
