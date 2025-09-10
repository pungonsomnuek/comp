<?php 
    session_start();
    require_once 'config.php';

    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usernameOrEmail = trim($_POST['username_or_email']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if($user['role'] === 'admin'){
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>

    <!-- Bootstrap CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #b0ff05ff, #ffd500ff);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            max-width: 420px;
            margin: auto;
            margin-top: 90px;
            padding: 35px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0px 10px 25px rgba(0,0,0,0.15);
        }
        .login-card h3 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 700;
            color: #059f71ff;
        }
        .form-label {
            font-weight: 500;
            color: #060606ff;
        }
        .form-control {
            border-radius: 10px;
            padding: 10px;
        }
        .btn-primary {
            width: 100%;
            border-radius: 10px;
            background-color: #1e8d91ff;
            border: none;
            font-weight: 600;
            padding: 10px;
        }
        .btn-primary:hover {
            background-color: #ff0066ff;
        }
        .btn-link {
            display: block;
            text-align: center;
            margin-top: 12px;
            color: #d000ffff;
            font-weight: 500;
        }
        .btn-link:hover {
            color: #dd5486ff;
        }
        .alert {
            max-width: 420px;
            margin: 20px auto;
            border-radius: 10px;
        }
    </style>
</head>
<body>

    <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
        <div class="alert alert-success text-center shadow-sm"> ✅ สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center shadow-sm">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="login-card">
        <h3>🔑 เข้าสู่ระบบ</h3>
        <form method="post" class="row g-3">
            <div class="col-12">
                <label for="username_or_email" class="form-label">ชื่อผู้ใช้หรืออีเมล</label>
                <input type="text" name="username_or_email" id="username_or_email" class="form-control" required>
            </div>
            <div class="col-12">
                <label for="password" class="form-label">รหัสผ่าน</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
                <a href="register.php" class="btn btn-link">สมัครสมาชิก</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
