<?php
// modules/cai_dat_handler.php
require_once '../core/auth.php';
check_login();

if (get_user_role() != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Truy cập bị từ chối.']);
    exit;
}

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

try {
    if ($action == 'update_settings') {
        // Cập nhật Cài đặt chung
        $settings = [
            'ten_phan_mem' => $_POST['ten_phan_mem'] ?? '',
            'copyright' => $_POST['copyright'] ?? '',
            'nam_hien_hanh' => $_POST['nam_hien_hanh'] ?? date('Y')
        ];

        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO cai_dat_he_thong (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
            $stmt->execute([$key, $value, $value]);
        }
        echo json_encode(['success' => true, 'message' => 'Cập nhật cài đặt hệ thống thành công.']);
        
    } elseif ($action == 'get_lvb' && isset($_GET['id'])) {
        // Lấy thông tin Loại VB
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM loai_van_ban WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);

    } elseif ($action == 'add_lvb' || $action == 'edit_lvb') {
        // Thêm/Sửa Loại Văn bản
        $ten_loai_vb = trim($_POST['ten_loai_vb'] ?? '');
        $ten_viet_tat = trim($_POST['ten_viet_tat'] ?? '');
        $goi_y_trich_yeu = $_POST['goi_y_trich_yeu'] ?? '';
        $goi_y_xu_ly = $_POST['goi_y_xu_ly'] ?? '';
        $lvb_id = (int)($_POST['lvb_id'] ?? 0);
        
        if (empty($ten_loai_vb) || empty($ten_viet_tat)) {
            throw new Exception("Tên loại VB và Tên viết tắt không được để trống.");
        }

        if ($action == 'add_lvb') {
            $stmt = $pdo->prepare("INSERT INTO loai_van_ban (ten_loai_vb, ten_viet_tat, goi_y_trich_yeu, goi_y_xu_ly) VALUES (?, ?, ?, ?)");
            $stmt->execute([$ten_loai_vb, $ten_viet_tat, $goi_y_trich_yeu, $goi_y_xu_ly]);
            echo json_encode(['success' => true, 'message' => 'Thêm Loại Văn bản thành công.']);
        } else {
            $stmt = $pdo->prepare("UPDATE loai_van_ban SET ten_loai_vb = ?, ten_viet_tat = ?, goi_y_trich_yeu = ?, goi_y_xu_ly = ? WHERE id = ?");
            $stmt->execute([$ten_loai_vb, $ten_viet_tat, $goi_y_trich_yeu, $goi_y_xu_ly, $lvb_id]);
            echo json_encode(['success' => true, 'message' => 'Cập nhật Loại Văn bản thành công.']);
        }

    } elseif ($action == 'delete_lvb' && isset($_POST['id'])) {
        // Xóa Loại Văn bản
        $id = (int)$_POST['id'];
        // TODO: Cần kiểm tra ràng buộc khóa ngoại trước khi xóa
        $stmt = $pdo->prepare("DELETE FROM loai_van_ban WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Xóa Loại Văn bản thành công.']);
    
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>