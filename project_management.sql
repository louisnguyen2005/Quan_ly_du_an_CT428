-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 28, 2026 lúc 10:40 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `project_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `project_management`;
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
  `file_size` int(11) DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `attachments`
--

INSERT INTO `attachments` (`attachment_id`, `task_id`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_by`, `uploaded_at`) VALUES
(1, 1, 'dashboard-wireframe.png', 'uploads/dashboard-wireframe.png', 'image/png', 248000, 1, '2026-06-27 12:48:12'),
(2, 4, 'responsive-checklist.pdf', 'uploads/responsive-checklist.pdf', 'application/pdf', 128000, 1, '2026-06-27 12:48:12');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `entity`, `entity_id`, `project_id`, `description`, `created_at`) VALUES
(1, 1, 'CREATE_TASK', 'tasks', 1, 1, 'Tạo task Thiết kế Dashboard Staff', '2026-06-27 12:48:12'),
(2, NULL, 'UPDATE_STATUS', 'tasks', 2, 2, 'Chuyển task Chuẩn hóa cơ sở dữ liệu sang Completed', '2026-06-27 12:48:12'),
(3, 1, 'SEND_NOTIFICATION', 'notifications', 1, 3, 'Gửi thông báo nhiệm vụ mới cho staff01', '2026-06-27 12:48:12');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `comments`
--

INSERT INTO `comments` (`comment_id`, `task_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Ưu tiên hiển thị task quá hạn và việc cần làm hôm nay.', '2026-06-27 12:48:12', '2026-06-27 12:48:12'),
(3, 4, 1, 'Cần kiểm tra thêm phần tablet width 768px.', '2026-06-27 12:48:12', '2026-06-27 12:48:12');

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
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `title`, `content`, `related_type`, `related_id`, `is_read`, `created_at`) VALUES
(15, 4, 'system', 'Hệ thống bảo trì', 'Hệ thống sẽ bảo trì vào lúc 23:00 hôm nay.', 'system', NULL, 0, '2026-06-27 18:31:08'),
(16, 4, 'task', 'Có nhiệm vụ cần kiểm tra', 'Một nhiệm vụ mới đang chờ Admin kiểm tra và phê duyệt.', 'task', 1, 0, '2026-06-27 18:21:08'),
(17, 4, 'comment', 'Bình luận mới trong hệ thống', 'Có bình luận mới được gửi trong một nhiệm vụ cần theo dõi.', 'comment', 1, 1, '2026-06-27 18:01:08'),
(18, 1, 'task', 'Nhiệm vụ mới được tạo', 'Bạn đã tạo một nhiệm vụ mới cho thành viên trong dự án.', 'task', 2, 0, '2026-06-27 17:31:08'),
(19, 1, 'system', 'Cập nhật tiến độ dự án', 'Một dự án bạn quản lý vừa được cập nhật tiến độ mới.', 'project', 1, 0, '2026-06-27 16:31:08'),
(20, 1, 'comment', 'Staff gửi báo cáo tiến độ', 'Một thành viên đã gửi báo cáo tiến độ cho nhiệm vụ được giao.', 'task', 3, 1, '2026-06-27 15:31:08'),
(21, 3, 'task', 'Bạn có nhiệm vụ mới', 'Bạn vừa được giao một nhiệm vụ mới. Hãy kiểm tra deadline và cập nhật tiến độ.', 'task', 4, 0, '2026-06-27 14:31:08'),
(22, 3, 'task', 'Nhiệm vụ sắp đến hạn', 'Một nhiệm vụ của bạn sắp đến hạn hoàn thành. Vui lòng kiểm tra và cập nhật trạng thái.', 'task', 5, 0, '2026-06-27 13:31:08'),
(23, 3, 'comment', 'Manager phản hồi báo cáo', 'Manager đã phản hồi báo cáo tiến độ của bạn.', 'comment', 2, 1, '2026-06-27 12:31:08'),
(24, 3, 'system', 'Cập nhật hồ sơ thành công', 'Thông tin tài khoản của bạn đã được cập nhật thành công.', 'system', NULL, 1, '2026-06-26 18:31:08');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dữ liệu mặc định cho bảng `notification_preferences`
--

INSERT INTO `notification_preferences` (`user_id`, `email_notifications`, `task_notifications`, `comment_notifications`, `deadline_notifications`) VALUES
(1, 1, 1, 1, 1),
(3, 1, 1, 1, 1),
(4, 1, 1, 1, 1),
(5, 1, 1, 1, 1);

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
  `is_deleted` tinyint(1) DEFAULT 0,
  `status` varchar(50) DEFAULT NULL,
  `priority` varchar(50) DEFAULT NULL,
  `progress` int(11) DEFAULT 0,
  `manager_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `projects`
--

INSERT INTO `projects` (`project_id`, `project_code`, `name`, `description`, `start_date`, `end_date`, `created_by`, `created_at`, `updated_at`, `is_deleted`, `status`, `priority`, `progress`, `manager_id`) VALUES
(1, 'PRJ-001', 'Website Quản Lý Dự Án', 'Xây dựng hệ thống quản lý dự án nội bộ cho nhóm phát triển.', '2026-06-01', '2026-08-01', 1, '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0, 'In Progress', 'High', 25, NULL),
(2, 'PRJ-002', 'Website Bán Hàng', 'Xây dựng website thương mại điện tử với phân hệ sản phẩm, giỏ hàng và thanh toán.', '2026-06-05', '2026-09-01', 1, '2026-06-27 12:48:12', '2026-06-27 13:11:38', 0, 'Planning', 'Medium', 80, 1),
(3, 'PRJ-003', 'Ứng dụng Báo Cáo Tiến Độ', 'Dashboard tổng hợp hiệu suất, tiến độ và cảnh báo deadline.', '2026-06-12', '2026-07-20', 1, '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0, 'Planning', 'High', 90, NULL),
(4, 'PRJ-20260628094514', 'ATTT', 'web secutiry lab xss, csrf, sqli, crypto, ss-hijacking, tunel networking', '2026-06-30', '2026-07-12', 1, '2026-06-28 07:45:14', '2026-06-28 07:45:14', 0, 'In Progress', 'Medium', 0, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `project_members`
--

CREATE TABLE `project_members` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_in_project` varchar(80) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `project_members`
--

INSERT INTO `project_members` (`project_id`, `user_id`, `role_in_project`, `joined_at`) VALUES
(1, 1, 'Manager', '2026-06-27 12:48:12'),
(1, 3, 'Backend Developer', '2026-06-27 12:48:12'),
(2, 1, 'Manager', '2026-06-27 12:48:12'),
(3, 1, 'Manager', '2026-06-27 12:48:12'),
(4, 1, 'Manager', '2026-06-28 07:45:14'),
(4, 3, 'Member', '2026-06-28 07:45:14'),
(4, 5, 'Member', '2026-06-28 07:45:14');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `roles`
--

INSERT INTO `roles` (`role_id`, `role_code`, `name`, `description`, `created_at`) VALUES
(1, 'ADMIN', 'Admin', 'Quản trị toàn hệ thống', '2026-06-27 12:48:12'),
(2, 'MANAGER', 'Manager', 'Quản lý dự án và phân công công việc', '2026-06-27 12:48:12'),
(3, 'STAFF', 'Staff', 'Nhân sự thực hiện nhiệm vụ', '2026-06-27 12:48:12');

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
  `approval_status` varchar(30) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejected_reason` text DEFAULT NULL,
  `progress_percent` int(11) NOT NULL DEFAULT 0,
  `progress_comment` text DEFAULT NULL,
  `progress_file` varchar(500) DEFAULT NULL,
  `priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `assignee_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tasks`
--

INSERT INTO `tasks` (`task_id`, `project_id`, `title`, `description`, `status`, `approval_status`, `approved_by`, `approved_at`, `rejected_reason`, `progress_percent`, `progress_comment`, `progress_file`, `priority`, `assignee_id`, `created_by`, `due_date`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 'Thiết kế Dashboard Staff', 'Thiết kế giao diện tổng quan cho nhân sự theo phong cách dashboard hiện có.', 'In Progress', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'High', NULL, 1, '2026-06-18', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(2, 1, 'Chuẩn hóa cơ sở dữ liệu', 'Đồng bộ tên cột theo schema mới và kiểm tra khóa ngoại.', 'Completed', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Medium', NULL, 1, '2026-06-16', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(3, 1, 'Tạo API cập nhật trạng thái task', 'Cho phép staff chuyển task sang Review hoặc Completed.', 'Pending', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Critical', NULL, 1, '2026-06-20', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(4, 2, 'Responsive UI cho trang sản phẩm', 'Tối ưu layout mobile và tablet.', 'Review', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'High', NULL, 1, '2026-06-25', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(5, 2, 'Tối ưu trang giỏ hàng', 'Rà soát luồng cập nhật số lượng và tổng tiền.', 'Completed', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Low', NULL, 1, '2026-06-19', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(6, 3, 'Biểu đồ workload cá nhân', 'Hiển thị số lượng task mở theo từng dự án.', 'Pending', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Medium', NULL, 1, '2026-06-22', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(7, 4, 'var', 'inovar', 'In Progress', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Medium', 3, 1, '2026-06-29', '2026-06-28 08:01:05', '2026-06-28 08:01:05', 0);

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
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `phone`, `address`, `password`, `role_id`, `status`, `refresh_token`, `token_expired_at`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 'manager', 'manager@gmail.com', NULL, NULL, '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 2, 'active', NULL, NULL, '2026-06-27 12:48:12', '2026-06-27 18:16:56', 0),
(3, 'staff02', 'staff02@gmail.com', NULL, NULL, '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-27 12:48:12', '2026-06-27 14:24:20', 0),
(4, 'admin', 'admin@gmail.com', NULL, NULL, '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 1, 'active', NULL, NULL, '2026-06-27 12:52:21', '2026-06-27 18:15:54', 0),
(5, 'staff', 'staff@gmail.com', NULL, NULL, '$2y$10$j6G9LK3Ki1n8.X9YFAgr/eP35VgCOgMmG3GaGQl2QlHsbN1/OKmfK', 3, 'active', NULL, NULL, '2026-06-27 18:16:34', '2026-06-28 08:01:38', 0);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Chỉ mục cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD UNIQUE KEY `project_code` (`project_code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Chỉ mục cho bảng `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_code` (`role_code`);

--
-- Chỉ mục cho bảng `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `assignee_id` (`assignee_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `attachments`
--
ALTER TABLE `attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `tasks_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
