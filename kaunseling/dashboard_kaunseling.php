<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check login & role
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../index.php');
    exit();
}

$page_title = 'Dashboard';

// Get filter program
$filter_program = $_GET['program'] ?? '';

// ========== QUERIES ==========
// Total alumni
$total_alumni = $connect->query("SELECT COUNT(*) FROM alumni")->fetchColumn();

// Status belum kemaskini
$belum_kemaskini = $connect->query("SELECT COUNT(*) FROM alumni WHERE status_alumni = 'belum kemaskini'")->fetchColumn();

// Status TELAH KEMASKINI = semua yang bukan 'belum kemaskini'
$telah_kemaskini = $total_alumni - $belum_kemaskini;
$peratus_kemaskini = ($total_alumni > 0) ? round($telah_kemaskini / $total_alumni * 100) : 0;

// Status Pekerjaan (dari status_alumni)
$bekerja = $connect->query("SELECT COUNT(*) FROM alumni WHERE status_alumni = 'bekerja'")->fetchColumn();
$sambung_belajar = $connect->query("SELECT COUNT(*) FROM alumni WHERE status_alumni = 'sambung_belajar'")->fetchColumn();
$usahawan = $connect->query("SELECT COUNT(*) FROM alumni WHERE status_alumni = 'usahawan'")->fetchColumn();
$belum_bekerja = $connect->query("SELECT COUNT(*) FROM alumni WHERE status_alumni = 'belum_bekerja'")->fetchColumn();

// Program stats
$program_stats = $connect->query("
    SELECT program, COUNT(*) as total 
    FROM alumni 
    WHERE program IS NOT NULL AND program != ''
    GROUP BY program 
    ORDER BY total DESC
")->fetchAll();

// Batch stats with filter
$batch_sql = "
    SELECT batch, COUNT(*) as total 
    FROM alumni 
    WHERE batch IS NOT NULL";
if($filter_program) {
    $batch_sql .= " AND program = '" . addslashes($filter_program) . "'";
}
$batch_sql .= " GROUP BY batch ORDER BY batch DESC";
$batch_stats = $connect->query($batch_sql)->fetchAll();

// Get programs for filter
$programs = $connect->query("
    SELECT DISTINCT program
    FROM alumni 
    WHERE program IS NOT NULL AND program != ''
    ORDER BY program
")->fetchAll();

// Set defaults
if(!$bekerja) $bekerja = 0;
if(!$sambung_belajar) $sambung_belajar = 0;
if(!$usahawan) $usahawan = 0;
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
<?php include_once 'includes/sidebar.php'; ?>

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

    <!-- Stats Cards - GUNA BOOTSTRAP GRID -->
<div class="row justify-content-center">
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
        <div class="stat-box stat-total h-100">
            <div class="stat-icon-bg"><i class="bi bi-people-fill"></i></div>
            <div class="stat-details">
                <span class="stat-label">Jumlah Alumni</span>
                <h2><?= $total_alumni ?></h2>
                <small>keseluruhan</small>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
        <div class="stat-box stat-updated h-100">
            <div class="stat-icon-bg"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-details">
                <span class="stat-label">Telah Kemaskini</span>
                <h2><?= $telah_kemaskini ?></h2>
                <small><?= $peratus_kemaskini ?>% daripada total</small>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
        <div class="stat-box stat-pending h-100">
            <div class="stat-icon-bg"><i class="bi bi-clock-history"></i></div>
            <div class="stat-details">
                <span class="stat-label">Belum Kemaskini</span>
                <h2><?= $belum_kemaskini ?></h2>
                <small>perlu tindakan</small>
            </div>
        </div>
    </div>
</div>

    <!-- Progress Card -->
    <div class="progress-section">
        <div class="progress-header">
            <h3><i class="bi bi-graph-up"></i> Status Kemaskini Alumni</h3>
            <span class="progress-badge"><?= $peratus_kemaskini ?>% Lengkap</span>
        </div>
        <!-- Dalam HTML -->
        <div class="progress-bar-custom">
            <div class="progress-fill-custom <?= ($peratus_kemaskini < 15) ? 'small-percent' : '' ?>" 
                style="width: <?= $peratus_kemaskini ?>%">
                <span><?= $peratus_kemaskini ?>%</span>
            </div>
        </div>
        
        <div class="progress-details">
            <div class="detail-item">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span><?= $telah_kemaskini ?> alumni telah lengkapkan data</span>
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
                        <span>Sambung Belajar</span>
                        <span><?= $sambung_belajar ?> alumni</span>
                    </div>
                    <div class="employment-bar">
                        <div class="employment-fill employ-fill-info" style="width: <?= ($total_alumni > 0) ? round($sambung_belajar/$total_alumni*100) : 0 ?>%"></div>
                    </div>
                    <div class="employment-percent"><?= ($total_alumni > 0) ? round($sambung_belajar/$total_alumni*100) : 0 ?>%</div>
                </div>
                <div class="employment-item">
                    <div class="employment-info">
                        <span>Usahawan</span>
                        <span><?= $usahawan ?> alumni</span>
                    </div>
                    <div class="employment-bar">
                        <div class="employment-fill employ-fill-warning" style="width: <?= ($total_alumni > 0) ? round($usahawan/$total_alumni*100) : 0 ?>%"></div>
                    </div>
                    <div class="employment-percent"><?= ($total_alumni > 0) ? round($usahawan/$total_alumni*100) : 0 ?>%</div>
                </div>
                <div class="employment-item">
                    <div class="employment-info">
                        <span>Belum Bekerja</span>
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
                        <span><?= htmlspecialchars($program['program']) ?></span>
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

    <!-- Batch Card with Filter -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h3><i class="bi bi-calendar-range-fill"></i> Taburan Mengikut Batch</h3>
            <div class="filter-select" style="width: auto;">
                <select id="programFilter" onchange="filterBatch()" class="form-select" style="padding: 5px 10px; font-size: 0.75rem;">
                    <option value="">Semua Program</option>
                    <?php foreach($programs as $p): ?>
                    <option value="<?= htmlspecialchars($p['program']) ?>" <?= $filter_program == $p['program'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['program']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="batch-grid-modern">
            <?php if(count($batch_stats) > 0): ?>
                <?php foreach($batch_stats as $batch): ?>
                <div class="batch-card-modern">
                    <div class="batch-year-modern"><?= $batch['batch'] ?></div>
                    <div class="batch-name-modern"><?= htmlspecialchars($filter_program ?: 'Semua Program') ?></div>
                    <div class="batch-count-modern"><?= $batch['total'] ?> Alumni</div>
                    <div class="batch-progress-modern">
                        <div class="batch-progress-fill" style="width: <?= ($total_alumni > 0) ? round($batch['total']/$total_alumni*100) : 0 ?>%"></div>
                    </div>
                    <div class="batch-percent-modern"><?= ($total_alumni > 0) ? round($batch['total']/$total_alumni*100) : 0 ?>%</div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-4">Tiada data batch</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h4><i class="bi bi-lightning-charge"></i> Quick Actions</h4>
        <div class="action-grid">
            <button class="action-btn" onclick="window.location.href='alumni/tambah.php'">
                <i class="bi bi-person-plus-fill"></i> Tambah Alumni
            </button>
            <button class="action-btn" onclick="window.location.href='ketua_program/tambah.php'">
                <i class="bi bi-person-badge-plus"></i> Tambah Ketua Program
            </button>
            <button class="action-btn" onclick="window.location.href='alumni/import.php'">
                <i class="bi bi-file-excel"></i> Import Alumni
            </button>
            <button class="action-btn" onclick="window.location.href='alumni/export.php'">
                <i class="bi bi-download"></i> Export Alumni
            </button>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <i class="bi bi-c-circle"></i> <?= date('Y') ?> Kolej Vokasional Shah Alam. Hak Cipta Terpelihara.
    </div>
</main>

<script>
function filterBatch() {
    let program = document.getElementById('programFilter').value;
    window.location.href = 'dashboard_kaunseling.php?program=' + encodeURIComponent(program);
}

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