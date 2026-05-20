<?php
// sidebar.php - Kaunseling module
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
            <a href="/Sistem Penjejakan Alumni/kaunseling/dashboard_kaunseling.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="/Sistem Penjejakan Alumni/kaunseling/alumni/senarai.php">
                <i class="bi bi-people"></i> Alumni
            </a>
        </div>
        
        <div class="nav-group">
            <label>MANAGEMENT</label>
            <a href="/Sistem Penjejakan Alumni/kaunseling/ketua_program/senarai.php">
                <i class="bi bi-person-badge"></i> Ketua Program
            </a>
        </div>
        
        <div class="nav-group">
            <label>REPORTS</label>
            <a href="/Sistem Penjejakan Alumni/kaunseling/laporan/laporan.php">
                <i class="bi bi-file-text"></i> Laporan
            </a>
        </div>
        
        <div class="nav-group">
            <label>ACCOUNT</label>
            <a href="/Sistem Penjejakan Alumni/kaunseling/profil.php">
                <i class="bi bi-person-circle"></i> Profil
            </a>
            <a href="/Sistem Penjejakan Alumni/logout.php" onclick="return confirm('Anda pasti mahu log keluar?')">
                <i class="bi bi-box-arrow-right"></i> Log Keluar
            </a>
        </div>
    </nav>
</aside>