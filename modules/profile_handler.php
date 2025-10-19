<?php
// modules/profile_handler.php
require_once '../core/auth.php';
check_login();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    if ($action == 'update_info') {
        // Cập nhật Tên đầy đủ
        $ten_day_du = trim($_POST['ten_day_du'] ?? '');
        
        if (empty($ten_day_du)) {
            throw new Exception("Tên đầy đủ không được để trống.");
        }
        
        $stmt = $pdo->prepare("UPDATE nguoi_dung SET ten_day_du = ? WHERE id = ?");
        $stmt->execute([$ten_day_du, $user_id]);
        
        // Cập nhật session
        $_SESSION['ten_day_du'] = $ten_day_du; 
        
        echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin cá nhân thành công.']);

    } elseif ($action == 'update_password') {
        // Đổi Mật khẩu
        $mat_khau_moi = $_POST['mat_khau_moi'] ?? '';
        $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'] ?? '';
        
        if (strlen($mat_khau_moi) < 6) {
            throw new Exception("Mật khẩu phải có ít nhất 6 ký tự.");
        }
        if ($mat_khau_moi !== $xac_nhan_mat_khau) {
            throw new Exception("Mật khẩu mới và xác nhận mật khẩu không khớp.");
        }
        
        $hashed_password = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE nguoi_dung SET mat_khau = ?, lan_sai_hien_tai = 0, thoi_gian_khoa = NULL WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại với mật khẩu mới.']);
        
    } else {
        http_response_code(400);
        throw new Exception("Hành động không hợp lệ.");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>