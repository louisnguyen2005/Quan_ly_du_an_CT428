-- =============================================================
-- project_management.sql
-- Schema day du, hop nhat, cho database `project_management`.
--
-- File nay THAY THE 3 file cu:
--   - project_management.sql (ban dump goc, thieu cot approval/progress)
--   - database_migration_approval.sql (migration duyet task - da gop vao day)
--   - database_migration_staff_workflow.sql (migration cho ManagerTaskReviewModel/
--     StaffTaskModel - CODE CHET, khong con controller/route nao goi toi, nen KHONG
--     dua bang task_history va cac cot progress/started_at/submitted_at/reviewed_at/
--     reviewed_by/review_note/estimated_hours/'Paused' vao file nay nua)
--
-- Noi dung file nay phan anh dung schema ma code hien tai (models/task.php,
-- models/user.php, models/notification.php, views/manager/_layout.php,
-- views/dashboard/staff.php) thuc su can, bao gom ca cac cot/bang duoc code tu
-- dong ALTER/CREATE khi chay lan dau (ensureApprovalColumns, ensureProfileColumns,
-- ensurePreferencesTable...). Import file nay 1 lan la du, khong can chay them
-- ALTER thu cong nao khac.
-- =============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Co so du lieu: `project_management`
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Cau truc bang cho bang `attachments`
-- --------------------------------------------------------

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

INSERT INTO `attachments` (`attachment_id`, `task_id`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_by`, `uploaded_at`) VALUES
(1, 1, 'dashboard-wireframe.png', 'uploads/dashboard-wireframe.png', 'image/png', 248000, 1, '2026-06-27 12:48:12'),
(2, 4, 'responsive-checklist.pdf', 'uploads/responsive-checklist.pdf', 'application/pdf', 128000, 1, '2026-06-27 12:48:12');

-- --------------------------------------------------------
-- Cau truc bang cho bang `audit_logs`
-- --------------------------------------------------------

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

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `entity`, `entity_id`, `project_id`, `description`, `created_at`) VALUES
(1, 1, 'CREATE_TASK', 'tasks', 1, 1, 'Tao task Thiet ke Dashboard Staff', '2026-06-27 12:48:12'),
(2, NULL, 'UPDATE_STATUS', 'tasks', 2, 2, 'Chuyen task Chuan hoa co so du lieu sang Completed', '2026-06-27 12:48:12'),
(3, 1, 'SEND_NOTIFICATION', 'notifications', 1, 3, 'Gui thong bao nhiem vu moi cho staff01', '2026-06-27 12:48:12');

-- --------------------------------------------------------
-- Cau truc bang cho bang `comments`
-- --------------------------------------------------------

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `comments` (`comment_id`, `task_id`, `user_id`, `content`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Uu tien hien thi task qua han va viec can lam hom nay.', '2026-06-27 12:48:12', '2026-06-27 12:48:12'),
(3, 4, 1, 'Can kiem tra them phan tablet width 768px.', '2026-06-27 12:48:12', '2026-06-27 12:48:12');

-- --------------------------------------------------------
-- Cau truc bang cho bang `notifications`
-- --------------------------------------------------------

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

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `title`, `content`, `related_type`, `related_id`, `is_read`, `created_at`) VALUES
(15, 4, 'system', 'He thong bao tri', 'He thong se bao tri vao luc 23:00 hom nay.', 'system', NULL, 0, '2026-06-27 18:31:08'),
(16, 4, 'task', 'Co nhiem vu can kiem tra', 'Mot nhiem vu moi dang cho Admin kiem tra va phe duyet.', 'task', 1, 0, '2026-06-27 18:21:08'),
(17, 4, 'comment', 'Binh luan moi trong he thong', 'Co binh luan moi duoc gui trong mot nhiem vu can theo doi.', 'comment', 1, 1, '2026-06-27 18:01:08'),
(18, 1, 'task', 'Nhiem vu moi duoc tao', 'Ban da tao mot nhiem vu moi cho thanh vien trong du an.', 'task', 2, 0, '2026-06-27 17:31:08'),
(19, 1, 'system', 'Cap nhat tien do du an', 'Mot du an ban quan ly vua duoc cap nhat tien do moi.', 'project', 1, 0, '2026-06-27 16:31:08'),
(20, 1, 'comment', 'Staff gui bao cao tien do', 'Mot thanh vien da gui bao cao tien do cho nhiem vu duoc giao.', 'task', 3, 1, '2026-06-27 15:31:08'),
(21, 3, 'task', 'Ban co nhiem vu moi', 'Ban vua duoc giao mot nhiem vu moi. Hay kiem tra deadline va cap nhat tien do.', 'task', 4, 0, '2026-06-27 14:31:08'),
(22, 3, 'task', 'Nhiem vu sap den han', 'Mot nhiem vu cua ban sap den han hoan thanh. Vui long kiem tra va cap nhat trang thai.', 'task', 5, 0, '2026-06-27 13:31:08'),
(23, 3, 'comment', 'Manager phan hoi bao cao', 'Manager da phan hoi bao cao tien do cua ban.', 'comment', 2, 1, '2026-06-27 12:31:08'),
(24, 3, 'system', 'Cap nhat ho so thanh cong', 'Thong tin tai khoan cua ban da duoc cap nhat thanh cong.', 'system', NULL, 1, '2026-06-26 18:31:08');

-- --------------------------------------------------------
-- Cau truc bang cho bang `notification_preferences`
-- (truoc day chi duoc tu dong tao luc runtime boi models/notification.php /
-- views/manager/_layout.php, nay dua thang vao schema cho day du)
-- --------------------------------------------------------

CREATE TABLE `notification_preferences` (
  `user_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `task_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `comment_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `deadline_notifications` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notification_preferences` (`user_id`, `email_notifications`, `task_notifications`, `comment_notifications`, `deadline_notifications`) VALUES
(1, 1, 1, 1, 1),
(3, 1, 1, 1, 1),
(4, 1, 1, 1, 1),
(5, 1, 1, 1, 1);

-- --------------------------------------------------------
-- Cau truc bang cho bang `projects`
-- --------------------------------------------------------

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

INSERT INTO `projects` (`project_id`, `project_code`, `name`, `description`, `start_date`, `end_date`, `created_by`, `created_at`, `updated_at`, `is_deleted`, `status`, `priority`, `progress`, `manager_id`) VALUES
(1, 'PRJ-001', 'Website Quan Ly Du An', 'Xay dung he thong quan ly du an noi bo cho nhom phat trien.', '2026-06-01', '2026-08-01', 1, '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0, 'In Progress', 'High', 25, NULL),
(2, 'PRJ-002', 'Website Ban Hang', 'Xay dung website thuong mai dien tu voi phan he san pham, gio hang va thanh toan.', '2026-06-05', '2026-09-01', 1, '2026-06-27 12:48:12', '2026-06-27 13:11:38', 0, 'Planning', 'Medium', 80, 1),
(3, 'PRJ-003', 'Ung dung Bao Cao Tien Do', 'Dashboard tong hop hieu suat, tien do va canh bao deadline.', '2026-06-12', '2026-07-20', 1, '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0, 'Planning', 'High', 90, NULL),
(4, 'PRJ-20260628094514', 'ATTT', 'web secutiry lab xss, csrf, sqli, crypto, ss-hijacking, tunel networking', '2026-06-30', '2026-07-12', 1, '2026-06-28 07:45:14', '2026-06-28 07:45:14', 0, 'In Progress', 'Medium', 0, 1);

-- --------------------------------------------------------
-- Cau truc bang cho bang `project_members`
-- --------------------------------------------------------

CREATE TABLE `project_members` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_in_project` varchar(80) DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `project_members` (`project_id`, `user_id`, `role_in_project`, `joined_at`) VALUES
(1, 1, 'Manager', '2026-06-27 12:48:12'),
(1, 3, 'Backend Developer', '2026-06-27 12:48:12'),
(2, 1, 'Manager', '2026-06-27 12:48:12'),
(3, 1, 'Manager', '2026-06-27 12:48:12'),
(4, 1, 'Manager', '2026-06-28 07:45:14'),
(4, 3, 'Member', '2026-06-28 07:45:14'),
(4, 5, 'Member', '2026-06-28 07:45:14');

-- --------------------------------------------------------
-- Cau truc bang cho bang `roles`
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_code` varchar(30) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`role_id`, `role_code`, `name`, `description`, `created_at`) VALUES
(1, 'ADMIN', 'Admin', 'Quan tri toan he thong', '2026-06-27 12:48:12'),
(2, 'MANAGER', 'Manager', 'Quan ly du an va phan cong cong viec', '2026-06-27 12:48:12'),
(3, 'STAFF', 'Staff', 'Nhan su thuc hien nhiem vu', '2026-06-27 12:48:12');

-- --------------------------------------------------------
-- Cau truc bang cho bang `tasks`
-- Da gop day du cot cua luong duyet task (approval_status/approved_by/approved_at/
-- rejected_reason/progress_percent/progress_comment/progress_file) dung nhu
-- models/task.php::ensureApprovalColumns() tu dong tao ra.
-- --------------------------------------------------------

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Review','Completed','Pending Approval','Approved','Rejected') NOT NULL DEFAULT 'Pending',
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

INSERT INTO `tasks` (`task_id`, `project_id`, `title`, `description`, `status`, `approval_status`, `approved_by`, `approved_at`, `rejected_reason`, `progress_percent`, `progress_comment`, `progress_file`, `priority`, `assignee_id`, `created_by`, `due_date`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 'Thiet ke Dashboard Staff', 'Thiet ke giao dien tong quan cho nhan su theo phong cach dashboard hien co.', 'In Progress', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'High', NULL, 1, '2026-06-18', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(2, 1, 'Chuan hoa co so du lieu', 'Dong bo ten cot theo schema moi va kiem tra khoa ngoai.', 'Completed', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Medium', NULL, 1, '2026-06-16', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(3, 1, 'Tao API cap nhat trang thai task', 'Cho phep staff chuyen task sang Review hoac Completed.', 'Pending', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Critical', NULL, 1, '2026-06-20', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(4, 2, 'Responsive UI cho trang san pham', 'Toi uu layout mobile va tablet.', 'Review', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'High', NULL, 1, '2026-06-25', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(5, 2, 'Toi uu trang gio hang', 'Ra soat luong cap nhat so luong va tong tien.', 'Completed', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Low', NULL, 1, '2026-06-19', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(6, 3, 'Bieu do workload ca nhan', 'Hien thi so luong task mo theo tung du an.', 'Pending', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Medium', NULL, 1, '2026-06-22', '2026-06-27 12:48:12', '2026-06-27 18:16:03', 0),
(7, 4, 'var', 'inovar', 'In Progress', NULL, NULL, NULL, NULL, 0, NULL, NULL, 'Medium', 3, 1, '2026-06-29', '2026-06-28 08:01:05', '2026-06-28 08:01:05', 0);

-- --------------------------------------------------------
-- Cau truc bang cho bang `users`
-- Da gop day du cot phone/address (models/user.php::ensureProfileColumns()
-- tu dong tao ra) va status (ensureStatusColumn()).
-- --------------------------------------------------------

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

INSERT INTO `users` (`user_id`, `username`, `email`, `phone`, `address`, `password`, `role_id`, `status`, `refresh_token`, `token_expired_at`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 'manager', 'manager@gmail.com', NULL, NULL, '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 2, 'active', NULL, NULL, '2026-06-27 12:48:12', '2026-06-27 18:16:56', 0),
(3, 'staff02', 'staff02@gmail.com', NULL, NULL, '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 3, 'active', NULL, NULL, '2026-06-27 12:48:12', '2026-06-27 14:24:20', 0),
(4, 'admin', 'admin@gmail.com', NULL, NULL, '$2y$10$j9TFTCXf/vMyUtfsOV/MJO8rluzZlKwH9I5lQXlEtpY.k1vfjKjde', 1, 'active', NULL, NULL, '2026-06-27 12:52:21', '2026-06-27 18:15:54', 0),
(5, 'staff', 'staff@gmail.com', NULL, NULL, '$2y$10$j6G9LK3Ki1n8.X9YFAgr/eP35VgCOgMmG3GaGQl2QlHsbN1/OKmfK', 3, 'active', NULL, NULL, '2026-06-27 18:16:34', '2026-06-28 08:01:38', 0);

-- --------------------------------------------------------
-- Chi muc cho cac bang da do
-- --------------------------------------------------------

ALTER TABLE `attachments`
  ADD PRIMARY KEY (`attachment_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`user_id`);

ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD UNIQUE KEY `project_code` (`project_code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `manager_id` (`manager_id`);

ALTER TABLE `project_members`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_code` (`role_code`);

ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `assignee_id` (`assignee_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

-- --------------------------------------------------------
-- AUTO_INCREMENT cho cac bang da do
-- --------------------------------------------------------

ALTER TABLE `attachments`
  MODIFY `attachment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------
-- Cac rang buoc cho cac bang da do
-- --------------------------------------------------------

ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`task_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `fk_notification_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `tasks_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
