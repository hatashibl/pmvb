-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 19, 2025 lúc 06:33 AM
-- Phiên bản máy phục vụ: 10.4.27-MariaDB
-- Phiên bản PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `qlvb_project`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cai_dat_he_thong`
--

CREATE TABLE `cai_dat_he_thong` (
  `key` varchar(50) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cai_dat_he_thong`
--

INSERT INTO `cai_dat_he_thong` (`key`, `value`) VALUES
('copyright', 'Bản quyền &copy; 2024'),
('nam_hien_hanh', '2025'),
('ten_phan_mem', 'PHẦN MỀM QUẢN LÝ VĂN BẢN');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loai_van_ban`
--

CREATE TABLE `loai_van_ban` (
  `id` int(11) NOT NULL,
  `ten_loai_vb` varchar(100) NOT NULL,
  `ky_hieu_ngan` varchar(10) NOT NULL,
  `goi_y_trich_yeu` text DEFAULT NULL,
  `goi_y_xu_ly` text DEFAULT NULL,
  `so_cuoi_vb_di` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `loai_van_ban`
--

INSERT INTO `loai_van_ban` (`id`, `ten_loai_vb`, `ky_hieu_ngan`, `goi_y_trich_yeu`, `goi_y_xu_ly`, `so_cuoi_vb_di`) VALUES
(1, 'Tờ trình', 'TT', 'Trình bày về việc...', 'Xin ý kiến Lãnh đạo', 0),
(2, 'Quyết định', 'QD', 'Ban hành quy chế...', 'Thực hiện theo quy định', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `ten_day_du` varchar(100) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `chuc_vu` enum('admin','quan_ly','thanh_vien') NOT NULL DEFAULT 'thanh_vien',
  `lan_sai_hien_tai` tinyint(1) NOT NULL DEFAULT 0,
  `thoi_gian_khoa` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ten_dang_nhap`, `ten_day_du`, `mat_khau`, `chuc_vu`, `lan_sai_hien_tai`, `thoi_gian_khoa`) VALUES
(2, 'admin', 'Quản trị Hệ thống', '$2y$10$fFpNHpgx3PfrX9et5BKDXu0hYGx2sAsO8SRrp1zqw1Hn6V5wVm2tm', 'admin', 0, NULL),
(3, 'dtd2', 'Đội trưởng Đ2', '$2y$10$H.NEuuymwc7Pjr781C7i2.nHdGPOViwbhoFEaIkrs1h/0.pvlmewi', 'thanh_vien', 0, NULL),
(4, 'dtd3', 'Đội trưởng Đ3', '$2y$10$XQ7UgTE0HfDsbaheT5a8fu2u39e9YNn4f06k148KiWQdwmfwRMqv6', 'thanh_vien', 0, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao_nguoi_nhan`
--

CREATE TABLE `thong_bao_nguoi_nhan` (
  `id` int(11) NOT NULL,
  `van_ban_den_id` int(11) NOT NULL COMMENT 'ID của Văn bản Đến được gửi',
  `nguoi_gui_id` int(11) NOT NULL COMMENT 'ID của người dùng gửi văn bản',
  `nguoi_nhan_id` int(11) NOT NULL COMMENT 'ID của người nhận tin nhắn',
  `noi_dung` text NOT NULL COMMENT 'Nội dung tin nhắn/thông báo kèm theo',
  `file_dinh_kem_reply` varchar(255) DEFAULT NULL,
  `trang_thai` enum('chua_doc','da_doc') NOT NULL DEFAULT 'chua_doc' COMMENT 'Trạng thái tin nhắn',
  `thoi_gian_gui` datetime NOT NULL COMMENT 'Thời gian gửi tin nhắn',
  `thoi_gian_doc` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu trữ thông báo/tin nhắn chuyển văn bản';

--
-- Đang đổ dữ liệu cho bảng `thong_bao_nguoi_nhan`
--

INSERT INTO `thong_bao_nguoi_nhan` (`id`, `van_ban_den_id`, `nguoi_gui_id`, `nguoi_nhan_id`, `noi_dung`, `file_dinh_kem_reply`, `trang_thai`, `thoi_gian_gui`, `thoi_gian_doc`) VALUES
(1, 16, 2, 3, 'Văn bản số đến **2322525/2025** (Trích yếu: sgsdgs) đã được chuyển cho bạn xem xét.', NULL, 'da_doc', '2025-10-18 16:45:03', NULL),
(2, 16, 2, 4, 'Văn bản số đến **2322525/2025** (Trích yếu: sgsdgs) đã được chuyển cho bạn xem xét.', NULL, 'chua_doc', '2025-10-18 16:45:03', NULL),
(3, 16, 3, 2, 'Văn bản [2322525/2025] đã được Quản trị Hệ thống xem vào lúc 17:16:52 18/10/2025', NULL, 'da_doc', '2025-10-18 22:16:52', NULL),
(4, 16, 2, 3, 'Văn bản [2322525/2025] đã được Đội trưởng Đ2 xem vào lúc 17:19:20 18/10/2025', NULL, 'da_doc', '2025-10-18 22:19:20', NULL),
(5, 8, 2, 3, 'xem thử xem (Kèm VB: 546/2025)', NULL, 'da_doc', '2025-10-18 17:19:48', NULL),
(6, 16, 3, 2, 'Văn bản [2322525/2025] đã được Quản trị Hệ thống xem vào lúc 17:20:16 18/10/2025', NULL, 'da_doc', '2025-10-18 22:20:16', '2025-10-19 00:17:07'),
(7, 8, 3, 2, 'Văn bản [546/2025] đã được Quản trị Hệ thống xem vào lúc 17:20:39 18/10/2025', NULL, 'da_doc', '2025-10-18 22:20:39', NULL),
(8, 16, 3, 2, 'xem ok chưa', NULL, 'da_doc', '2025-10-18 22:45:32', NULL),
(9, 16, 2, 3, 'Văn bản [2322525/2025] đã được Đội trưởng Đ2 xem vào lúc 17:45:47 18/10/2025', NULL, 'da_doc', '2025-10-18 22:45:47', '2025-10-19 08:05:27'),
(10, 8, 2, 3, 'Văn bản [546/2025] đã được Đội trưởng Đ2 xem vào lúc 17:45:53 18/10/2025', NULL, 'da_doc', '2025-10-18 22:45:53', '2025-10-19 08:06:00'),
(11, 16, 2, 3, 'đồng ý', NULL, 'da_doc', '2025-10-19 08:05:09', '2025-10-19 08:05:27'),
(12, 8, 3, 2, 'chưa thấy tin nhắn đến', NULL, 'da_doc', '2025-10-19 08:06:52', '2025-10-19 08:07:15'),
(13, 8, 2, 3, 'xem kỹ file đính kèm kèm theo', NULL, 'da_doc', '2025-10-19 08:07:41', '2025-10-19 08:07:56'),
(14, 16, 3, 2, 'xem file đính kèm', 'van_ban_tra_loi/2025/reply_16_3_2_1760836857.docx', 'da_doc', '2025-10-19 08:20:57', '2025-10-19 08:21:07'),
(15, 16, 2, 3, 'trả lời gấp', NULL, 'da_doc', '2025-10-19 08:58:11', '2025-10-19 08:58:22'),
(16, 16, 2, 3, 'sfsfsgg', NULL, 'da_doc', '2025-10-19 09:48:44', '2025-10-19 09:52:59'),
(17, 16, 2, 3, 'xem', 'van_ban_tra_loi/2025/reply_16_2_3_1760842267.docx', 'da_doc', '2025-10-19 09:51:07', '2025-10-19 09:52:59'),
(18, 16, 2, 3, 'xem d c chu', NULL, 'da_doc', '2025-10-19 11:02:50', '2025-10-19 11:03:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `van_ban_den`
--

CREATE TABLE `van_ban_den` (
  `id` int(11) NOT NULL,
  `so_den` int(11) NOT NULL,
  `nam_den` year(4) NOT NULL,
  `so_van_ban` varchar(50) DEFAULT NULL,
  `ngay_thang_vb` date NOT NULL,
  `noi_ban_hanh` varchar(255) DEFAULT NULL,
  `loai_vb_id` int(11) NOT NULL,
  `trich_yeu` text NOT NULL,
  `de_xuat_xu_ly` text DEFAULT NULL,
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `nguoi_nhap_id` int(11) NOT NULL,
  `thoi_gian_nhap` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `van_ban_den`
--

INSERT INTO `van_ban_den` (`id`, `so_den`, `nam_den`, `so_van_ban`, `ngay_thang_vb`, `noi_ban_hanh`, `loai_vb_id`, `trich_yeu`, `de_xuat_xu_ly`, `file_dinh_kem`, `nguoi_nhap_id`, `thoi_gian_nhap`) VALUES
(1, 223, 2025, NULL, '2025-01-22', 'ca', 2, 'Ban hành quy chế...', 'Thực hiện theo quy định', NULL, 2, '2025-10-18 13:52:34'),
(2, 32, 2025, NULL, '2025-01-22', 'ad', 2, 'Ban hành quy chế...', 'Thực hiện theo quy định', NULL, 2, '2025-10-18 13:54:46'),
(3, 123, 2025, NULL, '2024-10-22', 'vvwwf', 1, 'Trình bày về việc...', 'Xin ý kiến Lãnh đạo', NULL, 2, '2025-10-18 13:55:01'),
(4, 123, 2025, 'sưe', '2025-02-22', 'px03', 1, 'lộ trình thi đtv', '- xuất hiện đi e', NULL, 2, '2025-10-18 14:09:19'),
(5, 1234, 2025, 'q31', '2025-12-22', 'ad', 1, 'xem thử', 'ok', NULL, 2, '2025-10-18 14:18:08'),
(6, 23, 2025, 'ewr', '2025-01-22', 'px03', 2, 'quy chế làm việc', 'sdfdsfsd', 'assets/files/van_ban_den/vbden_68f385ec69188.docx', 2, '2025-10-18 14:19:20'),
(7, 2334, 2025, '232', '2024-04-23', 'xc', 2, 'Xu ly hinh su', 'Thực hiện theo quy định về xử lý hình sự đối tượng nguy hiểm cho người', 'Van_ban_den_file/2025/2025_2334_QD232_23042024_xu-ly-hinh-su.docx', 3, '2025-10-18 15:00:07'),
(8, 546, 2025, '866', '2025-01-22', 'ad', 2, 'ok', 'Thực hiện theo quy định', 'Van_ban_den_file/2025/2025_546_QD866_22012025_ok.docx', 2, '2025-10-18 15:08:53'),
(10, 22345, 2025, '212511', '2025-11-22', 'xpsqe', 2, 'sad é', 'Thực hiện theo quy định', NULL, 2, '2025-10-18 15:18:59'),
(11, 2345, 2025, '24252', '2025-02-22', 'vvwwf', 1, '1e141', 'Xin ý kiến Lãnh đạo', NULL, 2, '2025-10-18 15:20:29'),
(12, 233463, 2025, '342525', '2025-02-22', 'px03', 2, '2114r2rerew', 'Thực hiện theo quy định', NULL, 2, '2025-10-18 15:25:19'),
(14, 232144223, 2025, '212341', '2025-02-22', '2213', 2, 'rtetetet', 'Thực hiện theo quy định', NULL, 2, '2025-10-18 15:30:58'),
(15, 2324, 2025, '2324', '2025-01-22', 'sfeffs', 2, 'qqeq', 'Thực hiện theo quy định', NULL, 2, '2025-10-18 15:37:19'),
(16, 2322525, 2025, '23425', '2025-01-22', 'wegsg', 2, 'sgsdgs', 'Thực hiện theo quy định', NULL, 2, '2025-10-18 15:42:15'),
(17, 131239999, 2025, '124214', '2025-02-22', 'dffsfs', 2, 'qeafaf', 'Thực hiện theo qusfsfy định', NULL, 2, '2025-10-18 15:43:33'),
(18, 233, 2025, '3243', '2025-02-22', 'rgerge', 2, 'wwfwfw', 'Thực hiện theo quy định', NULL, 2, '2025-10-19 11:02:22'),
(19, 233, 2025, '3243', '2025-02-22', 'rgerge', 2, 'wwfwfw', 'Thực hiện theo quy định', NULL, 2, '2025-10-19 11:02:22');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `van_ban_di`
--

CREATE TABLE `van_ban_di` (
  `id` int(11) NOT NULL,
  `so` varchar(50) NOT NULL,
  `ngay_thang` date NOT NULL,
  `loai_vb_id` int(11) NOT NULL,
  `trich_yeu` text NOT NULL,
  `tra_loi_vb_den_id` int(11) DEFAULT NULL,
  `file_dinh_kem` varchar(255) DEFAULT NULL,
  `nguoi_nhap_id` int(11) NOT NULL,
  `thoi_gian_nhap` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `van_ban_di`
--

INSERT INTO `van_ban_di` (`id`, `so`, `ngay_thang`, `loai_vb_id`, `trich_yeu`, `tra_loi_vb_den_id`, `file_dinh_kem`, `nguoi_nhap_id`, `thoi_gian_nhap`) VALUES
(1, '1', '2025-10-17', 2, '<p>Ban h&agrave;nh quy chế.. v&egrave; học tập</p>', NULL, NULL, 2, '2025-10-17 11:18:20');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `van_ban_phan_cong`
--

CREATE TABLE `van_ban_phan_cong` (
  `id` int(11) NOT NULL,
  `vb_id` int(11) NOT NULL,
  `nguoi_gui_id` int(11) NOT NULL,
  `nguoi_nhan_id` int(11) NOT NULL,
  `noi_dung` text DEFAULT NULL,
  `thoi_gian_phan_cong` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `van_ban_phan_cong`
--

INSERT INTO `van_ban_phan_cong` (`id`, `vb_id`, `nguoi_gui_id`, `nguoi_nhan_id`, `noi_dung`, `thoi_gian_phan_cong`) VALUES
(1, 4, 2, 3, '', '2025-10-18 19:15:18'),
(2, 4, 2, 4, '', '2025-10-18 19:15:18'),
(3, 6, 2, 4, 'giao thực hiện ngay', '2025-10-18 19:20:20');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cai_dat_he_thong`
--
ALTER TABLE `cai_dat_he_thong`
  ADD PRIMARY KEY (`key`);

--
-- Chỉ mục cho bảng `loai_van_ban`
--
ALTER TABLE `loai_van_ban`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_viet_tat` (`ky_hieu_ngan`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`);

--
-- Chỉ mục cho bảng `thong_bao_nguoi_nhan`
--
ALTER TABLE `thong_bao_nguoi_nhan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tn_van_ban_den` (`van_ban_den_id`),
  ADD KEY `fk_tn_nguoi_gui` (`nguoi_gui_id`),
  ADD KEY `fk_tn_nguoi_nhan` (`nguoi_nhan_id`);

--
-- Chỉ mục cho bảng `van_ban_den`
--
ALTER TABLE `van_ban_den`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loai_vb_id` (`loai_vb_id`),
  ADD KEY `nguoi_nhap_id` (`nguoi_nhap_id`);

--
-- Chỉ mục cho bảng `van_ban_di`
--
ALTER TABLE `van_ban_di`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loai_vb_id` (`loai_vb_id`),
  ADD KEY `tra_loi_vb_den_id` (`tra_loi_vb_den_id`),
  ADD KEY `nguoi_nhap_id` (`nguoi_nhap_id`);

--
-- Chỉ mục cho bảng `van_ban_phan_cong`
--
ALTER TABLE `van_ban_phan_cong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vb_id` (`vb_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `loai_van_ban`
--
ALTER TABLE `loai_van_ban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `thong_bao_nguoi_nhan`
--
ALTER TABLE `thong_bao_nguoi_nhan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `van_ban_den`
--
ALTER TABLE `van_ban_den`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `van_ban_di`
--
ALTER TABLE `van_ban_di`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `van_ban_phan_cong`
--
ALTER TABLE `van_ban_phan_cong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `thong_bao_nguoi_nhan`
--
ALTER TABLE `thong_bao_nguoi_nhan`
  ADD CONSTRAINT `fk_tn_nguoi_gui` FOREIGN KEY (`nguoi_gui_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tn_nguoi_nhan` FOREIGN KEY (`nguoi_nhan_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tn_van_ban_den` FOREIGN KEY (`van_ban_den_id`) REFERENCES `van_ban_den` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `van_ban_den`
--
ALTER TABLE `van_ban_den`
  ADD CONSTRAINT `van_ban_den_ibfk_1` FOREIGN KEY (`loai_vb_id`) REFERENCES `loai_van_ban` (`id`),
  ADD CONSTRAINT `van_ban_den_ibfk_2` FOREIGN KEY (`nguoi_nhap_id`) REFERENCES `nguoi_dung` (`id`);

--
-- Các ràng buộc cho bảng `van_ban_di`
--
ALTER TABLE `van_ban_di`
  ADD CONSTRAINT `van_ban_di_ibfk_1` FOREIGN KEY (`loai_vb_id`) REFERENCES `loai_van_ban` (`id`),
  ADD CONSTRAINT `van_ban_di_ibfk_2` FOREIGN KEY (`tra_loi_vb_den_id`) REFERENCES `van_ban_den` (`id`),
  ADD CONSTRAINT `van_ban_di_ibfk_3` FOREIGN KEY (`nguoi_nhap_id`) REFERENCES `nguoi_dung` (`id`);

--
-- Các ràng buộc cho bảng `van_ban_phan_cong`
--
ALTER TABLE `van_ban_phan_cong`
  ADD CONSTRAINT `van_ban_phan_cong_ibfk_1` FOREIGN KEY (`vb_id`) REFERENCES `van_ban_den` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
