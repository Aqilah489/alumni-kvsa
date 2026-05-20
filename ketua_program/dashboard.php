<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check login & role
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ketua_program') {
    header('Location: ../index.php');
    exit();
}

$page_title = 'Dashboard Ketua Program';
$program = $_SESSION['program'] ?? '';

// Helper function
function formatNama($nama) {
    return ucwords(strtolower(trim($nama)));
}

// ========== QUERIES ==========
$nama_program = $program;

// Total alumni (semua, tanpa status_hidup)
$stmt = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE program = ?");
$stmt->execute([$program]);
$total_alumni = $stmt->fetchColumn();

// Status Alumni (guna status_alumni)
$stmt = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE program = ? AND status_alumni = 'belum kemaskini'");
$stmt->execute([$program]);
$belum_kemaskini = $stmt->fetchColumn();

$telah_kemaskini = $total_alumni - $belum_kemaskini;
$peratus_kemaskini = ($total_alumni > 0) ? round($telah_kemaskini / $total_alumni * 100) : 0;

// Status Pekerjaan (guna status_alumni)
$stmt = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE program = ? AND status_alumni = 'bekerja'");
$stmt->execute([$program]);
$bekerja = $stmt->fetchColumn();

$stmt = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE program = ? AND status_alumni = 'sambung belajar'");
$stmt->execute([$program]);
$sambung_belajar = $stmt->fetchColumn();

$stmt = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE program = ? AND status_alumni = 'usahawan'");
$stmt->execute([$program]);
$usahawan = $stmt->fetchColumn();

$stmt = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE program = ? AND status_alumni = 'belum bekerja'");
$stmt->execute([$program]);
$belum_bekerja = $stmt->fetchColumn();

// Batch stats
$batch_stats = $connect->prepare("
    SELECT batch, COUNT(*) as total 
    FROM alumni 
    WHERE program = ? AND batch IS NOT NULL
    GROUP BY batch 
    ORDER BY batch DESC
");
$batch_stats->execute([$program]);
$batch_stats = $batch_stats->fetchAll();

$page_css = 'dashboard_kp';
include_once 'includes/header_kp.php';
?>

<!-- WELCOME BANNER -->
<div class="welcome-banner">
    <div class="welcome-text">
        <h2>Selamat Datang, <?= formatNama($_SESSION['nama'] ?? 'Ketua Program') ?>! 👋</h2>
        <p>Anda adalah Ketua Program untuk: <strong><?= htmlspecialchars($nama_program) ?></strong></p>
    </div>
    <div class="date-badge">
        <i class="bi bi-calendar3"></i>
        <?= date('d F Y') ?>
    </div>
</div>

<!-- STATS CARDS -->
<div class="stats-row">
    <div class="stat-box stat-total">
        <div class="stat-icon-bg"><i class="bi bi-people-fill"></i></div>
        <div class="stat-details">
            <span class="stat-label">TOTAL ALUMNI</span>
            <h2><?= $total_alumni ?></h2>
            <small>program <?= htmlspecialchars($nama_program) ?></small>
        </div>
    </div>
    
    <div class="stat-box stat-updated">
        <div class="stat-icon-bg"><i class="bi bi-check-circle-fill"></i></div>
        <div class="stat-details">
            <span class="stat-label">TELAH KEMASKINI</span>
            <h2><?= $telah_kemaskini ?></h2>
            <small><?= $peratus_kemaskini ?>% daripada total</small>
        </div>
    </div>
    
    <div class="stat-box stat-pending">
        <div class="stat-icon-bg"><i class="bi bi-clock-history"></i></div>
        <div class="stat-details">
            <span class="stat-label">BELUM KEMASKINI</span>
            <h2><?= $belum_kemaskini ?></h2>
            <small>perlu tindakan</small>
        </div>
    </div>
</div>

<!-- PROGRESS SECTION -->
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
            <span><?= $telah_kemaskini ?> alumni telah lengkapkan data</span>
        </div>
        <div class="detail-item">
            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
            <span><?= $belum_kemaskini ?> alumni belum kemaskini</span>
        </div>
    </div>
</div>

<!-- TWO COLUMNS -->
<div class="two-columns">
    <!-- Employment Card - 4 status pekerjaan -->
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

    <!-- Batch Summary Card -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h3><i class="bi bi-calendar-range-fill"></i> Ringkasan Batch</h3>
        </div>
        <div class="program-list-modern">
            <?php if(count($batch_stats) > 0): ?>
                <?php foreach($batch_stats as $batch): ?>
                <div class="employment-item">
                    <div class="employment-info">
                        <span>Batch <?= $batch['batch'] ?></span>
                        <span><?= $batch['total'] ?> alumni</span>
                    </div>
                    <div class="employment-bar">
                        <div class="employment-fill employ-fill-success" style="width: <?= ($total_alumni > 0) ? round($batch['total']/$total_alumni*100) : 0 ?>%"></div>
                    </div>
                    <div class="employment-percent"><?= ($total_alumni > 0) ? round($batch['total']/$total_alumni*100) : 0 ?>%</div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center py-3">Tiada batch untuk program ini</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- BATCH GRID -->
<div class="card-modern">
    <div class="card-header-modern">
        <h3><i class="bi bi-calendar-range-fill"></i> Taburan Mengikut Batch</h3>
    </div>
    <div class="batch-grid-modern">
        <?php if(count($batch_stats) > 0): ?>
            <?php foreach($batch_stats as $batch): ?>
            <div class="batch-card-modern">
                <div class="batch-year-modern"><?= $batch['batch'] ?></div>
                <div class="batch-name-modern"><?= htmlspecialchars($nama_program) ?></div>
                <div class="batch-count-modern"><?= $batch['total'] ?> Alumni</div>
                <div class="batch-progress-modern">
                    <div class="batch-progress-fill" style="width: <?= ($total_alumni > 0) ? round($batch['total']/$total_alumni*100) : 0 ?>%"></div>
                </div>
                <div class="batch-percent-modern"><?= ($total_alumni > 0) ? round($batch['total']/$total_alumni*100) : 0 ?>%</div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center text-muted p-4">Tiada data batch</div>
        <?php endif; ?>
    </div>
</div>

<!-- QUICK ACTIONS -->
<div class="quick-actions">
    <h4><i class="bi bi-lightning-charge"></i> Quick Actions</h4>
    <div class="action-grid">
        <a href="alumni/senarai.php" class="action-btn">
            <i class="bi bi-list-ul"></i> Senarai Alumni
        </a>
        <a href="laporan/laporan.php" class="action-btn">
            <i class="bi bi-file-earmark-text"></i> Laporan
        </a>
    </div>
</div>

<?php include_once 'includes/footer_kp.php'; ?>