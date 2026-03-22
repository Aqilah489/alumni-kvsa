<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check login & role
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../index.php');
    exit();
}

// ========== QUERY SEDIA ADA ==========
$total_alumni = $connect->query("SELECT COUNT(*) FROM alumni")->fetchColumn();
$total_program = $connect->query("SELECT COUNT(*) FROM program")->fetchColumn();
$total_batch = $connect->query("SELECT COUNT(*) FROM batch")->fetchColumn();

// Alumni belum kemaskini
$belum_kemaskini = $connect->query("SELECT COUNT(*) FROM alumni WHERE status_kemaskini = 'belum'")->fetchColumn();
$telat_kemaskini = $total_alumni - $belum_kemaskini;
$peratus_kemaskini = ($total_alumni > 0) ? round($telat_kemaskini / $total_alumni * 100) : 0;

// Alumni terkini (5 orang terbaru)
$alumni_terkini = $connect->query("
    SELECT a.*, p.nama_program, b.nama_batch 
    FROM alumni a
    JOIN program p ON a.kod_program = p.kod_program
    JOIN batch b ON a.batch_id = b.id
    ORDER BY a.created_at DESC 
    LIMIT 5
")->fetchAll();

// ========== STATISTIK BARU ==========

// 1. Statistik Pekerjaan (guna field pekerjaan)
$bekerja = $connect->query("SELECT COUNT(*) FROM alumni WHERE pekerjaan IS NOT NULL AND pekerjaan != ''")->fetchColumn();
$belum_bekerja = $total_alumni - $bekerja;

// 2. Alumni baru bulan ini (guna created_at)
$alumni_bulan_ini = $connect->query("
    SELECT COUNT(*) FROM alumni 
    WHERE MONTH(created_at) = MONTH(CURDATE()) 
    AND YEAR(created_at) = YEAR(CURDATE())
")->fetchColumn();

// 3. Statistik mengikut program (guna kod_program)
$program_stats = $connect->query("
    SELECT p.nama_program, COUNT(a.alumni_id) as total
    FROM program p
    LEFT JOIN alumni a ON p.kod_program = a.kod_program
    GROUP BY p.kod_program
    ORDER BY total DESC
")->fetchAll();

// 4. Statistik mengikut batch (guna batch_id)
$batch_stats = $connect->query("
    SELECT b.nama_batch, b.tahun_grad, COUNT(a.alumni_id) as total
    FROM batch b
    LEFT JOIN alumni a ON b.id = a.batch_id
    GROUP BY b.id
    ORDER BY b.tahun_grad DESC
")->fetchAll();

// 5. Statistik Julat Gaji (guna julat_gaji)
$gaji_stats = $connect->query("
    SELECT julat_gaji, COUNT(*) as total
    FROM alumni 
    WHERE julat_gaji IS NOT NULL AND julat_gaji != ''
    GROUP BY julat_gaji
    ORDER BY total DESC
")->fetchAll();

// Set default value
if(!$alumni_bulan_ini) $alumni_bulan_ini = 0;
if(!$bekerja) $bekerja = 0;
if(!$belum_bekerja) $belum_bekerja = 0;
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kaunseling - Alumni KVSA</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/dashboard_kaunseling.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../css/header.css?v=<?= time() ?>">
    
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            <a href="#" class="active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="alumni.php">
                <i class="bi bi-people"></i> Alumni
            </a>
            <a href="program.php">
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
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="page-title">
            <h2>Dashboard</h2>
            <p>Selamat Datang ke Panel Kaunseling</p>
        </div>
        <div class="user-info">
            <span><?= htmlspecialchars($_SESSION['nama'] ?? 'Kaunseling') ?></span>
            <i class="bi bi-person-circle"></i>
        </div>
    </div>
    
    <!-- ========== DESIGN BARU ========== -->
    <div class="dashboard-container">
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

        <!-- Stats Cards Row 1 - Main Metrics -->
        <div class="stats-row">
            <div class="stat-box stat-total">
                <div class="stat-icon-bg">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-details">
                    <span class="stat-label">Total Alumni</span>
                    <h2><?= $total_alumni ?></h2>
                    <small>keseluruhan</small>
                </div>
            </div>
            
            <div class="stat-box stat-updated">
                <div class="stat-icon-bg">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-details">
                    <span class="stat-label">Telah Kemaskini</span>
                    <h2><?= $telat_kemaskini ?></h2>
                    <small><?= $peratus_kemaskini ?>% daripada total</small>
                </div>
            </div>
            
            <div class="stat-box stat-pending">
                <div class="stat-icon-bg">
                    <i class="bi bi-clock-history"></i>
                </div>
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

        <!-- Stats Row 2 - Employment & Program -->
        <div class="two-columns">
            <!-- Employment Card -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="bi bi-briefcase-fill"></i> Status Pekerjaan</h3>
                    <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" title="Alumni yang telah mengisi maklumat pekerjaan"></i>
                </div>
                <div class="employment-stats">
                    <div class="employment-item">
                        <div class="employment-info">
                            <span class="employment-label">Bekerja</span>
                            <span class="employment-value"><?= $bekerja ?> alumni</span>
                        </div>
                        <div class="employment-bar">
                            <div class="employment-fill employ-fill-success" style="width: <?= ($total_alumni > 0) ? round($bekerja/$total_alumni*100) : 0 ?>%"></div>
                        </div>
                        <span class="employment-percent"><?= ($total_alumni > 0) ? round($bekerja/$total_alumni*100) : 0 ?>%</span>
                    </div>
                    <div class="employment-item">
                        <div class="employment-info">
                            <span class="employment-label">Belum Bekerja / Tidak Diketahui</span>
                            <span class="employment-value"><?= $belum_bekerja ?> alumni</span>
                        </div>
                        <div class="employment-bar">
                            <div class="employment-fill employ-fill-secondary" style="width: <?= ($total_alumni > 0) ? round($belum_bekerja/$total_alumni*100) : 0 ?>%"></div>
                        </div>
                        <span class="employment-percent"><?= ($total_alumni > 0) ? round($belum_bekerja/$total_alumni*100) : 0 ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Program Distribution Card -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="bi bi-mortarboard-fill"></i> Taburan Program</h3>
                    <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" title="Bilangan alumni mengikut program"></i>
                </div>
                <div class="program-list-modern">
                    <?php if(count($program_stats) > 0): ?>
                        <?php foreach(array_slice($program_stats, 0, 4) as $program): ?>
                        <div class="program-item-modern">
                            <div class="program-info">
                                <span class="program-name-modern"><?= htmlspecialchars($program['nama_program']) ?></span>
                                <span class="program-count-modern"><?= $program['total'] ?> orang</span>
                            </div>
                            <div class="program-bar-modern">
                                <div class="program-fill-modern" style="width: <?= ($total_alumni > 0) ? round($program['total']/$total_alumni*100) : 0 ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(count($program_stats) > 4): ?>
                        <div class="view-more">
                            <a href="program.php">+<?= count($program_stats) - 4 ?> program lagi</a>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">Tiada data program</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Batch Distribution -->
        <div class="card-modern full-width">
            <div class="card-header-modern">
                <h3><i class="bi bi-calendar-range-fill"></i> Taburan Mengikut Batch</h3>
                <i class="bi bi-info-circle tooltip-icon" data-bs-toggle="tooltip" title="Alumni mengikut tahun graduasi"></i>
            </div>
            <div class="batch-grid-modern">
                <?php if(count($batch_stats) > 0): ?>
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
                <?php else: ?>
                    <div class="text-center text-muted py-3">Tiada data batch</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h4><i class="bi bi-lightning-charge"></i> Quick Actions</h4>
        <div class="action-grid">
            <button class="action-btn" onclick="window.location.href='tambah_alumni.php'">
                <i class="bi bi-person-plus-fill"></i> Tambah Alumni Baru
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
</main>

<!-- ========== SCRIPTS ========== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        }
    });
    
    // Action functions
    function viewAlumni(id) {
        window.location.href = 'view_alumni.php?id=' + id;
    }
    
    function editAlumni(id) {
        window.location.href = 'edit_alumni.php?id=' + id;
    }
    
    function messageAlumni(id) {
        window.location.href = 'message_alumni.php?id=' + id;
    }
    
    // Optional: Search function if needed
    function searchAlumni() {
        let input = document.getElementById('searchInput');
        if(input) {
            let filter = input.value.toUpperCase();
            let table = document.getElementById('alumniTable');
            if(table) {
                let rows = table.getElementsByTagName('tr');
                for(let i = 0; i < rows.length; i++) {
                    let name = rows[i].getElementsByTagName('td')[1];
                    let matrix = rows[i].getElementsByTagName('td')[0];
                    if(name || matrix) {
                        let textValue = (name ? name.textContent : '') + (matrix ? matrix.textContent : '');
                        rows[i].style.display = textValue.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
                    }
                }
            }
        }
    }
</script>
</body>
</html>
</body>
</html>