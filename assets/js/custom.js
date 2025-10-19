// assets/js/custom.js
// Lệnh báo lỗi này CHỈ CHẠY nếu custom.js đã được tải thành công
console.log("Custom JS Loaded."); 

// ----------------------------------------------------------------------
// CÁC HÀM CHUNG
// ----------------------------------------------------------------------

// Hàm hiển thị thông báo chung (Success/Error)
function showNotification(message, type = 'success') {
    // Xóa thông báo cũ (nếu có)
    $('.system-notification-alert').remove(); 
    
    const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show system-notification-alert" role="alert" style="position: sticky; top: 0; z-index: 1050; margin: 15px;">
                          ${message}
                          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
    // Thêm vào ngay bên trong #content-wrapper
    $('#content-wrapper').prepend(alertHtml); 
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        $('.system-notification-alert').alert('close');
    }, 5000);
}

// Hàm tải nội dung module bằng AJAX
function loadContent(pageName, params = {}) {
    const mainContent = $('#main-content');
    const queryString = new URLSearchParams(params).toString();
    const url = `modules/${pageName}.php?${queryString}`;

    mainContent.html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i> Đang tải...</div>');
    
    // Cập nhật active link
    $('#sidebar a').removeClass('active');
    // Lưu ý: Thêm logic để xử lý active cho các menu cha/con nếu cần
    $(`[data-page="${pageName}"]`).addClass('active');

    $.ajax({
        url: url,
        type: 'GET',
        success: function(data) {
            mainContent.html(data);
            // Sau khi tải nội dung mới, cần khởi tạo lại Select2 và TinyMCE
            initializePlugins();
        },
        error: function(xhr, status, error) {
            mainContent.html(`<div class="alert alert-danger">Lỗi tải: ${error}. Kiểm tra file modules/${pageName}.php</div>`);
            console.error("AJAX Error loading content:", status, error);
        }
    });
}

// Hàm khởi tạo Plugins (Select2, TinyMCE)
function initializePlugins() {
    // Khởi tạo Select2
    $('.select2').select2({
        theme: "bootstrap-5",
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
    });
    
    // Khởi tạo/Re-init TinyMCE
    if (typeof tinymce !== 'undefined') {
        tinymce.remove(); // Xóa editor cũ (nếu có)
        tinymce.init({
            selector: '.wysiwyg-editor', // Selector chung cho các vùng soạn thảo
            plugins: 'lists code link',
            toolbar: 'undo redo | bold italic | bullist numlist | link | code',
            menubar: false,
            height: 250
        });
    }
}


// ----------------------------------------------------------------------
// BẮT ĐẦU KHI DOCUMENT SẴN SÀNG
// ----------------------------------------------------------------------
$(document).ready(function() {
    console.log("jQuery Ready."); 
    
    // 1. Logic tải module khi click Sidebar (Sử dụng data-page)
    $(document).on('click', '[data-page]', function(e) {
        e.preventDefault(); 
        
        const page = $(this).data('page');
        console.log("Sidebar click detected: " + page); 
        
        loadContent(page);
    });
    
    // 2. Xử lý sự kiện click menu con trong dropdown (Hồ sơ)
    $(document).on('click', '[onclick*="loadContent"]', function(e) {
        // Hàm loadContent đã được gọi inline, không cần e.preventDefault()
    });

    // 3. Xử lý Form VB Đi/VB Đến (Đã được hợp nhất và tối ưu)
    $(document).on('submit', '#form_vb_di, #form_vb_den', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formId = form.attr('id');
        
        // Cần lấy nội dung TinyMCE nếu có
        if (form.find('.wysiwyg-editor').length) {
            form.find('.wysiwyg-editor').each(function() {
                const id = $(this).attr('id');
                // Gán lại giá trị cho textarea/input trước khi gửi
                $('#' + id).val(tinymce.get(id).getContent()); 
            });
        }
        
        // Thao tác gửi form
        submitBtn.prop('disabled', true).text('Đang Lưu...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                showNotification(response.message, response.success ? 'success' : 'danger');
                if (response.success) {
                    // Tải lại trang nhập liệu sau khi lưu
                    const reloadModule = (formId === 'form_vb_di') ? 'van_ban_di_nhap' : 'van_ban_den_nhap';
                    loadContent(reloadModule); 
                }
            },
            error: function(xhr) {
                let msg = "Đã xảy ra lỗi trong quá trình xử lý.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showNotification(msg, 'danger');
                submitBtn.prop('disabled', false).text(formId === 'form_vb_di' ? 'Lưu & Cấp Số' : 'Lưu Văn bản');
            }
        });
    });

    // 4. Xử lý Form Thêm/Sửa Người dùng (Modal)
    $(document).on('submit', '#form_user_crud', function(e) {
        // ... (Logic đã có, giữ nguyên) ...
    });
    
    // 5. Xử lý Form Cài đặt chung và Loại VB
    $(document).on('submit', '#form_system_settings, #form_lvb_crud', function(e) {
        e.preventDefault();
        
        // Lấy nội dung TinyMCE trước khi gửi (áp dụng cho form Loại VB)
        if ($(this).attr('id') === 'form_lvb_crud') {
             // Sử dụng cách cập nhật giá trị trước khi serialize
             $('#modal_goi_y_trich_yeu').val(tinymce.get('modal_goi_y_trich_yeu').getContent());
             $('#modal_goi_y_xu_ly').val(tinymce.get('modal_goi_y_xu_ly').getContent());
        }

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                showNotification(response.message, response.success ? 'success' : 'danger');
                if (response.success) {
                    $('#lvbModal').modal('hide'); 
                    loadContent('cai_dat_he_thong'); 
                }
            },
            error: function(xhr) {
                 // ... (Xử lý lỗi)
            }
        });
    });
    
    // 6. Xử lý Form Thông tin Cá nhân
    $(document).on('submit', '#form_update_info, #form_update_password', function(e) {
        // ... (Logic đã có, giữ nguyên) ...
    });
    
    // 7. Xử lý Form Chuyển tiếp Văn bản
    $(document).on('submit', '#form_forward_vb', function(e) {
        // ... (Logic đã có, giữ nguyên) ...
    });
    
    // 8. Khởi tạo plugin ngay sau khi trang tĩnh load (và sau khi AJAX load)
    initializePlugins();
});