<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$currentAdmin = getCurrentAdmin();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Admin Panel - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicons -->
    <link href="../assets/img/logo.png" rel="icon">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    
    <!-- Vendor CSS Files -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Main CSS File -->
    <link href="../assets/css/main.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 70px;
        }
        
        body {
            background: #f5f5f5;
            font-family: var(--default-font);
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #000000 0%, #1a1715 100%);
            color: var(--contrast-color);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-header img {
            width: 60px;
            height: 60px;
            margin-bottom: 0.5rem;
            border-radius: 8px;
        }
        
        .sidebar-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            color: var(--contrast-color);
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .menu-item {
            display: block;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: rgba(200, 87, 22, 0.1);
            color: var(--contrast-color);
            border-left-color: var(--accent-color);
        }
        
        .menu-item.active {
            background: rgba(200, 87, 22, 0.2);
            color: var(--accent-color);
            border-left-color: var(--accent-color);
            font-weight: 600;
        }
        
        .menu-item i {
            width: 24px;
            margin-right: 0.75rem;
        }
        
        .menu-section {
            padding: 1rem 1.5rem 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
        }
        
        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .admin-header {
            background: var(--surface-color);
            height: var(--header-height);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .admin-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: var(--heading-color);
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .admin-user-info {
            text-align: right;
        }
        
        .admin-user-name {
            font-weight: 600;
            color: var(--heading-color);
            font-size: 0.95rem;
        }
        
        .admin-user-role {
            font-size: 0.85rem;
            color: var(--default-color);
            opacity: 0.7;
        }
        
        .btn-logout {
            padding: 0.5rem 1rem;
            background: var(--accent-color);
            color: var(--contrast-color);
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: color-mix(in srgb, var(--accent-color), black 10%);
            color: var(--contrast-color);
        }
        
        .admin-content {
            padding: 2rem;
        }
        
        .card {
            background: var(--surface-color);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: none;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background: transparent;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            color: var(--heading-color);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }
        
        .alert-info {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border-left: 4px solid #0d6efd;
        }
        
        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border-left: 4px solid #ffc107;
        }
        
        .alert .btn-close {
            padding: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../assets/img/logo.png" alt="CrossLife">
                <h3>Admin Panel</h3>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-section">Main</div>
                <a href="index.php" class="menu-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
                
                <div class="menu-section">Content</div>
                <a href="sermons.php" class="menu-item <?php echo $currentPage === 'sermons.php' ? 'active' : ''; ?>">
                    <i class="bi bi-headphones"></i>Audio Sermons
                </a>
                <a href="events.html" class="menu-item <?php echo $currentPage === 'events.html' ? 'active' : ''; ?>">
                    <i class="bi bi-calendar-event"></i>Events
                </a>
                <a href="ministries.php" class="menu-item <?php echo $currentPage === 'ministries.php' ? 'active' : ''; ?>">
                    <i class="bi bi-building"></i>Ministries
                </a>
                <a href="discipleship.php" class="menu-item <?php echo $currentPage === 'discipleship.php' ? 'active' : ''; ?>">
                    <i class="bi bi-mortarboard"></i>Discipleship
                </a>
                <a href="leadership.php" class="menu-item <?php echo $currentPage === 'leadership.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people"></i>Leadership
                </a>
                
                <div class="menu-section">Communications</div>
                <a href="contacts.html" class="menu-item <?php echo $currentPage === 'contacts.html' ? 'active' : ''; ?>">
                    <i class="bi bi-envelope"></i>Contact Inquiries
                </a>
                <a href="prayer-requests.php" class="menu-item <?php echo $currentPage === 'prayer-requests.php' ? 'active' : ''; ?>">
                    <i class="bi bi-pray"></i>Prayer Requests
                </a>
                <a href="feedback.php" class="menu-item <?php echo $currentPage === 'feedback.php' ? 'active' : ''; ?>">
                    <i class="bi bi-chat-left-text"></i>Feedback
                </a>
                
                <div class="menu-section">System</div>
                <a href="settings.php" class="menu-item <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                    <i class="bi bi-gear"></i>Settings
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="bi bi-box-arrow-right"></i>Logout
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
                <div class="admin-user">
                    <div class="admin-user-info">
                        <div class="admin-user-name"><?php echo htmlspecialchars($currentAdmin['full_name']); ?></div>
                        <div class="admin-user-role"><?php echo ucfirst(str_replace('_', ' ', $currentAdmin['role'])); ?></div>
                    </div>
                    <a href="logout.php" class="btn-logout">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </div>
            </header>
            
            <div class="admin-content">
                <?php
                $flash = getFlashMessage();
                if ($flash):
                    $alertClass = 'alert-' . $flash['type'];
                    $iconClass = 'bi-';
                    switch ($flash['type']) {
                        case 'success':
                            $iconClass .= 'check-circle-fill';
                            break;
                        case 'danger':
                            $iconClass .= 'exclamation-triangle-fill';
                            break;
                        case 'warning':
                            $iconClass .= 'exclamation-circle-fill';
                            break;
                        case 'info':
                            $iconClass .= 'info-circle-fill';
                            break;
                        default:
                            $iconClass .= 'info-circle-fill';
                    }
                ?>
                    <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show" role="alert">
                        <i class="bi <?php echo $iconClass; ?> me-2"></i>
                        <?php echo htmlspecialchars($flash['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

