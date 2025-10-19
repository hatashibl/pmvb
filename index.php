<?php
// index.php
require_once 'core/auth.php';
// Bắt buộc phải kiểm tra đăng nhập trước khi tải trang
check_login(); 

// Lấy thông tin hệ thống (Admin có thể cấu hình)
global $pdo;
$stmt_settings = $pdo->query("SELECT `key`, `value` FROM cai_dat_he_thong");
$settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);

$ten_phan_mem = $settings['ten_phan_mem'] ?? 'Hệ thống Quản lý Văn bản';
$copyright = $settings['copyright'] ?? '&copy; 2024 QLVB Project';
$user_name = $_SESSION['ten_day_du'] ?? 'Người dùng';
$user_role = strtoupper($_SESSION['chuc_vu'] ?? 'GUEST');

// Logic lấy số VB chưa đọc (ví dụ: chỉ hiển thị cho Hộp thư Đến)
$unread_count = 0;
$stmt_unread = $pdo->prepare("
SELECT COUNT(*) 
    FROM thong_bao_nguoi_nhan
    WHERE nguoi_nhan_id = ? AND trang_thai = 'chua_doc'
");
$stmt_unread->execute([$_SESSION['user_id']]);
$unread_count = $stmt_unread->fetchColumn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ten_phan_mem) ?></title>
    
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="assets/css/custom_style.css" rel="stylesheet"> <style>
        /* CSS cho sidebar */
        body { min-height: 100vh; }
        #sidebar { min-width: 250px; max-width: 250px; background: #343a40; color: #fff; transition: all 0.3s; }
        #content-wrapper { width: 100%; }
        .nav-link { color: #ccc !important; }
        .nav-link:hover { background-color: #495057; color: #fff !important; }
        .nav-link.active { background-color: #0d6efd; color: #fff !important; }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <div id="sidebar">
            <div class="sidebar-heading text-center py-4 primary-text fs-4 fw-bold text-uppercase border-bottom">
                <?= htmlspecialchars($ten_phan_mem) ?>
            </div>
            <div class="list-group list-group-flush my-3">
                
                <a href="#" class="list-group-item list-group-item-action bg-transparent second-text fw-bold" data-page="dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-transparent second-text fw-bold" data-page="van_ban_den_nhap">
                    <i class="fas fa-file-import me-2"></i> Nhập Văn bản Đến
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-transparent second-text fw-bold" data-page="van_ban_di_nhap">
                    <i class="fas fa-file-export me-2"></i> Nhập Văn bản Đi (Cấp số)
                </a>

                <p class="text-white small px-3 pt-3 mb-1">HỘP THƯ</p>
                <a href="#" class="list-group-item list-group-item-action bg-transparent second-text" data-page="hop_thu_den">
                    <i class="fas fa-inbox me-2"></i> Hộp thư Đến 
                    <?php if ($unread_count > 0): ?>
                        <span class="badge bg-danger rounded-pill float-end"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-transparent second-text" data-page="hop_thu_di">
                    <i class="fas fa-paper-plane me-2"></i> Văn bản đã gửi
                </a>

                <p class="text-white small px-3 pt-3 mb-1">TRA CỨU VÀ BÁO CÁO</p>
                <a href="#" class="list-group-item list-group-item-action bg-transparent second-text" data-page="van_ban_den_tra_cuu">
                    <i class="fas fa-search me-2"></i> Tra cứu VB Đến
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-transparent second-text" data-page="van_ban_di_tra_cuu">
                    <i class="fas fa-search-plus me-2"></i> Tra cứu VB Đi
                </a>

                <?php if (get_user_role() != 'thanh_vien'): ?>
                <p class="text-white small px-3 pt-3 mb-1">QUẢN TRỊ</p>
                    <?php if (get_user_role() == 'admin'): ?>
                    <a href="#" class="list-group-item list-group-item-action bg-transparent second-text" data-page="quan_ly_nguoi_dung">
                        <i class="fas fa-users me-2"></i> Quản lý Người dùng
                    </a>
                    <a href="#" class="list-group-item list-group-item-action bg-transparent second-text" data-page="cai_dat_he_thong">
                        <i class="fas fa-cogs me-2"></i> Cài đặt Hệ thống
                    </a>
                    <a href="#" class="list-group-item list-group-item-action bg-transparent second-text" data-page="sao_luu_du_lieu">
                        <i class="fas fa-save me-2"></i> Sao lưu Dữ liệu
                    </a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <p class="text-white small px-3 pt-3 mb-1">CÁ NHÂN</p>
                <a href="#" class="list-group-item list-group-item-action bg-transparent second-text" data-page="thong_tin_ca_nhan">
                    <i class="fas fa-user-circle me-2"></i> Thông tin cá nhân
                </a>
                <a href="logout.php" class="list-group-item list-group-item-action bg-transparent text-danger fw-bold">
                    <i class="fas fa-power-off me-2"></i> Đăng xuất
                </a>
            </div>
        </div>
        <div id="content-wrapper" class="p-0 d-flex flex-column">
            <nav class="navbar navbar-expand-lg navbar-light bg-light py-4 px-4 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-align-left primary-text fs-4 me-3" id="menu-toggle" style="cursor: pointer;"></i>
                    <h2 class="fs-2 m-0">Dashboard</h2>
                </div>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle second-text fw-bold" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-2"></i> <?= htmlspecialchars($user_name) ?> (<?= $user_role ?>)
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#" onclick="loadContent('thong_tin_ca_nhan')">Hồ sơ</a></li>
                                <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <div id="main-content" class="container-fluid py-4 flex-grow-1">
                </div>
            <footer class="bg-light text-center text-lg-start mt-auto">
                <div class="text-center p-3 text-muted border-top">
                    <?= htmlspecialchars($copyright) ?>
                </div>
            </footer>
        </div>
    </div>

<script src="assets/js/jquery-3.7.1.min.js"></script> 

<script src="assets/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.tiny.cloud/1/[YOUR_TINYMCE_API_KEY]/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script src="assets/js/custom.js"></script> 

<script>
    // Logic JS cho giao diện
    var el = document.getElementById("wrapper");
    var toggleButton = document.getElementById("menu-toggle");

    // Toggle sidebar
    if (toggleButton) {
        toggleButton.onclick = function () {
            el.classList.toggle("toggled");
        };
    }
    
    // Mặc định load dashboard khi trang được tải lần đầu (nếu main-content rỗng)
    // Lệnh này dựa vào jQuery và hàm loadContent trong custom.js, nên phải nằm sau.
    $(document).ready(function() {
        if ($('#main-content').is(':empty')) {
             // LƯU Ý: Đảm bảo file modules/dashboard.php đã tồn tại.
             loadContent('dashboard'); 
        }
    });
</script>
</body>
</html>