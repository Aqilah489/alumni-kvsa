<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

if(isset($_SESSION['success'])) {
    $success_msg = $_SESSION['success'];
    unset($_SESSION['success']);
}

if(isset($_SESSION['error'])) {
    $error_msg = $_SESSION['error'];
    unset($_SESSION['error']);
}

$programs = $connect->query("SELECT * FROM program ORDER BY kod_program")->fetchAll();

$page_title = 'Senarai Program';
$page_css = 'program';

include_once '../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-book"></i> Senarai Program</h2>
    <a href="tambah.php" class="btn-add">
        <i class="bi bi-plus-circle"></i> Tambah Program
    </a>
</div>

<?php if(isset($success_msg)): ?>
    <div class="alert-custom alert-success">
        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_msg) ?>
    </div>
<?php endif; ?>

<?php if(isset($error_msg)): ?>
    <div class="alert-custom alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_msg) ?>
    </div>
<?php endif; ?>

<div class="filter-bar">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="searchInput" placeholder="Cari program...">
    </div>
    <div class="filter-select">
        <select id="statusFilter" onchange="filterTable()">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Tidak Aktif</option>
        </select>
    </div>
</div>

<div class="table-custom">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>Kod Program</th><th>Nama Program</th><th>Tempoh</th><th>Status</th><th>Tindakan</th> </tr>
            </thead>
            <tbody id="tableBody">
                <?php foreach($programs as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['kod_program']) ?></strong></td>
                    <td><?= htmlspecialchars($p['nama_program']) ?></td>
                    <td><?= $p['tempoh_pengajian'] ?> bulan</td>
                    <td class="status-cell">
                        <?php if($p['status'] == 'active'): ?>
                            <span class="badge-status badge-active"> Aktif</span>
                        <?php else: ?>
                            <span class="badge-status badge-inactive"> Tidak Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="kemaskini.php?id=<?= $p['kod_program'] ?>" class="btn-icon btn-edit"> Edit</a>
                        <a href="padam.php?id=<?= $p['kod_program'] ?>" class="btn-icon btn-delete" onclick="return confirm('Padam program ini?')"> Padam</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(count($programs) == 0): ?>
                <tr><td colspan="5" class="text-center">Tiada data program</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable() {
    let search = document.getElementById('searchInput').value.toLowerCase();
    let status = document.getElementById('statusFilter').value;
    let rows = document.querySelectorAll('#tableBody tr');
    
    console.log("Status selected:", status); // Debug
    
    for(let i = 0; i < rows.length; i++) {
        let row = rows[i];
        let kod = row.cells[0].innerText.toLowerCase();
        let nama = row.cells[1].innerText.toLowerCase();
        let statusText = row.cells[3].innerText;
        
        console.log("Row:", kod, "Status text:", statusText); // Debug
        
        let matchSearch = kod.includes(search) || nama.includes(search);
        let matchStatus = true;
        
        if(status === 'active') {
            // Check if status text contains "Aktif" AND NOT "Tidak"
            matchStatus = statusText.includes('Aktif') && !statusText.includes('Tidak');
        } else if(status === 'inactive') {
            matchStatus = statusText.includes('Tidak Aktif');
        }
        
        if(matchSearch && matchStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
</script>


<?php include_once '../includes/footer.php'; ?>