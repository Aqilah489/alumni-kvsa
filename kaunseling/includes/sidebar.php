<?php
// sidebar.php - Sidebar untuk kaunseling module
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<aside class="sidebar">
    <div class="sidebar-header text-center">
        <i class="bi bi-mortarboard"></i>
        <h3>Alumni KVSA</h3>
        <p>Kaunseling Panel</p>
    </div>
    
    <nav>
        <div class="nav-group">
            <label>MAIN</label>
            <a href="../dashboard_kaunseling.php" class="<?= $current_page == 'dashboard_kaunseling.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="../alumni/index.php" class="<?= $current_dir == 'alumni' ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Alumni
            </a>
            <a href="../program/senarai.php" class="<?= $current_dir == 'program' ? 'active' : '' ?>">
                <i class="bi bi-book"></i> Program
            </a>
            <a href="../batch/index.php" class="<?= $current_dir == 'batch' ? 'active' : '' ?>">
                <i class="bi bi-calendar"></i> Batch
            </a>
        </div>
        
        <div class="nav-group">
            <label>MANAGEMENT</label>
            <a href="../ketua_program/index.php">
                <i class="bi bi-person-badge"></i> Ketua Program
            </a>
        </div>
        
        <div class="nav-group">
            <label>REPORTS</label>
            <a href="../laporan/index.php">
                <i class="bi bi-file-text"></i> Laporan
            </a>
        </div>
        
        <div class="nav-group">
            <label>ACCOUNT</label>
            <a href="../profile.php">
                <i class="bi bi-person-circle"></i> Profil
            </a>
            <a href="../../logout.php">
                <i class="bi bi-box-arrow-right"></i> Log Keluar
            </a>
        </div>
    </nav>
</aside>