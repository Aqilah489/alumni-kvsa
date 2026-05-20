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

$stmt = $connect->prepare("SELECT * FROM ketua_program WHERE kp_id = ?");
$stmt->execute([$id]);
$kp = $stmt->fetch();

if(!$kp) {
    header('Location: senarai.php');
    exit();
}

$page_title = 'Kemaskini Ketua Program';
$page_css = 'alumni';
$error = '';

// Get unique programs from alumni table
$programs = $connect->query("
    SELECT DISTINCT program 
    FROM alumni 
    WHERE program IS NOT NULL AND program != '' 
    ORDER BY program
")->fetchAll();

// Check if current program exists in list
$program_exists = false;
foreach($programs as $p) {
    if($p['program'] == $kp['program']) {
        $program_exists = true;
        break;
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $emel = trim($_POST['emel']);
    $program_option = $_POST['program_option'];
    
    if($program_option == 'existing') {
        $program = $_POST['program_existing'];
        if(empty($program)) {
            $error = "Sila pilih program!";
        }
    } else {
        $program = trim($_POST['program_new']);
        if(empty($program)) {
            $error = "Sila masukkan nama program baru!";
        }
    }
    
    $jawatan = trim($_POST['jawatan'] ?? 'Ketua Program');
    $status = $_POST['status'] ?? 'aktif';
    
    if(empty($nama) || empty($emel)) {
        $error = "Nama dan emel wajib diisi!";
    }
    
    if(empty($error)) {
        // Check duplicate emel (exclude current)
        $check = $connect->prepare("SELECT * FROM ketua_program WHERE emel = ? AND kp_id != ?");
        $check->execute([$emel, $id]);
        if($check->fetch()) {
            $error = "Emel '$emel' sudah wujud!";
        } else {
            $stmt = $connect->prepare("UPDATE ketua_program SET nama = ?, emel = ?, program = ?, jawatan = ?, status = ? WHERE kp_id = ?");
            if($stmt->execute([$nama, $emel, $program, $jawatan, $status, $id])) {
                $_SESSION['success'] = "Ketua Program '$nama' berjaya dikemaskini!";
                header('Location: senarai.php');
                exit();
            } else {
                $error = "Gagal mengemaskini ketua program.";
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
            <h3>Kemaskini Ketua Program</h3>
            <p>Edit maklumat ketua program</p>
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
                            <i class="bi bi-person"></i> Nama Penuh 
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="nama" class="form-control" id="nama" value="<?= htmlspecialchars($kp['nama']) ?>" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Emel 
                            <span class="required">*</span>
                        </label>
                        <input type="email" name="emel" class="form-control" id="emel" value="<?= htmlspecialchars($kp['emel']) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-mortarboard"></i> Program 
                            <span class="required">*</span>
                        </label>
                        
                        <!-- Radio button pilih dari senarai atau baru -->
                        <div class="program-option">
                            <label class="radio-option">
                                <input type="radio" name="program_option" value="existing" <?= $program_exists ? 'checked' : '' ?>> Pilih dari senarai
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="program_option" value="new" <?= !$program_exists ? 'checked' : '' ?>> Program baru
                            </label>
                        </div>
                        
                        <!-- Dropdown program sedia ada -->
                        <div id="existing_program_div" style="<?= !$program_exists ? 'display: none;' : '' ?>">
                            <select name="program_existing" class="form-select">
                                <option value="">-- Pilih Program --</option>
                                <?php foreach($programs as $p): ?>
                                <option value="<?= htmlspecialchars($p['program']) ?>" <?= ($kp['program'] == $p['program']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['program']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Input untuk program baru -->
                        <div id="new_program_div" style="<?= $program_exists ? 'display: none;' : '' ?>">
                            <input type="text" name="program_new" class="form-control" value="<?= !$program_exists ? htmlspecialchars($kp['program']) : '' ?>" placeholder="Masukkan nama program baru...">
                        </div>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-briefcase"></i> Jawatan
                        </label>
                        <input type="text" name="jawatan" class="form-control" value="<?= htmlspecialchars($kp['jawatan'] ?? 'Ketua Program') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-toggle-on"></i> Status
                        </label>
                        <select name="status" class="form-select" id="status">
                            <option value="aktif" <?= $kp['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="bersara" <?= $kp['status'] == 'bersara' ? 'selected' : '' ?>>Bersara</option>
                            <option value="cuti" <?= $kp['status'] == 'cuti' ? 'selected' : '' ?>>Cuti</option>
                        </select>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="preview-card">
                    <div class="preview-header">
                        <i class="bi bi-eye"></i>
                        <span>Preview Ketua Program</span>
                    </div>
                    <div class="preview-grid">
                        <div class="preview-item"><span class="preview-label">Nama:</span><span class="preview-value" id="preview_nama"><?= htmlspecialchars($kp['nama']) ?></span></div>
                        <div class="preview-item"><span class="preview-label">Emel:</span><span class="preview-value" id="preview_emel"><?= htmlspecialchars($kp['emel']) ?></span></div>
                        <div class="preview-item"><span class="preview-label">Program:</span><span class="preview-value" id="preview_program"><?= htmlspecialchars($kp['program']) ?></span></div>
                        <div class="preview-item"><span class="preview-label">Status:</span><span class="preview-value" id="preview_status"><?= $kp['status'] == 'aktif' ? 'Aktif' : ($kp['status'] == 'bersara' ? 'Bersara' : 'Cuti') ?></span></div>
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

<style>
.program-option {
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
}
.radio-option {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8rem;
    cursor: pointer;
}
</style>

<script>
// Live preview
document.getElementById('nama').onkeyup = function() {
    document.getElementById('preview_nama').innerText = this.value || '-';
}
document.getElementById('emel').onkeyup = function() {
    document.getElementById('preview_emel').innerText = this.value || '-';
}
document.getElementById('status').onchange = function() {
    let text = this.options[this.selectedIndex]?.text || '-';
    document.getElementById('preview_status').innerText = text;
}

// Toggle between existing and new program
document.querySelectorAll('input[name="program_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if(this.value === 'existing') {
            document.getElementById('existing_program_div').style.display = 'block';
            document.getElementById('new_program_div').style.display = 'none';
            document.querySelector('[name="program_existing"]').required = true;
            document.querySelector('[name="program_new"]').required = false;
            
            // Update preview
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

// Update preview when select changes
document.querySelector('[name="program_existing"]').addEventListener('change', function() {
    let text = this.options[this.selectedIndex]?.text || '-';
    document.getElementById('preview_program').innerText = text;
});

// Update preview when new program input changes
document.querySelector('[name="program_new"]').addEventListener('keyup', function() {
    if(this.value) {
        document.getElementById('preview_program').innerText = this.value;
    } else {
        document.getElementById('preview_program').innerText = 'Program Baru';
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>