<?php
// modules/hop_thu_di.php
require_once '../core/auth.php';
check_login(); 
if (get_user_role() == 'thanh_vien') { 
    echo '<div class="alert alert-danger">Bạn không có quyền truy cập chức năng này.</div>'; 
    exit;
}
?>

<div class="container-fluid">
    <h2 class="text-primary mb-4">📤 Văn bản đã gửi</h2>

    <div class="shadow p-3 mb-4 bg-light rounded">
        <form id="form_search_outbox" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="keyword" placeholder="Số/Trích yếu VB...">
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="nam_ban_hanh" placeholder="Năm ban hành">
            </div>
            <div class="col-md-5 d-grid">
                <button type="submit" class="btn btn-primary">Tìm</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover bg-white">
            <thead>
                <tr class="table-primary">
                    <th>#</th>
                    <th>Số VB/Ngày</th>
                    <th>Trích yếu</th>
                    <th>Thời gian gửi</th>
                    <th>Người nhận (Đã xem/Chưa xem)</th>
                    <th>Tin nhắn/File trả lời mới</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody id="outbox_list">
                <tr><td colspan="7" class="text-center">Tải danh sách Văn bản đã gửi...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).on('submit', '#form_search_outbox', function(e) {
        e.preventDefault();
        const keyword = $('[name="keyword"]').val().trim();
        if (keyword === '') {
            alert('Chưa nhập nội dung tìm'); // Hiển thị popup
            return;
        }
        // Gọi AJAX để tải lại danh sách
        alert('Đang tìm kiếm...');
    });
</script>