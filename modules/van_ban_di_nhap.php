<?php
// modules/van_ban_di_nhap.php
require_once '../core/auth.php';
check_login(); 
// Thành viên và Quản lý đều có quyền nhập VB Đi (theo yêu cầu)

// Lấy danh sách loại văn bản cho dropdown
$stmt_loai_vb = $pdo->query("SELECT id, ten_loai_vb, ten_viet_tat FROM loai_van_ban ORDER BY ten_loai_vb");
$loai_van_ban = $stmt_loai_vb->fetchAll(PDO::FETCH_ASSOC);

// Lấy ID văn bản đi (nếu đang sửa) - Bỏ qua phần này cho mục đích demo tạo mới

?>
<script>
    // Khởi tạo TinyMCE
    tinymce.init({
        selector: '.wysiwyg-editor',
        // ... (cấu hình TinyMCE như file van_ban_den_nhap.php)
    });

    $(document).ready(function() {
        // Hàm đổ nội dung gợi ý Trích yếu (dùng AJAX)
        $('#loai_vb_id').change(function() {
            const loai_vb_id = $(this).val();
            if (loai_vb_id) {
                $.ajax({
                    url: 'modules/ajax_handler.php',
                    type: 'GET',
                    data: { action: 'get_loai_vb_detail', id: loai_vb_id },
                    dataType: 'json',
                    success: function(data) {
                        if (data && tinymce.get('trich_yeu')) {
                            // Cập nhật nội dung TinyMCE cho Trích yếu
                            tinymce.get('trich_yeu').setContent(data.goi_y_trich_yeu);
                        }
                    }
                });
            }
        });

        // Hàm tìm kiếm Văn bản Đến (cho mục trả lời VB Đến)
        $('#btn_find_vb_den').click(function() {
            // Logic tìm kiếm VB Đến và chọn ID (sẽ được implement chi tiết sau)
            alert('Chức năng tìm kiếm Văn bản Đến đang được phát triển...');
        });
        
        // Xử lý form Lưu Văn bản Đi
        $('#form_vb_di').on('submit', function(e) {
            // Thêm logic validation (kiểm tra rỗng) tương tự van_ban_den_nhap.php
            let isValid = true;
            // ... (Validation code ở đây)

            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ các trường bắt buộc.');
            }
        });
    });
</script>

<div class="container-fluid">
    <h2 class="text-primary mb-4">Nhập/Chỉnh sửa Văn bản Đi</h2>

    <form id="form_vb_di" action="modules/van_ban_di_handler.php" method="POST" enctype="multipart/form-data" class="shadow p-4 bg-white rounded">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="tra_loi_vb_den_id" id="tra_loi_vb_den_id" value=""> 

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="loai_vb_id" class="form-label fw-bold">Loại văn bản <span class="text-danger">*</span></label>
                <select class="form-select" id="loai_vb_id" name="loai_vb_id" required>
                    <option value="">-- Chọn loại văn bản --</option>
                    <?php foreach ($loai_van_ban as $loai): ?>
                        <option value="<?= $loai['id'] ?>" data-viet-tat="<?= htmlspecialchars($loai['ten_viet_tat']) ?>">
                            <?= htmlspecialchars($loai['ten_loai_vb']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small id="error_loai_vb_id"></small>
            </div>
            <div class="col-md-4">
                <label for="ngay_thang" class="form-label fw-bold">Ngày tháng VB Đi <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="ngay_thang" name="ngay_thang" value="<?= date('Y-m-d') ?>" required>
                <small id="error_ngay_thang"></small>
            </div>
            <div class="col-md-4">
                <label for="file_dinh_kem" class="form-label fw-bold">File đính kèm</label>
                <input type="file" class="form-control" id="file_dinh_kem" name="file_dinh_kem">
            </div>
        </div>

        <div class="mb-3">
            <label for="vb_nguon" class="form-label fw-bold">Văn bản nguồn (Trả lời cho VB Đến)</label>
            <div class="input-group">
                <input type="text" class="form-control" id="vb_nguon_info" readonly placeholder="Tìm kiếm và chọn Văn bản Đến...">
                <button class="btn btn-outline-secondary" type="button" id="btn_find_vb_den">Tìm kiếm/Chọn</button>
            </div>
            <small class="form-text text-muted">Thông tin VB Đến được chọn sẽ hiển thị ở đây.</small>
        </div>

        <div class="mb-3">
            <label for="trich_yeu" class="form-label fw-bold">Trích yếu <span class="text-danger">*</span></label>
            <textarea class="form-control wysiwyg-editor" id="trich_yeu" name="trich_yeu" rows="5"></textarea>
            <small id="error_trich_yeu"></small>
        </div>
        
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary me-2">Lưu & Cấp Số</button>
            <button type="reset" class="btn btn-secondary">Hủy</button>
        </div>
    </form>

    <h4 class="mt-5 mb-3">10 Văn bản Đi gần đây nhất</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead>
                <tr class="table-primary">
                    <th>STT</th>
                    <th>Số VB/Ngày</th>
                    <th>Loại VB</th>
                    <th>Trích yếu</th>
                    <th>File đính kèm</th>
                    <th>Người nhập</th>
                    <th>Chức năng</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="7" class="text-center">Chưa có dữ liệu văn bản đi gần đây.</td></tr>
            </tbody>
        </table>
    </div>
</div>