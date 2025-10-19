<?php
// modules/forward_handler.php
require_once '../core/auth.php';
check_login();

header('Content-Type: application/json');

$sender_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action == 'forward') {
    $vb_id = (int)($_POST['vb_id'] ?? 0);
    $receiver_ids = $_POST['receiver_id'] ?? [];
    $message = trim($_POST['message'] ?? '');
    
    if ($vb_id <= 0 || empty($receiver_ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng chọn Văn bản và Người nhận.']);
        exit;
    }

    $pdo->beginTransaction();
    try {
        // 1. Tạo bản ghi thông báo chính
        $stmt_tb = $pdo->prepare("INSERT INTO thong_bao (vb_den_id, nguoi_gui_id, noi_dung, thoi_gian_gui) VALUES (?, ?, ?, NOW())");
        $stmt_tb->execute([$vb_id, $sender_id, $message]);
        $thong_bao_id = $pdo->lastInsertId();
        
        // 2. Tạo bản ghi cho từng người nhận
        $sql_nhan = "INSERT INTO thong_bao_nguoi_nhan (thong_bao_id, nguoi_nhan_id, da_doc) VALUES (?, ?, 0)";
        $stmt_nhan = $pdo->prepare($sql_nhan);
        
        foreach ($receiver_ids as $receiver_id) {
            $stmt_nhan->execute([$thong_bao_id, (int)$receiver_id]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Chuyển tiếp văn bản thành công cho ' . count($receiver_ids) . ' người.']);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "Lỗi: " . $e->getMessage()]);
    }
    exit;
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
}
?>