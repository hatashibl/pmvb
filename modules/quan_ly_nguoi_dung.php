<?php
// modules/quan_ly_nguoi_dung.php
require_once '../core/auth.php';
check_login(); 
if (get_user_role() != 'admin') { 
    echo '<div class="alert alert-danger">Chỉ Admin mới có quyền truy cập.</div>'; 
    exit;
}

// Logic hiển thị danh sách người dùng
$where = "WHERE 1=1";
$params = [];
$filter_role = $_GET['chuc_vu'] ?? '';
$search_keyword = $_GET['keyword'] ?? '';

if ($filter_role) {
    $where .= " AND chuc_vu = :role";
    $params[':role'] = $filter_role;
}
if ($search_keyword) {
    $where .= " AND (ten_day_du LIKE :keyword OR ten_dang_nhap LIKE :keyword)";
    $params[':keyword'] = '%' . $search_keyword . '%';
}

$stmt = $pdo->prepare("SELECT id, ten_dang_nhap, ten_day_du, chuc_vu FROM nguoi_dung $where ORDER BY chuc_vu, ten_day_du");
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h2 class="text-primary mb-4">👥 Quản lý Người dùng (Admin)</h2>

    <div class="shadow p-3 mb-4 bg-light rounded">
        <form id="form_filter_user" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="keyword" placeholder="Tìm tên/tên đăng nhập" value="<?= htmlspecialchars($search_keyword) ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="chuc_vu">
                    <option value="">-- Lọc theo Chức vụ --</option>
                    <option value="admin" <?= $filter_role == 'admin' ? 'selected' : '' ?>>Quản trị</option>
                    <option value="quan_ly" <?= $filter_role == 'quan_ly' ? 'selected' : '' ?>>Quản lý</option>
                    <option value="thanh_vien" <?= $filter_role == 'thanh_vien' ? 'selected' : '' ?>>Thành viên</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-secondary">Lọc/Tìm</button>
            </div>
            <div class="col-md-3 d-grid">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                    + Thêm Người dùng
                </button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead>
                <tr class="table-primary">
                    <th>ID</th>
                    <th>Tên đầy đủ</th>
                    <th>Tên đăng nhập</th>
                    <th>Chức vụ</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="text-center">Không tìm thấy người dùng nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['ten_day_du']) ?></td>
                            <td><?= htmlspecialchars($user['ten_dang_nhap']) ?></td>
                            <td><?= strtoupper($user['chuc_vu']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info edit-user" data-id="<?= $user['id'] ?>">Sửa</button>
                                <button class="btn btn-sm btn-danger delete-user" data-id="<?= $user['id'] ?>" <?= $user['chuc_vu'] == 'admin' && $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>Xóa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form_user_crud" action="modules/user_handler.php" method="POST">
                <input type="hidden" name="user_id" id="modal_user_id" value="">
                <input type="hidden" name="action" id="modal_action" value="add">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="userModalLabel">Thêm Người dùng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ten_day_du" class="form-label">Tên đầy đủ</label>
                        <input type="text" class="form-control" id="modal_ten_day_du" name="ten_day_du" required>
                    </div>
                    <div class="mb-3">
                        <label for="ten_dang_nhap" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="modal_ten_dang_nhap" name="ten_dang_nhap" required>
                    </div>
                    <div class="mb-3">
                        <label for="mat_khau" class="form-label">Mật khẩu (Để trống nếu không đổi)</label>
                        <input type="password" class="form-control" id="modal_mat_khau" name="mat_khau">
                    </div>
                    <div class="mb-3">
                        <label for="chuc_vu" class="form-label">Chức vụ</label>
                        <select class="form-select" id="modal_chuc_vu" name="chuc_vu" required>
                            <option value="thanh_vien">Thành viên</option>
                            <option value="quan_ly">Quản lý</option>
                            <option value="admin">Quản trị</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Logic JavaScript cho Modal Sửa
    $(document).on('click', '.edit-user', function() {
        const userId = $(this).data('id');
        // Gọi AJAX để lấy thông tin chi tiết người dùng
        $.ajax({
            url: 'modules/user_handler.php',
            type: 'GET',
            data: { action: 'get_user', id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#userModalLabel').text('Chỉnh sửa Người dùng');
                    $('#modal_action').val('edit');
                    $('#modal_user_id').val(response.user.id);
                    $('#modal_ten_day_du').val(response.user.ten_day_du);
                    $('#modal_ten_dang_nhap').val(response.user.ten_dang_nhap);
                    $('#modal_chuc_vu').val(response.user.chuc_vu);
                    $('#modal_mat_khau').val(''); // Không đổ mật khẩu cũ
                    $('#userModal').modal('show');
                } else {
                    showNotification(response.message, 'danger');
                }
            }
        });
    });

    // Reset modal khi đóng hoặc mở cho chức năng Thêm mới
    $('#userModal').on('hidden.bs.modal', function () {
        $('#form_user_crud')[0].reset();
        $('#userModalLabel').text('Thêm Người dùng');
        $('#modal_action').val('add');
        $('#modal_user_id').val('');
    });
    
    // Xử lý Xóa người dùng
    $(document).on('click', '.delete-user', function() {
        if (confirm('Bạn có chắc chắn muốn xóa người dùng này không?')) {
            const userId = $(this).data('id');
            // Gửi AJAX xóa
            $.ajax({
                url: 'modules/user_handler.php',
                type: 'POST',
                data: { action: 'delete', user_id: userId },
                dataType: 'json',
                success: function(response) {
                    showNotification(response.message, response.success ? 'success' : 'danger');
                    if (response.success) {
                        loadContent('quan_ly_nguoi_dung'); // Tải lại trang sau khi xóa
                    }
                }
            });
        }
    });
    
    // Xử lý Form Thêm/Sửa (sử dụng delegated event handler trong custom.js)
    // Cần thêm logic xử lý form #form_user_crud vào custom.js
</script>