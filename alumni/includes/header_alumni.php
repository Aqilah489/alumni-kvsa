<?php
// header_alumni.php - Header untuk alumni module

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
    
    <?php
    if(isset($page_css)): ?>
        <link rel="stylesheet" href="/Sistem%20Penjejakan%20Alumni/css/<?= $page_css ?>.css?v=<?= time() ?>">
    <?php else: ?>
        <link rel="stylesheet" href="/Sistem%20Penjejakan%20Alumni/css/dashboard_alumni.css?v=<?= time() ?>">
    <?php endif; ?>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-header">
        <i class="bi bi-mortarboard"></i>
        <h3>Alumni KVSA</h3>
        <p>Alumni Panel</p>
    </div>
    
    <div class="nav-group">
        <label>MAIN</label>
        <a href="/Sistem%20Penjejakan%20Alumni/alumni/profil.php">
            <i class="bi bi-person-circle"></i> Profil
        </a>
    </div>
    
    <div class="nav-group">
        <label>ACCOUNT</label>
    
        <a href="/Sistem%20Penjejakan%20Alumni/logout.php">
            <i class="bi bi-box-arrow-right"></i> Log Keluar
        </a>
    </div>
</div>

<!-- MAIN CONTENT OPENING TAG -->
<main class="main-content">
    
    <!-- HEADER -->
    <div class="simple-header">
        <div class="logo-area">
            <i class="bi bi-mortarboard"></i>
            <div>
                <h4>Kolej Vokasional Shah Alam</h4>
                <p>Sistem Penjejakan Alumni | Alumni</p>
            </div>
        </div>
        <div class="user-area">
            <span><?= htmlspecialchars($_SESSION['nama'] ?? 'Alumni') ?></span>
            <i class="bi bi-person-circle"></i>
        </div>
    </div>