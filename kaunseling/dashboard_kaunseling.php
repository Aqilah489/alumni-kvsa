<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check login & role
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../index.php');
    exit();
}

$page_title = 'Dashboard';

// ========== QUERIES ==========
$total_alumni = $connect->query("SELECT COUNT(*) FROM alumni")->fetchColumn();
$total_program = $connect->query("SELECT COUNT(*) FROM program")->fetchColumn();
$belum_kemaskini = $connect->query("SELECT COUNT(*) FROM alumni WHERE status_kemaskini = 'belum'")->fetchColumn();
$telat_kemaskini = $total_alumni - $belum_kemaskini;
$peratus_kemaskini = ($total_alumni > 0) ? round($telat_kemaskini / $total_alumni * 100) : 0;

// Statistics
$bekerja = $connect->query("SELECT COUNT(*) FROM alumni WHERE pekerjaan IS NOT NULL AND pekerjaan != ''")->fetchColumn();
$belum_bekerja = $total_alumni - $bekerja;

$program_stats = $connect->query("SELECT p.nama_program, COUNT(a.alumni_id) as total FROM program p LEFT JOIN alumni a ON p.kod_program = a.kod_program GROUP BY p.kod_program ORDER BY total DESC")->fetchAll();
$batch_stats = $connect->query("SELECT b.nama_batch, b.tahun_grad, COUNT(a.alumni_id) as total FROM batch b LEFT JOIN alumni a ON b.id = a.batch_id GROUP BY b.id ORDER BY b.tahun_grad DESC")->fetchAll();

// Set defaults
if(!$bekerja) $bekerja = 0;
if(!$belum_bekerja) $belum_bekerja = 0;
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kaunseling - Alumni KVSA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard_kaunseling.css">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header text-center">
        <i class="bi bi-mortarboard"></i>
        <h3>Alumni KVSA</h3>
        <p>Kaunseling Panel</p>
    </div>
    
    <nav>
        <div class="nav-group">
            <label>MAIN</label>
            <a href="#" class="active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="alumni.php">
                <i class="bi bi-people"></i> Alumni
            </a>
            <a href="program/senarai.php">
                <i class="bi bi-book"></i> Program
            </a>
            <a href="batch.php">
                <i class="bi bi-calendar"></i> Batch
            </a>
        </div>
        
        <div class="nav-group">
            <label>MANAGEMENT</label>
            <a href="ketua_program.php">
                <i class="bi bi-person-badge"></i> Ketua Program
            </a>
        </div>
        
        <div class="nav-group">
            <label>REPORTS</label>
            <a href="laporan.php">
                <i class="bi bi-file-text"></i> Laporan
            </a>
        </div>
        
        <div class="nav-group">
            <label>ACCOUNT</label>
            <a href="profil.php">
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
                <p>Sistem Penjejakan Alumni | Kaunseling</p>
            </div>
        </div>
        <div class="user-area">
            <span><?= htmlspecialchars($_SESSION['nama'] ?? 'Kaunseling') ?></span>
            <i class="bi bi-person-circle"></i>
        </div>
    </div>
    
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Kaunseling') ?>! 👋</h2>
            <p>Berikut adalah ringkasan data alumni KVSA</p>
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
                <span class="stat-label">Jumlah Alumni</span>
                <h2><?= $total_alumni ?></h2>
                <small>keseluruhan</small>
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

        <!-- Program Card -->
        <div class="card-modern">
            <div class="card-header-modern">
                <h3><i class="bi bi-mortarboard-fill"></i> Taburan Program</h3>
            </div>
            <div class="program-list-modern">
                <?php foreach(array_slice($program_stats, 0, 5) as $program): ?>
                <div class="program-item-modern">
                    <div class="program-info">
                        <span><?= htmlspecialchars($program['nama_program']) ?></span>
                        <span><?= $program['total'] ?> orang</span>
                    </div>
                    <div class="program-bar-modern">
                        <div class="program-fill-modern" style="width: <?= ($total_alumni > 0) ? round($program['total']/$total_alumni*100) : 0 ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Batch Card -->
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
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h4><i class="bi bi-lightning-charge"></i> Quick Actions</h4>
        <div class="action-grid">
            <button class="action-btn" onclick="window.location.href='tambah_alumni.php'">
                <i class="bi bi-person-plus-fill"></i> Tambah Alumni
            </button>
            <button class="action-btn" onclick="window.location.href='tambah_kp.php'">
                <i class="bi bi-person-badge-plus"></i> Tambah Ketua Program
            </button>
            <button class="action-btn" onclick="window.location.href='tambah_program.php'">
                <i class="bi bi-book-plus"></i> Tambah Program
            </button>
            <button class="action-btn" onclick="window.location.href='tambah_batch.php'">
                <i class="bi bi-calendar-plus"></i> Tambah Batch
            </button>
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