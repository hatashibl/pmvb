<?php
// modules/van_ban_den_nhap.php - Module Nhập Văn bản Đến (Hoàn chỉnh)

require_once '../core/auth.php'; 
// Giả định: File core/auth.php đã tồn tại và có hàm check_login()
check_login();

// Giả định: Kết nối CSDL đã được thiết lập trong global $pdo
global $pdo;

// --- 1. Phân quyền và Lấy thông tin người dùng hiện tại ---
$current_user_id = $_SESSION['user_id'] ?? 0;
$current_user_chuc_vu = $_SESSION['chuc_vu'] ?? 'thanh_vien'; 

// Cấp độ được phép NHẬP/SỬA/XOÁ (Giả định: admin hoặc quan_ly)
$can_nhap_vb = in_array($current_user_chuc_vu, ['admin', 'quan_ly']);
// Admin luôn có quyền Sửa/Xóa mọi văn bản
$is_admin = ($current_user_chuc_vu == 'admin');

// --- 2. Lấy dữ liệu cần thiết từ CSDL (Giữ nguyên) ---

// Lấy tất cả Loại Văn bản và thông tin cần thiết
try {
    $stmt_lvb = $pdo->query("SELECT id, ten_loai_vb, goi_y_trich_yeu, goi_y_xu_ly FROM loai_van_ban ORDER BY ten_loai_vb");
    $loai_van_ban_map = [];
    $loai_van_ban_data = [];
    $loai_van_ban = $stmt_lvb->fetchAll(PDO::FETCH_ASSOC); 
    foreach ($loai_van_ban as $lvb) {
        $loai_van_ban_map[$lvb['id']] = $lvb['ten_loai_vb'];
        $loai_van_ban_data[$lvb['id']] = $lvb; 
    }
} catch (PDOException $e) { $loai_van_ban = []; $loai_van_ban_map = []; $loai_van_ban_data = []; }

// Lấy 10 Văn bản Đến mới nhất
try {
    $stmt_recent = $pdo->query("
        SELECT 
            vbd.id, vbd.so_den, vbd.nam_den, vbd.so_van_ban, vbd.ngay_thang_vb, vbd.trich_yeu, vbd.loai_vb_id, 
            vbd.thoi_gian_nhap, vbd.file_dinh_kem, vbd.nguoi_nhap_id, 
            nd.ten_day_du AS nguoi_nhap_ten 
        FROM van_ban_den vbd
        LEFT JOIN nguoi_dung nd ON vbd.nguoi_nhap_id = nd.id
        ORDER BY vbd.thoi_gian_nhap DESC
        LIMIT 10
    ");
    $recent_vbs = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $recent_vbs = []; }

$thoi_gian_nhap = date('Y-m-d H:i:s');
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-primary">📝 Nhập Văn bản Đến</h3>
            <span class="text-muted">Module: Văn bản Đến</span>
        </div>
    </div>
</div>

<div class="row">
    <?php if ($can_nhap_vb): ?>
    <div class="col-lg-12"> 
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                Thông tin Văn bản
            </div>
            <div class="card-body">
                <form id="form_vb_den" action="modules/ajax_handler.php?action=add_van_ban_den" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="thoi_gian_nhap" value="<?= htmlspecialchars($thoi_gian_nhap) ?>">

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="so_den" class="form-label">Số đến <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="so_den" name="so_den" required 
                                inputmode="numeric" pattern="\d+" 
                                title="Vui lòng chỉ nhập số (ví dụ: 123)">
                            <div class="invalid-feedback">Vui lòng chỉ nhập số.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="nam_den" class="form-label">Năm đến <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nam_den" name="nam_den" value="<?= date('Y') ?>" required readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="loai_vb_id" class="form-label">Loại Văn bản <span class="text-danger">*</span></label>
                            <select class="form-select select2 w-100" id="loai_vb_id" name="loai_vb_id" required data-placeholder="--- Chọn loại văn bản ---">
                                <option value=""></option>
                                <?php foreach ($loai_van_ban as $lvb): ?>
                                    <option 
                                        value="<?= htmlspecialchars($lvb['id']) ?>" 
                                        data-trieu-yeu="<?= htmlspecialchars($lvb['goi_y_trich_yeu']) ?>"
                                        data-xu-ly="<?= htmlspecialchars($lvb['goi_y_xu_ly']) ?>"
                                    >
                                        <?= htmlspecialchars($lvb['ten_loai_vb']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                         <div class="col-md-4">
                            <label for="so_van_ban" class="form-label">Số Văn bản <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="so_van_ban" name="so_van_ban" required
                                inputmode="numeric" pattern="\d+" 
                                title="Vui lòng chỉ nhập số (ví dụ: 123)">
                            <div class="invalid-feedback">Vui lòng chỉ nhập số cho Số Văn bản.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="ngay_thang_vb" class="form-label">Ngày VB (dd/mm/yyyy) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control date-picker" id="ngay_thang_vb" name="ngay_thang_vb" placeholder="dd/mm/yyyy" required data-format="dd/mm/yyyy">
                            <div class="invalid-feedback" id="ngay_thang_vb_feedback">Định dạng không hợp lệ. Vui lòng nhập theo định dạng DD/MM/YYYY.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="noi_ban_hanh" class="form-label">Nơi ban hành <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="noi_ban_hanh" name="noi_ban_hanh" required> 
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="trich_yeu" class="form-label">Trích yếu nội dung <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="trich_yeu" name="trich_yeu" rows="2" required placeholder="Nội dung trích yếu..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="de_xuat_xu_ly" class="form-label">Đề xuất xử lý <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="de_xuat_xu_ly" name="de_xuat_xu_ly" rows="2" required placeholder="Đề xuất xử lý..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="file_dinh_kem" class="form-label">File đính kèm (PDF/DOCX/ZIP)</label>
                        <input class="form-control" type="file" id="file_dinh_kem" name="file_dinh_kem" accept=".pdf, .docx, .doc, .zip">
                    </div>

                    <button type="submit" class="btn btn-success mt-3"><i class="fas fa-save me-2"></i> Lưu Văn bản Đến</button>
                    <button type="reset" class="btn btn-secondary mt-3">Nhập lại</button>

                </form>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-12">
        <div class="alert alert-danger text-center shadow-sm">
            <i class="fas fa-lock me-2"></i> **Cảnh báo:** Chức năng nhập văn bản đến chỉ dành cho cấp độ Quản lý hoặc Admin.
        </div>
    </div>
    <?php endif; ?>
</div>

<hr>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-info text-white">
                10 Văn bản Đến mới nhất
            </div>
            <div class="card-body p-0">
                <?php if (count($recent_vbs) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Thời gian nhập</th> 
                                <th>Số đến</th>
                                <th>Loại VB</th>
                                <th>Số VB</th>
                                <th>Ngày VB</th>
                                <th>Trích yếu</th>
                                <th>Người nhập</th> 
                                <th>File đính kèm</th> 
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_vbs as $vb): ?>
                            <tr data-vb-id="<?= htmlspecialchars($vb['id']) ?>">
                                <td><?= date('d/m/Y H:i', strtotime($vb['thoi_gian_nhap'])) ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($vb['so_den']) ?></span></td>
                                <td><?= htmlspecialchars($loai_van_ban_map[$vb['loai_vb_id']] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($vb['so_van_ban'] ?? 'N/A') ?></td>
                                <td><?= date('d/m/Y', strtotime($vb['ngay_thang_vb'])) ?></td>
                                <td title="<?= htmlspecialchars($vb['trich_yeu']) ?>" class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($vb['trich_yeu']) ?></td>
                                <td><?= htmlspecialchars($vb['nguoi_nhap_ten'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($vb['file_dinh_kem'])): ?>
                                        <a href="<?= htmlspecialchars($vb['file_dinh_kem']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Tải xuống">
                                            <i class="fas fa-file-download"></i> Tải
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Không</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                    <?php 
                                        // QUYỀN SỬA/XOÁ: Admin (quản trị hệ thống) HOẶC Quản lý/Thành viên tự nhập
                                        $can_edit_or_delete = $is_admin || ($vb['nguoi_nhap_id'] == $current_user_id && $can_nhap_vb);
                                    ?>
                                        
                                        <?php if ($can_edit_or_delete): ?>
                                        <button class="btn btn-warning" 
                                                onclick="loadContent('van_ban_den_sua', {id: <?= $vb['id'] ?>})" 
                                                title="Sửa Văn bản" 
                                                data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php endif; ?>

                                        <button class="btn btn-success" 
                                                onclick="showSendModal(<?= $vb['id'] ?>)" 
                                                title="Gửi Văn bản"
                                                data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>

                                        <?php if ($can_edit_or_delete): ?>
                                        <button class="btn btn-danger" 
                                                onclick="deleteVanBan(<?= $vb['id'] ?>)" 
                                                title="Xóa Văn bản"
                                                data-bs-toggle="tooltip" data-bs-placement="top">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="p-3 text-center text-muted">Chưa có văn bản đến nào được nhập.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sendVanBanModal" tabindex="-1" aria-labelledby="sendVanBanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="sendVanBanModalLabel"><i class="fas fa-paper-plane me-2"></i> Chuyển Giao Văn bản</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="send_vb_id">

                <div class="mb-3">
                    <label for="recipients" class="form-label">Chọn Người nhận <span class="text-danger">*</span></label>
                    <select class="form-select w-100" id="recipients" multiple="multiple" data-placeholder="Chọn một hoặc nhiều người nhận..." required>
                        </select>
                </div>

                <div class="mb-3">
                    <label for="send_message" class="form-label">Tin nhắn kèm theo (Tùy chọn)</label>
                    <textarea class="form-control" id="send_message" rows="3" placeholder="Nhập tin nhắn hoặc đề xuất xử lý nhanh..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" onclick="sendVanBan()"><i class="fas fa-paper-plane me-2"></i> Gửi</button>
            </div>
        </div>
    </div>
</div>
<script>
    // Hàm kiểm tra định dạng ngày tháng DD/MM/YYYY
    function isValidDate(dateString) {
        const regex = /^\d{2}\/\d{2}\/\d{4}$/;
        if (!regex.test(dateString)) {
            return false;
        }
        const [day, month, year] = dateString.split('/').map(Number);
        const date = new Date(year, month - 1, day);
        return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;
    }

    // Hàm load danh sách người dùng cho Select2 
    function loadRecipients() {
        const users = [
            <?php 
                try {
                    $stmt_all_users = $pdo->query("SELECT id, ten_day_du FROM nguoi_dung ORDER BY ten_day_du");
                    $all_users = $stmt_all_users->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($all_users as $user) {
                        echo "{id: " . $user['id'] . ", text: '" . htmlspecialchars($user['ten_day_du'], ENT_QUOTES) . "'},";
                    }
                } catch (PDOException $e) { /* Lỗi */ }
            ?>
        ];
        
        $('#recipients').select2({
            dropdownParent: $('#sendVanBanModal'),
            data: users,
            placeholder: "Chọn người dùng",
            allowClear: true
        });
    }


    $(document).ready(function() {
        // Khởi tạo các plugin (Select2, Datepicker, Tooltip)
        if (typeof initializePlugins === 'function') {
            initializePlugins(); 
        }
        loadRecipients();
        
        // Khởi tạo Tooltip (chú thích cho các icon Sửa/Xóa/Gửi)
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Logic Gợi ý Xử lý khi chọn Loại VB (Giữ nguyên)
        $('#loai_vb_id').on('change', function() {
            const selectedOption = $(this).find(':selected');
            const trieuYeu = selectedOption.data('trieu-yeu') || 'Nội dung trích yếu...';
            const xuLy = selectedOption.data('xu-ly') || ''; 
            
            $('#trich_yeu').attr('placeholder', trieuYeu);
            $('#de_xuat_xu_ly').val(xuLy); 
            $('#de_xuat_xu_ly').attr('placeholder', xuLy || 'Đề xuất xử lý...'); 
        }).trigger('change');
        
        $('#form_vb_den').on('reset', function() {
            setTimeout(function() {
                $('#loai_vb_id').trigger('change'); 
            }, 50); 
        });

        // Kiểm tra định dạng ngày tháng và số (Giữ nguyên)
        $('#ngay_thang_vb').on('blur', function() {
            const val = $(this).val();
            if (val && !isValidDate(val)) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        $('#so_den, #so_van_ban').on('input', function() {
            if (this.checkValidity()) {
                $(this).removeClass('is-invalid');
            } else {
                $(this).addClass('is-invalid');
            }
        });
        
        // Xử lý Form Submit (Đã tích hợp FIX lỗi trùng lặp)
        $('#form_vb_den').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');

            if (!form[0].checkValidity() || $('#ngay_thang_vb').hasClass('is-invalid') || $('#so_den').hasClass('is-invalid') || $('#so_van_ban').hasClass('is-invalid')) {
                form.addClass('was-validated');
                alert('Vui lòng sửa các lỗi nhập liệu trước khi lưu.');
                return;
            }
            
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        
                        // FIX LỖI TRÙNG LẶP: Reset triệt để và tải lại module
                        form[0].reset(); 
                        form.removeClass('was-validated');
                        $('#so_den').val(''); 
                        $('#so_van_ban').val(''); 
                        $('#ngay_thang_vb').val(''); 
                        
                        // Tải lại module van_ban_den_nhap
                        if (typeof loadContent === 'function') {
                             loadContent('van_ban_den_nhap', {t: Date.now()}); 
                        } else {
                             window.location.reload(true); 
                        }
                        
                    } else {
                        alert('Lỗi: ' + response.message);
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Lưu Văn bản Đến');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Lỗi không xác định khi lưu văn bản.';
                    // Logic xử lý lỗi AJAX
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const errorData = JSON.parse(xhr.responseText);
                            if (errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) { /* pass */ }
                    }
                    alert('Lỗi: ' + errorMessage);
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Lưu Văn bản Đến');
                }
            });
        });
    });
    
    // Các hàm showSendModal, sendVanBan, deleteVanBan (Giữ nguyên)
function showSendModal(vb_id) {
    // 1. Gán ID văn bản vào input hidden trong Modal
    $('#send_vb_id').val(vb_id);

    // 2. Reset các trường (Tùy chọn)
    $('#recipients').val(null).trigger('change'); // Xóa người nhận đã chọn (Select2)
    $('#send_message').val('');

    // 3. Hiển thị Modal
    const sendModal = new bootstrap.Modal(document.getElementById('sendVanBanModal'));
    sendModal.show();
}
function sendVanBan() {
    const vb_id = $('#send_vb_id').val();
    const recipient_ids = $('#recipients').val(); // Đây là array ID người dùng
    const message = $('#send_message').val();

    if (recipient_ids.length === 0) {
        alert('Vui lòng chọn ít nhất một người nhận.');
        return;
    }
    
    // Thêm feedback trực quan cho nút gửi
    const sendButton = $('#sendVanBanModal').find('.modal-footer button.btn-success');
    sendButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang gửi...');

    $.ajax({
        url: 'modules/ajax_handler.php?action=send_van_ban',
        method: 'POST',
        data: { 
            vb_id: vb_id, 
            recipients: recipient_ids, 
            message: message 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const sendModal = bootstrap.Modal.getInstance(document.getElementById('sendVanBanModal'));
                sendModal.hide();
                alert('Gửi văn bản thành công cho ' + response.count + ' người nhận!');
            } else {
                alert('Lỗi: ' + response.message);
            }
            // Kích hoạt lại nút gửi
            sendButton.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Gửi');
        },
		error: function(xhr) {
            let errorMessage = 'Đã xảy ra lỗi kết nối khi gửi văn bản.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                // Lỗi trả về từ JSON (PHP)
                errorMessage = 'Lỗi Server: ' + xhr.responseJSON.message;
            } else if (xhr.responseText) {
                // Lỗi PHP Fatal Error (Hiển thị 150 ký tự đầu của response)
                errorMessage = 'Lỗi Server (Chi tiết): ' + xhr.responseText.substring(0, 150) + '...';
            }
            alert(errorMessage);
            // Kích hoạt lại nút gửi
            sendButton.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Gửi');
        }
    });
}
    function deleteVanBan(id) {
        if (confirm('Bạn có chắc chắn muốn xóa Văn bản này không? Hành động này không thể hoàn tác.')) {
            $.ajax({
                url: 'modules/ajax_handler.php?action=delete_van_ban',
                method: 'POST',
                data: {id: id},
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Xóa văn bản thành công!');
                        if (typeof loadContent === 'function') {
                            loadContent('van_ban_den_nhap', {t: Date.now()});
                        } else {
                            window.location.reload(true);
                        }
                    } else {
                        alert('Lỗi xóa: ' + response.message);
                    }
                },
                error: function() {
                    alert('Lỗi kết nối khi xóa văn bản.');
                }
            });
        }
    }
</script>