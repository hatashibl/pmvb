<?php
// modules/van_ban_di_tra_cuu.php
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
    <h2 class="text-primary mb-4">🔎 Tra cứu Văn bản Đi</h2>

    <div class="shadow p-4 mb-4 bg-light rounded">
        <form id="form_search_vb_di" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="so_vb" placeholder="Số Văn bản">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="trich_yeu" placeholder="Trích yếu">
            </div>
            <div class="col-md-6">
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
                <button type="button" class="btn btn-success" disabled>
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
                        <th>Số VB/Ngày</th>
                        <th>Loại VB</th>
                        <th>Trích yếu</th>
                        <th>VB Trả lời cho (Số đến)</th>
                        <th>File</th>
                        <th>Chức năng</th>
                    </tr>
                </thead>
                <tbody id="vb_di_list">
                    <tr><td colspan="7" class="text-center">Nhập tiêu chí và bấm "Tìm"</td></tr>
                </tbody>
            </table>
            <nav id="pagination_controls"></nav> 
        </div>
    </div>
</div>

<script>
    // Logic tìm kiếm VB Đi (sử dụng AJAX tới một handler riêng)
    $(document).on('submit', '#form_search_vb_di', function(e) {
        e.preventDefault();
        alert('Đang tìm kiếm VB Đi...');
        // logicSearchVB('vb_di', $(this).serialize()); 
    });
</script>