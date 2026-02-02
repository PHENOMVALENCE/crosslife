<?php
/**
 * Student portal shared header – ELMS-style sidebar + top bar + content.
 * Expects: $pageTitle (string), $student (array from getCurrentStudent()), optional $breadcrumb (array of [label, url] or [label]).
 */
$siteName = defined('SITE_NAME') ? SITE_NAME : 'CrossLife';
$student = $student ?? getCurrentStudent();
$breadcrumb = $breadcrumb ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' – ' : ''; ?>School of Christ Academy – <?php echo htmlspecialchars($siteName); ?></title>
    <link href="../assets/img/logo.png" rel="icon">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <link href="../assets/css/elms-discipleship.css" rel="stylesheet">
    <link href="student-portal.css" rel="stylesheet">
</head>
<body class="student-portal elms-sidebar-layout">
    <div class="elms-sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>
    <aside class="elms-sidebar" id="elmsSidebar">
        <div class="p-3 border-bottom border-secondary">
            <a class="d-flex align-items-center text-decoration-none text-white" href="dashboard.php">
                <img src="../assets/img/logo.png" alt="" class="rounded me-2" width="36" height="36">
                <span class="fw-semibold">School of Christ Academy</span>
            </a>
        </div>
        <nav class="nav flex-column p-2">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) === 'account.php') ? 'active' : ''; ?>" href="account.php">
                <i class="bi bi-person"></i> Account
            </a>
        </nav>
        <div class="mt-auto p-2 border-top border-secondary">
            <a class="nav-link text-secondary" href="../discipleship.html"><i class="bi bi-info-circle"></i> About Discipleship</a>
            <a class="nav-link text-secondary" href="../index.html"><i class="bi bi-box-arrow-left"></i> Back to site</a>
        </div>
    </aside>
    <div class="elms-main-wrap">
        <header class="elms-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-link d-lg-none text-dark p-2" type="button" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <span class="text-secondary small d-none d-md-inline"><?php echo htmlspecialchars($student['full_name'] ?? 'Student'); ?></span>
            </div>
        </header>
        <div class="elms-content">
            <?php if (!empty($breadcrumb)): ?>
            <nav class="elms-breadcrumb-wrap d-flex justify-content-end">
                <ol class="breadcrumb">
                    <?php foreach ($breadcrumb as $i => $item): ?>
                    <li class="breadcrumb-item <?php echo ($i === count($breadcrumb) - 1 && (count($item) === 1 || empty($item[1]))) ? 'active' : ''; ?>">
                        <?php if ($i < count($breadcrumb) - 1 && !empty($item[1])): ?>
                            <a href="<?php echo htmlspecialchars($item[1]); ?>"><?php echo htmlspecialchars($item[0]); ?></a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($item[0]); ?>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <?php endif; ?>
            <?php $f = getFlashMessage(); if ($f): ?>
                <div class="alert alert-<?php echo $f['type'] === 'success' ? 'success' : ($f['type'] === 'danger' ? 'danger' : ($f['type'] === 'warning' ? 'warning' : 'info')); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($f['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
