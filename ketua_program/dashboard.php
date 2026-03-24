<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check login & role
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ketua_program') {
    header('Location: ../index.php');
    exit();
}

$page_title = 'Dashboard Ketua Program';
$kod_program = $_SESSION['kod_program'] ?? '';

// ========== QUERIES ==========
// Nama program
$stmt = $connect->prepare("SELECT nama_program FROM program WHERE kod_program = ?");
$stmt->execute([$kod_program]);
$nama_program = $stmt->fetchColumn();

// Statistik untuk program ini
$stmt = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE kod_program = ?");
$stmt->execute([$kod_program]);
$total_alumni = $stmt->fetchColumn();

$stmt = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE kod_program = ? AND status_kemaskini = 'belum'");
$stmt->execute([$kod_program]);
$belum_kemaskini = $stmt->fetchColumn();

$telat_kemaskini = $total_alumni - $belum_kemaskini;
$peratus_kemaskini = ($total_alumni > 0) ? round($telat_kemaskini / $total_alumni * 100) : 0;

// Status pekerjaan
$stmt = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE kod_program = ? AND (pekerjaan IS NOT NULL AND pekerjaan != '')");
$stmt->execute([$kod_program]);
$bekerja = $stmt->fetchColumn();

$belum_bekerja = $total_alumni - $bekerja;

// Batch untuk program ini
$batch_stats = $connect->prepare("
    SELECT b.nama_batch, b.tahun_grad, COUNT(a.alumni_id) as total 
    FROM batch b 
    LEFT JOIN alumni a ON b.id = a.batch_id 
    WHERE b.kod_program = ? 
    GROUP BY b.id 
    ORDER BY b.tahun_grad DESC
");
$batch_stats->execute([$kod_program]);
$batch_stats = $batch_stats->fetchAll();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ketua Program - Alumni KVSA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard_kp.css"
    
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header text-center">
        <i class="bi bi-mortarboard"></i>
        <h3>Alumni KVSA</h3>
        <p>Ketua Program Panel</p>
    </div>
    
    <nav>
        <div class="nav-group">
            <label>MAIN</label>
            <a href="#" class="active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="alumni/index.php">
                <i class="bi bi-people"></i> Alumni
            </a>
            <a href="batch/index.php">
                <i class="bi bi-calendar"></i> Batch
            </a>
        </div>
        
        <div class="nav-group">
            <label>REPORTS</label>
            <a href="laporan/index.php">
                <i class="bi bi-file-text"></i> Laporan
            </a>
        </div>
        
        <div class="nav-group">
            <label>ACCOUNT</label>
            <a href="profile.php">
                <i class="bi bi-person-circle"></i> Profil
            </a>
            <a href="../logout.php">
                <i class="bi bi-box-arrow-right"></i> Log Keluar
            </a>
        </div>
    </nav>
</aside>

<!-- Main Content -->
<main class="main-content">
    <!-- Header Simple -->
    <div class="simple-header">
        <div class="logo-area">
            <i class="bi bi-mortarboard"></i>
            <div>
                <h4>Kolej Vokasional Shah Alam</h4>
                <p>Sistem Penjejakan Alumni | Ketua Program</p>
            </div>
        </div>
        <div class="user-area">
            <span><?= htmlspecialchars($_SESSION['nama'] ?? 'Ketua Program') ?></span>
            <i class="bi bi-person-circle"></i>
        </div>
    </div>
    
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Ketua Program') ?>! 👋</h2>
            <p>Anda adalah Ketua Program untuk: <strong><?= htmlspecialchars($nama_program) ?></strong></p>
        </div>
        <div class="date-badge">
            <i class="bi bi-calendar3"></i>
            <?= date('d F Y') ?>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
        <div class="stat-box stat-total">
            <div class="stat-icon-bg"><i class="bi bi-people-fill"></i></div>
            <div class="stat-details">
                <span class="stat-label">Total Alumni</span>
                <h2><?= $total_alumni ?></h2>
                <small>program <?= htmlspecialchars($nama_program) ?></small>
            </div>
        </div>
        
        <div class="stat-box stat-updated">
            <div class="stat-icon-bg"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-details">
                <span class="stat-label">Telah Kemaskini</span>
                <h2><?= $telat_kemaskini ?></h2>
                <small><?= $peratus_kemaskini ?>% daripada total</small>
            </div>
        </div>
        
        <div class="stat-box stat-pending">
            <div class="stat-icon-bg"><i class="bi bi-clock-history"></i></div>
            <div class="stat-details">
                <span class="stat-label">Belum Kemaskini</span>
                <h2><?= $belum_kemaskini ?></h2>
                <small>perlu tindakan</small>
            </div>
        </div>
    </div>

    <!-- Progress Card -->
    <div class="progress-section">
        <div class="progress-header">
            <h3><i class="bi bi-graph-up"></i> Status Kemaskini Alumni</h3>
            <span class="progress-badge"><?= $peratus_kemaskini ?>% Lengkap</span>
        </div>
        <div class="progress-bar-custom">
            <div class="progress-fill-custom" style="width: <?= $peratus_kemaskini ?>%">
                <span><?= $peratus_kemaskini ?>%</span>
            </div>
        </div>
        <div class="progress-details">
            <div class="detail-item">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span><?= $telat_kemaskini ?> alumni telah lengkapkan data</span>
            </div>
            <div class="detail-item">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                <span><?= $belum_kemaskini ?> alumni belum kemaskini</span>
            </div>
        </div>
    </div>

    <!-- Two Columns -->
    <div class="two-columns">
        <!-- Employment Card -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h3><i class="bi bi-briefcase-fill"></i> Status Pekerjaan</h3>
            </div>
            <div class="employment-stats">
                <div class="employment-item">
                    <div class="employment-info">
                        <span>Bekerja</span>
                        <span><?= $bekerja ?> alumni</span>
                    </div>
                    <div class="employment-bar">
                        <div class="employment-fill employ-fill-success" style="width: <?= ($total_alumni > 0) ? round($bekerja/$total_alumni*100) : 0 ?>%"></div>
                    </div>
                    <div class="employment-percent"><?= ($total_alumni > 0) ? round($bekerja/$total_alumni*100) : 0 ?>%</div>
                </div>
                <div class="employment-item">
                    <div class="employment-info">
                        <span>Belum Bekerja / Tidak Diketahui</span>
                        <span><?= $belum_bekerja ?> alumni</span>
                    </div>
                    <div class="employment-bar">
                        <div class="employment-fill employ-fill-secondary" style="width: <?= ($total_alumni > 0) ? round($belum_bekerja/$total_alumni*100) : 0 ?>%"></div>
                    </div>
                    <div class="employment-percent"><?= ($total_alumni > 0) ? round($belum_bekerja/$total_alumni*100) : 0 ?>%</div>
                </div>
            </div>
        </div>

        <!-- Program Info Card (replace with batch summary) -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h3><i class="bi bi-calendar-range-fill"></i> Ringkasan Batch</h3>
            </div>
            <div class="program-list-modern">
                <?php foreach($batch_stats as $batch): ?>
                <div class="program-item-modern">
                    <div class="program-info">
                        <span><?= htmlspecialchars($batch['nama_batch']) ?></span>
                        <span><?= $batch['total'] ?> alumni</span>
                    </div>
                    <div class="program-bar-modern">
                        <div class="program-fill-modern" style="width: <?= ($total_alumni > 0) ? round($batch['total']/$total_alumni*100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(count($batch_stats) == 0): ?>
                <p class="text-muted text-center">Tiada batch untuk program ini</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Batch Card with more details -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h3><i class="bi bi-calendar-range-fill"></i> Taburan Mengikut Batch</h3>
        </div>
        <div class="batch-grid-modern">
            <?php foreach($batch_stats as $batch): ?>
            <div class="batch-card-modern">
                <div class="batch-year-modern"><?= $batch['tahun_grad'] ?? 'Tidak Diketahui' ?></div>
                <div class="batch-name-modern"><?= htmlspecialchars($batch['nama_batch']) ?></div>
                <div class="batch-count-modern"><?= $batch['total'] ?> Alumni</div>
                <div class="batch-progress-modern">
                    <div class="batch-progress-fill" style="width: <?= ($total_alumni > 0) ? round($batch['total']/$total_alumni*100) : 0 ?>%"></div>
                </div>
                <div class="batch-percent-modern"><?= ($total_alumni > 0) ? round($batch['total']/$total_alumni*100) : 0 ?>%</div>
            </div>
            <?php endforeach; ?>
            <?php if(count($batch_stats) == 0): ?>
            <div class="text-center text-muted p-4">Tiada data batch</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h4><i class="bi bi-lightning-charge"></i> Quick Actions</h4>
        <div class="action-grid">
            <a href="alumni/tambah.php" class="action-btn">
                <i class="bi bi-person-plus-fill"></i> Tambah Alumni
            </a>
            <a href="alumni/index.php" class="action-btn">
                <i class="bi bi-list-ul"></i> Senarai Alumni
            </a>
            <a href="batch/index.php" class="action-btn">
                <i class="bi bi-calendar-check"></i> Urus Batch
            </a>
            <a href="laporan/index.php" class="action-btn">
                <i class="bi bi-file-earmark-text"></i> Laporan
            </a>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <i class="bi bi-c-circle"></i> <?= date('Y') ?> Kolej Vokasional Shah Alam. Hak Cipta Terpelihara.
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar on mobile
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }
    
    // Add menu button for mobile
    const header = document.querySelector('.simple-header');
    if (header && window.innerWidth <= 768) {
        const menuBtn = document.createElement('button');
        menuBtn.innerHTML = '<i class="bi bi-list"></i>';
        menuBtn.className = 'menu-toggle-btn';
        menuBtn.style.cssText = 'background:none;border:none;font-size:1.5rem;color:var(--primary);cursor:pointer;margin-right:10px;';
        menuBtn.onclick = toggleSidebar;
        header.insertBefore(menuBtn, header.firstChild);
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const isMobile = window.innerWidth <= 768;
        if (isMobile && sidebar && !sidebar.contains(event.target) && !event.target.closest('.menu-toggle-btn')) {
            sidebar.classList.remove('active');
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('active');
        }
    });
</script>
</body>
</html>