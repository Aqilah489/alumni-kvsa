<?php
session_start();
require_once __DIR__ . '/../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../index.php');
    exit();
}

// Helper function untuk format text
function formatNama($nama) {
    return ucwords(strtolower(trim($nama)));
}

function formatProgram($program) {
    return ucwords(strtolower(trim($program)));
}

function formatNoMatrix($no_matrix) {
    return strtoupper(trim($no_matrix));
}

// Get filter
$filter_program = $_GET['program'] ?? '';
$filter_batch = $_GET['batch'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build WHERE clause for filtering
$where_clause = "WHERE 1=1";
$stats_params = [];

if($filter_program) {
    $where_clause .= " AND program = ?";
    $stats_params[] = $filter_program;
}
if($filter_batch) {
    $where_clause .= " AND batch = ?";
    $stats_params[] = $filter_batch;
}
if($filter_status) {
    $where_clause .= " AND status_alumni = ?";
    $stats_params[] = $filter_status;
}

// Statistics by status_alumni (with filters)
$stats_query = "SELECT 
    SUM(CASE WHEN status_alumni = 'bekerja' THEN 1 ELSE 0 END) as bekerja,
    SUM(CASE WHEN status_alumni = 'sambung belajar' THEN 1 ELSE 0 END) as sambung_belajar,
    SUM(CASE WHEN status_alumni = 'usahawan' THEN 1 ELSE 0 END) as usahawan,
    SUM(CASE WHEN status_alumni = 'belum bekerja' THEN 1 ELSE 0 END) as belum_bekerja,
    SUM(CASE WHEN status_alumni = 'belum kemaskini' THEN 1 ELSE 0 END) as belum_kemaskini,
    COUNT(*) as total
    FROM alumni $where_clause";

$stmt_stats = $connect->prepare($stats_query);
$stmt_stats->execute($stats_params);
$stats_row = $stmt_stats->fetch(PDO::FETCH_ASSOC);

$stats_bekerja = $stats_row['bekerja'] ?? 0;
$stats_sambung_belajar = $stats_row['sambung_belajar'] ?? 0;
$stats_usahawan = $stats_row['usahawan'] ?? 0;
$stats_belum_bekerja = $stats_row['belum_bekerja'] ?? 0;
$stats_belum_kemaskini = $stats_row['belum_kemaskini'] ?? 0;
$total_alumni = $stats_row['total'] ?? 0;

// Gaji stats for chart (with filters)
$gaji_sql = "SELECT julat_gaji, COUNT(*) as total 
    FROM alumni $where_clause 
    AND julat_gaji IS NOT NULL AND julat_gaji != '' 
    GROUP BY julat_gaji 
    ORDER BY total DESC";
$stmt_gaji = $connect->prepare($gaji_sql);
$stmt_gaji->execute($stats_params);
$gaji_stats = $stmt_gaji->fetchAll();

// Program stats for chart (with filters)
$program_sql = "SELECT program, COUNT(*) as total 
    FROM alumni $where_clause 
    AND program IS NOT NULL 
    GROUP BY program 
    ORDER BY total DESC";
$stmt_program = $connect->prepare($program_sql);
$stmt_program->execute($stats_params);
$program_stats = $stmt_program->fetchAll();

// Batch stats for chart (with filters)
$batch_sql = "SELECT batch, COUNT(*) as total 
    FROM alumni $where_clause 
    AND batch IS NOT NULL 
    GROUP BY batch 
    ORDER BY batch DESC";
$stmt_batch = $connect->prepare($batch_sql);
$stmt_batch->execute($stats_params);
$batch_stats = $stmt_batch->fetchAll();

// Get programs for filter
$programs = $connect->query("SELECT DISTINCT program FROM alumni WHERE program IS NOT NULL ORDER BY program")->fetchAll();
$batches = $connect->query("SELECT DISTINCT batch FROM alumni WHERE batch IS NOT NULL ORDER BY batch DESC")->fetchAll();

// Get alumni data for lists (with filters)
$sql = "SELECT a.* FROM alumni a WHERE 1=1";
$params = [];

if($filter_program) {
    $sql .= " AND a.program = ?";
    $params[] = $filter_program;
}
if($filter_batch) {
    $sql .= " AND a.batch = ?";
    $params[] = $filter_batch;
}
if($filter_status) {
    $sql .= " AND a.status_alumni = ?";
    $params[] = $filter_status;
}
$sql .= " ORDER BY a.created_at DESC";

$stmt = $connect->prepare($sql);
$stmt->execute($params);
$all_alumni = $stmt->fetchAll();

// Group alumni by status
$alumni_by_status = [
    'bekerja' => array_filter($all_alumni, function($a) { return $a['status_alumni'] == 'bekerja'; }),
    'sambung belajar' => array_filter($all_alumni, function($a) { return $a['status_alumni'] == 'sambung belajar'; }),
    'usahawan' => array_filter($all_alumni, function($a) { return $a['status_alumni'] == 'usahawan'; }),
    'belum bekerja' => array_filter($all_alumni, function($a) { return $a['status_alumni'] == 'belum bekerja'; }),
    'belum kemaskini' => array_filter($all_alumni, function($a) { return $a['status_alumni'] == 'belum kemaskini'; })
];

$page_title = 'Laporan Status Alumni';
$page_css = 'alumni';

include_once '../includes/header.php';
?>

<style>
/* Section headers */
.section-header {
    padding: 12px 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    margin-top: 25px;
}
.section-header h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}
.section-header i {
    margin-right: 8px;
}
.bg-bekerja { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
.bg-sambung { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
.bg-usahawan { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
.bg-belum-bekerja { background: #e2e3e5; color: #383d41; border-left: 4px solid #6c757d; }
.bg-belum-kemaskini { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
</style>

<div class="page-header">
    <h2><i class="bi bi-file-text"></i> Laporan Status Alumni</h2>
    <div>
        <button class="btn-add" onclick="window.print()">
            <i class="bi bi-printer"></i> Muat Turun
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="summary-status">
    <div class="status-card status-bekerja">
        <div class="status-icon"><i class="bi bi-briefcase-fill"></i></div>
        <div class="status-info">
            <h3><?= $stats_bekerja ?></h3>
            <p>Bekerja</p>
            <small><?= $total_alumni > 0 ? round($stats_bekerja/$total_alumni*100) : 0 ?>%</small>
        </div>
        <a href="?status=bekerja" class="status-link">Lihat →</a>
    </div>
    <div class="status-card status-sambung-belajar">
        <div class="status-icon"><i class="bi bi-book-fill"></i></div>
        <div class="status-info">
            <h3><?= $stats_sambung_belajar ?></h3>
            <p>Sambung Belajar</p>
            <small><?= $total_alumni > 0 ? round($stats_sambung_belajar/$total_alumni*100) : 0 ?>%</small>
        </div>
        <a href="?status=sambung%20belajar" class="status-link">Lihat →</a>
    </div>
    <div class="status-card status-usahawan">
        <div class="status-icon"><i class="bi bi-shop"></i></div>
        <div class="status-info">
            <h3><?= $stats_usahawan ?></h3>
            <p>Usahawan</p>
            <small><?= $total_alumni > 0 ? round($stats_usahawan/$total_alumni*100) : 0 ?>%</small>
        </div>
        <a href="?status=usahawan" class="status-link">Lihat →</a>
    </div>
    <div class="status-card status-belum-bekerja">
        <div class="status-icon"><i class="bi bi-question-circle-fill"></i></div>
        <div class="status-info">
            <h3><?= $stats_belum_bekerja ?></h3>
            <p>Belum Bekerja</p>
            <small><?= $total_alumni > 0 ? round($stats_belum_bekerja/$total_alumni*100) : 0 ?>%</small>
        </div>
        <a href="?status=belum%20bekerja" class="status-link">Lihat →</a>
    </div>
    <div class="status-card status-belum-kemaskini">
        <div class="status-icon"><i class="bi bi-clock-history"></i></div>
        <div class="status-info">
            <h3><?= $stats_belum_kemaskini ?></h3>
            <p>Belum Kemaskini</p>
            <small><?= $total_alumni > 0 ? round($stats_belum_kemaskini/$total_alumni*100) : 0 ?>%</small>
        </div>
        <a href="?status=belum%20kemaskini" class="status-link">Lihat →</a>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-container">
    <div class="filter-header">
        <i class="bi bi-funnel"></i>
        <span>Filter Laporan</span>
    </div>
    <div class="filter-row">
        <div class="filter-group">
            <label><i class="bi bi-mortarboard"></i> Program</label>
            <select id="programFilter" class="filter-select">
                <option value="">Semua Program</option>
                <?php foreach($programs as $p): ?>
                <option value="<?= htmlspecialchars($p['program']) ?>" <?= $filter_program == $p['program'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(formatProgram($p['program'])) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="bi bi-calendar"></i> Batch</label>
            <select id="batchFilter" class="filter-select">
                <option value="">Semua Batch</option>
                <?php foreach($batches as $b): ?>
                <option value="<?= $b['batch'] ?>" <?= $filter_batch == $b['batch'] ? 'selected' : '' ?>>
                    <?= $b['batch'] ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="bi bi-bar-chart"></i> Status</label>
            <select id="statusFilter" class="filter-select">
                <option value="">Semua Status</option>
                <option value="bekerja" <?= $filter_status == 'bekerja' ? 'selected' : '' ?>>Bekerja</option>
                <option value="sambung belajar" <?= $filter_status == 'sambung belajar' ? 'selected' : '' ?>>Sambung Belajar</option>
                <option value="usahawan" <?= $filter_status == 'usahawan' ? 'selected' : '' ?>>Usahawan</option>
                <option value="belum bekerja" <?= $filter_status == 'belum bekerja' ? 'selected' : '' ?>>Belum Bekerja</option>
                <option value="belum kemaskini" <?= $filter_status == 'belum kemaskini' ? 'selected' : '' ?>>Belum Kemaskini</option>
            </select>
        </div>
        <div class="filter-actions">
            <a href="laporan.php" class="btn-reset">Reset</a>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="charts-section">
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="bi bi-pie-chart"></i> Status Pekerjaan Alumni</h3>
        </div>
        <div class="chart-body">
            <canvas id="statusChart" width="600" height="300" style="max-width: 100%; height: auto;"></canvas>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="bi bi-bar-chart"></i> Taburan Julat Gaji</h3>
        </div>
        <div class="chart-body">
            <canvas id="gajiChart" width="800" height="300" style="max-width: 100%; height: auto;"></canvas>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="bi bi-mortarboard"></i> Taburan Mengikut Program</h3>
        </div>
        <div class="chart-body">
            <canvas id="programChart" width="800" height="300" style="max-width: 100%; height: auto;"></canvas>
        </div>
    </div>
    
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="bi bi-calendar"></i> Taburan Mengikut Batch</h3>
        </div>
        <div class="chart-body">
            <canvas id="batchChart" width="800" height="300" style="max-width: 100%; height: auto;"></canvas>
        </div>
    </div>
</div>

<!-- ========== SENARAI MENGIKUT STATUS ========== -->

<!-- 1. ALUMNI BEKERJA -->
<?php if(empty($filter_status) || $filter_status == 'bekerja'): ?>
<div class="section-header bg-bekerja">
    <h4><i class="bi bi-briefcase-fill"></i> Alumni Bekerja (<?= count($alumni_by_status['bekerja']) ?>)</h4>
</div>
<div class="table-custom mb-4">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>No Matriks</th>
                    <th>Nama</th>
                    <th>Program</th>
                    <th>Batch</th>
                    <th>Pekerjaan</th>
                    <th>Tempat Kerja</th>
                    <th>Jawatan</th>
                    <th>Julat Gaji</th>
                    <th>Tarikh Kemaskini</th>
                 </thead>
                <tbody>
                    <?php foreach($alumni_by_status['bekerja'] as $a): ?>
                    <tr>
                        <td><?= formatNoMatrix($a['no_matrix']) ?></td>
                        <td><?= formatNama($a['nama']) ?></td>
                        <td><?= formatProgram($a['program']) ?></td>
                        <td><?= $a['batch'] ?></td>
                        <td><?= htmlspecialchars($a['pekerjaan'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($a['tempat_kerja'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($a['jawatan'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($a['julat_gaji'] ?? '-') ?></td>
                        <td><?= $a['tarikh_kemaskini'] ?? '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($alumni_by_status['bekerja']) == 0): ?>
                        <tr><td colspan="9" class="text-center py-4">Tiada alumni bekerja</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>
<?php endif; ?>

<!-- 2. ALUMNI SAMBUNG BELAJAR -->
<?php if(empty($filter_status) || $filter_status == 'sambung belajar'): ?>
<div class="section-header bg-sambung">
    <h4><i class="bi bi-book-fill"></i> Alumni Sambung Belajar (<?= count($alumni_by_status['sambung belajar']) ?>)</h4>
</div>
<div class="table-custom mb-4">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>No Matriks</th>
                    <th>Nama</th>
                    <th>Program</th>
                    <th>Batch</th>
                    <th>Institusi</th>
                    <th>Bidang Pengajian</th>
                    <th>Tarikh Kemaskini</th>
                 </thead>
                <tbody>
                    <?php foreach($alumni_by_status['sambung belajar'] as $a): ?>
                    <tr>
                        <td><?= formatNoMatrix($a['no_matrix']) ?></td>
                        <td><?= formatNama($a['nama']) ?></td>
                        <td><?= formatProgram($a['program']) ?></td>
                        <td><?= $a['batch'] ?></td>
                        <td><?= htmlspecialchars($a['institusi'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($a['bidang_pengajian'] ?? '-') ?></td>
                        <td><?= $a['tarikh_kemaskini'] ?? '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($alumni_by_status['sambung belajar']) == 0): ?>
                        <tr><td colspan="7" class="text-center py-4">Tiada alumni sambung belajar</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>
<?php endif; ?>

<!-- 3. ALUMNI USAHAWAN -->
<?php if(empty($filter_status) || $filter_status == 'usahawan'): ?>
<div class="section-header bg-usahawan">
    <h4><i class="bi bi-shop"></i> Alumni Usahawan (<?= count($alumni_by_status['usahawan']) ?>)</h4>
</div>
<div class="table-custom mb-4">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>No Matriks</th>
                    <th>Nama</th>
                    <th>Program</th>
                    <th>Batch</th>
                    <th>Nama Perniagaan</th>
                    <th>Bidang Perniagaan</th>
                    <th>Tarikh Kemaskini</th>
                 </thead>
                <tbody>
                    <?php foreach($alumni_by_status['usahawan'] as $a): ?>
                    <tr>
                        <td><?= formatNoMatrix($a['no_matrix']) ?></td>
                        <td><?= formatNama($a['nama']) ?></td>
                        <td><?= formatProgram($a['program']) ?></td>
                        <td><?= $a['batch'] ?></td>
                        <td><?= htmlspecialchars($a['nama_perniagaan'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($a['bidang_perniagaan'] ?? '-') ?></td>
                        <td><?= $a['tarikh_kemaskini'] ?? '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($alumni_by_status['usahawan']) == 0): ?>
                        <tr><td colspan="7" class="text-center py-4">Tiada alumni usahawan</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>
<?php endif; ?>

<!-- 4. ALUMNI BELUM BEKERJA -->
<?php if(empty($filter_status) || $filter_status == 'belum bekerja'): ?>
<div class="section-header bg-belum-bekerja">
    <h4><i class="bi bi-question-circle-fill"></i> Alumni Belum Bekerja (<?= count($alumni_by_status['belum bekerja']) ?>)</h4>
</div>
<div class="table-custom mb-4">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>No Matriks</th>
                    <th>Nama</th>
                    <th>Program</th>
                    <th>Batch</th>
                    <th>Status</th>
                    <th>Tarikh Kemaskini</th>
                 </thead>
                <tbody>
                    <?php foreach($alumni_by_status['belum bekerja'] as $a): ?>
                    <tr>
                        <td><?= formatNoMatrix($a['no_matrix']) ?></td>
                        <td><?= formatNama($a['nama']) ?></td>
                        <td><?= formatProgram($a['program']) ?></td>
                        <td><?= $a['batch'] ?></td>
                        <td><span class="badge-status badge-inactive">Belum Bekerja</span></td>
                        <td><?= $a['tarikh_kemaskini'] ?? '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($alumni_by_status['belum bekerja']) == 0): ?>
                        <tr><td colspan="6" class="text-center py-4">Tiada alumni belum bekerja</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>
<?php endif; ?>

<!-- 5. ALUMNI BELUM KEMASKINI -->
<?php if(empty($filter_status) || $filter_status == 'belum kemaskini'): ?>
<div class="section-header bg-belum-kemaskini">
    <h4><i class="bi bi-exclamation-triangle-fill"></i> Alumni Perlu Kemaskini (<?= count($alumni_by_status['belum kemaskini']) ?>)</h4>
</div>
<div class="table-custom mb-4">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>No Matriks</th>
                    <th>Nama</th>
                    <th>Program</th>
                    <th>Batch</th>
                    <th>Status</th>
                    <th>Tarikh Daftar</th>
                 </thead>
                <tbody>
                    <?php foreach($alumni_by_status['belum kemaskini'] as $a): ?>
                    <tr>
                        <td><?= formatNoMatrix($a['no_matrix']) ?></td>
                        <td><?= formatNama($a['nama']) ?></td>
                        <td><?= formatProgram($a['program']) ?></td>
                        <td><?= $a['batch'] ?></td>
                        <td><span class="badge-status badge-warning">Belum Kemaskini</span></td>
                        <td><?= $a['created_at'] ?? '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($alumni_by_status['belum kemaskini']) == 0): ?>
                        <tr><td colspan="6" class="text-center py-4">Tiada alumni perlu kemaskini</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Data from PHP with filters applied
const statusData = {
    bekerja: <?= $stats_bekerja ?>,
    sambungBelajar: <?= $stats_sambung_belajar ?>,
    usahawan: <?= $stats_usahawan ?>,
    belumBekerja: <?= $stats_belum_bekerja ?>,
    belumKemaskini: <?= $stats_belum_kemaskini ?>
};

const gajiLabels = [<?php foreach($gaji_stats as $g) { echo '"' . addslashes($g['julat_gaji']) . '",'; } ?>];
const gajiData = [<?php foreach($gaji_stats as $g) { echo $g['total'] . ','; } ?>];

const programLabels = [<?php foreach($program_stats as $p) { echo '"' . addslashes($p['program']) . '",'; } ?>];
const programData = [<?php foreach($program_stats as $p) { echo $p['total'] . ','; } ?>];

const batchLabels = [<?php 
    $batch_years = [];
    $batch_totals = [];
    foreach($batch_stats as $b) {
        if(!empty($b['batch']) && is_numeric($b['batch']) && $b['batch'] >= 2000 && $b['batch'] <= 2100) {
            $batch_years[] = $b['batch'];
            $batch_totals[] = (int)$b['total'];
        }
    }
    echo implode(',', $batch_years);
?>];

const batchDataRaw = [<?php echo implode(',', $batch_totals); ?>];

function createCharts() {
    setTimeout(function() {
        // Status Chart
        const statusCanvas = document.getElementById('statusChart');
        if (statusCanvas) {
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Bekerja', 'Sambung Belajar', 'Usahawan', 'Belum Bekerja', 'Belum Kemaskini'],
                    datasets: [{
                        data: [statusData.bekerja, statusData.sambungBelajar, statusData.usahawan, statusData.belumBekerja, statusData.belumKemaskini],
                        backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#6c757d', '#a92121']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
            });
        }
        
        // Gaji Chart
        const gajiCanvas = document.getElementById('gajiChart');
        if (gajiCanvas && gajiLabels.length > 0 && gajiData.some(v => v > 0)) {
            new Chart(gajiCanvas, {
                type: 'bar',
                data: {
                    labels: gajiLabels,
                    datasets: [{ label: 'Jumlah Alumni', data: gajiData, backgroundColor: '#667eea', borderRadius: 5 }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, stepSize: 1, ticks: { precision: 0 }, title: { display: true, text: 'Jumlah Alumni' } } }
                }
            });
        }
        
        // Program Chart
        const programCanvas = document.getElementById('programChart');
        if (programCanvas && programLabels.length > 0 && programData.some(v => v > 0)) {
            new Chart(programCanvas, {
                type: 'bar',
                data: {
                    labels: programLabels,
                    datasets: [{ label: 'Jumlah Alumni', data: programData, backgroundColor: '#764ba2', borderRadius: 5 }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: { x: { beginAtZero: true, stepSize: 1, ticks: { precision: 0 }, title: { display: true, text: 'Jumlah Alumni' } } }
                }
            });
        }
        
        // Batch Chart
        const batchCanvas = document.getElementById('batchChart');
        if (batchCanvas && batchLabels.length > 0 && batchDataRaw.some(v => v > 0)) {
            const maxBatchValue = Math.max(...batchDataRaw, 1);
            let stepSize = 1;
            if (maxBatchValue <= 8) stepSize = 1;
            else if (maxBatchValue <= 15) stepSize = 2;
            else if (maxBatchValue <= 30) stepSize = 3;
            else if (maxBatchValue <= 50) stepSize = 5;
            else if (maxBatchValue <= 80) stepSize = 8;
            else stepSize = 10;
            
            new Chart(batchCanvas, {
                type: 'bar',
                data: {
                    labels: batchLabels,
                    datasets: [{ label: 'Jumlah Alumni', data: batchDataRaw, backgroundColor: '#4f46e5', borderRadius: 5 }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, stepSize: stepSize, ticks: { precision: 0 }, title: { display: true, text: 'Jumlah Alumni' } } }
                }
            });
        }
    }, 100);
}

// Filter functions
function applyFilters() {
    let params = new URLSearchParams();
    let program = document.getElementById('programFilter').value;
    let batch = document.getElementById('batchFilter').value;
    let status = document.getElementById('statusFilter').value;
    
    if(program) params.set('program', program);
    if(batch) params.set('batch', batch);
    if(status) params.set('status', status);
    
    window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('programFilter').addEventListener('change', applyFilters);
    document.getElementById('batchFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    createCharts();
});
</script>

<?php include_once '../includes/footer.php'; ?>