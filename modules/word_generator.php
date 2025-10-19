<?php
// modules/word_generator.php
require_once '../core/auth.php';
check_login();

// Bắt buộc: Include autoload của Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$type = $_GET['type'] ?? ''; // 'vb_di' hoặc 'vb_den'
$id = (int)($_GET['id'] ?? 0);

if (!$id || !in_array($type, ['vb_di', 'vb_den'])) {
    die("Tham số không hợp lệ.");
}

try {
    // 1. Lấy dữ liệu Văn bản từ CSDL
    global $pdo;
    $table = ($type == 'vb_di') ? 'van_ban_di' : 'van_ban_den';
    $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    if (!$data) {
        die("Không tìm thấy dữ liệu văn bản.");
    }

    // 2. Xác định đường dẫn Mẫu Word (Template)
    // Giả định bạn có các file mẫu trong thư mục 'assets/templates/'
    $templatePath = '../assets/templates/Mau_CongVan_Di.docx'; 
    if ($type == 'vb_den') {
         // Nếu là VB Đến, có thể cần mẫu để in phiếu xử lý
         $templatePath = '../assets/templates/Mau_PhieuXuLy_Den.docx'; 
    }
    
    if (!file_exists($templatePath)) {
        die("Lỗi: Không tìm thấy file mẫu Word.");
    }
    
    $templateProcessor = new TemplateProcessor($templatePath);

    // 3. Thay thế các Placeholder trong mẫu bằng dữ liệu
    $templateProcessor->setValue('SO_VB', $data['so'] ?? $data['so_den']);
    $templateProcessor->setValue('NGAY_THANG', date('d/m/Y', strtotime($data['ngay_thang'] ?? $data['ngay_den'])));
    $templateProcessor->setValue('TRICH_YEU', $data['trich_yeu']);
    $templateProcessor->setValue('NOI_GUI_NHAN', $data['noi_gui'] ?? $data['noi_nhan']);
    
    // (Thêm các setValue khác tương ứng với placeholders trong mẫu Word của bạn)

    // 4. Thiết lập Header và Lưu file
    $fileName = ($type == 'vb_di' ? 'VBDI_' : 'VBDEN_') . $id . '.docx';
    
    header("Content-Type: application/octet-stream");
    header('Content-Disposition: attachment; filename="' . $fileName . '"');

    // Lưu file tạm thời và xuất nội dung (vì PHPWord cần save trước khi xuất)
    $tempFile = tempnam(sys_get_temp_dir(), 'phpword');
    $templateProcessor->saveAs($tempFile);
    
    readfile($tempFile);
    unlink($tempFile); // Xóa file tạm
    exit;

} catch (Exception $e) {
    die("Lỗi tạo file Word: " . $e->getMessage());
}
?>