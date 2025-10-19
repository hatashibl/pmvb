<?php
// modules/ajax_inbox_handler.php
require_once '../core/auth.php';
check_login();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$type = $_GET['type'] ?? 'inbox'; // 'inbox' (Hộp thư đến) hoặc 'outbox' (Hộp thư đi)
$page = (int)($_GET['page'] ?? 1);
$per_page = 20; // Số lượng bản ghi mỗi trang
$offset = ($page - 1) * $per_page;

$where_clause = "WHERE 1=1";
$params = [];

// Xử lý bộ lọc tìm kiếm
$keyword = trim($_GET['keyword'] ?? '');
if ($keyword) {
    $where_clause .= " AND (vbd.trich_yeu LIKE :keyword OR vbd.so_van_ban LIKE :keyword)";
    $params[':keyword'] = '%' . $keyword . '%';
}

try {
    if ($type === 'inbox') {
        // --- 1. Hộp thư ĐẾN (VB được gửi đến người dùng) ---
        
        $sql_select = "
            SELECT 
                tbn.da_doc,
                tb.thoi_gian_gui,
                vbd.so_den, vbd.nam_den, vbd.trich_yeu, vbd.file_dinh_kem,
                nd_gui.ten_day_du AS nguoi_gui,
                lvb.ten_viet_tat
            FROM thong_bao_nguoi_nhan tbn
            JOIN thong_bao tb ON tbn.thong_bao_id = tb.id
            JOIN van_ban_den vbd ON tb.vb_den_id = vbd.id
            JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
            JOIN loai_van_ban lvb ON vbd.loai_vb_id = lvb.id
            WHERE tbn.nguoi_nhan_id = :user_id 
        ";
        
        // Tính tổng số lượng
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM thong_bao_nguoi_nhan tbn JOIN thong_bao tb ON tbn.thong_bao_id = tb.id WHERE tbn.nguoi_nhan_id = :user_id");
        $stmt_count->execute([':user_id' => $user_id]);
        $total_records = $stmt_count->fetchColumn();

        // Lấy dữ liệu phân trang
        $sql_final = $sql_select . " ORDER BY tb.thoi_gian_gui DESC LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($sql_final);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif ($type === 'outbox') {
        // --- 2. Hộp thư ĐI (VB người dùng đã gửi/chuyển tiếp) ---
        
        $sql_select = "
            SELECT 
                tb.id AS thong_bao_id,
                tb.thoi_gian_gui,
                vbd.so_den, vbd.nam_den, vbd.trich_yeu, 
                (SELECT COUNT(tbn.nguoi_nhan_id) FROM thong_bao_nguoi_nhan tbn WHERE tbn.thong_bao_id = tb.id) AS total_nhan,
                (SELECT COUNT(tbn.nguoi_nhan_id) FROM thong_bao_nguoi_nhan tbn WHERE tbn.thong_bao_id = tb.id AND tbn.da_doc = 1) AS da_doc_count
            FROM thong_bao tb
            JOIN van_ban_den vbd ON tb.vb_den_id = vbd.id
            WHERE tb.nguoi_gui_id = :user_id 
        ";

        // Tính tổng số lượng
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM thong_bao tb WHERE tb.nguoi_gui_id = :user_id");
        $stmt_count->execute([':user_id' => $user_id]);
        $total_records = $stmt_count->fetchColumn();
        
        // L