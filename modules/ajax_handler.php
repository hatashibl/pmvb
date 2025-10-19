<?php
// modules/ajax_handler.php - Xử lý Backend cho Văn bản Đến (Đã FIX lỗi thiếu ID và logic file)

// =========================================================
// 1. CÁC HÀM TIỆN ÍCH (UTILITY FUNCTIONS)
// =========================================================

// Định nghĩa hàm chuyển đổi ngày tháng
function convert_date_to_sql($date_str) {
    if (!$date_str) return null;
    // Chuyển đổi từ DD/MM/YYYY sang YYYY-MM-DD
    $dt = DateTime::createFromFormat('d/m/Y', $date_str);
    return $dt ? $dt->format('Y-m-d') : null;
}

// Hàm chuẩn hóa chuỗi cho tên file (tạo slug)
function clean_string_for_filename($string) {
    // 1. Chuyển tiếng Việt có dấu thành không dấu
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    // 2. Chuyển thành chữ thường
    $string = strtolower($string);
    // 3. Thay thế các ký tự không phải chữ cái, số, khoảng trắng thành dấu gạch ngang
    $string = preg_replace('/[^a-z0-9\s]/', '-', $string);
    // 4. Thay thế nhiều khoảng trắng/gạch ngang liên tiếp thành một dấu gạch ngang
    $string = preg_replace('/[\s-]+/', '-', $string);
    // 5. Giới hạn độ dài chuỗi
    $string = substr($string, 0, 50); 
    return trim($string, '-');
}

// Hàm lấy Loại Văn bản ngắn gọn
function get_loai_vb_short($loai_vb_id, $pdo) {
    $stmt = $pdo->prepare("SELECT ky_hieu_ngan FROM loai_van_ban WHERE id = ?");
    $stmt->execute([$loai_vb_id]);
    $ky_hieu = $stmt->fetchColumn();
    return $ky_hieu ?: 'VB'; 
}


// =========================================================
// 2. CÀI ĐẶT CHUNG (SETUP)
// =========================================================

require_once '../core/auth.php'; 
check_login();

global $pdo; 
$current_user_id = $_SESSION['user_id'] ?? 0;
$current_user_chuc_vu = $_SESSION['chuc_vu'] ?? 'thanh_vien'; 
$can_nhap_vb = in_array($current_user_chuc_vu, ['admin', 'quan_ly']);
$is_admin = ($current_user_chuc_vu == 'admin');

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

$upload_dir_base = '../assets/files/vb_den/';

// =========================================================
// 3. XỬ LÝ ACTION (HANDLER)
// =========================================================

switch ($action) {
	
   case 'add_van_ban_den':
        if (!$can_nhap_vb) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thêm văn bản.']);
            exit;
        }

        // 1. Lấy dữ liệu và Chuẩn hóa
        $so_den = trim($_POST['so_den'] ?? '');
        $nam_den = (int)($_POST['nam_den'] ?? 0);
        $so_van_ban = trim($_POST['so_van_ban'] ?? '');
        $ngay_thang_vb_str = trim($_POST['ngay_thang_vb'] ?? '');
        $noi_ban_hanh = trim($_POST['noi_ban_hanh'] ?? '');
        $loai_vb_id = (int)($_POST['loai_vb_id'] ?? 0);
        $trich_yeu = trim($_POST['trich_yeu'] ?? '');
        $de_xuat_xu_ly = trim($_POST['de_xuat_xu_ly'] ?? '');
        $nguoi_nhap_id = $current_user_id;
        
        $ngay_thang_vb = convert_date_to_sql($ngay_thang_vb_str);
        $file_info = $_FILES['file_dinh_kem'] ?? null;
        $file_dinh_kem_db = null;

        // 2. VALIDATION
        $errors = [];
        if (empty($so_den) || !is_numeric($so_den) || $so_den <= 0) { $errors[] = "Số đến không hợp lệ (chỉ được nhập số > 0)."; }
        if ($loai_vb_id <= 0) { $errors[] = "Vui lòng chọn Loại Văn bản."; }
        if (empty($so_van_ban)) { $errors[] = "Số Văn bản không được để trống."; }
        if (empty($ngay_thang_vb_str) || !$ngay_thang_vb) { $errors[] = "Ngày Văn bản không hợp lệ (hoặc định dạng DD/MM/YYYY không đúng)."; }
        if (empty($noi_ban_hanh)) { $errors[] = "Nơi ban hành không được để trống."; }
        if (empty($trich_yeu)) { $errors[] = "Trích yếu nội dung không được để trống."; }
        if (empty($de_xuat_xu_ly)) { $errors[] = "Đề xuất xử lý không được để trống."; }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng kiểm tra lại các trường bắt buộc.', 'errors' => $errors]);
            exit;
        }

        $pdo->beginTransaction();
        try {
            // 3. Xử lý Upload File (Nếu có)
            if ($file_info && $file_info['error'] == UPLOAD_ERR_OK) {
                $file_extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                $vb_short = get_loai_vb_short($loai_vb_id, $pdo);
                $trich_yeu_slug = clean_string_for_filename($trich_yeu);
                
                $ten_file_moi = sprintf(
                    "%d-%s-%s-%s.%s",
                    $so_den,
                    $vb_short,
                    $trich_yeu_slug,
                    time(),
                    $file_extension
                );

                $upload_path = $upload_dir . $ten_file_moi;
                
                if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                    $file_dinh_kem_db = $ten_file_moi;
                } else {
                    throw new Exception("Lỗi khi upload file.");
                }
            }
            
            // 4. Chèn dữ liệu vào VAN_BAN_DEN
            $sql = "INSERT INTO van_ban_den (
                        so_den, nam_den, so_van_ban, ngay_thang_vb, noi_ban_hanh, loai_vb_id, 
                        trich_yeu, de_xuat_xu_ly, file_dinh_kem, nguoi_nhap_id, thoi_gian_nhap
                    ) VALUES (
                        :so_den, :nam_den, :so_van_ban, :ngay_thang_vb, :noi_ban_hanh, :loai_vb_id, 
                        :trich_yeu, :de_xuat_xu_ly, :file_dinh_kem, :nguoi_nhap_id, NOW()
                    )";

            $stmt_insert = $pdo->prepare($sql);
            $stmt_insert->execute([
                ':so_den' => $so_den,
                ':nam_den' => $nam_den,
                ':so_van_ban' => $so_van_ban,
                ':ngay_thang_vb' => $ngay_thang_vb,
                ':noi_ban_hanh' => $noi_ban_hanh,
                ':loai_vb_id' => $loai_vb_id,
                ':trich_yeu' => $trich_yeu,
                ':de_xuat_xu_ly' => $de_xuat_xu_ly,
                ':file_dinh_kem' => $file_dinh_kem_db,
                ':nguoi_nhap_id' => $nguoi_nhap_id,
            ]);

            $new_vb_id = $pdo->lastInsertId();

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Thêm Văn bản Đến thành công!', 'id' => $new_vb_id]);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            error_log("Lỗi khi thêm Văn bản Đến: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi thêm văn bản: ' . $e->getMessage()]);
        }
        exit;
	

    // =========================================================
    // HÀNH ĐỘNG 1: SỬA VĂN BẢN ĐẾN (edit_van_ban_den)
    // =========================================================
    case 'edit_van_ban_den':
        $vb_id = (int)($_POST['id'] ?? 0);
        if ($vb_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID Văn bản để chỉnh sửa.']);
            exit;
        }

        // --- 1. Lấy thông tin VB cũ và Kiểm tra quyền ---
        try {
            $stmt_old_vb = $pdo->prepare("SELECT nguoi_nhap_id, file_dinh_kem FROM van_ban_den WHERE id = ?");
            $stmt_old_vb->execute([$vb_id]);
            $van_ban_cu = $stmt_old_vb->fetch(PDO::FETCH_ASSOC);

            if (!$van_ban_cu) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy Văn bản để cập nhật.']);
                exit;
            }

            // Kiểm tra quyền SỬA: Phải là người nhập HOẶC là admin/quản lý
            if (!($can_nhap_vb && ($van_ban_cu['nguoi_nhap_id'] == $current_user_id || $is_admin))) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền sửa Văn bản này.']);
                exit;
            }

            // --- 2. Lấy và Xử lý dữ liệu POST ---
            $so_den = (int)$_POST['so_den'];
            $nam_den = (int)$_POST['nam_den'];
            $so_van_ban = trim($_POST['so_van_ban']);
            $ngay_thang_vb = convert_date_to_sql($_POST['ngay_thang_vb']);
            $noi_ban_hanh = trim($_POST['noi_ban_hanh'] ?? '');
            $loai_vb_id = (int)$_POST['loai_vb_id'];
            $trich_yeu = $_POST['trich_yeu'];
            $de_xuat_xu_ly = $_POST['de_xuat_xu_ly'];
            
            // Xử lý file đính kèm
            $file_dinh_kem_db = $van_ban_cu['file_dinh_kem']; // Giữ lại file cũ

            if (isset($_FILES['file_dinh_kem']) && $_FILES['file_dinh_kem']['error'] === UPLOAD_ERR_OK) {
                $file_info = $_FILES['file_dinh_kem'];
                $file_extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
                $loai_vb_short = get_loai_vb_short($loai_vb_id, $pdo);
                
                // Tên file mới: [loai_vb_short]-[so_den]-[nam_den]-[slug_trich_yeu].[ext]
                $base_name = clean_string_for_filename($loai_vb_short . '-' . $so_den . '-' . $nam_den . '-' . $trich_yeu);
                $ten_file_moi = $base_name . '.' . $file_extension;
                
                // Thư mục lưu trữ theo năm
                $upload_dir = $upload_dir_base . $nam_den . '/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $upload_path = $upload_dir . $ten_file_moi;

                // Di chuyển file mới
                if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                    // Xóa file cũ (nếu có)
                    if (!empty($van_ban_cu['file_dinh_kem']) && file_exists($upload_dir_base . $van_ban_cu['file_dinh_kem'])) {
                        unlink($upload_dir_base . $van_ban_cu['file_dinh_kem']);
                    }
                    // Lưu tên file đầy đủ (kèm thư mục năm) vào CSDL
                    $file_dinh_kem_db = $nam_den . '/' . $ten_file_moi;
                } else {
                    throw new Exception("Lỗi khi upload file mới.");
                }
            }
            
            // --- 3. Cập nhật dữ liệu vào VAN_BAN_DEN ---
            $pdo->beginTransaction();
            $sql = "UPDATE van_ban_den SET
                        so_den = :so_den, 
                        nam_den = :nam_den, 
                        so_van_ban = :so_van_ban, 
                        ngay_thang_vb = :ngay_thang_vb, 
                        noi_ban_hanh = :noi_ban_hanh,
                        loai_vb_id = :loai_vb_id, 
                        trich_yeu = :trich_yeu, 
                        de_xuat_xu_ly = :de_xuat_xu_ly, 
                        file_dinh_kem = :file_dinh_kem,
                        thoi_gian_cap_nhat = NOW()
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':so_den' => $so_den,
                ':nam_den' => $nam_den,
                ':so_van_ban' => $so_van_ban,
                ':ngay_thang_vb' => $ngay_thang_vb,
                ':noi_ban_hanh' => $noi_ban_hanh,
                ':loai_vb_id' => $loai_vb_id,
                ':trich_yeu' => $trich_yeu,
                ':de_xuat_xu_ly' => $de_xuat_xu_ly,
                ':file_dinh_kem' => $file_dinh_kem_db,
                ':id' => $vb_id
            ]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Cập nhật Văn bản Đến thành công!']);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            error_log("Lỗi khi sửa Văn bản Đến: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật văn bản: ' . $e->getMessage()]);
        }
        exit;

    // =========================================================
    // HÀNH ĐỘNG 2: XÓA VĂN BẢN ĐẾN (delete_van_ban)
    // =========================================================
    case 'delete_van_ban':
        $vb_id = (int)($_POST['id'] ?? 0);
        if ($vb_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID Văn bản để xóa.']);
            exit;
        }

        $pdo->beginTransaction();
        try {
            // 1. Kiểm tra quyền và lấy thông tin file cũ
            $stmt_old_vb = $pdo->prepare("SELECT nguoi_nhap_id, file_dinh_kem FROM van_ban_den WHERE id = ?");
            $stmt_old_vb->execute([$vb_id]);
            $van_ban_cu = $stmt_old_vb->fetch(PDO::FETCH_ASSOC);

            if (!$van_ban_cu) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy Văn bản để xóa.']);
                exit;
            }

            // Kiểm tra quyền XÓA: Phải là người nhập HOẶC là admin
            if (!($can_nhap_vb && ($van_ban_cu['nguoi_nhap_id'] == $current_user_id || $is_admin))) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa Văn bản này.']);
                exit;
            }

            // 2. Xóa dữ liệu trong CSDL
            $stmt_delete = $pdo->prepare("DELETE FROM van_ban_den WHERE id = ?");
            $stmt_delete->execute([$vb_id]);

            // 3. Xóa vật lý file đính kèm (nếu có)
            if (!empty($van_ban_cu['file_dinh_kem'])) {
                $file_path = $upload_dir_base . $van_ban_cu['file_dinh_kem'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Xóa Văn bản Đến thành công!']);

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Lỗi SQL khi xóa VB: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL khi xóa văn bản.']);
        }
        exit;

    // =========================================================
    // HÀNH ĐỘNG 3: TẢI CHI TIẾT VĂN BẢN (get_van_ban_den)
    // =========================================================
    case 'get_van_ban_den':
        $vb_id = (int)($_GET['id'] ?? 0);
        if ($vb_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID Văn bản.']);
            exit;
        }

        try {
            $sql = "
                SELECT 
                    vbd.id, vbd.so_den, vbd.nam_den, vbd.so_van_ban, DATE_FORMAT(vbd.ngay_thang_vb, '%d/%m/%Y') AS ngay_thang_vb_display, 
                    vbd.ngay_thang_vb, vbd.noi_ban_hanh, 
                    vbd.loai_vb_id, vbd.trich_yeu, vbd.de_xuat_xu_ly, vbd.file_dinh_kem, 
                    u.ten_hien_thi AS nguoi_nhap
                FROM van_ban_den vbd
                LEFT JOIN loai_van_ban lvb ON vbd.loai_vb_id = lvb.id
                LEFT JOIN nguoi_dung u ON vbd.nguoi_nhap_id = u.id
                WHERE vbd.id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$vb_id]);
            $van_ban = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($van_ban) {
                // Xử lý hiển thị ngày tháng
                if (empty($van_ban['ngay_thang_vb_display']) && $van_ban['ngay_thang_vb']) {
                     $van_ban['ngay_thang_vb_display'] = date('d/m/Y', strtotime($van_ban['ngay_thang_vb']));
                } else if (!$van_ban['ngay_thang_vb']) {
                    $van_ban['ngay_thang_vb_display'] = '';
                }

                // Xử lý đường dẫn file
                $van_ban['file_dinh_kem_path'] = $van_ban['file_dinh_kem'] ? 'assets/files/vb_den/' . $van_ban['file_dinh_kem'] : '';

                echo json_encode(['success' => true, 'data' => $van_ban]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy văn bản này.']);
            }

        } catch (PDOException $e) {
            error_log("Lỗi SQL khi xem chi tiết VB: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL khi tải chi tiết.']);
        }
        exit;

    // =========================================================
    // HÀNH ĐỘNG 4: XÓA FILE ĐÍNH KÈM CŨ (delete_vb_file)
    // =========================================================
    case 'delete_vb_file':
        $vb_id = (int)($_POST['id'] ?? 0);
        if ($vb_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID Văn bản.']);
            exit;
        }

        $pdo->beginTransaction();
        try {
            // 1. Kiểm tra quyền và lấy thông tin file cũ
            $stmt_old_vb = $pdo->prepare("SELECT nguoi_nhap_id, file_dinh_kem FROM van_ban_den WHERE id = ?");
            $stmt_old_vb->execute([$vb_id]);
            $van_ban_cu = $stmt_old_vb->fetch(PDO::FETCH_ASSOC);

            if (!$van_ban_cu) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy Văn bản.']);
                exit;
            }
            
            // Kiểm tra quyền SỬA/XOÁ: Phải là người nhập HOẶC là admin/quản lý
            if (!($can_nhap_vb && ($van_ban_cu['nguoi_nhap_id'] == $current_user_id || $is_admin))) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền sửa/xóa file đính kèm của Văn bản này.']);
                exit;
            }

            // 2. Xóa vật lý file đính kèm (nếu có)
            $file_deleted = false;
            $file_dinh_kem_cu = $van_ban_cu['file_dinh_kem'];
            if (!empty($file_dinh_kem_cu)) {
                $file_path = $upload_dir_base . $file_dinh_kem_cu;
                if (file_exists($file_path)) {
                    if (unlink($file_path)) {
                        $file_deleted = true;
                    } else {
                        throw new Exception("Lỗi khi xóa file vật lý trên server.");
                    }
                } else {
                    $file_deleted = true; 
                }
            } else {
                $pdo->rollBack();
                echo json_encode(['success' => true, 'message' => 'Văn bản này không có file đính kèm.']);
                exit;
            }

            // 3. Cập nhật DB (set file_dinh_kem = NULL)
            $stmt_update = $pdo->prepare("UPDATE van_ban_den SET file_dinh_kem = NULL, thoi_gian_cap_nhat = NOW() WHERE id = ?");
            $stmt_update->execute([$vb_id]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Xóa file đính kèm thành công!']);

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Lỗi khi xóa file đính kèm: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi xóa file: ' . $e->getMessage()]);
        }
        exit;
    
    // =========================================================
    // HÀNH ĐỘNG 5: TRẢ LỜI TIN NHẮN (reply_message)
    // =========================================================
    case 'reply_message':
        $vb_id = (int)($_POST['van_ban_den_id'] ?? 0);
        $recipient_id = (int)($_POST['recipient_id'] ?? 0);
        $reply_message = trim($_POST['reply_message'] ?? '');
        $current_user_id = $_SESSION['user_id'] ?? 0;

        if ($vb_id <= 0 || $recipient_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin Văn bản hoặc Người nhận.']);
            exit;
        }

        if (empty($reply_message) && (!isset($_FILES['file_dinh_kem_reply']) || $_FILES['file_dinh_kem_reply']['error'] != UPLOAD_ERR_OK)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập nội dung trả lời hoặc đính kèm file.']);
            exit;
        }

        $file_reply_path = null;
        $upload_dir_reply = '../assets/files/thong_bao/';
        
        // Xử lý file đính kèm trả lời
        if (isset($_FILES['file_dinh_kem_reply']) && $_FILES['file_dinh_kem_reply']['error'] === UPLOAD_ERR_OK) {
            $file_info = $_FILES['file_dinh_kem_reply'];
            $file_extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
            
            $stmt_vb = $pdo->prepare("SELECT so_den, nam_den, loai_vb_id FROM van_ban_den WHERE id = ?");
            $stmt_vb->execute([$vb_id]);
            $vb_info = $stmt_vb->fetch(PDO::FETCH_ASSOC);

            if ($vb_info) {
                $loai_vb_short = get_loai_vb_short($vb_info['loai_vb_id'], $pdo);
                $so_den = $vb_info['so_den'];
                $nam_den = $vb_info['nam_den'];
            } else {
                $loai_vb_short = 'VB';
                $so_den = '0';
                $nam_den = date('Y');
            }
            
            $base_name = 'reply-' . $loai_vb_short . '-' . $so_den . '-' . $nam_den . '-' . $current_user_id;
            $ten_file_moi = clean_string_for_filename($base_name) . '.' . $file_extension;
            
            if (!is_dir($upload_dir_reply)) {
                mkdir($upload_dir_reply, 0777, true);
            }

            $upload_path = $upload_dir_reply . $ten_file_moi;
            
            if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                $file_reply_path = $ten_file_moi; 
            } else {
                throw new Exception("Lỗi khi upload file trả lời.");
            }
        }


        // --- INSERT TIN NHẮN TRẢ LỜI ---
        $pdo->beginTransaction();
        try {
            $sql = "INSERT INTO thong_bao_nguoi_nhan (
                van_ban_den_id, nguoi_gui_id, nguoi_nhan_id, noi_dung, file_dinh_kem_reply, trang_thai, thoi_gian_gui
            ) VALUES (
                :vb_id, :nguoi_gui, :nguoi_nhan, :noi_dung, :file_reply, 'chua_doc', NOW()
            )";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':vb_id', $vb_id, PDO::PARAM_INT);
            $stmt->bindParam(':nguoi_gui', $current_user_id, PDO::PARAM_INT); 
            $stmt->bindParam(':nguoi_nhan', $recipient_id, PDO::PARAM_INT); 
            $stmt->bindParam(':noi_dung', $reply_message);
            $stmt->bindParam(':file_reply', $file_reply_path);
            
            $stmt->execute();

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Gửi trả lời thành công!']);

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Lỗi SQL khi gửi trả lời tin nhắn: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi gửi trả lời: ' . $e->getMessage()]);
        }
        exit;
        
    // =========================================================
    // HÀNH ĐỘNG 6: CHUYỂN GIAO VĂN BẢN (chuyen_giao_vb)
    // =========================================================
    case 'chuyen_giao_vb':
        $vb_id = (int)($_POST['van_ban_den_id'] ?? 0);
        $recipients = $_POST['nguoi_nhan_ids'] ?? [];
        $noi_dung = trim($_POST['noi_dung'] ?? '');
        $current_user_id = $_SESSION['user_id'] ?? 0;

        if ($vb_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID Văn bản để chuyển giao.']);
            exit;
        }
        
        $recipient_ids = array_filter(array_map('intval', $recipients), function($id) use ($current_user_id) {
            return $id > 0 && $id != $current_user_id; 
        });

        if (empty($recipient_ids)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ít nhất một người nhận hợp lệ.']);
            exit;
        }

        // --- INSERT TIN NHẮN CHUYỂN GIAO ---
        $pdo->beginTransaction();
        try {
            $sql = "INSERT INTO thong_bao_nguoi_nhan (
                van_ban_den_id, nguoi_gui_id, nguoi_nhan_id, noi_dung, trang_thai, thoi_gian_gui
            ) VALUES (
                :vb_id, :nguoi_gui, :nguoi_nhan, :noi_dung, 'chua_doc', NOW()
            )";

            $stmt = $pdo->prepare($sql);
            $success_count = 0;
            
            foreach ($recipient_ids as $recipient_id) {
                if ($recipient_id > 0 && $recipient_id != $current_user_id) {
                    $stmt->execute([
                        ':vb_id' => $vb_id,
                        ':nguoi_gui' => $current_user_id,
                        ':nguoi_nhan' => $recipient_id,
                        ':noi_dung' => $noi_dung
                    ]);
                    $success_count++;
                }
            }

            if ($success_count == 0) {
                throw new Exception("Không có người nhận hợp lệ nào được chọn để chuyển giao.");
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => "Chuyển giao Văn bản thành công cho {$success_count} người nhận."]);

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Lỗi SQL khi chuyển giao VB: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi chuyển giao: ' . $e->getMessage()]);
        }
        exit;
        
    // =========================================================
    // HÀNH ĐỘNG 7: ĐÁNH DẤU TIN NHẮN ĐÃ ĐỌC (mark_read)
    // =========================================================
    case 'mark_read':
        $message_id = (int)($_POST['id'] ?? 0);
        $current_user_id = $_SESSION['user_id'] ?? 0;

        if ($message_id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID tin nhắn.']);
            exit;
        }
        
        try {
            $sql = "UPDATE thong_bao_nguoi_nhan SET trang_thai = 'da_doc' WHERE id = :id AND nguoi_nhan_id = :user_id AND trang_thai = 'chua_doc'";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $message_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Đã đánh dấu là đã đọc.']);

        } catch (PDOException $e) {
            error_log("Lỗi SQL khi đánh dấu đã đọc: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống khi cập nhật trạng thái.']);
        }
        exit;
        
    // =========================================================
    // HÀNH ĐỘNG MẶC ĐỊNH
    // =========================================================
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
        exit;
}