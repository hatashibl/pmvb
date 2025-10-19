<?php
// modules/van_ban_den_sua.php - Module Chỉnh sửa Văn bản Đến

require_once '../core/auth.php'; 
check_login();

global $pdo;

// 1. Lấy ID Văn bản
$vb_id = (int)($_GET['id'] ?? 0);

if ($vb_id <= 0) {
    echo "<div class='alert alert-danger'>Lỗi: Không tìm thấy ID Văn bản để chỉnh sửa.</div>";
    exit;
}

// 2. Lấy dữ liệu Văn bản Đến hiện tại
try {
    $stmt_vb = $pdo->prepare("
        SELECT 
            vbd.*, 
            DATE_FORMAT(vbd.ngay_thang_vb, '%d/%m/%Y') AS ngay_thang_vb_format 
        FROM van_ban_den vbd
        WHERE vbd.id = ?
    ");
    $stmt_vb->execute([$vb_id]);
    $van_ban = $stmt_vb->fetch(PDO::FETCH_ASSOC);

    if (!$van_ban) {
        echo "<div class='alert alert-danger'>Lỗi: Văn bản có ID {$vb_id} không tồn tại.</div>";
        exit;
    }

} catch (PDOException $e) {
    error_log("Lỗi SQL khi lấy VB sửa: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Lỗi hệ thống khi tải dữ liệu văn bản.</div>";
    exit;
}

// 3. Lấy tất cả Loại Văn bản
try {
    $stmt_lvb = $pdo->query("SELECT id, ten_loai_vb, goi_y_trich_yeu, goi_y_xu_ly FROM loai_van_ban ORDER BY ten_loai_vb");
    $loai_van_ban = $stmt_lvb->fetchAll(PDO::FETCH_ASSOC); 
} catch (PDOException $e) { $loai_van_ban = []; }

?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="text-warning"><i class="fas fa-edit me-2"></i> Chỉnh sửa Văn bản Đến: Số đến <?= htmlspecialchars($van_ban['so_den']) ?>/<?= htmlspecialchars($van_ban['nam_den']) ?></h3>
            <span class="text-muted">Module: Văn bản Đến</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12"> 
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-warning text-white">
                Thông tin Văn bản
            </div>
            <div class="card-body">
                <form id="form_vb_den_edit" action="modules/ajax_handler.php?action=edit_van_ban_den" method="POST" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?php echo htmlspecialchars($van_ban['id']); ?>">
    <input type="hidden" name="action" value="edit_van_ban_den">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="so_den" class="form-label">Số đến <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="so_den" name="so_den" required 
                                inputmode="numeric" pattern="\d+" 
                                value="<?= htmlspecialchars($van_ban['so_den']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="nam_den" class="form-label">Năm đến <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nam_den" name="nam_den" value="<?= htmlspecialchars($van_ban['nam_den']) ?>" required readonly>
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
                                        <?= ($lvb['id'] == $van_ban['loai_vb_id']) ? 'selected' : '' ?>
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
                            <input type="text" class="form-control" id="so_van_ban" name="so_van_ban" required value="<?= htmlspecialchars($van_ban['so_van_ban']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="ngay_thang_vb" class="form-label">Ngày VB (dd/mm/yyyy) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control date-picker" id="ngay_thang_vb" name="ngay_thang_vb" placeholder="dd/mm/yyyy" required data-format="dd/mm/yyyy" value="<?= htmlspecialchars($van_ban['ngay_thang_vb_format']) ?>">
                            <div class="invalid-feedback" id="ngay_thang_vb_feedback">Định dạng không hợp lệ. Vui lòng nhập theo định dạng DD/MM/YYYY.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="noi_ban_hanh" class="form-label">Nơi ban hành <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="noi_ban_hanh" name="noi_ban_hanh" required value="<?= htmlspecialchars($van_ban['noi_ban_hanh']) ?>"> 
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="trich_yeu" class="form-label">Trích yếu nội dung <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="trich_yeu" name="trich_yeu" rows="2" required placeholder="Nội dung trích yếu..."><?= htmlspecialchars($van_ban['trich_yeu']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="de_xuat_xu_ly" class="form-label">Đề xuất xử lý <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="de_xuat_xu_ly" name="de_xuat_xu_ly" rows="2" required placeholder="Đề xuất xử lý..."><?= htmlspecialchars($van_ban['de_xuat_xu_ly']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="file_dinh_kem" class="form-label">File đính kèm (PDF/DOCX)</label>
                        <div class="input-group">
                            <input class="form-control" type="file" id="file_dinh_kem" name="file_dinh_kem" accept=".pdf, .docx, .doc, .zip">
                            
                            <?php if (!empty($van_ban['file_dinh_kem'])): ?>
                                <span class="input-group-text bg-light border-warning">
                                    <a href="../<?= htmlspecialchars($van_ban['file_dinh_kem']) ?>" target="_blank" class="text-warning me-2"><i class="fas fa-file-alt"></i> File cũ</a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteExistingFile(<?= $van_ban['id'] ?>)">Xoá File cũ</button>
                                </span>
                                <input type="hidden" id="current_file_path" name="current_file_path" value="<?= htmlspecialchars($van_ban['file_dinh_kem']) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="form-text text-muted">Nếu bạn chọn file mới, file cũ sẽ bị thay thế.</div>
                    </div>

                    <div class="mt-4 pt-2 border-top">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-sync-alt me-2"></i> Cập nhật Văn bản</button>
                        <button type="button" class="btn btn-secondary" onclick="loadContent('van_ban_den_nhap')">Hủy / Quay lại</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Hàm kiểm tra định dạng ngày tháng DD/MM/YYYY (Dùng lại từ module nhập)
    function isValidDate(dateString) {
        const regex = /^\d{2}\/\d{2}\/\d{4}$/;
        if (!regex.test(dateString)) {
            return false;
        }
        const [day, month, year] = dateString.split('/').map(Number);
        const date = new Date(year, month - 1, day);
        return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;
    }

    $(document).ready(function() {
        if (typeof initializePlugins === 'function') {
            initializePlugins(); // Khởi tạo Select2 và Datepicker
        }
        
        // Cài đặt Placeholder gợi ý cho Trích yếu và Xử lý khi load trang
        $('#loai_vb_id').on('change', function() {
            const selectedOption = $(this).find(':selected');
            const trieuYeu = selectedOption.data('trieu-yeu') || 'Nội dung trích yếu...';
            const xuLy = selectedOption.data('xu-ly') || 'Đề xuất xử lý...';
            
            // Chỉ đặt placeholder nếu trường đó chưa có dữ liệu (hoặc khi reset)
            if ($('#trich_yeu').val().trim() === '') {
                 $('#trich_yeu').attr('placeholder', trieuYeu);
            }
            if ($('#de_xuat_xu_ly').val().trim() === '') {
                 $('#de_xuat_xu_ly').attr('placeholder', xuLy);
            }

        }).trigger('change'); 

        // Kiểm tra định dạng ngày tháng
        $('#ngay_thang_vb').on('blur', function() {
            const val = $(this).val();
            if (val && !isValidDate(val)) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Xử lý Form Submit AJAX
        $('#form_vb_den_edit').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
			const sendButton = $('#saveVanBanBtn');

            if (!form[0].checkValidity() || $('#ngay_thang_vb').hasClass('is-invalid')) {
                form.addClass('was-validated');
                alert('Vui lòng điền đầy đủ và đúng định dạng các trường bắt buộc.');
                return;
            }
sendButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang lưu...');
$.ajax({
        url: 'modules/ajax_handler.php?action=edit_van_ban_den',
        method: 'POST',
        data: new FormData(this),
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                
                // --- CHUYỂN HƯỚNG VỀ TRANG NHẬP SAU KHI SỬA THÀNH CÔNG ---
                if (typeof loadContent === 'function') {
                    // Tải lại module van_ban_den_nhap để xem kết quả
                    loadContent('van_ban_den_nhap', {t: Date.now()}); 
                } else {
                    window.location.href = 'index.php?module=van_ban_den_nhap'; 
                }
                // --- KẾT THÚC CHUYỂN HƯỚNG ---
                
            } else {
                alert('Lỗi: ' + response.message);
                // ... (Kích hoạt lại nút) ...
            }
        },
error: function(xhr) {
                // Xử lý lỗi chi tiết hơn nếu cần
                let errorMessage = 'Lỗi kết nối khi cập nhật văn bản.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMessage = 'Lỗi Server (Chi tiết): ' + xhr.responseText.substring(0, 150) + '...';
                }
                alert('Lỗi: ' + errorMessage);
                sendButton.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Lưu chỉnh sửa');
            }
        });
    });
    });

    // Hàm xóa file đính kèm cũ (Xử lý riêng để tránh lỗi khi người dùng chỉ muốn xóa file)
    function deleteExistingFile(vb_id) {
        if (!confirm('Bạn có chắc chắn muốn xóa file đính kèm cũ của văn bản này không?')) {
            return;
        }
        
        $.ajax({
            url: 'modules/ajax_handler.php?action=delete_vb_file',
            method: 'POST',
            data: { id: vb_id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    // Tải lại form sửa để cập nhật giao diện
                    loadContent('van_ban_den_sua', {id: vb_id});
                } else {
                    alert('Lỗi xóa file: ' + response.message);
                }
            },
            error: function() {
                alert('Lỗi kết nối khi xóa file.');
            }
        });
    }
</script>