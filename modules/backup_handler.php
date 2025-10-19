<?php
// modules/backup_handler.php
require_once '../core/auth.php';
check_login(); 
if (get_user_role() != 'admin') { 
    die("Truy cập bị từ chối."); 
}

$action = $_GET['action'] ?? '';

if ($action == 'backup_db') {
    $backup_file = 'db_backup_' . date("Ymd_His") . '.sql';

    // Thiết lập header để tải file
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $backup_file . '"');
    header('Content-Transfer-Encoding: binary');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Khởi tạo output buffer
    ob_start();

    // Lấy thông tin kết nối từ core/database.php
    $db_name = DB_NAME;
    $db_user = DB_USERNAME;
    $db_pass = DB_PASSWORD;
    $db_host = DB_SERVER;

    try {
        // Sử dụng lệnh mysqldump (yêu cầu quyền truy cập CLI và mysqldump phải có sẵn trong PATH)
        // Đây là phương pháp hiệu quả nhất cho DB lớn
        $command = "mysqldump --opt -h" . $db_host . " -u" . $db_user . " -p" . $db_pass . " " . $db_name;
        
        // Thực thi lệnh và đẩy kết quả ra output
        passthru($command);

    } catch (Exception $e) {
        // Nếu mysqldump không hoạt động, sử dụng phương pháp PHP thuần (chỉ cho DB nhỏ)
        // Đây là đoạn code phức tạp và chỉ nên dùng nếu mysqldump không khả dụng
        
        echo "-- Lỗi: Không thể thực thi lệnh mysqldump. Lỗi: " . $e->getMessage() . "\n";
        echo "-- Hãy sử dụng phpMyAdmin hoặc MySQL Workbench để sao lưu thủ công.\n";

    }

    $sql_output = ob_get_clean();
    echo $sql_output;
    exit();
}
// Các action khác (ví dụ: backup files) có thể được thêm vào đây.
?>