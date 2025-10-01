<?php
require_once 'config.php';

$error = [];// Array to hold error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];

    if (empty($username) || empty($fullname) || empty($email) || empty($password) || empty($confirmpassword)) {
        $error[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error[] = "กรุณากรอกอีเมลให้ถูกต้อง";
    } elseif ($password !== $confirmpassword) {
        $error[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว";
        }
    }

    if (empty($error)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(username, full_name, email, password,role) VALUES (?, ?, ?, ?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);

        header("Location: login.php?register=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            color: #1e3c72;
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .form-control {
            border-radius: 8px;
        }

        .btn-primary {
            background: #1e3c72;
            border: none;
            border-radius: 8px;
            width: 100%;
            padding: 10px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: #16325c;
        }

        .btn-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #1e3c72;
            font-weight: 500;
        }

        .btn-link:hover {
            color: #0d2350;
        }

        .alert {
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>สมัครสมาชิก</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($error as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="row g-3">
            <div class="col-12">
                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="กรุณากรอกชื่อผู้ใช้"
                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
            </div>
            <div class="col-12">
                <label for="fullname" class="form-label">ชื่อ-สกุล</label>
                <input type="text" name="fullname" id="fullname" class="form-control" placeholder="กรุณากรอกชื่อ-สกุล"
                    value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>" required>
            </div>
            <div class="col-12">
                <label for="email" class="form-label">อีเมล</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="กรุณากรอกอีเมล"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
            </div>
            <div class="col-12">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" name="password" id="password" class="form-control"
                    placeholder="กรุณากรอกรหัสผ่าน" required>
            </div>
            <div class="col-12">
                <label for="confirmpassword" class="form-label">ยืนยันรหัสผ่าน</label>
                <input type="password" name="confirmpassword" id="confirmpassword" class="form-control"
                    placeholder="กรุณายืนยันรหัสผ่าน" required>
            </div>
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
                <a href="login.php" class="btn btn-link">เข้าสู่ระบบ</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>