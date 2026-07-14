<?php
// 1. ĐỌC CẤU HÌNH TỪ COOKIE (Nếu chưa có thì đặt mặc định là 'light' và 'vi')
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
$current_lang = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : 'vi';

// 2. KHỞI TẠO TỪ ĐIỂN ĐA NGÔN NGỮ
$translations = [
    'vi' => [
        'sys_settings'   => 'Cài đặt hệ thống',
        'settings_desc'  => 'Quản lý thông báo, giao diện và bảo mật tài khoản',
        'notifications'  => 'Thông báo',
        'email_notif'    => 'Email thông báo',
        'email_desc'     => 'Nhận thông báo qua email',
        'appearance'     => 'Giao diện',
        'display_mode'   => 'Chế độ hiển thị',
        'language'       => 'Ngôn ngữ',
        'security'       => 'Bảo mật',
        'curr_password'  => 'Mật khẩu hiện tại',
        'new_password'   => 'Mật khẩu mới',
        'conf_password'  => 'Xác nhận mật khẩu mới',
        'btn_change_pwd' => 'Đổi mật khẩu',
        'task_manage'    => 'Task Management',
        'task_desc'      => 'Quản lý nhiệm vụ dự án'
    ],
    'en' => [
        'sys_settings'   => 'System Settings',
        'settings_desc'  => 'Manage notifications, appearance, and account security',
        'notifications'  => 'Notifications',
        'email_notif'    => 'Email Notifications',
        'email_desc'     => 'Receive notifications via email',
        'appearance'     => 'Appearance',
        'display_mode'   => 'Display Mode',
        'language'       => 'Language',
        'security'       => 'Security',
        'curr_password'  => 'Current Password',
        'new_password'   => 'New Password',
        'conf_password'  => 'Confirm New Password',
        'btn_change_pwd' => 'Change Password',
        'task_manage'    => 'Task Management',
        'task_desc'      => 'Project task management'
    ]
];

// Lấy bộ ngôn ngữ hiện tại để dùng cho các trang
$lang = $translations[$current_lang];
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>PMS - Project Management System</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/users.css">
    <link rel="stylesheet" href="assets/css/projects.css">
    <link rel="stylesheet" href="assets/css/tasks.css">
    <link rel="stylesheet" href="assets/css/roles.css">
    <link rel="stylesheet" href="assets/css/modal.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="assets/css/settings.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="<?= $theme === 'dark' ? 'dark-mode' : '' ?>">
<div class="app">
<?php include __DIR__ . '/sidebar.php'; ?>
<?php include __DIR__ . '/navbar.php'; ?>