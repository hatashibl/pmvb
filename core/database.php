<?php
// core/database.php

// Thiết lập thông tin kết nối CSDL
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Thay đổi khi triển khai thực tế
define('DB_PASSWORD', '');     // Thay đổi khi triển khai thực tế
define('DB_NAME', 'qlvb_project');

try {
    // Tạo chuỗi DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    // Khởi tạo PDO
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Báo lỗi dưới dạng Exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mặc định trả về mảng kết hợp
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Tắt chế độ giả lập prepare
    ]);
    
} catch (PDOException $e) {
    // Ghi log lỗi kết nối (không hiển thị chi tiết lỗi cho người dùng)
    error_log("Lỗi kết nối CSDL: " . $e->getMessage());
    
    // Hiển thị thông báo thân thiện
    die("Lỗi kết nối hệ thống. Vui lòng thử lại sau.");
}
?>