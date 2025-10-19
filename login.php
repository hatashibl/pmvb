<?php
// login.php
require_once 'core/auth.php'; // Đảm bảo đã include auth.php
$error_message = '';

// Nếu người dùng đã đăng nhập, chuyển hướng ngay
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Vui lòng nhập tên đăng nhập và mật khẩu.';
    } else {
        $result = attempt_login($username, $password);
        if ($result['success']) {
            header("Location: index.php");
            exit();
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Quản lý Văn bản</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin-top: 100px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container mx-auto">
            <h3 class="text-center text-primary mb-4">HỆ THỐNG QLVB</h3>
            <p class="text-center">Đăng nhập để tiếp tục</p>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Tên đăng nhập</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
                </div>
            </form>
            
            <p class="text-center mt-3 text-muted">
                &copy; 2024 QLVB Project
            </p>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>