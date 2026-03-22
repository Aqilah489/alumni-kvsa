<?php
// includes/header.php
if(!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Dashboard' ?> - Alumni KVSA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../css/dashboard_kaunseling.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/header_footer.css?v=<?= time() ?>">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header text-center">
        <i class="bi bi-mortarboard"></i>
        <h3>Alumni KVSA</h3>
        <p>Kaunseling Panel</p>
    </div>
    
    <nav class="nav-menu">
        <div class="nav-group">
            <label>MAIN</label>
            <a href="dashboard_kaunseling.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard_kaunseling.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="alumni.php" class="<?= basename($_SERVER['PHP_SELF']) == 'alumni.php' ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Alumni
            </a>
            <a href="program.php" class="<?= basename($_SERVER['PHP_SELF']) == 'program.php' ? 'active' : '' ?>">
                <i class="bi bi-book"></i> Program
            </a>
            <a href="batch.php" class="<?= basename($_SERVER['PHP_SELF']) == 'batch.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar"></i> Batch
            </a>
        </div>
        
        <div class="nav-group">
            <label>MANAGEMENT</label>
            <a href="ketua_program.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ketua_program.php' ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i> Ketua Program
            </a>
        </div>
        
        <div class="nav-group">
            <label>REPORTS</label>
            <a href="laporan.php" class="<?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>">
                <i class="bi bi-file-text"></i> Laporan
            </a>
        </div>
        
        <div class="nav-group">
            <label>ACCOUNT</label>
            <a href="profil.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i> Profil
            </a>
            <a href="../logout.php">
                <i class="bi bi-box-arrow-right"></i> Log Keluar
            </a>
        </div>
    </nav>
</aside>

<!-- Header Tanpa Notifikasi -->
<header class="dashboard-header">
    <div class="header-left">
        <button class="menu-toggle-btn" id="menuToggle">
            <i class="bi bi-list"></i>
        </button>
        <div class="header-logo">
            <img src="../images/kvsa-logo.png" alt="KVSA Logo" class="logo-img" onerror="this.src='https://via.placeholder.com/40'">
            <div class="header-title">
                <h4>Kolej Vokasional Shah Alam</h4>
                <p>Sistem Penjejakan Alumni | <span class="badge-role">Kaunseling</span></p>
            </div>
        </div>
    </div>
    <div class="header-right">
        <!-- User Dropdown Sahaja - Tanpa Notifikasi -->
        <div class="user-dropdown" id="userDropdown">
            <div class="user-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="user-info-header">
                <span class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Kaunseling') ?></span>
                <span class="user-role">Kaunseling Panel</span>
            </div>
            <i class="bi bi-chevron-down"></i>
        </div>
    </div>
</header>

<main class="main-content"></main>