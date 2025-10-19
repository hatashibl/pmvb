<?php
// logout.php

// Bắt đầu phiên làm việc
session_start();

// Hủy tất cả các biến phiên
$_SESSION = array();

// Nếu muốn hủy phiên hoàn toàn, hủy cả cookie phiên.
// Lưu ý: Việc này sẽ làm hỏng phiên trong các ứng dụng khác nếu chúng dùng chung cookie phiên.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy phiên
session_destroy();

// Chuyển hướng về trang đăng nhập
header("Location: login.php");
exit;
?>