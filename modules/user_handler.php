<?php
// modules/user_handler.php
require_once '../core/auth.php';
check_login();

if (get_user_role() != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Truy cập bị từ chối.']);
    exit;
}

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $action == 'get_user' && isset($_GET['id'])) {
    // Lấy thông tin người dùng để sửa
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT id, ten_day_du, ten_dang_nhap, chuc_vu FROM nguoi_dung WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại.']);
    }
    exit;
} 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_day_du = trim($_POST['ten_day_du'] ?? '');
    $ten_dang_nhap = trim($_POST['ten_dang_nhap'] ?? '');
    $mat_khau = $_POST['mat_khau'] ?? '';
    $chuc_vu = $_POST['chuc_vu'] ?? 'thanh_vien';
    $user_id = (int)($_POST['user_id'] ?? 0);

    try {
        if ($action == 'add') {
            // Kiểm tra tên đăng nhập đã tồn tại
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM nguoi_dung WHERE ten_dang_nhap = ?");
            $stmt->execute([$ten_dang_nhap]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Tên đăng nhập đã tồn tại.");
            }
            if (empty($mat_khau)) {
                 throw new Exception("Mật khẩu không được để trống khi thêm mới.");
            }

            $hashed_password = password_hash($mat_khau, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO nguoi_dung (ten_dang_nhap, ten_day_du, mat_khau, chuc_vu) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ten_dang_nhap, $ten_day_du, $hashed_password, $chuc_vu]);
            echo json_encode(['success' => true, 'message' => 'Thêm người dùng thành công.']);

        } elseif ($action == 'edit' && $user_id > 0) {
            $sql = "UPDATE nguoi_dung SET ten_day_du = ?, ten_dang_nhap = ?, chuc_vu = ? ";
            $params = [$ten_day_du, $ten_dang_nhap, $chuc_vu, $user_id];
            
            // Nếu có nhập mật khẩu mới
            if (!empty($mat_khau)) {
                $hashed_password = password_hash($mat_khau, PASSWORD_DEFAULT);
                $sql .= ", mat_khau = ? ";
                // Thêm mật khẩu vào vị trí thứ 4 (trước id)
                array_splice($params, count($params) - 1, 0, $hashed_password); 
            }
            $sql .= "WHERE id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'message' => 'Cập nhật người dùng thành công.']);
            
        } elseif ($action == 'delete' && $user_id > 0) {
            if ($user_id == $_SESSION['user_id']) {
                 throw new Exception("Không thể xóa chính mình.");
            }
            $stmt = $pdo->prepare("DELETE FROM nguoi_dung WHERE id = ?");
            $stmt->execute([$user_id]);
            echo json_encode(['success' => true, 'message' => 'Xóa người dùng thành công.']);
        } else {
            throw new Exception("Yêu cầu không hợp lệ.");
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>