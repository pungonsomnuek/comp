<?php
require '../config.php';
require 'auth.admin.php'; // ตรวจสอบสทิ ธิ์admin

// ตรวจสอบกำรสง่ ขอ้มลูจำกฟอรม์
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u_id'])) {
$user_id = $_POST['u_id'];

//sql ลบผู้ใช้
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
    $stmt->execute([$user_id]);

// ลบผูใ้ชจ้ำกฐำนขอ้มลู


// สง่ผลลัพธก์ ลับไปยังหนำ้ user.php
header("Location: user.php");
exit;
}
?>