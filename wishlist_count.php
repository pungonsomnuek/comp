<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo 0;
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
echo $stmt->fetchColumn();
?>

// ไฟล์นี้จะส่งกลับจำนวนสินค้าที่อยู่ในรายการโปรดของผู้ใช้ (ใช้ AJAX เรียกได้ทุกหน้า)