<?php
// modules/sao_luu_du_lieu.php
require_once '../core/auth.php';
check_login(); 
if (get_user_role() != 'admin') { 
    echo '<div class="alert alert-danger">Chỉ Admin mới có quyền truy cập.</div>'; 
    exit;
}
?>

<div class="container-fluid">
    <h2 class="text-primary mb-4">💾 Sao lưu Dữ liệu (Admin)</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="shadow p-4 bg-white rounded">
                <h4>Sao lưu Cơ sở Dữ liệu</h4>
                <p>Tạo một bản sao lưu toàn bộ cơ sở dữ liệu (file `.sql`) bao gồm tất cả các bảng và dữ liệu.</p>
                <a href="modules/backup_handler.php?action=backup_db" class="btn btn-success" id="btn_backup_db">
                    <i class="fas fa-database"></i> Sao lưu Database ngay
                </a>
                <small class="form-text text-muted d-block mt-2">File sao lưu sẽ được tải về máy của bạn.</small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="shadow p-4 bg-white rounded">
                <h4>Sao lưu Tệp đính kèm</h4>
                <p>Nén toàn bộ thư mục chứa các tệp văn bản đính kèm (file `.zip`).</p>
                <button class="btn btn-info" disabled>
                    <i class="fas fa-file-archive"></i> Tạo Zip File Đính kèm
                </button>
                <small class="form-text text-muted d-block mt-2">Chức năng đang được phát triển.</small>
            </div>
        </div>
    </div>
    
    <div class="mt-5">
        <h4>Lịch sử Sao lưu Gần đây</h4>
        <div class="alert alert-info">Hiển thị danh sách các file sao lưu đã tạo gần đây để tải lại.</div>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">