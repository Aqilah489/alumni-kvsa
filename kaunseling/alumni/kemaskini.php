<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

$id = $_GET['id'] ?? 0;
if(!$id) {
    header('Location: senarai.php');
    exit();
}

// Get alumni data
$stmt = $connect->prepare("SELECT * FROM alumni WHERE alumni_id = ?");
$stmt->execute([$id]);
$alumni = $stmt->fetch();

if(!$alumni) {
    header('Location: senarai.php');
    exit();
}

// Get existing programs for dropdown
$programs = $connect->query("
    SELECT DISTINCT program 
    FROM alumni 
    WHERE program IS NOT NULL AND program != '' 
    ORDER BY program
")->fetchAll();

// If no programs, get from ketua_program
if(empty($programs)) {
    $programs = $connect->query("
        SELECT DISTINCT program 
        FROM ketua_program 
        WHERE program IS NOT NULL AND program != '' 
        ORDER BY program
    ")->fetchAll();
}

$page_title = 'Kemaskini Alumni';
$page_css = 'alumni';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_matrix = strtoupper(trim($_POST['no_matrix']));
    $nama = ucwords(strtolower(trim($_POST['nama'])));
    $emel = strtolower(trim($_POST['emel']));
    $no_telefon = trim($_POST['no_telefon'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $program_option = $_POST['program_option'] ?? 'existing';
    $batch = trim($_POST['batch'] ?? '');
    $status_alumni = $_POST['status_alumni'] ?? $alumni['status_alumni'];
    
    if($program_option == 'existing') {
        $program = trim($_POST['program_existing'] ?? '');
        if(empty($program)) {
            $error = "Sila pilih program!";
        }
    } else {
        $program = ucwords(strtolower(trim($_POST['program_new'] ?? '')));
        if(empty($program)) {
            $error = "Sila masukkan nama program baru!";
        }
    }
    
    if(empty($no_matrix) || empty($nama) || empty($emel) || empty($program) || empty($batch)) {
        $error = "No matriks, nama, emel, program dan batch wajib diisi!";
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
                // Update alumni
                $stmt = $connect->prepare("
                    UPDATE alumni SET 
                        no_matrix = ?, 
                        nama = ?, 
                        emel = ?, 
                        no_telefon = ?, 
                        lokasi = ?, 
                        program = ?, 
                        batch = ?,
                        status_alumni = ?,
                        tarikh_kemaskini = CURDATE()
                    WHERE alumni_id = ?
                ");
                if($stmt->execute([$no_matrix, $nama, $emel, $no_telefon, $lokasi, $program, $batch, $status_alumni, $id])) {
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

include_once '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="header-icon">
                <i class="bi bi-pencil-square"></i>
            </div>
            <h3>Kemaskini Alumni</h3>
            <p>Edit maklumat alumni</p>
        </div>
        
        <div class="form-body">
            <?php if(!empty($error)): ?>
                <div class="alert-custom alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-upc-scan"></i> No Matriks 
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="no_matrix" class="form-control" id="no_matrix" 
                               value="<?= htmlspecialchars($alumni['no_matrix']) ?>" required>
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
                        <input type="tel" name="no_telefon" class="form-control" 
                               value="<?= htmlspecialchars($alumni['no_telefon'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-mortarboard"></i> Program 
                            <span class="required">*</span>
                        </label>
                        
                        <!-- Radio button pilih dari senarai atau baru -->
                        <div class="program-option" style="display: flex; gap: 20px; margin-bottom: 12px;">
                            <label class="radio-option" style="display: flex; align-items: center; gap: 5px;">
                                <input type="radio" name="program_option" value="existing" checked> Pilih dari senarai
                            </label>
                            <label class="radio-option" style="display: flex; align-items: center; gap: 5px;">
                                <input type="radio" name="program_option" value="new"> Program baru
                            </label>
                        </div>
                        
                        <!-- Dropdown program sedia ada -->
                        <div id="existing_program_div">
                            <select name="program_existing" class="form-select">
                                <option value="">-- Pilih Program --</option>
                                <?php foreach($programs as $p): ?>
                                <option value="<?= htmlspecialchars($p['program']) ?>" <?= $alumni['program'] == $p['program'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['program']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Input untuk program baru -->
                        <div id="new_program_div" style="display: none;">
                            <input type="text" name="program_new" class="form-control" placeholder="Masukkan nama program baru...">
                        </div>
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
                            <i class="bi bi-geo-alt"></i> lokasi
                        </label>
                        <textarea name="lokasi" class="form-control" rows="2"><?= htmlspecialchars($alumni['lokasi'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="preview-card">
                    <div class="preview-header">
                        <i class="bi bi-eye"></i>
                        <span>Preview Alumni</span>
                    </div>
                    <div class="preview-grid">
                        <div class="preview-item"><span class="preview-label">No Matriks:</span><span class="preview-value" id="preview_no_matrix"><?= htmlspecialchars($alumni['no_matrix']) ?></span></div>
                        <div class="preview-item"><span class="preview-label">Nama:</span><span class="preview-value" id="preview_nama"><?= htmlspecialchars($alumni['nama']) ?></span></div>
                        <div class="preview-item"><span class="preview-label">Emel:</span><span class="preview-value" id="preview_emel"><?= htmlspecialchars($alumni['emel']) ?></span></div>
                        <div class="preview-item"><span class="preview-label">Telefon:</span><span class="preview-value" id="preview_telefon"><?= htmlspecialchars($alumni['no_telefon'] ?? '-') ?></span></div>
                        <div class="preview-item"><span class="preview-label">Program:</span><span class="preview-value" id="preview_program"><?= htmlspecialchars($alumni['program']) ?></span></div>
                        <div class="preview-item"><span class="preview-label">Batch:</span><span class="preview-value" id="preview_batch"><?= $alumni['batch'] ?></span></div>
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
document.getElementById('no_matrix').onkeyup = function() {
    this.value = this.value.toUpperCase();
    document.getElementById('preview_no_matrix').innerText = this.value || '-';
}
document.getElementById('nama').onkeyup = function() {
    document.getElementById('preview_nama').innerText = this.value || '-';
}
document.getElementById('emel').onkeyup = function() {
    document.getElementById('preview_emel').innerText = this.value || '-';
}
document.getElementById('no_telefon').onkeyup = function() {
    document.getElementById('preview_telefon').innerText = this.value || '-';
}

// Toggle between existing and new program
document.querySelectorAll('input[name="program_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if(this.value === 'existing') {
            document.getElementById('existing_program_div').style.display = 'block';
            document.getElementById('new_program_div').style.display = 'none';
            document.querySelector('[name="program_existing"]').required = true;
            document.querySelector('[name="program_new"]').required = false;
            
            let select = document.querySelector('[name="program_existing"]');
            let text = select.options[select.selectedIndex]?.text || '-';
            document.getElementById('preview_program').innerText = text;
        } else {
            document.getElementById('existing_program_div').style.display = 'none';
            document.getElementById('new_program_div').style.display = 'block';
            document.querySelector('[name="program_existing"]').required = false;
            document.querySelector('[name="program_new"]').required = true;
            document.getElementById('preview_program').innerText = 'Program Baru';
        }
    });
});

document.querySelector('[name="program_existing"]').addEventListener('change', function() {
    let text = this.options[this.selectedIndex]?.text || '-';
    document.getElementById('preview_program').innerText = text;
});

document.querySelector('[name="program_new"]').addEventListener('keyup', function() {
    document.getElementById('preview_program').innerText = this.value || 'Program Baru';
});

document.getElementById('batch').onchange = function() {
    document.getElementById('preview_batch').innerText = this.value || '-';
}


</script>

<?php include_once '../includes/footer.php'; ?>