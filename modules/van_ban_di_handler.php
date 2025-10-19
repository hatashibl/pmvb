<?php
// modules/van_ban_di_handler.php
require_once '../core/auth.php';
check_login();

// Hàm chuẩn hóa chuỗi (tạo slug không dấu)
function slugify($text) {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-zA-Z0-9]/', '', $text);
    return strtolower($text);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'save') {
    $loai_vb_id = (int)$_POST['loai_vb_id'];
    $ngay_thang = $_POST['ngay_thang'];
    $trich_yeu = $_POST['trich_yeu'];
    $tra_loi_vb_den_id = !empty($_POST['tra_loi_vb_den_id']) ? (int)$_POST['tra_loi_vb_den_id'] : NULL;
    $nguoi_nhap_id = $_SESSION['user_id'];
    
    $pdo->beginTransaction();
    try {
        // 1. Lấy và Tăng số văn bản tự động (Locking để đảm bảo độc quyền cấp số)
        $stmt_lock = $pdo->prepare("SELECT ten_viet_tat, so_cuoi_vb_di FROM loai_van_ban WHERE id = :id FOR UPDATE");
        $stmt_lock->bindParam(':id', $loai_vb_id);
        $stmt_lock->execute();
        $loai_vb_data = $stmt_lock->fetch(PDO::FETCH_ASSOC);

        if (!$loai_vb_data) {
            throw new Exception("Loại văn bản không tồn tại.");
        }

        $new_so = $loai_vb_data['so_cuoi_vb_di'] + 1;
        $ten_viet_tat = $loai_vb_data['ten_viet_tat'];
        
        // 2. Cập nhật số cuối cùng đã sử dụng
        $stmt_update_so = $pdo->prepare("UPDATE loai_van_ban SET so_cuoi_vb_di = :new_so WHERE id = :id");
        $stmt_update_so->execute([':new_so' => $new_so, ':id' => $loai_vb_id]);

        // 3. Xử lý File Upload và Đổi tên File
        $file_dinh_kem_db = NULL;
        if (isset($_FILES['file_dinh_kem']) && $_FILES['file_dinh_kem']['error'] == UPLOAD_ERR_OK) {
            $file_info = $_FILES['file_dinh_kem'];
            $file_ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
            
            // TT123_020125_ToTrinhDaiHoi
            $ten_file_moi = 
                $ten_viet_tat . 
                $new_so . 
                '_' . date('dmy', strtotime($ngay_thang)) . 
                '_' . substr(slugify(strip_tags($trich_yeu)), 0, 50) . 
                '.' . $file_ext;
            
            $upload_path = '../assets/files/vb_di/' . $ten_file_moi;
            
            if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                $file_dinh_kem_db = $ten_file_moi;
            } else {
                throw new Exception("Lỗi khi upload file.");
            }
        }
        
        // 4. Chèn dữ liệu vào VAN_BAN_DI
        $stmt_insert = $pdo->prepare("
            INSERT INTO van_ban_di (so, ngay_thang, loai_vb_id, trich_yeu, tra_loi_vb_den_id, file_dinh_kem, nguoi_nhap_id) 
            VALUES (:so, :ngay_thang, :loai_vb_id, :trich_yeu, :tra_loi_vb_den_id, :file_dinh_kem, :nguoi_nhap_id)
        ");
        $stmt_insert->execute([
            ':so' => (string)$new_so, // Lưu số VB
            ':ngay_thang' => $ngay_thang,
            ':loai_vb_id' => $loai_vb_id,
            ':trich_yeu' => $trich_yeu,
            ':tra_loi_vb_den_id' => $tra_loi_vb_den_id,
            ':file_dinh_kem' => $file_dinh_kem_db,
            ':nguoi_nhap_id' => $nguoi_nhap_id
        ]);
        
        $pdo->commit();
        
        // CHỈ HIỂN THỊ SỐ KHI LƯU THÀNH CÔNG
        $message = "Lưu văn bản đi thành công! Số văn bản đã được cấp: <strong>{$new_so}/{$ten_viet_tat}</strong>.";
        $vb_di_id = $pdo->lastInsertId(); // Lấy ID VB vừa tạo
        
        // Chuyển hướng về trang nhập/sửa (ví dụ: load lại trang và hiển thị thông báo)
        echo json_encode(['success' => true, 'message' => $message, 'id' => $vb_di_id]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "Lỗi: " . $e->getMessage()]);
    }
    exit;
}
?>