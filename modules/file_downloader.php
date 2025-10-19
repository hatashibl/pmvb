<?php
// modules/file_downloader.php
require_once '../core/auth.php';
check_login(); // Chỉ cho phép người dùng đã đăng nhập tải file

$vb_type = $_GET['type'] ?? ''; // 'vb_den' hoặc 'vb_di'
$file_name = $_GET['file'] ?? '';

if (empty($file_name) || !in_array($vb_type, ['vb_den', 'vb_di'])) {
    die("Tham số không hợp lệ.");
}

// 1. Khởi tạo đường dẫn an toàn
$folder_path = dirname(__DIR__, 2) . "/assets/files/{$vb_type}/"; // Đi đến thư mục assets/files/vb_den hoặc vb_di
$file_path = $folder_path . basename($file_name); // Sử dụng basename để tránh tấn công directory traversal

if (!file_exists($file_path)) {
    die("File không tồn tại.");
}

// 2. Logic kiểm tra quyền (Cần thiết lập chi tiết hơn nếu cần phân quyền theo văn bản)
// Ví dụ: Người dùng có phải là người nhập, người nhận, hay Admin không?
// Hiện tại, chỉ cần kiểm tra đã đăng nhập.

// 3. Thiết lập Header để tải file
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// 4. Đẩy nội dung file ra
readfile($file_path);
exit;

// Lưu ý: Trong các module giao diện (view_van_ban.php, tra_cuu,...)
// Thay thế link tải file trực tiếp (e.g., ../assets/files/vb_den/tenfile.pdf)
// bằng link qua file downloader: modules/file_downloader.php?type=vb_den&file=tenfile.pdf
?>