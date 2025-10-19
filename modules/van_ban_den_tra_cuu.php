<?php
// modules/van_ban_den_tra_cuu.php
require_once '../core/auth.php';
check_login(); 
if (get_user_role() == 'thanh_vien') { 
    echo '<div class="alert alert-danger">Bạn không có quyền truy cập chức năng này.</div>'; 
    exit;
}

// Lấy danh sách loại văn bản cho form tìm kiếm
$stmt_loai_vb = $pdo->query("SELECT id, ten_loai_vb FROM loai_van_ban ORDER BY ten_loai_vb");
$loai_van_ban = $stmt_loai_vb->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h2 class="text-primary mb-4">🔍 Tra cứu Văn bản Đến</h2>

    <div class="shadow p-4 mb-4 bg-light rounded">
        <form id="form_search_vb_den" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="so_den_vb" placeholder="Số đến / Số VB">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="nam_vb" placeholder="Năm (Đến/Ban hành)">
            </div>
            
            <div class="col-md-3">
                <input type="text" class="form-control" name="trich_yeu" placeholder="Trích yếu">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="loai_vb_id">
                    <option value="">-- Chọn Loại văn bản --</option>
                    <?php foreach ($loai_van_ban as $loai): ?>
                        <option value="<?= $loai['id'] ?>"><?= htmlspecialchars($loai['ten_loai_vb']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Từ ngày:</label>
                <input type="date" class="form-control" name="date_from">
            </div>
            <div class="col-md-3">
                <label class="form-label">Đến ngày:</label>
                <input type="date" class="form-control" name="date_to">
            </div>
            
            <div class="col-md-3 d-grid align-self-end">
                <button type="submit" class="btn btn-primary">Tìm</button>
            </div>
            <div class="col-md-3 d-grid align-self-end">
                <button type="button" id="btn_export_excel" class="btn btn-success" disabled>
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </button>
            </div>
        </form>
    </div>

    <div id="search_results">
        <h4 class="mb-3">Kết quả (Tổng số: <span id="total_count">0</span>)</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead>
                    <tr class="table-primary">
                        <th>STT</th>
                        <th>Số đến/Năm</th>
                        <th>Số VB/Ngày</th>
                        <th>Loại VB</th>
                        <th>Trích yếu</th>
                        <th>File</th>
                        <th>Chức năng</th>
                    </tr>
                </thead>
                <tbody id="vb_den_list">
                    <tr><td colspan="7" class="text-center">Nhập tiêu chí và bấm "Tìm"</td></tr>
                </tbody>
            </table>
            <nav id="pagination_controls"></nav> 
        </div>
    </div>
</div>

<script>
    // Xử lý Tìm kiếm
    $(document).on('submit', '#form_search_vb_den', function(e) {
        e.preventDefault();
        const formData = $(this).serializeArray();
        let hasSearchTerm = false;

        // Kiểm tra xem có bất kỳ trường nào được nhập không
        formData.forEach(item => {
            if (item.value.trim() !== '' && item.name !== 'loai_vb_id') {
                hasSearchTerm = true;
            }
        });
        
        if ($('[name="loai_vb_id"]').val() !== '') {
            hasSearchTerm = true;
        }

        if (!hasSearchTerm) {
            alert('Chưa nhập nội dung tìm'); // Yêu cầu popup
            return;
        }

        // Gọi AJAX để lấy kết quả (bao gồm cả phân trang)
        // ajaxSearchVB('vb_den', $(this).serialize()); 
        $('#total_count').text('...');
        $('#vb_den_list').html('<tr><td colspan="7" class="text-center">Đang tải dữ liệu...</td></tr>');
        $('#btn_export_excel').prop('disabled', false); 
        alert('Đang thực hiện tìm kiếm...');
    });
    
    // Xử lý Xuất Excel (chỉ cần gọi một AJAX khác)
    $('#btn_export_excel').on('click', function() {
        alert('Đang xuất Excel...');
        // window.location.href = 'modules/export_excel.php?type=vb_den&' + $('#form_search_vb_den').serialize();
    });
</script>