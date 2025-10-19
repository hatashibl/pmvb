<?php
// templates/sidebar.php
$role = get_user_role();
?>
<div id="sidebar" class="bg-primary text-white p-3" style="width: 250px; min-height: 100vh;">
    <h5 class="text-center py-2 mb-4 border-bottom"><?= strtoupper($role) ?></h5>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-white active" href="#" data-page="dashboard">🏠 Dashboard</a>
        </li>
        
        <?php if ($role == 'thanh_vien'): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-page="hop_thu_den">📥 Hộp thư đến</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-page="van_ban_di_nhap">✍️ Nhập/Sửa VB Đi</a>
            </li>
        <?php endif; ?>

        <?php if ($role == 'quan_ly' || $role == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-page="hop_thu_di">📤 Hộp thư đi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-page="van_ban_den_nhap">📝 Nhập/Sửa VB Đến</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-page="van_ban_den_tra_cuu">🔍 Tra cứu VB Đến</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-page="van_ban_di_tra_cuu">🔎 Tra cứu VB Đi</a>
            </li>
        <?php endif; ?>

        <?php if ($role == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-page="quan_ly_nguoi_dung">👥 Quản lý người dùng</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-page="cai_dat_he_thong">⚙️ Cài đặt hệ thống</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-page="sao_luu_du_lieu">💾 Sao lưu dữ liệu</a>
            </li>
        <?php endif; ?>

        <li class="nav-item mt-3 border-top pt-3">
            <a class="nav-link text-white" href="#" data-page="thong_tin_ca_nhan">👤 Thông tin cá nhân</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="logout.php">🚪 Đăng xuất</a>
        </li>
    </ul>
</div>