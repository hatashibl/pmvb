<?php
// modules/van_ban_den_handler.php - Xử lý Thêm mới Văn bản Đến
require_once '../core/auth.php';
check_login();

// GIẢ ĐỊNH: File core/db.php hoặc file tương tự đã được require ở nơi khác để khởi tạo $pdo
global $pdo; 

header('Content-Type: application/json');

// --- HÀM TIỆN ÍCH (Copy từ ajax_handler để đảm bảo hoạt động độc lập) ---
function convert_date_to_sql($date_str) {
    if (!$date_str) return null;
    // Chuyển đổi từ DD/MM/YYYY sang YYYY-MM-DD
    $dt = DateTime::createFromFormat('d/m/Y', $date_str);
    return $dt ? $dt->format('Y-m-d') : null;
}
function clean_string_for_filename($string) {
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\\s]/', '-', $string);
    $string = preg_replace('/[\\s-]+/', '-', $string);
    $string = substr($string, 0, 50); 
    return trim($string, '-');
}
// ------------------------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['action'] ?? '') == 'save') {
    $pdo->beginTransaction();
    try {
        // 1. Lấy dữ liệu & Chuyển đổi ngày tháng
        $so_den = (int)($_POST['so_den'] ?? 0);
        $nam_den = (int)($_POST['nam_den'] ?? date('Y'));
        $so_van_ban = trim($_POST['so_van_ban'] ?? '');
        $ngay_thang_vb_post = trim($_POST['ngay_thang_vb'] ?? '');
        $ngay_thang_vb = convert_date_to_sql($ngay_thang_vb_post); // SỬA LỖI: Chuyển đổi ngày
        
        $loai_vb_id = (int)($_POST['loai_vb_id'] ?? 0);
        $trich_yeu = trim($_POST['trich_yeu'] ?? '');
        $de_xuat_xu_ly = trim($_POST['de_xuat_xu_ly'] ?? '');
        $noi_ban_hanh = trim($_POST['noi_ban_hanh'] ?? ''); // Thêm trường bị thiếu
        $nguoi_nhap_id = $_SESSION['user_id'] ?? 0;
        
        $file_dinh_kem_db = null;
        $upload_dir = '../Van_ban_den_file/'; // Đảm bảo thư mục này tồn tại

		// --- Bổ sung: KIỂM TRA TRÙNG LẶP SỐ ĐẾN VÀ NĂM ĐẾN ---
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM van_ban_den WHERE so_den = :so_den AND nam_den = :nam_den");
    $stmt_check->execute([':so_den' => $so_den, ':nam_den' => $nam_den]);
    if ($stmt_check->fetchColumn() > 0) {
        throw new Exception("Lỗi: Đã có Văn bản Đến số {$so_den} trong năm {$nam_den}.");
    }
    // ---------------------------------------------------------

    $file_dinh_kem_db = null;
    $loai_vb_ky_hieu = get_loai_vb_short($loai_vb_id, $pdo); // Lấy ký hiệu ngắn
		
		
		
		
		
        // 2. Xử lý File đính kèm
if (isset($_FILES['file_dinh_kem']) && $_FILES['file_dinh_kem']['error'] === UPLOAD_ERR_OK) {
        $file_info = $_FILES['file_dinh_kem'];
        $file_ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        
        // --- Cập nhật: TẠO TÊN FILE MỚI theo format ---
        $safe_trich_yeu = clean_string_for_filename($trich_yeu);
        // Format: Năm đến_số đến_Số văn bản_Ký hiêu ngắn_Trích yếu văn bản.ext
        $ten_file_moi = "{$nam_den}_{$so_den}_{$so_van_ban}_{$loai_vb_ky_hieu}_{$safe_trich_yeu}." . $file_ext;
        
        // --- Cập nhật: ĐƯỜNG DẪN LƯU FILE (Thêm thư mục Năm Đến) ---
        $upload_dir = '../Van_ban_den_file/' . $nam_den . '/';
        
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $upload_path = $upload_dir . $ten_file_moi;
        
        if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
            // Lưu tên file đầy đủ (kèm thư mục năm) vào CSDL
            $file_dinh_kem_db = $nam_den . '/' . $ten_file_moi; 
        } else {
            throw new Exception("Lỗi khi upload file.");
        }
    }
    
    // 3. Chèn dữ liệu vào VAN_BAN_DEN
    $stmt_insert = $pdo->prepare("
        INSERT INTO van_ban_den (so_den, nam_den, so_van_ban, ngay_thang_vb, noi_ban_hanh, loai_vb_id, trich_yeu, de_xuat_xu_ly, file_dinh_kem, nguoi_nhap_id, thoi_gian_nhap) 
        VALUES (:so_den, :nam_den, :so_van_ban, :ngay_thang_vb, :noi_ban_hanh, :loai_vb_id, :trich_yeu, :de_xuat_xu_ly, :file_dinh_kem, :nguoi_nhap_id, NOW())
    ");
    $stmt_insert->execute([
        // ... (Các tham số khác)
        ':file_dinh_kem' => $file_dinh_kem_db,
        ':nguoi_nhap_id' => $nguoi_nhap_id
    ]);
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Thêm Văn bản Đến thành công!', 'new_id' => $pdo->lastInsertId()]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400); // Bad Request cho lỗi nghiệp vụ
    error_log("Lỗi khi thêm Văn bản Đến: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
}

// ... Các action khác (nếu có)
?>