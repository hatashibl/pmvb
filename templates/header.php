<?php
// templates/header.php
// Đảm bảo đã include core/auth.php trước đó (trong index.php)

$ten_phan_mem = get_setting('ten_phan_mem');
?>
<header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom p-3">
    <div class="container-fluid">
        <a class="navbar-brand text-primary fw-bold" href="#" onclick="loadContent('dashboard')">
            <?= htmlspecialchars($ten_phan_mem) ?>
        </a>
        <div class="ms-auto d-flex align-items-center">
            <span class="me-3 text-secondary">
                Xin chào, 
                <strong class="text-dark"><?= htmlspecialchars($_SESSION['ten_day_du'] ?? 'Người dùng') ?></strong> 
                (<?= strtoupper(get_user_role()) ?>)
            </span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </div>
    </div>
</header>