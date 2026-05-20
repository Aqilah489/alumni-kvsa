<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

// Helper function untuk format text
function formatNama($nama) {
    return ucwords(strtolower(trim($nama)));
}

function formatProgram($program) {
    return ucwords(strtolower(trim($program)));
}

function formatEmail($emel) {
    return strtolower(trim($emel));
}

// Get filter
$filter_status = $_GET['status'] ?? '';
$filter_program = $_GET['program'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT k.* FROM ketua_program k WHERE 1=1";
$params = [];

if($filter_status) {
    $sql .= " AND k.status = ?";
    $params[] = $filter_status;
}
if($filter_program) {
    $sql .= " AND k.program = ?";
    $params[] = $filter_program;
}
if($search) {
    $sql .= " AND (k.nama LIKE ? OR k.emel LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}
$sql .= " ORDER BY k.created_at DESC";

$stmt = $connect->prepare($sql);
$stmt->execute($params);
$ketua = $stmt->fetchAll();

// Get programs for filter
$programs = $connect->query("
    SELECT DISTINCT program 
    FROM ketua_program 
    WHERE program IS NOT NULL 
    ORDER BY program
")->fetchAll();

$page_title = 'Senarai Ketua Program';
$page_css = 'alumni';

include_once '../includes/header.php';
?>

<style>
/* Khas untuk page ketua program - override truncation */
.table-custom td {
    max-width: none !important;
    white-space: normal !important;
    word-break: break-word !important;
    overflow: visible !important;
}

/* Lebarkan kolum yang perlu */
.table-custom td:nth-child(1),
.table-custom th:nth-child(1) {
    min-width: 200px;
}

.table-custom td:nth-child(2),
.table-custom th:nth-child(2) {
    min-width: 220px;
}

.table-custom td:nth-child(3),
.table-custom th:nth-child(3) {
    min-width: 220px;
}

.table-custom td:nth-child(6),
.table-custom th:nth-child(6) {
    min-width: 160px;
}

/* Action buttons sebaris */
.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: nowrap;
    white-space: nowrap;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
}

.btn-edit {
    background: #fff3cd;
    color: #856404;
}

.btn-edit:hover {
    background: #ffeaa7;
}

.btn-delete {
    background: #fee2e2;
    color: #e74c3c;
}

.btn-delete:hover {
    background: #fccaca;
}

/* Responsive */
@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
    }
    .table-custom table {
        min-width: 800px;
    }
}
</style>

<div class="page-header">
    <h2><i class="bi bi-person-badge"></i> Senarai Ketua Program</h2>
    <a href="tambah.php" class="btn-add">
        <i class="bi bi-plus-circle"></i> Tambah Ketua Program
    </a>
</div>

<!-- Filter Bar -->
<div class="filter-container">
    <div class="filter-header">
        <i class="bi bi-funnel"></i>
        <span>Filter Ketua Program</span>
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
            <label><i class="bi bi-check-circle"></i> Status</label>
            <select id="statusFilter" class="filter-select">
                <option value="">Semua Status</option>
                <option value="aktif" <?= $filter_status == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="bersara" <?= $filter_status == 'bersara' ? 'selected' : '' ?>>Bersara</option>
                <option value="cuti" <?= $filter_status == 'cuti' ? 'selected' : '' ?>>Cuti</option>
            </select>
        </div>
        
        <div class="filter-group search-group">
            <label><i class="bi bi-search"></i> Cari</label>
            <div class="search-wrapper">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Nama atau emel..." 
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
</div>

<div class="table-custom">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Emel</th>
                    <th>Program</th>
                    <th>Jawatan</th>
                    <th>Status</th>
                    <th>Tindakan</th>
                 </thead>
            <tbody>
                <?php if(count($ketua) > 0): ?>
                    <?php foreach($ketua as $k): ?>
                     <tr>
                        <td><strong><?= formatNama($k['nama']) ?></strong></td>
                        <td><?= formatEmail($k['emel']) ?></td>
                        <td><?= formatProgram($k['program']) ?></td>
                        <td><?= htmlspecialchars($k['jawatan'] ?? 'Ketua Program') ?></td>
                        <td>
                            <?php if($k['status'] == 'aktif'): ?>
                                <span class="badge-status badge-active"><i class="bi bi-check-circle"></i> Aktif</span>
                            <?php elseif($k['status'] == 'bersara'): ?>
                                <span class="badge-status badge-warning"><i class="bi bi-flag"></i> Bersara</span>
                            <?php else: ?>
                                <span class="badge-status badge-inactive"><i class="bi bi-pause-circle"></i> Cuti</span>
                            <?php endif; ?>
                        </td>
                        <td class="action-buttons">
                            <a href="kemaskini.php?id=<?= $k['kp_id'] ?>" class="btn-icon btn-edit">
                                <i class="bi bi-pencil"></i> Kemas Kini
                            </a>
                            <a href="javascript:void(0)" 
                               onclick="confirmDelete(<?= $k['kp_id'] ?>, '<?= addslashes($k['nama']) ?>', 'ketua_program')" 
                               class="btn-icon btn-delete">
                                <i class="bi bi-trash"></i> Padam
                            </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                     <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Tiada data ketua program</p>
                                <a href="tambah.php" class="btn-add mt-2" style="display: inline-flex;">
                                    <i class="bi bi-plus-circle"></i> Tambah Ketua Program Pertama
                                </a>
                            </div>
                        </td>
                      </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilters() {
    let params = new URLSearchParams();
    
    let program = document.getElementById('programFilter').value;
    let status = document.getElementById('statusFilter').value;
    let search = document.getElementById('searchInput').value;
    
    if(program) params.set('program', program);
    if(status) params.set('status', status);
    if(search) params.set('search', search);
    
    window.location.href = 'senarai.php?' + params.toString();
}

// Event listeners
document.getElementById('programFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

// Search with debounce
let searchTimeout;
document.getElementById('searchInput').addEventListener('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
});

function confirmDelete(id, name, module) {
    Swal.fire({
        title: 'Padam ' + name + '?',
        text: "Tindakan ini tidak boleh dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Padam!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../../padam.php?id=' + id + '&module=' + module;
        }
    });
}
</script>

<?php include_once '../includes/footer.php'; ?>