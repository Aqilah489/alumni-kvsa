<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

$page_title = 'Tambah Program';
$page_css = 'program';

$error = '';
$success = '';
$kod_program = '';
$nama_program = '';
$tempoh_pengajian = 30;
$status = 'active';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kod_program = strtoupper(trim($_POST['kod_program']));
    $nama_program = trim($_POST['nama_program']);
    $tempoh_pengajian = intval($_POST['tempoh_pengajian']);
    $status = $_POST['status'] ?? 'active';
    
    if(empty($kod_program) || empty($nama_program)) {
        $error = "Kod program dan nama program wajib diisi!";
    } else {
        $check = $connect->prepare("SELECT * FROM program WHERE kod_program = ?");
        $check->execute([$kod_program]);
        if($check->fetch()) {
            $error = "Kod program '$kod_program' sudah wujud!";
        } else {
            $stmt = $connect->prepare("INSERT INTO program (kod_program, nama_program, tempoh_pengajian, status) VALUES (?, ?, ?, ?)");
            if($stmt->execute([$kod_program, $nama_program, $tempoh_pengajian, $status])) {
                $_SESSION['success'] = "Program '$kod_program' berjaya ditambah!";
                header('Location: senarai.php');
                exit();
            } else {
                $error = "Gagal menambah program. Sila cuba lagi.";
            }
        }
    }
}

include_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <i class="bi bi-book-plus"></i>
            <h3>Tambah Program Baru</h3>
        </div>
        <div class="form-body">
            <?php if($error): ?>
                <div class="alert-custom alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Kod Program <span class="text-danger">*</span></label>
                    <input type="text" name="kod_program" class="form-control" id="kod_program" 
                           value="<?= htmlspecialchars($kod_program) ?>" required>
                    <div class="form-text">Gunakan huruf besar, tanpa spacing. Contoh: DP, DS, DE</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Nama Program <span class="text-danger">*</span></label>
                    <input type="text" name="nama_program" class="form-control" id="nama_program" 
                           value="<?= htmlspecialchars($nama_program) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tempoh Pengajian (bulan)</label>
                    <input type="number" name="tempoh_pengajian" class="form-control" id="tempoh" 
                           value="<?= $tempoh_pengajian ?>">
                    <div class="form-text">Contoh: 30 bulan untuk diploma 2.5 tahun</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" id="status">
                        <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Aktif</option>
                        <option value="inactive" <?= $status == 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
                
                <!-- Preview Card -->
                <div class="preview-card">
                    <h6><i class="bi bi-eye"></i> Preview Program</h6>
                    <div class="preview-item">
                        <span class="preview-label">Kod Program:</span>
                        <span class="preview-value" id="preview_kod">-</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Nama Program:</span>
                        <span class="preview-value" id="preview_nama">-</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Tempoh:</span>
                        <span class="preview-value" id="preview_tempoh">-</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Status:</span>
                        <span class="preview-value" id="preview_status">-</span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="senarai.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Live preview
    document.getElementById('kod_program').onkeyup = function() {
        document.getElementById('preview_kod').innerText = this.value.toUpperCase() || '-';
    }
    document.getElementById('nama_program').onkeyup = function() {
        document.getElementById('preview_nama').innerText = this.value || '-';
    }
    document.getElementById('tempoh').onchange = function() {
        document.getElementById('preview_tempoh').innerText = (this.value || '30') + ' bulan';
    }
    document.getElementById('status').onchange = function() {
        let text = this.value == 'active' ? 'Aktif' : 'Tidak Aktif';
        document.getElementById('preview_status').innerText = text;
    }
    
    // Trigger initial preview
    document.getElementById('tempoh').dispatchEvent(new Event('change'));
    document.getElementById('status').dispatchEvent(new Event('change'));
    
    // Kod program to uppercase
    document.getElementById('kod_program').onkeyup = function() {
        this.value = this.value.toUpperCase();
        document.getElementById('preview_kod').innerText = this.value || '-';
    }
</script>

<?php include_once '../includes/footer.php'; ?>