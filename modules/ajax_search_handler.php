<?php
// modules/ajax_search_handler.php
require_once '../core/auth.php';
check_login();

header('Content-Type: application/json');

$search_type = $_GET['type'] ?? 'vb_den'; // 'vb_den' hoặc 'vb_di'
$page = (int)($_GET['page'] ?? 1);
$per_page = 20; 
$offset = ($page - 1) * $per_page;

$params = [];
$where_clause = "WHERE 1=1";

try {
    if ($search_type === 'vb_den') {
        // --- 1. Tra cứu Văn bản ĐẾN ---
        $table = 'van_ban_den';
        $select_fields = "vbd.*, lvb.ten_loai_vb, lvb.ten_viet_tat";
        $join_clause = "vbd JOIN loai_van_ban lvb ON vbd.loai_vb_id = lvb.id";
        
        $so_den_vb = trim($_GET['so_den_vb'] ?? '');
        $nam_vb = (int)($_GET['nam_vb'] ?? 0);
        $loai_vb_id = (int)($_GET['loai_vb_id'] ?? 0);
        $trich_yeu = trim($_GET['trich_yeu'] ?? '');
        
        if ($so_den_vb) { $where_clause .= " AND (vbd.so_den = :so_den OR vbd.so_van_ban LIKE :so_vb)"; $params[':so_den'] = $so_den_vb; $params[':so_vb'] = '%' . $so_den_vb . '%'; }
        if ($nam_vb > 0) { $where_clause .= " AND vbd.nam_den = :nam_vb"; $params[':nam_vb'] = $nam_vb; }
        if ($loai_vb_id > 0) { $where_clause .= " AND vbd.loai_vb_id = :lvb_id"; $params[':lvb_id'] = $loai_vb_id; }
        if ($trich_yeu) { $where_clause .= " AND vbd.trich_yeu LIKE :trich_yeu"; $params[':trich_yeu'] = '%' . $trich_yeu . '%'; }

        // Thêm lọc ngày tháng nếu cần
        // ...
        $order_by = "vbd.so_den DESC, vbd.nam_den DESC";

    } elseif ($search_type === 'vb_di') {
        // --- 2. Tra cứu Văn bản ĐI ---
        $table = 'van_ban_di';
        $select_fields = "vbd.*, lvb.ten_loai_vb, lvb.ten_viet_tat, vb_nguon.so_den AS so_den_nguon, vb_nguon.nam_den AS nam_den_nguon";
        $join_clause = "van_ban_di vbd 
                        JOIN loai_van_ban lvb ON vbd.loai_vb_id = lvb.id
                        LEFT JOIN van_ban_den vb_nguon ON vbd.tra_loi_vb_den_id = vb_nguon.id";

        $so_vb = trim($_GET['so_vb'] ?? '');
        $loai_vb_id = (int)($_GET['loai_vb_id'] ?? 0);
        $trich_yeu = trim($_GET['trich_yeu'] ?? '');
        
        if ($so_vb) { $where_clause .= " AND vbd.so = :so_vb"; $params[':so_vb'] = $so_vb; }
        if ($loai_vb_id > 0) { $where_clause .= " AND vbd.loai_vb_id = :lvb_id"; $params[':lvb_id'] = $loai_vb_id; }
        if ($trich_yeu) { $where_clause .= " AND vbd.trich_yeu LIKE :trich_yeu"; $params[':trich_yeu'] = '%' . $trich_yeu . '%'; }

        $order_by = "vbd.ngay_thang DESC, vbd.so DESC";
    } else {
         throw new Exception("Loại tra cứu không hợp lệ.");
    }
    
    // Tính tổng số lượng
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM " . $join_clause . " " . $where_clause);
    $stmt_count->execute($params);
    $total_records = $stmt_count->fetchColumn();

    // Lấy dữ liệu phân trang
    $sql_final = "SELECT " . $select_fields . " FROM " . $join_clause . " " . $where_clause . " ORDER BY " . $order_by . " LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql_final);
    
    // Gán tham số LIMIT và OFFSET
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    // Gán các tham số điều kiện khác
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_pages = ceil($total_records / $per_page);

    echo json_encode([
        'success' => true,
        'data' => $results,
        'pagination' => [
            'total_records' => $total_records,
            'total_pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Lỗi truy vấn DB: " . $e->getMessage()]);
}
?>