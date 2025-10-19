<?php
// modules/dashboard.php
require_once '../core/auth.php';
check_login(); 

$role = get_user_role();
?>
<div class="row">
    <div class="col-12">
        <h2 class="text-primary mb-4">Tổng quan Hệ thống</h2>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                            VB Đến Chưa Đọc
                        </div>
                        <div class="h5 mb-0 fw-bold text-gray-800">12</div> </div>
                    <div class="col-auto">
                        <i class="fas fa-inbox fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                            Tổng VB Đi (Năm nay)
                        </div>
                        <div class="h5 mb-0 fw-bold text-gray-800">45</div> </div>
                    <div class="col-auto">
                        <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col me-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">
                            Quyền hạn của bạn
                        </div>
                        <div class="h5 mb-0 fw-bold text-dark"><?= strtoupper($role) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <h4 class="text-secondary">Thông báo/Hoạt động gần đây</h4>
    <div class="alert alert-info">
        Đây là phần hiển thị các thông báo quan trọng hoặc 5 hoạt động gần nhất của bạn.
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">