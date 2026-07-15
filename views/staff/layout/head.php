<?php
/**
 * Shared <head> + opening wrapper for every Staff page.
 * Expects $pageTitle and $pageSubtitle to be set by the including view.
 */
$pageTitle    = $pageTitle ?? 'Staff Workspace';
$pageSubtitle = $pageSubtitle ?? '';
$staffCurrentPage = $_GET['page'] ?? 'staff-dashboard';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= staff_e($pageTitle) ?> - PMS Staff</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/staff/staff-app.css">
    <?php if (!empty($extraCss)): foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= staff_e($css) ?>">
    <?php endforeach; endif; ?>
</head>
<body class="staff-body">
<div class="staff-app">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="staff-main">
        <?php include __DIR__ . '/topbar.php'; ?>

        <main class="staff-content">
