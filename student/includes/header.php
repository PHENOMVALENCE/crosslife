<?php
/**
 * Student portal shared header – nav, flash, open main.
 * Expects: $pageTitle (string), $student (array from getCurrentStudent()).
 */
$siteName = defined('SITE_NAME') ? SITE_NAME : 'CrossLife';
$student = $student ?? getCurrentStudent();
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
    <link href="student-portal.css" rel="stylesheet">
</head>
<body class="student-portal">
    <header class="student-header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                    <img src="../assets/img/logo.png" alt="" class="student-logo me-2" width="36" height="36">
                    <span>School of Christ Academy</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav" aria-controls="studentNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="studentNav">
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><i class="bi bi-grid-1x2 me-1"></i>Dashboard</a>
                        </li>
                        <?php if ($student): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="account.php"><i class="bi bi-person-circle me-1"></i>Account</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="d-none d-md-inline me-1"><?php echo htmlspecialchars($student['full_name']); ?></span>
                                <i class="bi bi-person-fill"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                                <li><span class="dropdown-item-text text-muted small"><?php echo htmlspecialchars($student['email']); ?></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="account.php"><i class="bi bi-person me-2"></i>My account</a></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="student-main">
        <div class="container py-4">
            <?php $f = getFlashMessage(); if ($f): ?>
                <div class="alert alert-<?php echo $f['type'] === 'success' ? 'success' : ($f['type'] === 'danger' ? 'danger' : ($f['type'] === 'warning' ? 'warning' : 'info')); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($f['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
