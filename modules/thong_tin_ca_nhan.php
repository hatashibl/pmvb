<?php
// modules/thong_tin_ca_nhan.php
require_once '../core/auth.php';
check_login();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT ten_day_du, ten_dang_nhap, chuc_vu FROM nguoi_dung WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<div class="container-fluid">
    <h2 class="text-primary mb-4">👤 Thông tin Cá nhân</h2>

    <div class="row">
        <div class="col-md-6">
            <div class="shadow p-4 bg-white rounded">
                <h4>Chỉnh sửa Thông tin cơ bản</h4>
                <form id="form_update_info" action="modules/profile_handler.php" method="POST">
                    <input type="hidden" name="action" value="update_info">
                    <div class="mb-3">
                        <label class="form-label">Tên đầy đủ</label>
                        <input type="text" class="form-control" name="ten_day_du" value="<?= htmlspecialchars($user_info['ten_day_du']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" name="ten_dang_nhap" value="<?= htmlspecialchars($user_info['ten_dang_nhap']) ?>" disabled>
                        <small class="text-muted">Không thể thay đổi tên đăng nhập.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chức vụ</label>
                        <input type="text" class="form-control" value="<?= strtoupper($user_info['chuc_vu']) ?>" disabled>
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
             <div class="shadow p-4 bg-white rounded">
                <h4>Đổi Mật khẩu</h4>
                <form id="form_update_password" action="modules/profile_handler.php" method="POST">
                    <input type="hidden" name="action" value="update_password">
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" class="form-control" name="mat_khau_moi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Xác nhận Mật khẩu mới</label>
                        <input type="password" class="form-control" name="xac_nhan_mat_khau" required>
                    </div>
                    <button type="submit" class="btn btn-danger">Đổi Mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>