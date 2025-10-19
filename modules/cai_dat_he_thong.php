<?php
// modules/cai_dat_he_thong.php
require_once '../core/auth.php';
check_login(); 
if (get_user_role() != 'admin') { 
    echo '<div class="alert alert-danger">Chỉ Admin mới có quyền truy cập.</div>'; 
    exit;
}

// Lấy danh sách Loại Văn bản
$stmt_lvb = $pdo->query("SELECT * FROM loai_van_ban ORDER BY ten_loai_vb");
$loai_van_ban = $stmt_lvb->fetchAll(PDO::FETCH_ASSOC);

// Lấy Cài đặt Hệ thống
$settings = $pdo->query("SELECT * FROM cai_dat_he_thong")->fetchAll(PDO::FETCH_KEY_PAIR);

?>
<script>
    tinymce.init({
        selector: '.wysiwyg-lvb-editor', // Dùng selector riêng cho modal
        plugins: 'lists code',
        toolbar: 'undo redo | bold italic | bullist numlist | code',
        menubar: false,
        height: 200
    });

    // Logic Sửa Loại Văn bản
    $(document).on('click', '.edit-lvb', function() {
        const id = $(this).data('id');
        // Gọi AJAX để lấy chi tiết Loại VB
        $.ajax({
            url: 'modules/cai_dat_handler.php',
            type: 'GET',
            data: { action: 'get_lvb', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#lvbModalLabel').text('Sửa Loại Văn bản');
                    $('#modal_lvb_id').val(response.data.id);
                    $('#modal_lvb_action').val('edit_lvb');
                    $('#modal_ten_loai_vb').val(response.data.ten_loai_vb);
                    $('#modal_ten_viet_tat').val(response.data.ten_viet_tat);
                    
                    // Cập nhật nội dung TinyMCE trong modal
                    tinymce.get('modal_goi_y_trich_yeu').setContent(response.data.goi_y_trich_yeu);
                    tinymce.get('modal_goi_y_xu_ly').setContent(response.data.goi_y_xu_ly);
                    
                    $('#lvbModal').modal('show');
                }
            }
        });
    });
    
    // Reset modal khi mở cho chức năng Thêm mới
    $('#lvbModal').on('show.bs.modal', function () {
        if ($('#modal_lvb_action').val() !== 'edit_lvb') {
            $('#lvbModalLabel').text('Thêm Loại Văn bản');
            $('#modal_lvb_id').val('');
            $('#modal_lvb_action').val('add_lvb');
            $('#form_lvb_crud')[0].reset();
            tinymce.get('modal_goi_y_trich_yeu').setContent('');
            tinymce.get('modal_goi_y_xu_ly').setContent('');
        }
    });

    // Xử lý Xóa Loại Văn bản (tương tự như user_handler)
    // ...
</script>

<div class="container-fluid">
    <h2 class="text-primary mb-4">⚙️ Cài đặt Hệ thống (Admin)</h2>

    <div class="shadow p-4 mb-4 bg-white rounded">
        <h4>Thông tin Hệ thống</h4>
        <form id="form_system_settings" action="modules/cai_dat_handler.php" method="POST">
            <input type="hidden" name="action" value="update_settings">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên phần mềm (Header)</label>
                    <input type="text" class="form-control" name="ten_phan_mem" value="<?= htmlspecialchars($settings['ten_phan_mem'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Copyright (Footer)</label>
                    <input type="text" class="form-control" name="copyright" value="<?= htmlspecialchars($settings['copyright'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Năm hiện hành (Mặc định cho Văn bản Đến)</label>
                    <input type="number" class="form-control" name="nam_hien_hanh" value="<?= htmlspecialchars($settings['nam_hien_hanh'] ?? date('Y')) ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Lưu Cài đặt</button>
        </form>
    </div>

    <div class="shadow p-4 mb-4 bg-white rounded">
        <h4>Quản lý Loại Văn bản</h4>
        <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#lvbModal">
            + Thêm Loại Văn bản
        </button>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr class="table-info">
                        <th>ID</th>
                        <th>Tên Loại VB</th>
                        <th>Tên Viết tắt</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loai_van_ban as $lvb): ?>
                        <tr>
                            <td><?= $lvb['id'] ?></td>
                            <td><?= htmlspecialchars($lvb['ten_loai_vb']) ?></td>
                            <td><?= htmlspecialchars($lvb['ten_viet_tat']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info edit-lvb" data-id="<?= $lvb['id'] ?>">Sửa</button>
                                <button class="btn btn-sm btn-danger delete-lvb" data-id="<?= $lvb['id'] ?>">Xóa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="lvbModal" tabindex="-1" aria-labelledby="lvbModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form_lvb_crud" action="modules/cai_dat_handler.php" method="POST">
                <input type="hidden" name="lvb_id" id="modal_lvb_id" value="">
                <input type="hidden" name="action" id="modal_lvb_action" value="add_lvb">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="lvbModalLabel">Thêm Loại Văn bản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="modal_ten_loai_vb" class="form-label">Tên Loại VB</label>
                            <input type="text" class="form-control" id="modal_ten_loai_vb" name="ten_loai_vb" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_ten_viet_tat" class="form-label">Tên Viết tắt</label>
                            <input type="text" class="form-control" id="modal_ten_viet_tat" name="ten_viet_tat" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gợi ý Trích yếu</label>
                        <textarea class="form-control wysiwyg-lvb-editor" id="modal_goi_y_trich_yeu" name="goi_y_trich_yeu"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gợi ý Xử lý</label>
                        <textarea class="form-control wysiwyg-lvb-editor" id="modal_goi_y_xu_ly" name="goi_y_xu_ly"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>