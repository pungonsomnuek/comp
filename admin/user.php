
<!DOCTYPE html>
<?php
 
        require '../config.php'; // เชื่อมต่อฐานข้อมูล
        require_once 'auth.admin.php';
    // ลบสมาชิก
        if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    // ป้องกันลบตัวเอง
        if ($user_id != $_SESSION['user_id']) {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
    $stmt->execute([$user_id]);
}
    header("Location: user.php");
    exit;
    }
    // ดึงข้อมูลสมาชิก
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดกำรสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- // cdn sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: #f8f9fa;
        }
        h2 {
            font-weight: bold;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
        }
        table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        thead {
            background: #0d6efd;
            color: #fff;
        }
        .btn {
            border-radius: 30px;
        }
    </style>
</head>
<body class="container mt-4">
    <h2 class="mb-3">📋 จัดการสมาชิก</h2>
    <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>
<?php if (count($users) === 0): ?>
    <div class="alert alert-warning text-center">ยังไม่มีสมาชิกในระบบ</div>
<?php else: ?>
    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered text-center align-middle">
        <thead>
        <tr>
            <th>ชื่อผู้ใช้</th>
            <th>ชื่อ-นามสกุล</th>
            <th>อีเมล</th>
            <th>วันที่สมัคร</th>
            <th>จัดการ</th>
        </tr>
        </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
        <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="btn btn-sm btn-warning">✏️ แก้ไข</a>
        <!-- <a href="users.php?delete=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger"
    onclick="return confirm('คุณต้องการลบสมาชิกนี้หรือไม่?')">🗑️ ลบ</a> -->
    
    <form action="delUser_Sweet.php" method="POST" style="display:inline;">
<input type="hidden" name="u_id" value="<?php echo $user['user_id']; ?>">
<button type="button" class="delete-button btn btn-danger btn-sm " data-user-id="<?php echo
$user['user_id']; ?>">ลบ</button>
    </form>
    </td>
    </tr>
    <?php endforeach; ?>
        </tbody>
            </table>
    </div>
<?php endif; ?>


<script>
// ฟังกช์ นั ส ำหรับแสดงกลอ่ งยนื ยัน SweetAlert2
function showDeleteConfirmation(userId) {
Swal.fire({
title: 'คุณแน่ใจหรือไม่?',
text: 'คุณจะไม่สำมำรถเรียกคืนข ้อมูลกลับได ้!',
icon: 'warning',
showCancelButton: true,
confirmButtonText: 'ลบ',
cancelButtonText: 'ยกเลิก',
}).then((result) => {
if (result.isConfirmed) {
// หำกผใู้ชย้นื ยัน ใหส้ ง่ คำ่ ฟอรม์ ไปยัง delete.php เพื่อลบข ้อมูล
const form = document.createElement('form');
form.method = 'POST';
form.action = 'delUser_Sweet.php';          //เปลี่ยนได้เเค่จุดที่คอมเม้นไว้
const input = document.createElement('input');
input.type = 'hidden';
input.name = 'u_id';                      //ต้องตั้งชื่อให้ตรงกับฟอม
input.value = userId;                    //ต้องตั้งชื่อให้ตรงกับฟอม
form.appendChild(input);               
document.body.appendChild(form);
form.submit();
}
});
}
// แนบตัวตรวจจับเหตุกำรณ์คลิกกับองค์ปุ ่่มลบทั ่ ้งหมดที่มีคลำส delete-button
const deleteButtons = document.querySelectorAll('.delete-button');
deleteButtons.forEach((button) => {
button.addEventListener('click', () => {
const userId = button.getAttribute('data-user-id');
showDeleteConfirmation(userId);
});
});
</script>
</body>
</html>
