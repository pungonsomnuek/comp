<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'wishlist_count' => 0,
        'cart_count' => 0
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงจำนวนสินค้าใน wishlist
$stmt = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$stmt->execute([$user_id]);
$wishlist_count = $stmt->fetchColumn();

// ดึงจำนวนสินค้าใน cart
$stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_count = $stmt->fetchColumn();

echo json_encode([
    'wishlist_count' => $wishlist_count,
    'cart_count' => $cart_count
]);
