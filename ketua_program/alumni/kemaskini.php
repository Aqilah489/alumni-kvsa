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

$id = $_GET['id'] ?? 0;
if(!$id) {
    header('Location: senarai.php');
    exit();
}

// Get alumni data - pastikan hanya untuk program KP
$stmt = $connect->prepare("SELECT * FROM alumni WHERE alumni_id = ? AND program = ?");
$stmt->execute([$id, $program_kp]);
$alumni = $stmt->fetch();

if(!$alumni) {
    header('Location: senarai.php');
    exit();
}

$page_title = 'Kemaskini Alumni';
$page_css = 'alumni';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_matrix = formatNoMatrix($_POST['no_matrix']);
    $nama = formatNama($_POST['nama']);
    $emel = formatEmail($_POST['emel']);
    $no_telefon = trim($_POST['no_telefon'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $batch = $_POST['batch'];
    
    if(empty($no_matrix) || empty($nama) || empty($emel) || empty($batch)) {
        $error = "No matriks, nama, emel dan batch wajib diisi!";
    } else {
        // Check duplicate no_matrix (exclude current)
        $check = $connect->prepare("SELECT * FROM alumni WHERE no_matrix = ? AND alumni_id != ?");
        $check->execute([$no_matrix, $id]);
        if($check->fetch()) {
            $error = "No matriks '$no_matrix' sudah wujud!";
        } else {
            // Check duplicate emel (exclude current)
            $check = $connect->prepare("SELECT * FROM alumni WHERE emel = ? AND alumni_id != ?");
            $check->execute([$emel, $id]);
            if($check->fetch()) {
                $error = "Emel '$emel' sudah wujud!";
            } else {
                // UPDATE hanya maklumat asas (status dan pekerjaan tidak diubah)
                $stmt = $connect->prepare("
                    UPDATE alumni SET 
                        no_matrix = ?, 
                        nama = ?, 
                        emel = ?, 
                        no_telefon = ?, 
                        lokasi = ?, 
                        batch = ?
                    WHERE alumni_id = ?
                ");
                if($stmt->execute([$no_matrix, $nama, $emel, $no_telefon, $lokasi, $batch, $id])) {
                    $_SESSION['success'] = "Alumni '$nama' berjaya dikemaskini!";
                    header('Location: senarai.php');
                    exit();
                } else {
                    $error = "Gagal mengemaskini alumni.";
                }
            }
        }
    }
}

include_once '../includes/header_kp.php';
?>

<style>
.preview-card {
    background: #f8fafc;
    border-radius: 16px;
    padding: 20px;
    margin: 20px 0;
    border: 1px solid #e2e8f0;
}

.preview-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
}

.preview-header i {
    font-size: 1rem;
    color: #667eea;
}

.preview-header span {
    font-size: 0.8rem;
    font-weight: 600;
    color: #2c3e50;
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.preview-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: white;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.preview-item.full-width {
    grid-column: span 2;
}

.preview-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
}

.preview-value {
    font-size: 0.8rem;
    font-weight: 500;
    color: #2c3e50;
}
</style>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="header-icon">
                <i class="bi bi-pencil-square"></i>
            </div>
            <h3>Kemaskini Alumni</h3>
            <p>Edit maklumat asas alumni - Program: <?= htmlspecialchars($program_kp) ?></p>
        </div>
        
        <div class="form-body">
            <?php if(!empty($error)): ?>
                <div class="alert-custom alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="alumniForm">
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-upc-scan"></i> No Matriks 
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="no_matrix" class="form-control" id="no_matrix" 
                               value="<?= htmlspecialchars($alumni['no_matrix']) ?>" required>
                        <div class="form-hint">Gunakan huruf besar</div>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-person"></i> Nama Penuh 
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="nama" class="form-control" id="nama" 
                               value="<?= htmlspecialchars($alumni['nama']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Emel 
                            <span class="required">*</span>
                        </label>
                        <input type="email" name="emel" class="form-control" id="emel" 
                               value="<?= htmlspecialchars($alumni['emel']) ?>" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-telephone"></i> No Telefon
                        </label>
                        <input type="tel" name="no_telefon" class="form-control" id="no_telefon" 
                               value="<?= htmlspecialchars($alumni['no_telefon'] ?? '') ?>"
                               placeholder="012-3456789">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-mortarboard"></i> Program 
                            <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($program_kp) ?>" disabled>
                        <div class="form-hint">Program tidak boleh ditukar</div>
                        <input type="hidden" name="program" value="<?= htmlspecialchars($program_kp) ?>">
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-calendar"></i> Batch 
                            <span class="required">*</span>
                        </label>
                        <select name="batch" class="form-select" id="batch" required>
                            <option value="">-- Pilih Tahun --</option>
                            <?php for($year = date('Y') + 1; $year >= 2016; $year--): ?>
                            <option value="<?= $year ?>" <?= $alumni['batch'] == $year ? 'selected' : '' ?>><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field full-width">
                        <label class="form-label">
                            <i class="bi bi-geo-alt"></i> Lokasi
                        </label>
                        <input type="text" name="lokasi" class="form-control" id="lokasi"
                               value="<?= htmlspecialchars($alumni['lokasi'] ?? '') ?>"
                               placeholder="Contoh: Johor Bahru/Johor atau Singapore">
                        <div class="form-hint">
                            Format: <strong>Daerah/Negeri</strong> (dalam Malaysia) atau <strong>Bandar/Negara</strong> (luar negara)
                        </div>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="preview-card">
                    <div class="preview-header">
                        <i class="bi bi-eye"></i>
                        <span>Preview Alumni</span>
                    </div>
                    <div class="preview-grid">
                        <div class="preview-item">
                            <span class="preview-label">No Matriks</span>
                            <span class="preview-value" id="preview_no_matrix"><?= htmlspecialchars($alumni['no_matrix']) ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Nama</span>
                            <span class="preview-value" id="preview_nama"><?= htmlspecialchars($alumni['nama']) ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Emel</span>
                            <span class="preview-value" id="preview_emel"><?= htmlspecialchars($alumni['emel']) ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Telefon</span>
                            <span class="preview-value" id="preview_telefon"><?= htmlspecialchars($alumni['no_telefon'] ?? '-') ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Program</span>
                            <span class="preview-value" id="preview_program"><?= htmlspecialchars($program_kp) ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Batch</span>
                            <span class="preview-value" id="preview_batch"><?= $alumni['batch'] ?></span>
                        </div>
                        <div class="preview-item full-width">
                            <span class="preview-label">Lokasi</span>
                            <span class="preview-value" id="preview_lokasi"><?= htmlspecialchars($alumni['lokasi'] ?? '-') ?></span>
                        </div>
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
// Auto uppercase for no matrix
document.getElementById('no_matrix').onkeyup = function() {
    this.value = this.value.toUpperCase();
    document.getElementById('preview_no_matrix').innerText = this.value || '-';
}

// Live preview for nama
document.getElementById('nama').onkeyup = function() {
    document.getElementById('preview_nama').innerText = this.value || '-';
}

// Live preview for emel
document.getElementById('emel').onkeyup = function() {
    document.getElementById('preview_emel').innerText = this.value || '-';
}

// Live preview for telefon
document.getElementById('no_telefon').onkeyup = function() {
    document.getElementById('preview_telefon').innerText = this.value || '-';
}

// Live preview for lokasi
document.getElementById('lokasi').onkeyup = function() {
    document.getElementById('preview_lokasi').innerText = this.value || '-';
}

// Live preview for batch
document.getElementById('batch').onchange = function() {
    document.getElementById('preview_batch').innerText = this.value || '-';
}
</script>

<?php include_once '../includes/footer_kp.php'; ?>