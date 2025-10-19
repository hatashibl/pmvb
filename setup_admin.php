<?php
// setup_admin.php
// Dùng để kiểm tra và tạo tài khoản Admin mặc định nếu CSDL rỗng.

// Đường dẫn tương đối từ file này đến core/database.php
require_once 'core/database.php'; 

$default_username = 'admin';
$default_password = 'admin@123'; // Mật khẩu mặc định, NÊN đổi ngay sau khi đăng nhập
$hashed_password = password_hash($default_password, PASSWORD_DEFAULT);

try {
    // 1. Kiểm tra xem có bất kỳ người dùng nào trong CSDL chưa
    $stmt = $pdo->query("SELECT COUNT(id) FROM nguoi_dung");
    $user_count = $stmt->fetchColumn();

    if ($user_count == 0) {
        // 2. Nếu không có, tiến hành tạo tài khoản Admin mặc định
        $stmt_insert = $pdo->prepare("
            INSERT INTO nguoi_dung (ten_dang_nhap, ten_day_du, mat_khau, chuc_vu) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt_insert->execute([
            $default_username, 
            'Quản trị Hệ thống', 
            $hashed_password, 
            'admin'
        ]);

        $message = "🎉 Khởi tạo thành công!";
        $details = "Tài khoản Admin đã được tạo.<br>Tên đăng nhập: **$default_username**<br>Mật khẩu: **$default_password** (Vui lòng đăng nhập và đổi mật khẩu ngay lập tức).";
        $alert_type = 'success';
    } else {
        $message = "Tài khoản Admin đã tồn tại.";
        $details = "Hệ thống đã có $user_count người dùng. Không cần khởi tạo.";
        $alert_type = 'info';
    }

} catch (PDOException $e) {
    $message = "Lỗi CSDL khi khởi tạo.";
    $details = "Lỗi: " . $e->getMessage();
    $alert_type = 'danger';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khởi tạo Admin</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container" style="max-width: 600px; margin-top: 50px;">
        <h3 class="text-primary mb-4">SETUP HỆ THỐNG QLVB</h3>
        <div class="alert alert-<?= $alert_type ?>" role="alert">
            <h4><?= $message ?></h4>
            <p><?= $details ?></p>
        </div>
        <p class="text-center"><a href="login.php" class="btn btn-secondary">Chuyển đến trang Đăng nhập</a></p>
    </div>
</body>
</html>