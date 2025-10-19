tạo phần mềm quản lý điều hành văn bản bằng php và sql.


cơ sở dữ liệu gồm có các bảng:

- văn bản đến: id, số đến, năm đến, số văn bản, ngày tháng, loại văn bản (select), trích yếu, đề xuất xử lý, đính kèm file.
- văn bản đi: id, số, ngày tháng, loại văn bản (select), trích yếu, trả lời cho văn bản đến (lấy từ bảng văn bản đến), đính kèm file.
- tin nhắn trao đổi giữa các user: id, van_ban_den_id, nguoi_gui_id, nguoi_nhan_id, noi_dung, file_dinh_kem_reply, trang_thai, thoi_gian_gui, thoi_gian_doc
- Loại văn bản: id, tên loại văn bản, tên viết tắt loại văn bản, gợi ý trích yếu, gợi ý xử lý.
- Sổ theo dõi số văn bản đi: id, so_den, nam_den, so_van_ban, ngay_thang_vb, noi_ban_hanh, loai_vb_id, trich_yeu, de_xuat_xu_ly, file_dinh_kem.
- Sổ theo dõi văn bản đi: id, so_di, ngay_thang_ban_hanh, loai_vb_id, trich_yeu, tra_loi_vb_den_id, file_dinh_kem, nguoi_nhap_id, thoi_gian_nhap
- Theo dõi lịch sử hoạt động của toàn thành viên.
- cai_dat_he_thong: key, value
- người dùng: tên đăng nhập, tên đầy đủ, mật khẩu, phòng ban (quản trị, quản lý, lãnh đạo, văn thư, thành viên), quyền truy cập các tab sidebar, lần đăng nhập sai mật khẩu.







Phần mềm ngôn ngữ php, tông màu xanh dương sáng và trắng, giao diện responsive đẹp, gồm có các phần index, login, logout, header, footer, slidebar, dashboard. 
Trong đó:
- index là trang chính hiển thị đổ các trang dashboard, hộp thư, văn bản đến, văn bản đi,.. vào phần main, không nhảy trang khác.
- sidebar hiển thị bên trái, bên phải là main.
Khi vào index.php phải đăng nhập. sau khi đăng nhập thành công thì index tải dashboard.
textbox sử dụng wysiwyg bản full chức năng và sử dụng được offlien của timyMCE.

1. Sidebar gồm các tab hiển thị trên xuống theo thứ tự: 
-Hộp thư
-Văn bản đến
-Văn bản đi
- Giao nhiệm vụ.
- Nhiệm vụ.
-Tra cứu văn bản đến
-Tra cứu văn bản đi
-Thông tin cá nhân: Cài đặt, sửa tên, mật khẩu.
- Lịch sử hoạt động.
- Cài đặt thông tin hệ thống.
- Quản lý người dùng.
- Phân quyền.
- Sao lưu.
- Cài đặt hệ thống.
Trong đó Hiển thị theo Phân quyền nhóm người dùng của admin.


2. Trang login: có nút ẩn hiện password, giới hạn nhập sai 05 lần, hiển thị số lần sai/tổng số lần. Nếu quá 05 lần thì 10 phút sau mới được đăng nhập tiếp.

3. Trang Cài đặt hệ thống (admin)
- Cài đặt Logo, tên phần mềm trên Header.
- Cài đặt năm mặc định của văn bản đến.
- Cài đặt copyright ở footer.
- Thêm, sửa, xoá Bảng loại văn bản.
- Cài đặt đường dẫn mặc định lưu file: Văn bản đên, văn bản đi, văn bản đính kèm tin nhắn trả lời.
- Cài đặt cú pháp lưu tên file: Văn bản đến, văn bản đi, văn bản đính kèm tin nhắn.
Cú pháp mặc định văn bản đến
Nhập Số đến 666, năm đến 2024, Tờ trình số 123 ngày 02/01/2025 có trích yếu "Tờ trình đại hội". 
Khi gửi kềm file thì file gửi kèm được đổi tên thành: 2024_666_TT123_020125_ToTrinhDaiHoi (Viết hoa chữ đầu từ từ trích yếu)

Cú pháp mặc định văn bản đi
Nhập Tờ trình số 123 ngày 02/01/2025 có trích yếu "Tờ trình đại hội". 
Khi gửi kềm file thì file gửi kèm được đổi tên thành: TT123_020125_ToTrinhDaiHoi (Viết hoa chữ đầu từ từ trích yếu)

Cú pháp mặc định văn bản đính kèm tin nhắn: {yyyy}_{mm}_{dd}_id người gửi_id người nhận_hh_mm_ss_
- Cài đặt định dạng file đính kèm được upload, dung lượng tối đa.

4. Quản lý người dùng: Hiển thị danh sách người dùng, có thể lọc theo chức danh, tìm kiếm tên đầy đủ và tên người dùng. 
Có thể Thêm, sửa, xoá phòng ban (nhóm người dùng), người dùng.


5. Phân quyền (admin):
Cài đặt chức năng hiển thị sidebar của nhóm người dùng để sử dụng (phân quyền) theo dạng checkbox nhiều lựa chọn:
- Nhập văn bản đến: Mở nhập mới, Cho phép sửa của chính người nhập, cho phép sửa toàn bộ, cho phép xoá, cho phép trình lãnh đạo, cho phép in đề xuất, cho phép gửi tin nhắn.
- Nhập văn bản đi. Mở nhập mới, Cho phép sửa của chính người nhập, cho phép sửa toàn bộ, cho phép xoá, cho phép in record, cho phép gửi tin nhắn.
- Tra cứu văn bản đến. Cho phép truy cập, cho phép xem file đính kèm, cho phép sao lưu excel.
- Tra cứu văn bản đi. Cho phép truy cập, cho phép xem file đính kèm, cho phép sao lưu excel.
- Quản lý người dùng: Cho phép truy cập, cho phép thêm, sửa, xoá nhóm thành viên, thành viên.
- Giao nhiệm vụ: Cho phép truy cập.
- Nhiệm vụ: Cho phép truy cập.
- Lịch sử hoạt động: Cho phép truy cập.

6. Sao lưu (admin):
- Sao lưu thành viên: Xuất Excell
- Sao lưu loại văn bản:Xuất Excell
- Sao lưu danh sách văn bản đến: Toàn bộ hoặc theo từng loại văn bản, theo mốc thời gian: Xuất Excell.
- Sao lưu văn bản đi: Toàn bộ hoặc theo từng loại văn bản, theo mốc thời gian: Xuất Excell.
- Sao lưu toàn bộ đatabase, nén zip.
- Sao lưu toàn bộ code phần mềm, nén zip.

7. trang nhập văn bản đến:

nhập số đến cùng hàng năm đến. Bắt buộc nhập. Yêu cầu chỉ nhập số, năm đến đổ dữ liệu từ năm hiện hành của admin, có thể chỉnh sửa.
loại văn bản select từ cơ sở dữ liệu, bắt buộc nhập
ngày tháng ban hành văn bản có lựa chọn ngày trực quan, bắt buộc nhập.
ô trích yếu đổ placeholder 'gợi ý trích yếu' của loại văn bản khi được lựa chọn, không đổ dữ liệu trực tiếp. bắt buộc nhập.
ô đề xuất xử lý đổ nội dụng 'gợi ý xử lý' của loại văn bản khi được lựa chọn và có thể sửa. Bắt buộc nhập
File đính kèm: hình ảnh, tài liệu. Kích thước và đường dẫn lưu trữ lấy tài cài đặt hệ thống.
Khi bấm Lưu thì Các ô nhập liệu (trừ upload file), nếu bỏ trống thì hiện chữ màu đỏ bên dưới "Bắt buộc nhập".
Nếu có file đính kèm thì đổi tên file v.à lưu vào đường dẫn do admin cài đặt.

Bên dưới là 10 văn bản nhập gần đây nhất hiển thị dạng bảng. 
Nếu văn bản do đúng tên người nhập nhập vào thì File đính kèm có có nút Sửa, Xoá, Trình Lãnh đạo, In đề xuất xử lý.
Nút Sửa, xoá reload lại trang.
Nút Gửi có thể gửi cho nhiều người và ghi nhận thời gian gửi.
Nút In đề xuất xử lý : sử dụng PHPword đổ biến vào file xulyvanbanden.docx.

8. trang nhập văn bản đi: 
loại văn bản select từ cơ sở dữ liệu.
ngày tháng có lựa chọn ngày trực quan.
ô trích yếu đổ nội dụng 'gợi ý trích yếu' của loại văn bản khi được lựa chọn và có thể sửa.
Van bản nguồn: Tìm kiếm và chọn thông tin từ bảng văn bản đến.
Khi bấm Lưu thì Các ô nhập liệu (trừ upload file) phải yêu cầu không bỏ trống, nếu bỏ trống thì hiện chữ màu đỏ bên dưới "Bắt buộc nhập".
Nếu có file đính kèm thì đổi tên file và lưu vào vị trí do admin cài đặt.
Bên dưới là 10 văn bản nhập gần đây nhất hiển thị dạng bảng. 
Nếu văn bản do đúng tên người nhập nhập vào thì File đính kèm có có nút Thêm, Xoá. 
Có các nút chức năng "Sửa", "Xoá", "Gửi", "In" record do chính người nhập vào, admin toàn quyền.
Nút Sửa, xoá reload lại trang.
Nút Gửi có thể gửi cho nhiều người và ghi nhận thời gian gửi. Tiêu đề mặc định tin nhắn: Văn bản đi số, loại, ngày, người gửi, thời gian gửi và nội dung nhắn tin người gửi
Nút In ở văn bản đi: sử dụng PHPword đổ biến vào file xulyvanbandi.docx.



9. Hộp thư đến: Tiêu đề "Văn bản đã nhận", có hiển thị số văn bản chưa đọc. 
Bên dưới là thanh tìm kiếm theo: số đến, năm đếm, số văn bản, năm ban hành văn bản, trích yếu (tìm cụm từ có trong trích yếu), kết quả đổ về là các văn bản được gửi cho người dùng đó.
Tiếp theo bên dưới Mặc định hiển thị các văn bản và tin nhắn gửi kèm được nhóm lại theo tin nhắn đầu tiên gửi đến, thời gian gửi cho người đó theo dạng bảng. Chia làm 2 phần:
- Văn bản đến chưa đọc thì hiển thị màu nền trắng và trên cùng theo thứ tự thời gian.
- Văn bản đã đọc hiển thị nền xám. Có nút để Người nhận có thể nhắn tin và gửi file trả lời cho người gửi (có hiển thị các đuôi file cho phép). Nếu gửi thành công thì ghi nhận thời gian gửi, nội dung, file gửi vào cơ sở dữ liệu.
Khi nhập nội dung và bấm "Tìm" thì két quả trả về tải lại trang có kết quả khớp nội dung và gửi cho người đó. nếu chưa nhập nội dung và bấm "Tìm" sẽ hiện popup "Chưa nhập nội dung tìm".

10. Hộp thư của người gửi: Tiêu đề "Văn bản đã gửi". 
Bên dưới là thanh tìm kiếm theo: số đến và năm đếm hoặc số văn bản và năm ban hành hoặc trích yếu văn bản và năm ban hành. 
Bên dưới là danh sách văn bản đã gửi sẽ được hiện đầy đủ các nội dung nhập vào của văn bản kèm tin nhắn của người gửi theo bảng. Trong đó có kèm: thời gian gửi (hiển thị theo giờ phút, ngày tháng năm), danh sách người nhận đã bấm xem văn bản, danh sách người nhận chưa xem, tên và tin nhắn,thời gian trả lời và file người nhận trả lời. Khi có file và tin nhắn trả lời mới, chưa đọc thì báo dấu đỏ đầu văn bản và hiển thị trên cùng.
Khi nhập nội dung và bấm "Tìm" thì két quả trả về tải lại trang là record văn bản và tin nhắn, file kèm theo có kết quả khớp nội dung và do người đó gửi đi. nếu chưa nhập nội dung và bấm "Tìm" sẽ hiện popup "Chưa nhập nội dung tìm".



11. Tra cứu văn bản đến:
Thêm chức năng tìm kiếm dưới tiêu đề quản lý văn bản đến.
Tìm kiếm theo các dạng: số đến, năm đếm, số văn bản, năm ban hành văn bản, trích yếu (tìm cụm từ có trong trích yếu)
Bên dưới mặc định là hiển thị tổng số văn bản, và các record văn bản đến kèm nút "Sửa", "Xoá", "In" theo dạng bảng.
Khi bấm nút "Tìm" kiểm tra có nhập dữ liệu tìm chưa, nếu chưa thì thông báo popup "Chưa nhập nội dung tìm".
Nếu có nội dung thì Kết quả trả về Tổng số văn bản tìm thấy, bên dưới là kết quả trả về dạng bảng record được đánh số thứ tự, định dạng thời gian: giờ phút, ngày tháng năm khi hiển thị, thêm nút "Sửa", "Xoá", "Gửi", "In". Phân trang 20 record/trang.
Có nút "Xuất Excell" để xuất dữ liệu tìm kiếm trả về (bao gồm cột số thứ tự). 

12. Tra cứu văn bản đi:
Thêm chức năng tìm kiếm dưới tiêu đề quản lý văn bản đi.
Tìm kiếm theo các dạng: số văn bản, năm ban hành, Loại văn bản, cụm từ có trong trích yếu, theo mốc thời gian từ ngày tháng năm đến ngày tháng năm.
Bên dưới mặc định là hiển thị tổng số văn bản, và các record văn bản đến kèm nút "Sửa", "Xoá", "In" theo dạng bảng.
Khi bấm nút "Tìm" kiểm tra có nhập dữ liệu tìm chưa, nếu chưa thì thông báo popup "Chưa nhập nội dung tìm".
Nếu có nội dung thì Kết quả trả về Tổng số văn bản tìm thấy, bên dưới là kết quả trả về dạng bảng record được đánh số thứ tự, định dạng thời gian: giờ phút, ngày tháng năm khi hiển thị, thêm nút "Sửa", "Xoá", "Gửi", "In". Phân trang 20 record/trang.
Có nút "Xuất Excell" để xuất dữ liệu tìm kiếm trả về (bao gồm cột số thứ tự). 

13. Lịch sử hoạt động: Liệt kê log toàn bộ thành viên thực hiện các thao tác đến csdl, gồm thời thực hiện, thao tác. Có thể lọc theo tên thành viên, thời gian lọc từ ngày đến ngày.


