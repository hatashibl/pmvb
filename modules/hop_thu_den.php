<?php
// modules/hop_thu_den.php - Module Hộp thư Đến (Đã sửa lỗi đường dẫn file và cú pháp PHP)

require_once '../core/auth.php';
check_login();

global $pdo;
// SỬA CÚ PHÁP: Thay ?? bằng isset() cho PHP cũ hơn
$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; 
$unread_count = 0; 
$grouped_messages = []; 

try {
    // 1. Lấy COUNT tin nhắn chưa đọc TỔNG (Giữ nguyên)
    $stmt_count = $pdo->prepare("
        SELECT COUNT(*)
        FROM thong_bao_nguoi_nhan
        WHERE nguoi_nhan_id = :user_id AND trang_thai = 'chua_doc'
    ");
    $stmt_count->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt_count->execute();
    $unread_count = $stmt_count->fetchColumn();

    // 2. Lấy TẤT CẢ các tin nhắn liên quan (Giữ nguyên)
    $stmt_all_messages = $pdo->prepare("
        SELECT 
            tn.id, tn.van_ban_den_id, tn.nguoi_gui_id, tn.noi_dung, tn.trang_thai, tn.thoi_gian_gui,
            vb.so_den, vb.nam_den, vb.trich_yeu,
            ng.ten_day_du AS nguoi_gui_ten
        FROM thong_bao_nguoi_nhan tn
        
        LEFT JOIN van_ban_den vb ON tn.van_ban_den_id = vb.id
        LEFT JOIN nguoi_dung ng ON tn.nguoi_gui_id = ng.id
        
        WHERE tn.nguoi_nhan_id = :user_id 
        ORDER BY tn.van_ban_den_id DESC, tn.thoi_gian_gui DESC
    ");
    $stmt_all_messages->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt_all_messages->execute();
    $all_messages = $stmt_all_messages->fetchAll(PDO::FETCH_ASSOC);

    // 3. Xử lý Nhóm (Threading)
    $grouped_messages = [];
    foreach ($all_messages as $message) {
        // SỬA CÚ PHÁP: Thay ?? bằng isset()
        $vb_id = isset($message['van_ban_den_id']) ? $message['van_ban_den_id'] : 0; 
        $group_key = (string)$vb_id;

        if (!isset($grouped_messages[$group_key])) {
            $grouped_messages[$group_key] = [
                'latest_message' => $message, 
                'van_ban_info' => [
                    'id' => $vb_id,
                    // SỬA CÚ PHÁP: Thay ?? bằng isset()
                    'so_den' => isset($message['so_den']) ? $message['so_den'] : 'N/A',
                    'nam_den' => isset($message['nam_den']) ? $message['nam_den'] : 'N/A',
                    'trich_yeu' => isset($message['trich_yeu']) ? $message['trich_yeu'] : 'Tin nhắn độc lập/Không liên quan đến VB',
                ],
                'unread_count' => 0, 
                'total_messages' => 0,
            ];
        }
        
        $grouped_messages[$group_key]['total_messages']++;

        if ($message['trang_thai'] === 'chua_doc') {
            $grouped_messages[$group_key]['unread_count']++;
        }
    }
    
    usort($grouped_messages, function($a, $b) {
        return strtotime($b['latest_message']['thoi_gian_gui']) - strtotime($a['latest_message']['thoi_gian_gui']);
    });

} catch (PDOException $e) {
    error_log("Lỗi CSDL khi tải Hộp thư Đến: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Lỗi CSDL: Không thể tải Hộp thư Đến. Vui lòng kiểm tra log server.</div>";
    $grouped_messages = [];
    $unread_count = 0;
}
?>

<style>
    /* Giới hạn nội dung hiển thị tối đa 3 dòng */
    .message-content-hidden {
        display: -webkit-box;
        -webkit-line-clamp: 3; /* Giới hạn 3 dòng */
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
    }
    
    /* Hiển thị toàn bộ khi có class 'expanded' */
    .message-content-hidden.expanded {
        -webkit-line-clamp: unset;
        overflow: visible;
    }
    
    /* Ẩn nút "Xem thêm" khi nội dung đã hiển thị đầy đủ (thông qua JS) */
    .btn-toggle-content.d-none {
        display: none !important;
    }
</style>
<div class="container-fluid">
    <h2 class="text-primary mb-4">📥 Văn bản đã nhận (Chưa đọc: <?= $unread_count ?>)</h2>

    <div class="shadow p-3 mb-4 bg-light rounded">
        <form id="form_search_inbox" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="keyword" placeholder="Số/Trích yếu VB...">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="nam_ban_hanh" placeholder="Năm ban hành">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="so_den" placeholder="Số đến">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control" name="nam_den" placeholder="Năm đến">
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search me-2"></i> Tìm</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover bg-white">
            <thead>
                <tr class="table-primary">
                    <th>#</th>
                    <th>Trạng thái</th>
                    <th>Người gửi</th>
                    <th>Số VB/Trích yếu</th>
                    <th>Ngày gửi</th>
                    <th>Xem & Trả lời</th>
                </tr>
            </thead>
            <tbody id="inbox_list">
                <?php if (count($grouped_messages) > 0): ?>
                    <?php $stt = 1; ?>
                    <?php foreach ($grouped_messages as $group):
                        $latest = $group['latest_message'];
                        $vb = $group['van_ban_info'];
                        $thread_count = $group['total_messages']; 

                        $vb_id_for_modal = $vb['id'] > 0 ? $vb['id'] : 0; 

                        $group_class = ($group['unread_count'] > 0) ? 'fw-bold bg-light' : '';
                    ?>
                    <tr class="<?= $group_class ?>" data-message-id="<?= $latest['id'] ?>">
                        <td><?= $stt++ ?></td>
                        <td>
                            <?php if ($group['unread_count'] > 0): ?>
                                <span class="badge bg-danger"><?= $group['unread_count'] ?> Chưa đọc</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Đã đọc</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars(isset($latest['nguoi_gui_ten']) ? $latest['nguoi_gui_ten'] : 'N/A') ?>
                        </td>
                        <td>
                            <?php if ($vb['id'] > 0): ?>
                                VB: **<?= htmlspecialchars($vb['so_den']) ?>/<?= htmlspecialchars($vb['nam_den']) ?>**
                                <br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($vb['trich_yeu']) ?>
                                </small>
                            <?php else: ?>
                                <span class="text-danger">**<?= htmlspecialchars($vb['trich_yeu']) ?>**</span>
                                <br><small class="text-muted">Không liên kết VB</small>
                            <?php endif; ?>
                            
                            <?php if ($thread_count > 1): ?>
                                <span class="ms-2 badge bg-dark">có <?= $thread_count ?> tin</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($latest['thoi_gian_gui'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" 
                                onclick="showViewModal(<?= $latest['id'] ?>, <?= $vb_id_for_modal ?>)" 
                                title="Xem chi tiết và chuỗi tin nhắn">
                                <i class="fas fa-comments"></i> Xem
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">
                            <i class="fas fa-envelope-open-text me-2"></i> **Hộp thư đến trống!**
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="viewReplyModal" tabindex="-1" aria-labelledby="viewReplyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewReplyModalLabel"><i class="fas fa-file-alt me-2"></i> Chi Tiết Văn bản Đến và Trả lời</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-info text-white">Thông tin Văn bản</div>
                            <div class="card-body" id="vb_detail_content">
                                Đang tải chi tiết văn bản...
                            </div>
                            <div class="card-footer text-muted d-flex justify-content-between">
                                <span id="nguoi_gui_info"></span>
                                <span id="trang_thai_doc"></span>
                            </div>
                        </div>
                        
                        <div id="message_thread_container" class="mt-4">
                        </div>
                    </div>
                    
                    <div class="col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-header bg-success text-white">Trả lời Người gửi</div>
                            <div class="card-body">
                                <form id="form_reply_van_ban" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="reply_van_ban">
                                    <input type="hidden" name="original_vb_id" id="reply_original_vb_id">
                                    <input type="hidden" name="original_message_id" id="reply_original_message_id">
                                    <input type="hidden" name="recipient_id" id="reply_recipient_id">

                                    <div class="mb-3">
                                        <label for="reply_message" class="form-label">Nội dung Trả lời <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reply_message" name="reply_message" rows="3" required placeholder="Nhập tin nhắn trả lời..."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reply_file" class="form-label">File đính kèm Trả lời (Tùy chọn)</label>
                                        <input class="form-control" type="file" id="reply_file" name="reply_file" accept=".pdf, .docx, .doc, .zip">
                                    </div>

                                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-paper-plane me-2"></i> Gửi Trả lời</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Hàm chuyển đổi ẩn/hiện nội dung tin nhắn (Giữ nguyên)
    function toggleMessageContent(buttonElement) {
        const contentElement = $(buttonElement).prev('.message-content-hidden');
        contentElement.toggleClass('expanded');

        if (contentElement.hasClass('expanded')) {
            $(buttonElement).html('<i class="fas fa-eye-slash"></i> Ẩn bớt');
        } else {
            $(buttonElement).html('<i class="fas fa-eye"></i> Xem thêm');
        }
    }

    /**
     * Hiển thị Modal chi tiết VB và cập nhật trạng thái đã đọc
     */
    function showViewModal(latestMessageId, vanBanId) {
        // ... (Phần Khởi tạo và reset giữ nguyên) ...
        const modalElement = document.getElementById('viewReplyModal');
        const modal = new bootstrap.Modal(modalElement);
        const detailContent = $('#vb_detail_content');
        const threadContainer = $('#message_thread_container');

        // 1. Cập nhật ID cho form trả lời và reset
        $('#reply_original_message_id').val(latestMessageId);
        $('#reply_original_vb_id').val(vanBanId);
        $('#form_reply_van_ban')[0].reset(); 
        $('#form_reply_van_ban button[type="submit"]').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Gửi Trả lời');

        // Tạm thời reset nội dung và hiển thị trạng thái tải
        detailContent.html('<div class="text-center p-5"><span class="spinner-border text-primary"></span> Đang tải dữ liệu và cập nhật trạng thái đã đọc...</div>');
        threadContainer.html('<div class="text-center p-3"><span class="spinner-border spinner-border-sm"></span> Đang tải chuỗi tin nhắn...</div>');
        $('#nguoi_gui_info').text('Đang tải...');
        $('#trang_thai_doc').html('');
        $('#reply_recipient_id').val('');
        
        // 2. Gửi AJAX
        $.ajax({
            url: 'modules/ajax_handler.php?action=get_vb_detail_and_mark_read', 
            method: 'GET',
            data: { message_id: latestMessageId, vb_id: vanBanId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    
                    const vb = response.van_ban;
                    const latest_message = response.latest_message;
                    const nguoiGui = response.nguoi_gui; 
                    const thread = response.thread;
                    
                    if (!latest_message || !nguoiGui) {
                        detailContent.html('<div class="alert alert-warning">Không tìm thấy thông tin tin nhắn hoặc Người gửi.</div>');
                        return;
                    }
                    
                    // Tải chi tiết VB
                    if (vb && vanBanId > 0) {
                        const vbPath = vb.file_dinh_kem ? vb.file_dinh_kem.trim() : null;
                        // SỬA LỖI ĐƯỜNG DẪN 404: Bỏ `../` để đường dẫn file tính từ thư mục gốc của ứng dụng
                        const vbFileLink = vbPath ? 
                            `<a href="${encodeURI(vbPath)}" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i> Tải về</a>` : 'Không có';

                        detailContent.html(`
                            <p><strong>Số/Ngày VB:</strong> ${vb.so_van_ban} ngày ${vb.ngay_thang_vb_display || vb.ngay_thang_vb || 'N/A'}</p>
                            <p><strong>Loại VB:</strong> ${vb.ten_loai_vb || 'N/A'}</p>
                            <p><strong>Nơi ban hành:</strong> ${vb.noi_ban_hanh || 'N/A'}</p>
                            <p><strong>Trích yếu:</strong> ${vb.trich_yeu}</p>
                            <p><strong>Đề xuất xử lý:</strong> ${vb.de_xuat_xu_ly || 'N/A'}</p>
                            <hr>
                            <p><strong>File đính kèm VB:</strong> ${vbFileLink}</p>
                        `);
                    } else {
                        detailContent.html(`
                            <div class="alert alert-info">Tin nhắn độc lập: **${latest_message.noi_dung}**</div>
                            <p class="mt-3 text-muted">Không liên kết với Văn bản Đến cụ thể nào.</p>
                        `);
                    }

                    // ************ Tải Chuỗi Tin nhắn (Threading) ************
                    let threadHtml = '<h6 class="border-bottom pb-2">Chuỗi Trao đổi:</h6>';
                    
                    if (thread && thread.length > 0) {
                        const currentUserId = <?= $current_user_id ?>;
                            thread.forEach((msg, index) => { 
                                const isMe = (msg.nguoi_gui_id == currentUserId); 
                                const cardClass = isMe ? 'bg-light border-success' : (msg.trang_thai === 'chua_doc' ? 'border-primary' : 'border-light');
                                const senderName = isMe ? 'Bạn (Trả lời)' : (msg.nguoi_gui_ten || 'Hệ thống/N/A');
                                
                                const replyPath = msg.file_dinh_kem_reply ? msg.file_dinh_kem_reply.trim() : null;
                                // SỬA LỖI ĐƯỜNG DẪN 404: Bỏ `../`
                                const replyFileLink = replyPath ? 
                                    `<a href="${encodeURI(replyPath)}" target="_blank" class="badge bg-info mt-1"><i class="fas fa-file-download"></i> File Đính kèm</a>` : '';

                                const contentId = `msg-content-${msg.id}-${index}`;
                                
                                threadHtml += `
                                    <div class="card mb-2 ${cardClass} shadow-sm">
                                        <div class="card-body py-2">
                                            <p class="mb-1">
                                                <strong>${senderName}</strong> <small class="text-muted ms-2">${msg.thoi_gian_gui}</small>
                                                ${msg.trang_thai === 'chua_doc' ? '<span class="badge bg-danger ms-2">Chưa đọc</span>' : ''}
                                            </p>
                                            
                                            <p id="${contentId}" class="mb-1 message-content-hidden">${msg.noi_dung}</p>
                                            
                                            <button type="button" 
                                                    class="btn btn-link btn-sm p-0 mt-1 text-decoration-none btn-toggle-content"
                                                    onclick="toggleMessageContent(this)"
                                                    data-content-id="${contentId}">
                                                <i class="fas fa-eye"></i> Xem thêm
                                            </button>
                                            
                                            ${replyFileLink}
                                        </div>
                                    </div>
                                `;
                            });
                    } else {
                        threadHtml += '<p class="text-muted">Không có lịch sử trao đổi.</p>';
                    }
                    threadContainer.html(threadHtml);

                    // LOGIC ẨN/HIỆN (Giữ nguyên)
                    thread.forEach((msg, index) => {
                         const contentElement = document.getElementById(`msg-content-${msg.id}-${index}`);
                         if (contentElement) {
                             if (contentElement.scrollHeight <= contentElement.clientHeight) {
                                 $(contentElement).next('.btn-toggle-content').hide();
                             }
                         }
                    });

                    // ... (Phần cập nhật footer, form và bảng giữ nguyên) ...
                    $('#nguoi_gui_info').html(`<strong>Người gửi/Nhập VB:</strong> ${nguoiGui.ten_day_du}`);
                    
                    const current_trang_thai = latest_message.trang_thai;
                    $('#trang_thai_doc').html(current_trang_thai === 'da_doc' ? '<span class="badge bg-secondary">Đã đọc</span>' : '<span class="badge bg-success">Vừa xem (Đã đọc)</span>');
                    
                    $('#reply_recipient_id').val(latest_message.nguoi_gui_id); 
                    
                    if(response.is_newly_read) {
                        // Cập nhật lại trạng thái dòng trên bảng và tổng số chưa đọc
                        const row = $(`tr[data-message-id="${latestMessageId}"]`);
                        row.removeClass('fw-bold bg-light');
                        
                        const unreadBadge = row.find('td:nth-child(2) .badge');
                        if(unreadBadge.length) {
                            if (unreadBadge.hasClass('bg-danger')) {
                                unreadBadge.removeClass('bg-danger').addClass('bg-secondary').text('Đã đọc');
                            }
                        }

                        const h2Element = $('.container-fluid h2');
                        let currentHtml = h2Element.html();
                        const match = currentHtml.match(/Chưa đọc:\s*(\d+)/); 
                        if (match && match[1]) {
                             const currentCount = parseInt(match[1]);
                             const newCount = Math.max(0, currentCount - (response.messages_marked_read || 0)); 
                             const newHtml = currentHtml.replace(`Chưa đọc: ${currentCount}`, `Chưa đọc: ${newCount}`);
                             h2Element.html(newHtml);
                        }
                    }

                    modal.show();
                } else {
                    alert('Lỗi tải chi tiết: ' + (response.message || 'Không rõ lỗi'));
                    detailContent.html('<div class="alert alert-danger">Lỗi tải chi tiết: ' + (response.message || 'Không rõ lỗi') + '</div>');
                    threadContainer.empty();
                }
            },
            error: function(xhr) {
                // Sửa lỗi hiển thị
                alert('Lỗi kết nối khi tải chi tiết văn bản: ' + (xhr.responseText ? xhr.responseText.substring(0, 200) + '...' : xhr.statusText));
                detailContent.html('<div class="alert alert-danger">Không thể kết nối đến máy chủ. Vui lòng kiểm tra console/log server.</div>');
                threadContainer.empty();
            }
        });
    }

    // Xử lý Gửi Trả lời (Giữ nguyên)
    $(document).ready(function() {
        $('#form_reply_van_ban').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');

            if ($('#reply_message').val().trim() === '') {
                alert('Vui lòng nhập nội dung trả lời.');
                return;
            }

            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Đang gửi...');

            $.ajax({
                url: 'modules/ajax_handler.php',
                method: 'POST',
                data: new FormData(this), // Gửi kèm file
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Trả lời thành công! Người gửi đã nhận được tin nhắn trả lời của bạn.');
                        window.location.reload(true); 
                    } else {
                        alert('Lỗi: ' + response.message);
                    }
                    submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Gửi Trả lời');
                },
                error: function(xhr) {
                    alert('Lỗi kết nối khi gửi trả lời: ' + (xhr.responseText ? xhr.responseText.substring(0, 200) + '...' : xhr.statusText));
                    submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Gửi Trả lời');
                }
            });
        });
    });
</script>