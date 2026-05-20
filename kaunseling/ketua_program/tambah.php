<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

$page_title = 'Tambah Ketua Program';
$page_css = 'alumni';
$error = '';

// Dapatkan program yang BELUM ada ketua program (dari alumni)
$programs = $connect->query("
    SELECT DISTINCT a.program 
    FROM alumni a
    LEFT JOIN ketua_program k ON a.program = k.program
    WHERE a.program IS NOT NULL 
    AND a.program != ''
    AND k.program IS NULL
    ORDER BY a.program
")->fetchAll();

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
    $password = password_hash('kp123', PASSWORD_DEFAULT);
    
    if(empty($nama) || empty($emel)) {
        $error = "Nama dan emel wajib diisi!";
    }
    
    if(empty($error)) {
        // Check duplicate emel
        $check = $connect->prepare("SELECT * FROM ketua_program WHERE emel = ?");
        $check->execute([$emel]);
        if($check->fetch()) {
            $error = "Emel '$emel' sudah wujud!";
        } else {
            // Check program dah ada ketua ke belum
            $check = $connect->prepare("SELECT * FROM ketua_program WHERE program = ?");
            $check->execute([$program]);
            if($check->fetch()) {
                $error = "Program '$program' sudah mempunyai Ketua Program!";
            } else {
                $stmt = $connect->prepare("INSERT INTO ketua_program (nama, emel, password, program, jawatan, status) VALUES (?, ?, ?, ?, ?, ?)");
                if($stmt->execute([$nama, $emel, $password, $program, $jawatan, $status])) {
                    $_SESSION['success'] = "Ketua Program '$nama' berjaya ditambah! (Password: kp123)";
                    header('Location: senarai.php');
                    exit();
                } else {
                    $error = "Gagal menambah ketua program.";
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
                <i class="bi bi-person-badge"></i>
            </div>
            <h3>Tambah Ketua Program Baru</h3>
            <p>Isi maklumat ketua program</p>
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
                        <input type="text" name="nama" class="form-control" id="nama" placeholder="Alif Bin Abu" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Emel 
                            <span class="required">*</span>
                        </label>
                        <input type="email" name="emel" class="form-control" id="emel" placeholder="kp123@kvsa.edu" required>
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
                                <input type="radio" name="program_option" value="existing" checked> Pilih dari senarai
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="program_option" value="new"> Program baru
                            </label>
                        </div>
                        
                        <!-- Dropdown program sedia ada -->
                        <div id="existing_program_div">
                            <select name="program_existing" class="form-select">
                                <option value="">-- Pilih Program --</option>
                                <?php foreach($programs as $p): ?>
                                <option value="<?= htmlspecialchars($p['program']) ?>">
                                    <?= htmlspecialchars($p['program']) ?>
                                </option>
                                <?php endforeach; ?>
                                <option value="Lain-lain">Lain-lain (Isi Manual)</option>
                            </select>
                            <div class="form-hint">Program yang sudah wujud dalam sistem alumni.</div>
                        </div>
                        
                        <!-- Input untuk program baru -->
                        <div id="new_program_div" style="display: none;">
                            <input type="text" name="program_new" class="form-control" placeholder="Masukkan nama program baru...">
                            <div class="form-hint">Program baru akan ditambah untuk program ini.</div>
                        </div>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-briefcase"></i> Jawatan
                        </label>
                        <input type="text" name="jawatan" class="form-control" value="Ketua Program">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-toggle-on"></i> Status
                        </label>
                        <select name="status" class="form-select" id="status">
                            <option value="aktif">Aktif</option>
                            <option value="bersara">Bersara</option>
                            <option value="cuti">Cuti</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-key"></i> Password
                        </label>
                        <input type="text" class="form-control" value="kp123" disabled>
                        <div class="form-hint">Password default: kp123</div>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="preview-card">
                    <div class="preview-header">
                        <i class="bi bi-eye"></i>
                        <span>Preview Ketua Program</span>
                    </div>
                    <div class="preview-grid">
                        <div class="preview-item"><span class="preview-label">Nama:</span><span class="preview-value" id="preview_nama">-</span></div>
                        <div class="preview-item"><span class="preview-label">Emel:</span><span class="preview-value" id="preview_emel">-</span></div>
                        <div class="preview-item"><span class="preview-label">Program:</span><span class="preview-value" id="preview_program">-</span></div>
                        <div class="preview-item"><span class="preview-label">Status:</span><span class="preview-value" id="preview_status">-</span></div>
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