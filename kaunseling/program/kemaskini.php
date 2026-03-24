<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

$kod = $_GET['id'] ?? '';
if(empty($kod)) {
    header('Location: senarai.php');
    exit();
}

$stmt = $connect->prepare("SELECT * FROM program WHERE kod_program = ?");
$stmt->execute([$kod]);
$program = $stmt->fetch();

if(!$program) {
    header('Location: senarai.php');
    exit();
}

$page_title = 'Edit Program';
$page_css = 'program';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_program = trim($_POST['nama_program']);
    $tempoh_pengajian = intval($_POST['tempoh_pengajian']);
    $status = $_POST['status'] ?? 'active';
    
    if(empty($nama_program)) {
        $_SESSION['error'] = "Nama program wajib diisi!";
        header('Location: kemaskini.php?id=' . $kod);
        exit();
    } else {
        $stmt = $connect->prepare("UPDATE program SET nama_program = ?, tempoh_pengajian = ?, status = ? WHERE kod_program = ?");
        if($stmt->execute([$nama_program, $tempoh_pengajian, $status, $kod])) {
            $_SESSION['success'] = "Program " . htmlspecialchars($kod) . " berjaya dikemaskini!";
            header('Location: senarai.php');
            exit();
        } else {
            $_SESSION['error'] = "Gagal mengemaskini program. Sila cuba lagi.";
            header('Location: kemaskini.php?id=' . $kod);
            exit();
        }
    }
}

include_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <i class="bi bi-pencil-square"></i>
            <h3>Edit Program</h3>
            <p>Kod: <strong><?= htmlspecialchars($program['kod_program']) ?></strong></p>
        </div>
        <div class="form-body">
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert-custom alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Kod Program</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($program['kod_program']) ?>" disabled>
                    <div class="form-text">Kod program tidak boleh diubah.</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Nama Program <span class="text-danger">*</span></label>
                    <input type="text" name="nama_program" class="form-control" id="nama_program" 
                           value="<?= htmlspecialchars($program['nama_program']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tempoh Pengajian (bulan)</label>
                    <input type="number" name="tempoh_pengajian" class="form-control" id="tempoh" 
                           value="<?= $program['tempoh_pengajian'] ?>">
                    <div class="form-text">Contoh: 30 bulan untuk diploma 2.5 tahun</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" id="status">
                        <option value="active" <?= $program['status'] == 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="inactive" <?= $program['status'] == 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
                
                <!-- Preview Card -->
                <div class="preview-card">
                    <h6><i class="bi bi-eye"></i> Preview Program</h6>
                    <div class="preview-item">
                        <span class="preview-label">Kod Program:</span>
                        <span class="preview-value" id="preview_kod"><?= htmlspecialchars($program['kod_program']) ?></span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Nama Program:</span>
                        <span class="preview-value" id="preview_nama"><?= htmlspecialchars($program['nama_program']) ?></span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Tempoh:</span>
                        <span class="preview-value" id="preview_tempoh"><?= $program['tempoh_pengajian'] ?> bulan</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Status:</span>
                        <span class="preview-value" id="preview_status"><?= $program['status'] == 'active' ? 'Aktif' : 'Tidak Aktif' ?></span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="senarai.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-save"></i> Kemaskini
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Live preview for edit form
    const namaInput = document.getElementById('nama_program');
    const tempohInput = document.getElementById('tempoh');
    const statusSelect = document.getElementById('status');
    
    if(namaInput) {
        namaInput.addEventListener('keyup', function() {
            document.getElementById('preview_nama').innerText = this.value || '-';
        });
    }
    
    if(tempohInput) {
        tempohInput.addEventListener('change', function() {
            document.getElementById('preview_tempoh').innerText = (this.value || '0') + ' bulan';
        });
    }
    
    if(statusSelect) {
        statusSelect.addEventListener('change', function() {
            let text = this.value == 'active' ? 'Aktif' : 'Tidak Aktif';
            document.getElementById('preview_status').innerText = text;
        });
    }
</script>

<?php include_once '../includes/footer.php'; ?>