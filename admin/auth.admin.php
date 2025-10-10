<?php
// ✅ ป้องกันการเรียก session_start() ซ้ำซ้อน
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
