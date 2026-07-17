-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th7 16, 2026 lúc 03:23 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `project_management`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `attachments`
--

CREATE TABLE `attachments` (
  `attachment_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(80) DEFAULT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `attachments`
--

INSERT INTO `attachments` (`attachment_id`, `task_id`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_by`, `uploaded_at`) VALUES
(1, 2, 'bao_cao_task_2.pdf', 'uploads/tasks/bao_cao_task_2.pdf', 'application/pdf', 137350, 6, '2026-07-03 07:00:00'),
(2, 4, 'bao_cao_task_4.docx', 'uploads/tasks/bao_cao_task_4.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 154700, 8, '2026-07-03 12:00:00'),
(3, 6, 'bao_cao_task_6.pdf', 'uploads/tasks/bao_cao_task_6.pdf', 'application/pdf', 172050, 5, '2026-07-03 17:00:00'),
(4, 8, 'bao_cao_task_8.docx', 'uploads/tasks/bao_cao_task_8.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 189400, 11, '2026-07-03 22:00:00'),
(5, 10, 'bao_cao_task_10.pdf', 'uploads/tasks/bao_cao_task_10.pdf', 'application/pdf', 206750, 13, '2026-07-04 03:00:00'),
(6, 12, 'bao_cao_task_12.docx', 'uploads/tasks/bao_cao_task_12.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 224100, 8, '2026-07-04 08:00:00'),
(7, 14, 'bao_cao_task_14.pdf', 'uploads/tasks/bao_cao_task_14.pdf', 'application/pdf', 241450, 14, '2026-07-04 13:00:00'),
(8, 16, 'bao_cao_task_16.docx', 'uploads/tasks/bao_cao_task_16.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 258800, 10, '2026-07-04 18:00:00'),
(9, 18, 'bao_cao_task_18.pdf', 'uploads/tasks/bao_cao_task_18.pdf', 'application/pdf', 276150, 12, '2026-07-04 23:00:00'),
(10, 20, 'bao_cao_task_20.docx', 'uploads/tasks/bao_cao_task_20.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 293500, 14, '2026-07-05 04:00:00'),
(11, 22, 'bao_cao_task_22.pdf', 'uploads/tasks/bao_cao_task_22.pdf', 'application/pdf', 310850, 12, '2026-07-05 09:00:00'),
(12, 24, 'bao_cao_task_24.docx', 'uploads/tasks/bao_cao_task_24.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 328200, 17, '2026-07-05 14:00:00'),
(13, 26, 'bao_cao_task_26.pdf', 'uploads/tasks/bao_cao_task_26.pdf', 'application/pdf', 345550, 10, '2026-07-05 19:00:00'),
(14, 28, 'bao_cao_task_28.docx', 'uploads/tasks/bao_cao_task_28.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 362900, 15, '2026-07-06 00:00:00'),
(15, 30, 'bao_cao_task_30.pdf', 'uploads/tasks/bao_cao_task_30.pdf', 'application/pdf', 380250, 20, '2026-07-06 05:00:00'),
(16, 32, 'bao_cao_task_32.docx', 'uploads/tasks/bao_cao_task_32.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 397600, 6, '2026-07-06 10:00:00'),
(17, 34, 'bao_cao_task_34.pdf', 'uploads/tasks/bao_cao_task_34.pdf', 'application/pdf', 414950, 17, '2026-07-06 15:00:00'),
(18, 36, 'bao_cao_task_36.docx', 'uploads/tasks/bao_cao_task_36.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 432300, 7, '2026-07-06 20:00:00'),
(19, 38, 'bao_cao_task_38.pdf', 'uploads/tasks/bao_cao_task_38.pdf', 'application/pdf', 449650, 11, '2026-07-07 01:00:00'),
(20, 40, 'bao_cao_task_40.docx', 'uploads/tasks/bao_cao_task_40.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 467000, 20, '2026-07-07 06:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `entity` varchar(80) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `entity`, `entity_id`, `project_id`, `description`, `created_at`) VALUES
(1, 2, 'CREATE_PROJECT', 'projects', 1, 1, 'Tạo dự án Hệ thống Quản lý Dự án', '2026-06-02 02:00:00'),
(2, 2, 'CREATE_PROJECT', 'projects', 2, 2, 'Tạo dự án Cổng Đào tạo Nội bộ', '2026-06-03 02:00:00'),
(3, 2, 'CREATE_PROJECT', 'projects', 3, 3, 'Tạo dự án Ứng dụng Báo cáo Tiến độ', '2026-06-04 02:00:00'),
(4, 3, 'CREATE_PROJECT', 'projects', 4, 4, 'Tạo dự án Website Thương mại Điện tử', '2026-06-05 02:00:00'),
(5, 3, 'CREATE_PROJECT', 'projects', 5, 5, 'Tạo dự án CRM Chăm sóc Khách hàng', '2026-06-06 02:00:00'),
(6, 3, 'CREATE_PROJECT', 'projects', 6, 6, 'Tạo dự án Kho dữ liệu Báo cáo', '2026-06-07 02:00:00'),
(7, 4, 'CREATE_PROJECT', 'projects', 7, 7, 'Tạo dự án Hệ thống Giám sát An ninh', '2026-06-08 02:00:00'),
(8, 4, 'CREATE_PROJECT', 'projects', 8, 8, 'Tạo dự án Ứng dụng Quản lý Tài sản', '2026-06-09 02:00:00'),
(9, 4, 'CREATE_PROJECT', 'projects', 9, 9, 'Tạo dự án Cổng Dịch vụ Nhân sự', '2026-06-10 02:00:00'),
(10, 4, 'CREATE_PROJECT', 'projects', 10, 10, 'Tạo dự án Mobile Field Service', '2026-06-11 02:00:00'),
(11, 2, 'CREATE_TASK', 'tasks', 1, 1, 'Tạo nhiệm vụ Phân tích yêu cầu nghiệp vụ', '2026-07-01 05:00:00'),
(12, 2, 'CREATE_TASK', 'tasks', 2, 1, 'Tạo nhiệm vụ Thiết kế cơ sở dữ liệu', '2026-07-01 09:00:00'),
(13, 2, 'CREATE_TASK', 'tasks', 3, 1, 'Tạo nhiệm vụ Xây dựng chức năng chính', '2026-07-01 13:00:00'),
(14, 2, 'CREATE_TASK', 'tasks', 4, 1, 'Tạo nhiệm vụ Kiểm thử và sửa lỗi', '2026-07-01 17:00:00'),
(15, 2, 'CREATE_TASK', 'tasks', 5, 1, 'Tạo nhiệm vụ Hoàn thiện tài liệu triển khai', '2026-07-01 21:00:00'),
(16, 2, 'CREATE_TASK', 'tasks', 6, 2, 'Tạo nhiệm vụ Phân tích yêu cầu nghiệp vụ', '2026-07-02 01:00:00'),
(17, 2, 'CREATE_TASK', 'tasks', 7, 2, 'Tạo nhiệm vụ Thiết kế cơ sở dữ liệu', '2026-07-02 05:00:00'),
(18, 2, 'CREATE_TASK', 'tasks', 8, 2, 'Tạo nhiệm vụ Xây dựng chức năng chính', '2026-07-02 09:00:00'),
(19, 2, 'CREATE_TASK', 'tasks', 9, 2, 'Tạo nhiệm vụ Kiểm thử và sửa lỗi', '2026-07-02 13:00:00'),
(20, 2, 'CREATE_TASK', 'tasks', 10, 2, 'Tạo nhiệm vụ Hoàn thiện tài liệu triển khai', '2026-07-02 17:00:00'),
(21, 2, 'CREATE_TASK', 'tasks', 11, 3, 'Tạo nhiệm vụ Phân tích yêu cầu nghiệp vụ', '2026-07-02 21:00:00'),
(22, 2, 'CREATE_TASK', 'tasks', 12, 3, 'Tạo nhiệm vụ Thiết kế cơ sở dữ liệu', '2026-07-03 01:00:00'),
(23, 2, 'CREATE_TASK', 'tasks', 13, 3, 'Tạo nhiệm vụ Xây dựng chức năng chính', '2026-07-03 05:00:00'),
(24, 2, 'CREATE_TASK', 'tasks', 14, 3, 'Tạo nhiệm vụ Kiểm thử và sửa lỗi', '2026-07-03 09:00:00'),
(25, 2, 'CREATE_TASK', 'tasks', 15, 3, 'Tạo nhiệm vụ Hoàn thiện tài liệu triển khai', '2026-07-03 13:00:00'),
(26, 5, 'UPDATE_PROGRESS', 'tasks', 1, 1, 'Cập nhật tiến độ nhiệm vụ Phân tích yêu cầu nghiệp vụ', '2026-07-07 08:00:00'),
(27, 6, 'UPDATE_PROGRESS', 'tasks', 2, 1, 'Cập nhật tiến độ nhiệm vụ Thiết kế cơ sở dữ liệu', '2026-07-07 13:00:00'),
(28, 7, 'UPDATE_PROGRESS', 'tasks', 3, 1, 'Cập nhật tiến độ nhiệm vụ Xây dựng chức năng chính', '2026-07-07 18:00:00'),
(29, 8, 'UPDATE_PROGRESS', 'tasks', 4, 1, 'Cập nhật tiến độ nhiệm vụ Kiểm thử và sửa lỗi', '2026-07-07 23:00:00'),
(30, 9, 'UPDATE_PROGRESS', 'tasks', 5, 1, 'Cập nhật tiến độ nhiệm vụ Hoàn thiện tài liệu triển khai', '2026-07-08 04:00:00'),
(31, 5, 'UPDATE_PROGRESS', 'tasks', 6, 2, 'Cập nhật tiến độ nhiệm vụ Phân tích yêu cầu nghiệp vụ', '2026-07-08 09:00:00'),
(32, 7, 'UPDATE_PROGRESS', 'tasks', 7, 2, 'Cập nhật tiến độ nhiệm vụ Thiết kế cơ sở dữ liệu', '2026-07-08 14:00:00'),
(33, 11, 'UPDATE_PROGRESS', 'tasks', 8, 2, 'Cập nhật tiến độ nhiệm vụ Xây dựng chức năng chính', '2026-07-08 19:00:00'),
(34, 12, 'UPDATE_PROGRESS', 'tasks', 9, 2, 'Cập nhật tiến độ nhiệm vụ Kiểm thử và sửa lỗi', '2026-07-09 00:00:00'),
(35, 13, 'UPDATE_PROGRESS', 'tasks', 10, 2, 'Cập nhật tiến độ nhiệm vụ Hoàn thiện tài liệu triển khai', '2026-07-09 05:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `comments`
--

INSERT INTO `comments` (`comment_id`, `task_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 'Đã cập nhật tiến độ theo kế hoạch, vui lòng kiểm tra.', '2026-07-01 04:00:00', '2026-07-16 13:10:17'),
(2, 2, 6, 'Cần bổ sung thêm tiêu chí kiểm thử cho trường hợp ngoại lệ.', '2026-07-01 07:00:00', '2026-07-16 13:10:17'),
(3, 3, 2, 'Phần giao diện đã hoàn thành, đang chờ tích hợp backend.', '2026-07-01 10:00:00', '2026-07-16 13:10:17'),
(4, 4, 8, 'Đã sửa lỗi theo góp ý và đẩy phiên bản mới.', '2026-07-01 13:00:00', '2026-07-16 13:10:17'),
(5, 5, 9, 'Vui lòng xác nhận yêu cầu trước khi chuyển sang trạng thái Review.', '2026-07-01 16:00:00', '2026-07-16 13:10:17'),
(6, 6, 2, 'File báo cáo đã được đính kèm trong nhiệm vụ.', '2026-07-01 19:00:00', '2026-07-16 13:10:17'),
(7, 7, 7, 'Đã cập nhật tiến độ theo kế hoạch, vui lòng kiểm tra.', '2026-07-01 22:00:00', '2026-07-16 13:10:17'),
(8, 8, 11, 'Cần bổ sung thêm tiêu chí kiểm thử cho trường hợp ngoại lệ.', '2026-07-02 01:00:00', '2026-07-16 13:10:17'),
(9, 9, 2, 'Phần giao diện đã hoàn thành, đang chờ tích hợp backend.', '2026-07-02 04:00:00', '2026-07-16 13:10:17'),
(10, 10, 13, 'Đã sửa lỗi theo góp ý và đẩy phiên bản mới.', '2026-07-02 07:00:00', '2026-07-16 13:10:17'),
(11, 11, 6, 'Vui lòng xác nhận yêu cầu trước khi chuyển sang trạng thái Review.', '2026-07-02 10:00:00', '2026-07-16 13:10:17'),
(12, 12, 2, 'File báo cáo đã được đính kèm trong nhiệm vụ.', '2026-07-02 13:00:00', '2026-07-16 13:10:17'),
(13, 13, 9, 'Đã cập nhật tiến độ theo kế hoạch, vui lòng kiểm tra.', '2026-07-02 16:00:00', '2026-07-16 13:10:17'),
(14, 14, 14, 'Cần bổ sung thêm tiêu chí kiểm thử cho trường hợp ngoại lệ.', '2026-07-02 19:00:00', '2026-07-16 13:10:17'),
(15, 15, 2, 'Phần giao diện đã hoàn thành, đang chờ tích hợp backend.', '2026-07-02 22:00:00', '2026-07-16 13:10:17'),
(16, 16, 10, 'Đã sửa lỗi theo góp ý và đẩy phiên bản mới.', '2026-07-03 01:00:00', '2026-07-16 13:10:17'),
(17, 17, 11, 'Vui lòng xác nhận yêu cầu trước khi chuyển sang trạng thái Review.', '2026-07-03 04:00:00', '2026-07-16 13:10:17'),
(18, 18, 3, 'File báo cáo đã được đính kèm trong nhiệm vụ.', '2026-07-03 07:00:00', '2026-07-16 13:10:17'),
(19, 19, 13, 'Đã cập nhật tiến độ theo kế hoạch, vui lòng kiểm tra.', '2026-07-03 10:00:00', '2026-07-16 13:10:17'),
(20, 20, 14, 'Cần bổ sung thêm tiêu chí kiểm thử cho trường hợp ngoại lệ.', '2026-07-03 13:00:00', '2026-07-16 13:10:17'),
(21, 21, 3, 'Phần giao diện đã hoàn thành, đang chờ tích hợp backend.', '2026-07-03 16:00:00', '2026-07-16 13:10:17'),
(22, 22, 12, 'Đã sửa lỗi theo góp ý và đẩy phiên bản mới.', '2026-07-03 19:00:00', '2026-07-16 13:10:17'),
(23, 23, 16, 'Vui lòng xác nhận yêu cầu trước khi chuyển sang trạng thái Review.', '2026-07-03 22:00:00', '2026-07-16 13:10:17'),
(24, 24, 3, 'File báo cáo đã được đính kèm trong nhiệm vụ.', '2026-07-04 01:00:00', '2026-07-16 13:10:17'),
(25, 25, 18, 'Đã cập nhật tiến độ theo kế hoạch, vui lòng kiểm tra.', '2026-07-04 04:00:00', '2026-07-16 13:10:17'),
(26, 26, 10, 'Cần bổ sung thêm tiêu chí kiểm thử cho trường hợp ngoại lệ.', '2026-07-04 07:00:00', '2026-07-16 13:10:17'),
(27, 27, 3, 'Phần giao diện đã hoàn thành, đang chờ tích hợp backend.', '2026-07-04 10:00:00', '2026-07-16 13:10:17'),
(28, 28, 15, 'Đã sửa lỗi theo góp ý và đẩy phiên bản mới.', '2026-07-04 13:00:00', '2026-07-16 13:10:17'),
(29, 29, 19, 'Vui lòng xác nhận yêu cầu trước khi chuyển sang trạng thái Review.', '2026-07-04 16:00:00', '2026-07-16 13:10:17'),
(30, 30, 3, 'File báo cáo đã được đính kèm trong nhiệm vụ.', '2026-07-04 19:00:00', '2026-07-16 13:10:17'),
(31, 31, 5, 'Đã cập nhật tiến độ theo kế hoạch, vui lòng kiểm tra.', '2026-07-04 22:00:00', '2026-07-16 13:10:17'),
(32, 32, 6, 'Cần bổ sung thêm tiêu chí kiểm thử cho trường hợp ngoại lệ.', '2026-07-05 01:00:00', '2026-07-16 13:10:17'),
(33, 33, 4, 'Phần giao diện đã hoàn thành, đang chờ tích hợp backend.', '2026-07-05 04:00:00', '2026-07-16 13:10:17'),
(34, 34, 17, 'Đã sửa lỗi theo góp ý và đẩy phiên bản mới.', '2026-07-05 07:00:00', '2026-07-16 13:10:17'),
(35, 35, 18, 'Vui lòng xác nhận yêu cầu trước khi chuyển sang trạng thái Review.', '2026-07-05 10:00:00', '2026-07-16 13:10:17'),
(36, 36, 4, 'File báo cáo đã được đính kèm trong nhiệm vụ.', '2026-07-05 13:00:00', '2026-07-16 13:10:17'),
(37, 37, 8, 'Đã cập nhật tiến độ theo kế hoạch, vui lòng kiểm tra.', '2026-07-05 16:00:00', '2026-07-16 13:10:17'),
(38, 38, 11, 'Cần bổ sung thêm tiêu chí kiểm thử cho trường hợp ngoại lệ.', '2026-07-05 19:00:00', '2026-07-16 13:10:17'),
(39, 39, 4, 'Phần giao diện đã hoàn thành, đang chờ tích hợp backend.', '2026-07-05 22:00:00', '2026-07-16 13:10:17'),
(40, 40, 20, 'Đã sửa lỗi theo góp ý và đẩy phiên bản mới.', '2026-07-06 01:00:00', '2026-07-16 13:10:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `title`, `content`, `related_type`, `related_id`, `is_read`, `created_at`) VALUES
(1, 5, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Phân tích yêu cầu nghiệp vụ.', 'task', 1, 0, '2026-07-05 02:30:00'),
(2, 6, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Thiết kế cơ sở dữ liệu.', 'task', 2, 1, '2026-07-05 04:30:00'),
(3, 7, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Xây dựng chức năng chính.', 'task', 3, 0, '2026-07-05 06:30:00'),
(4, 8, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Kiểm thử và sửa lỗi.', 'task', 4, 1, '2026-07-05 08:30:00'),
(5, 9, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Hoàn thiện tài liệu triển khai.', 'task', 5, 0, '2026-07-05 10:30:00'),
(6, 5, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Phân tích yêu cầu nghiệp vụ.', 'task', 6, 0, '2026-07-05 12:30:00'),
(7, 7, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Thiết kế cơ sở dữ liệu.', 'task', 7, 0, '2026-07-05 14:30:00'),
(8, 11, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Xây dựng chức năng chính.', 'task', 8, 1, '2026-07-05 16:30:00'),
(9, 12, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Kiểm thử và sửa lỗi.', 'task', 9, 0, '2026-07-05 18:30:00'),
(10, 13, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Hoàn thiện tài liệu triển khai.', 'task', 10, 0, '2026-07-05 20:30:00'),
(11, 6, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Phân tích yêu cầu nghiệp vụ.', 'task', 11, 1, '2026-07-05 22:30:00'),
(12, 8, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Thiết kế cơ sở dữ liệu.', 'task', 12, 1, '2026-07-06 00:30:00'),
(13, 9, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Xây dựng chức năng chính.', 'task', 13, 0, '2026-07-06 02:30:00'),
(14, 14, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Kiểm thử và sửa lỗi.', 'task', 14, 0, '2026-07-06 04:30:00'),
(15, 15, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Hoàn thiện tài liệu triển khai.', 'task', 15, 0, '2026-07-06 06:30:00'),
(16, 10, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Phân tích yêu cầu nghiệp vụ.', 'task', 16, 1, '2026-07-06 08:30:00'),
(17, 11, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Thiết kế cơ sở dữ liệu.', 'task', 17, 0, '2026-07-06 10:30:00'),
(18, 12, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Xây dựng chức năng chính.', 'task', 18, 0, '2026-07-06 12:30:00'),
(19, 13, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Kiểm thử và sửa lỗi.', 'task', 19, 0, '2026-07-06 14:30:00'),
(20, 14, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Hoàn thiện tài liệu triển khai.', 'task', 20, 1, '2026-07-06 16:30:00'),
(21, 9, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Phân tích yêu cầu nghiệp vụ.', 'task', 21, 0, '2026-07-06 18:30:00'),
(22, 12, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Thiết kế cơ sở dữ liệu.', 'task', 22, 0, '2026-07-06 20:30:00'),
(23, 16, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Xây dựng chức năng chính.', 'task', 23, 0, '2026-07-06 22:30:00'),
(24, 17, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Kiểm thử và sửa lỗi.', 'task', 24, 1, '2026-07-07 00:30:00'),
(25, 18, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Hoàn thiện tài liệu triển khai.', 'task', 25, 0, '2026-07-07 02:30:00'),
(26, 10, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Phân tích yêu cầu nghiệp vụ.', 'task', 26, 0, '2026-07-07 04:30:00'),
(27, 13, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Thiết kế cơ sở dữ liệu.', 'task', 27, 0, '2026-07-07 06:30:00'),
(28, 15, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Xây dựng chức năng chính.', 'task', 28, 1, '2026-07-07 08:30:00'),
(29, 19, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Kiểm thử và sửa lỗi.', 'task', 29, 0, '2026-07-07 10:30:00'),
(30, 20, 'task', 'Nhiệm vụ mới được giao', 'Bạn được giao nhiệm vụ: Hoàn thiện tài liệu triển khai.', 'task', 30, 0, '2026-07-07 12:30:00'),
(31, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Phân tích yêu cầu nghiệp vụ.', 'comment', 1, 0, '2026-07-08 05:00:00'),
(32, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Thiết kế cơ sở dữ liệu.', 'comment', 2, 0, '2026-07-08 08:00:00'),
(33, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Xây dựng chức năng chính.', 'comment', 3, 0, '2026-07-08 11:00:00'),
(34, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Kiểm thử và sửa lỗi.', 'comment', 4, 0, '2026-07-08 14:00:00'),
(35, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Hoàn thiện tài liệu triển khai.', 'comment', 5, 0, '2026-07-08 17:00:00'),
(36, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Phân tích yêu cầu nghiệp vụ.', 'comment', 6, 0, '2026-07-08 20:00:00'),
(37, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Thiết kế cơ sở dữ liệu.', 'comment', 7, 0, '2026-07-08 23:00:00'),
(38, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Xây dựng chức năng chính.', 'comment', 8, 0, '2026-07-09 02:00:00'),
(39, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Kiểm thử và sửa lỗi.', 'comment', 9, 0, '2026-07-09 05:00:00'),
(40, 2, 'comment', 'Bình luận mới từ Staff', 'Staff đã cập nhật trao đổi trong nhiệm vụ: Hoàn thiện tài liệu triển khai.', 'comment', 10, 0, '2026-07-09 08:00:00'),
(41, 2, 'project', 'Cập nhật tiến độ dự án', 'Dự án Hệ thống Quản lý Dự án vừa được cập nhật tiến độ.', 'project', 1, 0, '2026-07-10 04:00:00'),
(42, 2, 'project', 'Cập nhật tiến độ dự án', 'Dự án Cổng Đào tạo Nội bộ vừa được cập nhật tiến độ.', 'project', 2, 0, '2026-07-10 05:00:00'),
(43, 2, 'project', 'Cập nhật tiến độ dự án', 'Dự án Ứng dụng Báo cáo Tiến độ vừa được cập nhật tiến độ.', 'project', 3, 1, '2026-07-10 06:00:00'),
(44, 3, 'project', 'Cập nhật tiến độ dự án', 'Dự án Website Thương mại Điện tử vừa được cập nhật tiến độ.', 'project', 4, 0, '2026-07-10 07:00:00'),
(45, 3, 'project', 'Cập nhật tiến độ dự án', 'Dự án CRM Chăm sóc Khách hàng vừa được cập nhật tiến độ.', 'project', 5, 0, '2026-07-10 08:00:00'),
(46, 3, 'project', 'Cập nhật tiến độ dự án', 'Dự án Kho dữ liệu Báo cáo vừa được cập nhật tiến độ.', 'project', 6, 1, '2026-07-10 09:00:00'),
(47, 4, 'project', 'Cập nhật tiến độ dự án', 'Dự án Hệ thống Giám sát An ninh vừa được cập nhật tiến độ.', 'project', 7, 0, '2026-07-10 10:00:00'),
(48, 4, 'project', 'Cập nhật tiến độ dự án', 'Dự án Ứng dụng Quản lý Tài sản vừa được cập nhật tiến độ.', 'project', 8, 0, '2026-07-10 11:00:00'),
(49, 4, 'project', 'Cập nhật tiến độ dự án', 'Dự án Cổng Dịch vụ Nhân sự vừa được cập nhật tiến độ.', 'project', 9, 1, '2026-07-10 12:00:00'),
(50, 4, 'project', 'Cập nhật tiến độ dự án', 'Dự án Mobile Field Service vừa được cập nhật tiến độ.', 'project', 10, 0, '2026-07-10 13:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `task_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `comment_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `deadline_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notification_preferences`
--

INSERT INTO `notification_preferences` (`user_id`, `email_notifications`, `task_notifications`, `comment_notifications`, `deadline_notifications`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(2, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(3, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(4, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(5, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(6, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(7, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(8, 0, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(9, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(10, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(11, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(12, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(13, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(14, 0, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(15, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(16, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(17, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(18, 1, 1, 0, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(19, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(20, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17'),
(21, 1, 1, 1, 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_code` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(50) DEFAULT 'Planning',
  `priority` varchar(50) DEFAULT 'Medium',
  `progress` int(11) NOT NULL DEFAULT 0,
  `manager_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `projects`
--

INSERT INTO `projects` (`project_id`, `project_code`, `name`, `description`, `start_date`, `end_date`, `created_by`, `created_at`, `updated_at`, `is_deleted`, `status`, `priority`, `progress`, `manager_id`) VALUES
(1, 'PMS-001', 'Hệ thống Quản lý Dự án', 'Xây dựng nền tảng quản lý dự án, nhiệm vụ, tiến độ và thông báo.', '2026-06-01', '2026-09-30', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'In Progress', 'High', 68, 2),
(2, 'PMS-002', 'Cổng Đào tạo Nội bộ', 'Quản lý khóa học, tài liệu và kết quả học tập nhân viên.', '2026-06-15', '2026-11-15', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'In Progress', 'Medium', 45, 2),
(3, 'PMS-003', 'Ứng dụng Báo cáo Tiến độ', 'Tổng hợp báo cáo công việc và cảnh báo deadline theo thời gian thực.', '2026-07-01', '2026-10-31', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'Planning', 'High', 20, 2),
(4, 'PMS-004', 'Website Thương mại Điện tử', 'Quản lý sản phẩm, giỏ hàng, đơn hàng và thanh toán.', '2026-06-10', '2026-12-20', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'In Progress', 'Critical', 55, 3),
(5, 'PMS-005', 'CRM Chăm sóc Khách hàng', 'Quản lý khách hàng, lịch sử tương tác và chiến dịch chăm sóc.', '2026-07-05', '2026-12-10', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'Planning', 'Medium', 15, 3),
(6, 'PMS-006', 'Kho dữ liệu Báo cáo', 'Xây dựng kho dữ liệu và dashboard phân tích vận hành.', '2026-05-20', '2026-10-20', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'Review', 'High', 82, 3),
(7, 'PMS-007', 'Hệ thống Giám sát An ninh', 'Thu thập log, cảnh báo sự kiện và hỗ trợ điều tra sự cố.', '2026-06-01', '2027-01-31', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'In Progress', 'Critical', 50, 4),
(8, 'PMS-008', 'Ứng dụng Quản lý Tài sản', 'Theo dõi tài sản, cấp phát, bảo trì và kiểm kê.', '2026-07-01', '2026-12-31', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'Planning', 'Medium', 10, 4),
(9, 'PMS-009', 'Cổng Dịch vụ Nhân sự', 'Quản lý hồ sơ, nghỉ phép và quy trình đề xuất nội bộ.', '2026-05-15', '2026-09-15', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'Review', 'High', 88, 4),
(10, 'PMS-010', 'Mobile Field Service', 'Điều phối công việc hiện trường và cập nhật trạng thái trên di động.', '2026-07-10', '2027-02-28', 1, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0, 'Planning', 'High', 5, 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `project_members`
--

CREATE TABLE `project_members` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_in_project` varchar(80) DEFAULT 'Member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `project_members`
--

INSERT INTO `project_members` (`project_id`, `user_id`, `role_in_project`, `joined_at`) VALUES
(1, 2, 'Manager', '2026-07-16 13:10:17'),
(1, 5, 'Backend Developer', '2026-07-16 13:10:17'),
(1, 6, 'Frontend Developer', '2026-07-16 13:10:17'),
(1, 7, 'Tester', '2026-07-16 13:10:17'),
(1, 8, 'UI/UX Designer', '2026-07-16 13:10:17'),
(1, 9, 'Security Analyst', '2026-07-16 13:10:17'),
(1, 10, 'Data Analyst', '2026-07-16 13:10:17'),
(2, 2, 'Manager', '2026-07-16 13:10:17'),
(2, 5, 'Backend Developer', '2026-07-16 13:10:17'),
(2, 7, 'Frontend Developer', '2026-07-16 13:10:17'),
(2, 11, 'Tester', '2026-07-16 13:10:17'),
(2, 12, 'UI/UX Designer', '2026-07-16 13:10:17'),
(2, 13, 'Security Analyst', '2026-07-16 13:10:17'),
(3, 2, 'Manager', '2026-07-16 13:10:17'),
(3, 6, 'Backend Developer', '2026-07-16 13:10:17'),
(3, 8, 'Frontend Developer', '2026-07-16 13:10:17'),
(3, 9, 'Tester', '2026-07-16 13:10:17'),
(3, 14, 'UI/UX Designer', '2026-07-16 13:10:17'),
(3, 15, 'Security Analyst', '2026-07-16 13:10:17'),
(4, 3, 'Manager', '2026-07-16 13:10:17'),
(4, 10, 'Backend Developer', '2026-07-16 13:10:17'),
(4, 11, 'Frontend Developer', '2026-07-16 13:10:17'),
(4, 12, 'Tester', '2026-07-16 13:10:17'),
(4, 13, 'UI/UX Designer', '2026-07-16 13:10:17'),
(4, 14, 'Security Analyst', '2026-07-16 13:10:17'),
(4, 15, 'Data Analyst', '2026-07-16 13:10:17'),
(5, 3, 'Manager', '2026-07-16 13:10:17'),
(5, 9, 'Backend Developer', '2026-07-16 13:10:17'),
(5, 12, 'Frontend Developer', '2026-07-16 13:10:17'),
(5, 16, 'Tester', '2026-07-16 13:10:17'),
(5, 17, 'UI/UX Designer', '2026-07-16 13:10:17'),
(5, 18, 'Security Analyst', '2026-07-16 13:10:17'),
(6, 3, 'Manager', '2026-07-16 13:10:17'),
(6, 10, 'Backend Developer', '2026-07-16 13:10:17'),
(6, 13, 'Frontend Developer', '2026-07-16 13:10:17'),
(6, 15, 'Tester', '2026-07-16 13:10:17'),
(6, 19, 'UI/UX Designer', '2026-07-16 13:10:17'),
(6, 20, 'Security Analyst', '2026-07-16 13:10:17'),
(7, 4, 'Manager', '2026-07-16 13:10:17'),
(7, 5, 'Backend Developer', '2026-07-16 13:10:17'),
(7, 6, 'Frontend Developer', '2026-07-16 13:10:17'),
(7, 16, 'Tester', '2026-07-16 13:10:17'),
(7, 17, 'UI/UX Designer', '2026-07-16 13:10:17'),
(7, 18, 'Security Analyst', '2026-07-16 13:10:17'),
(7, 19, 'Data Analyst', '2026-07-16 13:10:17'),
(8, 4, 'Manager', '2026-07-16 13:10:17'),
(8, 7, 'Backend Developer', '2026-07-16 13:10:17'),
(8, 8, 'Frontend Developer', '2026-07-16 13:10:17'),
(8, 11, 'Tester', '2026-07-16 13:10:17'),
(8, 14, 'UI/UX Designer', '2026-07-16 13:10:17'),
(8, 20, 'Security Analyst', '2026-07-16 13:10:17'),
(9, 4, 'Manager', '2026-07-16 13:10:17'),
(9, 9, 'Backend Developer', '2026-07-16 13:10:17'),
(9, 12, 'Frontend Developer', '2026-07-16 13:10:17'),
(9, 13, 'Tester', '2026-07-16 13:10:17'),
(9, 15, 'UI/UX Designer', '2026-07-16 13:10:17'),
(9, 21, 'Security Analyst', '2026-07-16 13:10:17'),
(10, 4, 'Manager', '2026-07-16 13:10:17'),
(10, 10, 'Backend Developer', '2026-07-16 13:10:17'),
(10, 16, 'Frontend Developer', '2026-07-16 13:10:17'),
(10, 17, 'Tester', '2026-07-16 13:10:17'),
(10, 18, 'UI/UX Designer', '2026-07-16 13:10:17'),
(10, 19, 'Security Analyst', '2026-07-16 13:10:17'),
(10, 20, 'Data Analyst', '2026-07-16 13:10:17'),
(10, 21, 'Backend Developer', '2026-07-16 13:10:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_code` varchar(30) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `role_code`, `name`, `description`, `created_at`) VALUES
(1, 'ADMIN', 'Admin', 'Quản trị toàn hệ thống', '2026-07-16 13:10:17'),
(2, 'MANAGER', 'Manager', 'Quản lý dự án và phân công công việc', '2026-07-16 13:10:17'),
(3, 'STAFF', 'Staff', 'Nhân sự thực hiện nhiệm vụ', '2026-07-16 13:10:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Review','Completed','Pending Approval','Approved','Rejected') DEFAULT 'Pending',
  `priority` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `assignee_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `due_date` date DEFAULT NULL,
  `progress_percent` int(11) NOT NULL DEFAULT 0,
  `progress_comment` text DEFAULT NULL,
  `progress_file` varchar(500) DEFAULT NULL,
  `approval_status` enum('Not Required','Pending Approval','Approved','Rejected') NOT NULL DEFAULT 'Not Required',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tasks`
--

INSERT INTO `tasks` (`task_id`, `project_id`, `title`, `description`, `status`, `priority`, `assignee_id`, `created_by`, `due_date`, `progress_percent`, `progress_comment`, `progress_file`, `approval_status`, `approved_by`, `approved_at`, `rejected_reason`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 'Phân tích yêu cầu nghiệp vụ - Hệ thống Quản lý Dự án', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'In Progress', 'Medium', 5, 2, '2026-06-15', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(2, 1, 'Thiết kế cơ sở dữ liệu - Hệ thống Quản lý Dự án', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'Review', 'Critical', 6, 2, '2026-06-27', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(3, 1, 'Xây dựng chức năng chính - Hệ thống Quản lý Dự án', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'Pending', 'High', 7, 2, '2026-07-09', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(4, 1, 'Kiểm thử và sửa lỗi - Hệ thống Quản lý Dự án', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'Pending', 'Low', 8, 2, '2026-07-21', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(5, 1, 'Hoàn thiện tài liệu triển khai - Hệ thống Quản lý Dự án', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'Completed', 'High', 9, 2, '2026-08-02', 100, NULL, NULL, 'Approved', 2, '2026-08-02 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(6, 2, 'Phân tích yêu cầu nghiệp vụ - Cổng Đào tạo Nội bộ', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'Review', 'Critical', 5, 2, '2026-06-29', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(7, 2, 'Thiết kế cơ sở dữ liệu - Cổng Đào tạo Nội bộ', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'Pending', 'High', 7, 2, '2026-07-11', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(8, 2, 'Xây dựng chức năng chính - Cổng Đào tạo Nội bộ', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'Pending', 'Low', 11, 2, '2026-07-23', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(9, 2, 'Kiểm thử và sửa lỗi - Cổng Đào tạo Nội bộ', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'Completed', 'High', 12, 2, '2026-08-04', 100, NULL, NULL, 'Approved', 2, '2026-08-04 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(10, 2, 'Hoàn thiện tài liệu triển khai - Cổng Đào tạo Nội bộ', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'In Progress', 'Medium', 13, 2, '2026-08-16', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(11, 3, 'Phân tích yêu cầu nghiệp vụ - Ứng dụng Báo cáo Tiến độ', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'Pending', 'High', 6, 2, '2026-07-15', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(12, 3, 'Thiết kế cơ sở dữ liệu - Ứng dụng Báo cáo Tiến độ', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'Pending', 'Low', 8, 2, '2026-07-27', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(13, 3, 'Xây dựng chức năng chính - Ứng dụng Báo cáo Tiến độ', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'Completed', 'High', 9, 2, '2026-08-08', 100, NULL, NULL, 'Approved', 2, '2026-08-08 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(14, 3, 'Kiểm thử và sửa lỗi - Ứng dụng Báo cáo Tiến độ', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'In Progress', 'Medium', 14, 2, '2026-08-20', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(15, 3, 'Hoàn thiện tài liệu triển khai - Ứng dụng Báo cáo Tiến độ', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'Review', 'Critical', 15, 2, '2026-09-01', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(16, 4, 'Phân tích yêu cầu nghiệp vụ - Website Thương mại Điện tử', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'Pending', 'Low', 10, 3, '2026-06-24', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(17, 4, 'Thiết kế cơ sở dữ liệu - Website Thương mại Điện tử', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'Completed', 'High', 11, 3, '2026-07-06', 100, NULL, NULL, 'Approved', 3, '2026-07-06 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(18, 4, 'Xây dựng chức năng chính - Website Thương mại Điện tử', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'In Progress', 'Medium', 12, 3, '2026-07-18', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(19, 4, 'Kiểm thử và sửa lỗi - Website Thương mại Điện tử', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'Review', 'Critical', 13, 3, '2026-07-30', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(20, 4, 'Hoàn thiện tài liệu triển khai - Website Thương mại Điện tử', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'Pending', 'High', 14, 3, '2026-08-11', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(21, 5, 'Phân tích yêu cầu nghiệp vụ - CRM Chăm sóc Khách hàng', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'Completed', 'High', 9, 3, '2026-07-19', 100, NULL, NULL, 'Approved', 3, '2026-07-19 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(22, 5, 'Thiết kế cơ sở dữ liệu - CRM Chăm sóc Khách hàng', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'In Progress', 'Medium', 12, 3, '2026-07-31', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(23, 5, 'Xây dựng chức năng chính - CRM Chăm sóc Khách hàng', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'Review', 'Critical', 16, 3, '2026-08-12', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(24, 5, 'Kiểm thử và sửa lỗi - CRM Chăm sóc Khách hàng', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'Pending', 'High', 17, 3, '2026-08-24', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(25, 5, 'Hoàn thiện tài liệu triển khai - CRM Chăm sóc Khách hàng', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'Pending', 'Low', 18, 3, '2026-09-05', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(26, 6, 'Phân tích yêu cầu nghiệp vụ - Kho dữ liệu Báo cáo', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'In Progress', 'Medium', 10, 3, '2026-06-03', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(27, 6, 'Thiết kế cơ sở dữ liệu - Kho dữ liệu Báo cáo', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'Review', 'Critical', 13, 3, '2026-06-15', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(28, 6, 'Xây dựng chức năng chính - Kho dữ liệu Báo cáo', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'Pending', 'High', 15, 3, '2026-06-27', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(29, 6, 'Kiểm thử và sửa lỗi - Kho dữ liệu Báo cáo', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'Pending', 'Low', 19, 3, '2026-07-09', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(30, 6, 'Hoàn thiện tài liệu triển khai - Kho dữ liệu Báo cáo', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'Completed', 'High', 20, 3, '2026-07-21', 100, NULL, NULL, 'Approved', 3, '2026-07-21 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(31, 7, 'Phân tích yêu cầu nghiệp vụ - Hệ thống Giám sát An ninh', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'Review', 'Critical', 5, 4, '2026-06-15', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(32, 7, 'Thiết kế cơ sở dữ liệu - Hệ thống Giám sát An ninh', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'Pending', 'High', 6, 4, '2026-06-27', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(33, 7, 'Xây dựng chức năng chính - Hệ thống Giám sát An ninh', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'Pending', 'Low', 16, 4, '2026-07-09', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(34, 7, 'Kiểm thử và sửa lỗi - Hệ thống Giám sát An ninh', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'Completed', 'High', 17, 4, '2026-07-21', 100, NULL, NULL, 'Approved', 4, '2026-07-21 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(35, 7, 'Hoàn thiện tài liệu triển khai - Hệ thống Giám sát An ninh', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'In Progress', 'Medium', 18, 4, '2026-08-02', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(36, 8, 'Phân tích yêu cầu nghiệp vụ - Ứng dụng Quản lý Tài sản', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'Pending', 'High', 7, 4, '2026-07-15', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(37, 8, 'Thiết kế cơ sở dữ liệu - Ứng dụng Quản lý Tài sản', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'Pending', 'Low', 8, 4, '2026-07-27', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(38, 8, 'Xây dựng chức năng chính - Ứng dụng Quản lý Tài sản', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'Completed', 'High', 11, 4, '2026-08-08', 100, NULL, NULL, 'Approved', 4, '2026-08-08 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(39, 8, 'Kiểm thử và sửa lỗi - Ứng dụng Quản lý Tài sản', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'In Progress', 'Medium', 14, 4, '2026-08-20', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(40, 8, 'Hoàn thiện tài liệu triển khai - Ứng dụng Quản lý Tài sản', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'Review', 'Critical', 20, 4, '2026-09-01', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(41, 9, 'Phân tích yêu cầu nghiệp vụ - Cổng Dịch vụ Nhân sự', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'Pending', 'Low', 9, 4, '2026-05-29', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(42, 9, 'Thiết kế cơ sở dữ liệu - Cổng Dịch vụ Nhân sự', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'Completed', 'High', 12, 4, '2026-06-10', 100, NULL, NULL, 'Approved', 4, '2026-06-10 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(43, 9, 'Xây dựng chức năng chính - Cổng Dịch vụ Nhân sự', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'In Progress', 'Medium', 13, 4, '2026-06-22', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(44, 9, 'Kiểm thử và sửa lỗi - Cổng Dịch vụ Nhân sự', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'Review', 'Critical', 15, 4, '2026-07-04', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(45, 9, 'Hoàn thiện tài liệu triển khai - Cổng Dịch vụ Nhân sự', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'Pending', 'High', 21, 4, '2026-07-16', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(46, 10, 'Phân tích yêu cầu nghiệp vụ - Mobile Field Service', 'Thu thập và chuẩn hóa yêu cầu từ các bên liên quan.', 'Completed', 'High', 10, 4, '2026-07-24', 100, NULL, NULL, 'Approved', 4, '2026-07-24 15:00:00', NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(47, 10, 'Thiết kế cơ sở dữ liệu - Mobile Field Service', 'Thiết kế bảng, khóa ngoại, chỉ mục và dữ liệu mẫu.', 'In Progress', 'Medium', 16, 4, '2026-08-05', 55, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(48, 10, 'Xây dựng chức năng chính - Mobile Field Service', 'Phát triển chức năng nghiệp vụ trọng tâm của phân hệ.', 'Review', 'Critical', 17, 4, '2026-08-17', 90, NULL, NULL, 'Pending Approval', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(49, 10, 'Kiểm thử và sửa lỗi - Mobile Field Service', 'Thực hiện kiểm thử chức năng, bảo mật và hồi quy.', 'Pending', 'High', 18, 4, '2026-08-29', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0),
(50, 10, 'Hoàn thiện tài liệu triển khai - Mobile Field Service', 'Chuẩn hóa tài liệu hướng dẫn và kịch bản demo.', 'Pending', 'Low', 19, 4, '2026-09-10', 0, NULL, NULL, 'Not Required', NULL, NULL, NULL, '2026-07-16 13:10:17', '2026-07-16 13:10:17', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `refresh_token` varchar(255) DEFAULT NULL,
  `token_expired_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `phone`, `address`, `password`, `role_id`, `status`, `refresh_token`, `token_expired_at`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 'admin', 'admin@gmail.com', '+84 900 000 001', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 1, 'active', NULL, NULL, '2026-06-01 17:00:00', '2026-07-16 13:21:53', 0),
(2, 'manager01', 'manager01@gmail.com', '+84 901 000 001', 'Ninh Kiều, Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 2, 'active', NULL, NULL, '2026-06-02 17:00:00', '2026-07-16 13:21:23', 0),
(3, 'manager02', 'manager02@gmail.com', '+84 901 000 002', 'Bình Thủy, Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 2, 'active', NULL, NULL, '2026-06-03 17:00:00', '2026-07-16 13:22:16', 0),
(4, 'manager03', 'manager03@gmail.com', '+84 901 000 003', 'Cái Răng, Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 2, 'active', NULL, NULL, '2026-06-04 17:00:00', '2026-07-16 13:19:12', 0),
(5, 'staff01', 'staff01@gmail.com', '+84 902 000 001', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-05 17:00:00', '2026-07-16 13:19:17', 0),
(6, 'staff02', 'staff02@gmail.com', '+84 902 000 002', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-06 17:00:00', '2026-07-16 13:22:48', 0),
(7, 'staff03', 'staff03@gmail.com', '+84 902 000 003', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-07 17:00:00', '2026-07-16 13:19:30', 0),
(8, 'staff04', 'staff04@gmail.com', '+84 902 000 004', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-08 17:00:00', '2026-07-16 13:19:35', 0),
(9, 'staff05', 'staff05@gmail.com', '+84 902 000 005', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-09 17:00:00', '2026-07-16 13:19:44', 0),
(10, 'staff06', 'staff06@gmail.com', '+84 902 000 006', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-10 17:00:00', '2026-07-16 13:19:49', 0),
(11, 'staff07', 'staff07@gmail.com', '+84 902 000 007', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-11 17:00:00', '2026-07-16 13:19:56', 0),
(12, 'staff08', 'staff08@gmail.com', '+84 902 000 008', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-12 17:00:00', '2026-07-16 13:20:03', 0),
(13, 'staff09', 'staff09@gmail.com', '+84 902 000 009', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-13 17:00:00', '2026-07-16 13:20:09', 0),
(14, 'staff10', 'staff10@gmail.com', '+84 902 000 010', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-14 17:00:00', '2026-07-16 13:20:16', 0),
(15, 'staff11', 'staff11@gmail.com', '+84 902 000 011', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-15 17:00:00', '2026-07-16 13:20:21', 0),
(16, 'staff12', 'staff12@gmail.com', '+84 902 000 012', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-16 17:00:00', '2026-07-16 13:20:31', 0),
(17, 'staff13', 'staff13@gmail.com', '+84 902 000 013', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-17 17:00:00', '2026-07-16 13:20:39', 0),
(18, 'staff14', 'staff14@gmail.com', '+84 902 000 014', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-18 17:00:00', '2026-07-16 13:20:47', 0),
(19, 'staff15', 'staff15@gmail.com', '+84 902 000 015', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-19 17:00:00', '2026-07-16 13:20:53', 0),
(20, 'staff16', 'staff16@gmail.com', '+84 902 000 016', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-20 17:00:00', '2026-07-16 13:20:57', 0),
(21, 'staff17', 'staff17@gmail.com', '+84 902 000 017', 'Cần Thơ', '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-21 17:00:00', '2026-07-16 13:21:01', 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `idx_attachments_task` (`task_id`),
  ADD KEY `idx_attachments_user` (`uploaded_by`);

--
-- Chỉ mục cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_project` (`project_id`),
  ADD KEY `idx_audit_entity` (`entity`,`entity_id`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `idx_comments_task` (`task_id`),
  ADD KEY `idx_comments_user` (`user_id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_notifications_type` (`type`);

--
-- Chỉ mục cho bảng `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`user_id`);

--
-- Chỉ mục cho bảng `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD UNIQUE KEY `uk_projects_code` (`project_code`),
  ADD KEY `idx_projects_creator` (`created_by`),
  ADD KEY `idx_projects_manager` (`manager_id`),
  ADD KEY `idx_projects_status_deleted` (`status`,`is_deleted`);

--
-- Chỉ mục cho bảng `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `idx_pm_user` (`user_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `uk_roles_code` (`role_code`);

--
-- Chỉ mục cho bảng `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `idx_tasks_project` (`project_id`),
  ADD KEY `idx_tasks_assignee` (`assignee_id`),
  ADD KEY `idx_tasks_creator` (`created_by`),
  ADD KEY `idx_tasks_approver` (`approved_by`),
  ADD KEY `idx_tasks_status_deleted` (`status`,`is_deleted`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uk_users_username` (`username`),
  ADD UNIQUE KEY `uk_users_email` (`email`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_status_deleted` (`status`,`is_deleted`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `attachments`
--
ALTER TABLE `attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT cho bảng `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `fk_attachments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attachments_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `fk_notification_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_projects_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `fk_pm_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tasks_assignee` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tasks_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
