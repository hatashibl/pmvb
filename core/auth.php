<?php
// core/auth.php

// Bắt đầu phiên làm việc nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Bắt buộc phải có file database.php để kết nối CSDL
require_once 'database.php';

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 */
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        // Lưu lại trang hiện tại trước khi chuyển hướng (tùy chọn)
        // $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit();
    }
}

/**
 * Lấy vai trò (chức vụ) của người dùng hiện tại
 * @return string 'admin', 'quan_ly', 'thanh_vien', hoặc 'guest'
 */
function get_user_role() {
    return $_SESSION['chuc_vu'] ?? 'guest';
}

/**
 * Hàm kiểm tra đăng nhập chi tiết
 * @param string $username
 * @param string $password
 * @return array ['success', 'message']
 */
function attempt_login($username, $password) {
    global $pdo;
    
    // 1. Tìm người dùng
    $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE ten_dang_nhap = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'message' => 'Tên đăng nhập không tồn tại.'];
    }

    // 2. Kiểm tra tài khoản bị khóa
    if (!empty($user['thoi_gian_khoa']) && strtotime($user['thoi_gian_khoa']) > time()) {
        $unlock_time = date('H:i:s d/m/Y', strtotime($user['thoi_gian_khoa']));
        return ['success' => false, 'message' => "Tài khoản bị khóa đến $unlock_time do nhập sai quá nhiều lần."];
    }
    
    // 3. Xác thực mật khẩu
    if (password_verify($password, $user['mat_khau'])) {
        // Đăng nhập thành công: Reset lỗi, thiết lập Session
        $stmt_reset = $pdo->prepare("UPDATE nguoi_dung SET lan_sai_hien_tai = 0, thoi_gian_khoa = NULL WHERE id = ?");
        $stmt_reset->execute([$user['id']]);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['ten_day_du'] = $user['ten_day_du'];
        $_SESSION['chuc_vu'] = $user['chuc_vu'];

        return ['success' => true, 'message' => 'Đăng nhập thành công!'];
        
    } else {
        // Đăng nhập thất bại: Tăng số lần sai
        $lan_sai_hien_tai = $user['lan_sai_hien_tai'] + 1;
        $message = "Mật khẩu không đúng. Bạn còn " . (5 - $lan_sai_hien_tai) . " lần thử.";
        
        if ($lan_sai_hien_tai >= 5) {
            // Khóa tài khoản trong 5 phút
            $lock_time = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            $stmt_lock = $pdo->prepare("UPDATE nguoi_dung SET lan_sai_hien_tai = ?, thoi_gian_khoa = ? WHERE id = ?");
            $stmt_lock->execute([$lan_sai_hien_tai, $lock_time, $user['id']]);
            $message = "Bạn đã nhập sai quá 5 lần. Tài khoản bị khóa trong 5 phút.";
        } else {
            $stmt_update = $pdo->prepare("UPDATE nguoi_dung SET lan_sai_hien_tai = ? WHERE id = ?");
            $stmt_update->execute([$lan_sai_hien_tai, $user['id']]);
        }
        
        return ['success' => false, 'message' => $message];
    }
}

function get_setting($key) {
    global $pdo;
    static $settings_cache = null;

    // Tải tất cả cài đặt vào cache nếu chưa tải
    if ($settings_cache === null) {
        try {
            $stmt = $pdo->query("SELECT `key`, `value` FROM cai_dat_he_thong");
            $settings_cache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            // Ghi log lỗi nếu không thể kết nối CSDL hoặc bảng không tồn tại
            error_log("Lỗi tải cài đặt hệ thống: " . $e->getMessage());
            $settings_cache = []; 
        }
    }

    // Trả về giá trị theo key, nếu không tìm thấy thì là null
    return $settings_cache[$key] ?? null;
}

?>