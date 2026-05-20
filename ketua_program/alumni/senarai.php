<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ketua_program') {
    header('Location: ../../index.php');
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

function formatEmail($emel) {
    return strtolower(trim($emel));
}

// Dapatkan program KP dari session
$program_kp = $_SESSION['program'] ?? '';

if(empty($program_kp)) {
    $stmt = $connect->prepare("SELECT program FROM ketua_program WHERE emel = ?");
    $stmt->execute([$_SESSION['emel']]);
    $kp = $stmt->fetch();
    if($kp) {
        $program_kp = $kp['program'];
        $_SESSION['program'] = $program_kp;
    }
}

// Get filter
$filter_batch = $_GET['batch'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT a.* FROM alumni a WHERE a.program = ?";
$params = [$program_kp];

if($filter_batch) {
    $sql .= " AND a.batch = ?";
    $params[] = $filter_batch;
}
if($filter_status) {
    $sql .= " AND a.status_alumni = ?";
    $params[] = $filter_status;
}
if($search) {
    $sql .= " AND (a.nama LIKE ? OR a.no_matrix LIKE ? OR a.emel LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}
$sql .= " ORDER BY a.created_at DESC";

$stmt = $connect->prepare($sql);
$stmt->execute($params);
$alumni = $stmt->fetchAll();

// Get batches for filter
$batches = $connect->prepare("
    SELECT DISTINCT batch
    FROM alumni 
    WHERE program = ? AND batch IS NOT NULL 
    ORDER BY batch DESC
");
$batches->execute([$program_kp]);
$batches = $batches->fetchAll();

$page_title = 'Senarai Alumni - ' . formatProgram($program_kp);
$page_css = 'alumni';

include_once '../includes/header_kp.php';
?>

<div class="page-header">
    <h2><i class="bi bi-people"></i> Senarai Alumni - <?= htmlspecialchars(formatProgram($program_kp)) ?></h2>
</div>

<!-- Filter Bar -->
<div class="filter-container">
    <div class="filter-header">
        <i class="bi bi-funnel"></i>
        <span>Filter Alumni</span>
    </div>
    
    <div class="filter-row">
        <div class="filter-group">
            <label><i class="bi bi-calendar"></i> Tahun Batch</label>
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
            <label><i class="bi bi-person"></i> Status Alumni</label>
            <select id="statusFilter" class="filter-select">
                <option value="">Semua Status</option>
                <option value="belum kemaskini" <?= $filter_status == 'belum kemaskini' ? 'selected' : '' ?>>Belum Kemaskini</option>
                <option value="bekerja" <?= $filter_status == 'bekerja' ? 'selected' : '' ?>>Bekerja</option>
                <option value="sambung belajar" <?= $filter_status == 'sambung belajar' ? 'selected' : '' ?>>Sambung Belajar</option>
                <option value="usahawan" <?= $filter_status == 'usahawan' ? 'selected' : '' ?>>Usahawan</option>
                <option value="belum bekerja" <?= $filter_status == 'belum bekerja' ? 'selected' : '' ?>>Belum Bekerja</option>
            </select>
        </div>
        
        <div class="filter-group search-group">
            <label><i class="bi bi-search"></i> Cari</label>
            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Nama atau No Matriks..." 
                       value="<?= htmlspecialchars($search) ?>">
                <?php if($search): ?>
                <a href="senarai.php" class="clear-search"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="filter-actions">
            <a href="senarai.php" class="btn-reset">
                <i class="bi bi-arrow-repeat"></i> Reset
            </a>
        </div>
    </div>
    
    <div class="quick-filters">
        <a href="senarai.php?status=belum%20kemaskini" class="quick-filter-tag <?= $filter_status == 'belum kemaskini' ? 'active' : '' ?>">
            <i class="bi bi-clock-history"></i> Belum Kemaskini
        </a>
        <a href="senarai.php?status=bekerja" class="quick-filter-tag <?= $filter_status == 'bekerja' ? 'active' : '' ?>">
            <i class="bi bi-briefcase"></i> Bekerja
        </a>
        <a href="senarai.php?status=sambung%20belajar" class="quick-filter-tag <?= $filter_status == 'sambung belajar' ? 'active' : '' ?>">
            <i class="bi bi-book"></i> Sambung Belajar
        </a>
        <a href="senarai.php?status=usahawan" class="quick-filter-tag <?= $filter_status == 'usahawan' ? 'active' : '' ?>">
            <i class="bi bi-shop"></i> Usahawan
        </a>
        <a href="senarai.php?status=belum%20bekerja" class="quick-filter-tag <?= $filter_status == 'belum bekerja' ? 'active' : '' ?>">
            <i class="bi bi-question-circle"></i> Belum Bekerja
        </a>
    </div>
</div>

<div class="table-custom">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>No Matriks</th>
                    <th>Nama</th>
                    <th>Batch</th>
                    <th>Emel</th>
                    <th>Status</th>
                    <th>Tindakan</th>
                 </thead>
            <tbody>
                <?php if(count($alumni) > 0): ?>
                    <?php foreach($alumni as $a): 
                        switch($a['status_alumni']) {
                            case 'belum kemaskini':
                                $status_badge = '<span class="badge-status badge-warning"><i class="bi bi-clock-history"></i> Belum Kemaskini</span>';
                                break;
                            case 'bekerja':
                                $status_badge = '<span class="badge-status badge-bekerja"><i class="bi bi-briefcase-fill"></i> Bekerja</span>';
                                break;
                            case 'sambung belajar':
                                $status_badge = '<span class="badge-status badge-sambung"><i class="bi bi-book-fill"></i> Sambung Belajar</span>';
                                break;
                            case 'usahawan':
                                $status_badge = '<span class="badge-status badge-usahawan"><i class="bi bi-shop"></i> Usahawan</span>';
                                break;
                            case 'belum bekerja':
                                $status_badge = '<span class="badge-status badge-belum-bekerja"><i class="bi bi-clock"></i> Belum Bekerja</span>';
                                break;
                            default:
                                $status_badge = '<span class="badge-status">-</span>';
                        }
                    ?>
                    <tr>
                        <td><strong><?= formatNoMatrix($a['no_matrix']) ?></strong></td>
                        <td><?= formatNama($a['nama']) ?></td>
                        <td><?= $a['batch'] ?></td>
                        <td><?= formatEmail($a['emel']) ?></td>
                        <td><?= $status_badge ?></td>
                        <td class="actions">
                            <button class="btn-icon btn-view" onclick="viewAlumni(<?= $a['alumni_id'] ?>)" title="View">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="kemaskini.php?id=<?= $a['alumni_id'] ?>" class="btn-icon btn-edit">
                                <i class="bi bi-pencil"></i> Kemas Kini
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>Tiada data alumni</p>
                        </div>
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal View Alumni (SAMA MACAM KAUNSELING) -->
<div class="modal fade" id="viewAlumniModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Filter functions
function applyFilters() {
    let params = new URLSearchParams();
    
    let batch = document.getElementById('batchFilter').value;
    let status = document.getElementById('statusFilter').value;
    let search = document.getElementById('searchInput').value;
    
    if(batch) params.set('batch', batch);
    if(status) params.set('status', status);
    if(search) params.set('search', search);
    
    window.location.href = 'senarai.php?' + params.toString();
}

// Event listeners
document.getElementById('batchFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

let searchTimeout;
document.getElementById('searchInput').addEventListener('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
});

// Function to view alumni details (SAMA MACAM KAUNSELING)
function viewAlumni(id) {
    $.ajax({
        url: 'get_alumni_detail.php?id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if(data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }
            
            // Status badge
            let statusBadge = '';
            switch(data.status_alumni) {
                case 'bekerja':
                    statusBadge = '<span class="badge-status badge-bekerja"><i class="bi bi-briefcase-fill"></i> Bekerja</span>';
                    break;
                case 'sambung belajar':
                    statusBadge = '<span class="badge-status badge-sambung"><i class="bi bi-book-fill"></i> Sambung Belajar</span>';
                    break;
                case 'usahawan':
                    statusBadge = '<span class="badge-status badge-usahawan"><i class="bi bi-shop"></i> Usahawan</span>';
                    break;
                case 'belum bekerja':
                    statusBadge = '<span class="badge-status badge-belum-bekerja"><i class="bi bi-clock"></i> Belum Bekerja</span>';
                    break;
                case 'belum kemaskini':
                    statusBadge = '<span class="badge-status badge-belum-kemaskini"><i class="bi bi-exclamation-triangle-fill"></i> Perlu Kemaskini</span>';
                    break;
                default:
                    statusBadge = '<span class="badge-status">-</span>';
            }
            
            // Build dynamic content based on status
            let workInfoHtml = '';
            
            if(data.status_alumni == 'bekerja') {
                workInfoHtml = `
                    <div class="info-card">
                        <div class="card-title"><i class="bi bi-briefcase"></i> Maklumat Pekerjaan</div>
                        <div class="card-content">
                            <div class="info-row"><div class="info-label">Pekerjaan</div><div class="info-value">${data.pekerjaan || '-'}</div></div>
                            <div class="info-row"><div class="info-label">Tempat Kerja</div><div class="info-value">${data.tempat_kerja || '-'}</div></div>
                            <div class="info-row"><div class="info-label">Jawatan</div><div class="info-value">${data.jawatan || '-'}</div></div>
                            <div class="info-row"><div class="info-label">Julat Gaji</div><div class="info-value">${data.julat_gaji || '-'}</div></div>
                        </div>
                    </div>
                `;
            } else if(data.status_alumni == 'sambung belajar') {
                workInfoHtml = `
                    <div class="info-card">
                        <div class="card-title"><i class="bi bi-book"></i> Maklumat Pengajian</div>
                        <div class="card-content">
                            <div class="info-row"><div class="info-label">Institusi</div><div class="info-value">${data.institusi || '-'}</div></div>
                            <div class="info-row"><div class="info-label">Bidang Pengajian</div><div class="info-value">${data.bidang_pengajian || '-'}</div></div>
                        </div>
                    </div>
                `;
            } else if(data.status_alumni == 'usahawan') {
                workInfoHtml = `
                    <div class="info-card">
                        <div class="card-title"><i class="bi bi-shop"></i> Maklumat Perniagaan</div>
                        <div class="card-content">
                            <div class="info-row"><div class="info-label">Nama Perniagaan</div><div class="info-value">${data.nama_perniagaan || '-'}</div></div>
                            <div class="info-row"><div class="info-label">Bidang Perniagaan</div><div class="info-value">${data.bidang_perniagaan || '-'}</div></div>
                        </div>
                    </div>
                `;
            } else if(data.status_alumni == 'belum kemaskini') {
                workInfoHtml = `
                    <div class="info-card">
                        <div class="card-title"><i class="bi bi-clock-history"></i> Status Profil</div>
                        <div class="card-content">
                            <div class="info-row"><div class="info-label">Status</div><div class="info-value">Profil belum dikemaskini</div></div>
                            <div class="info-row"><div class="info-label">Tindakan</div><div class="info-value">Sila kemaskini maklumat terkini</div></div>
                        </div>
                    </div>
                `;
            } else {
                workInfoHtml = `
                    <div class="info-card">
                        <div class="card-title"><i class="bi bi-info-circle"></i> Maklumat</div>
                        <div class="card-content">
                            <div class="info-row"><div class="info-label">Status</div><div class="info-value">${statusBadge}</div></div>
                        </div>
                    </div>
                `;
            }
            
            let modalHtml = `
                <div class="modal-header">
                    <div class="header-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="header-info">
                        <h3>${data.nama}</h3>
                        <p>${data.emel}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom: 15px;">
                        ${statusBadge}
                    </div>
                    
                    <div class="modal-grid">
                        <!-- Maklumat Asas Card -->
                        <div class="info-card">
                            <div class="card-title"><i class="bi bi-person-badge"></i> Maklumat Asas</div>
                            <div class="card-content">
                                <div class="info-row"><div class="info-label">No Matriks</div><div class="info-value">${data.no_matrix || '-'}</div></div>
                                <div class="info-row"><div class="info-label">No Telefon</div><div class="info-value">${data.no_telefon || '-'}</div></div>
                                <div class="info-row"><div class="info-label">Batch</div><div class="info-value">${data.batch || '-'}</div></div>
                                <div class="info-row"><div class="info-label">Lokasi</div><div class="info-value">${data.lokasi || '-'}</div></div>
                            </div>
                        </div>
                        
                        ${workInfoHtml}
                    </div>
                    
                    <div class="info-card" style="margin-top: 15px;">
                        <div class="card-title"><i class="bi bi-calendar"></i> Maklumat Sistem</div>
                        <div class="card-content">
                            <div class="info-row"><div class="info-label">Tarikh Kemaskini</div><div class="info-value">${data.tarikh_kemaskini_formatted || '-'}</div></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Tutup</button>
                    <a href="kemaskini.php?id=${data.alumni_id}" class="btn-edit-modal"><i class="bi bi-pencil"></i> Kemaskini</a>
                </div>
            `;
            
            // Update modal content and show
            $('#viewAlumniModal .modal-content').html(modalHtml);
            $('#viewAlumniModal').modal('show');
        },
        error: function(xhr, status, error) {
            Swal.fire('Ralat', 'Gagal memuatkan maklumat alumni', 'error');
        }
    });
}
</script>

<?php include_once '../includes/footer_kp.php'; ?>