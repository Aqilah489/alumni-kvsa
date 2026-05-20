<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

$page_title = 'Tambah Alumni';
$page_css = 'alumni';
$error = '';

// Get unique programs from alumni table
$programs = $connect->query("
    SELECT DISTINCT program 
    FROM alumni 
    WHERE program IS NOT NULL AND program != '' 
    ORDER BY program
")->fetchAll();

// Jika tiada program dalam alumni, ambil dari ketua_program
if(empty($programs)) {
    $programs = $connect->query("
        SELECT DISTINCT program 
        FROM ketua_program 
        WHERE program IS NOT NULL AND program != '' 
        ORDER BY program
    ")->fetchAll();
}

// Jika masih kosong, set default
if(empty($programs)) {
    $programs = [];
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_matrix = strtoupper(trim($_POST['no_matrix']));
    $nama = ucwords(strtolower(trim($_POST['nama'])));
    $emel = strtolower(trim($_POST['emel']));
    $no_telefon = trim($_POST['no_telefon'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $program_option = $_POST['program_option'] ?? 'existing';
    $batch = trim($_POST['batch'] ?? '');

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
    
    // Default password
    $password = password_hash('alumni123', PASSWORD_DEFAULT);
    
    if(empty($no_matrix) || empty($nama) || empty($emel) || empty($program) || empty($batch)) {
        $error = "No matriks, nama, emel, program dan batch wajib diisi!";
    } else {
        // Check duplicate no_matrix
        $check = $connect->prepare("SELECT * FROM alumni WHERE no_matrix = ?");
        $check->execute([$no_matrix]);
        if($check->fetch()) {
            $error = "No matriks '$no_matrix' sudah wujud!";
        } else {
            // Check duplicate emel
            $check = $connect->prepare("SELECT * FROM alumni WHERE emel = ?");
            $check->execute([$emel]);
            if($check->fetch()) {
                $error = "Emel '$emel' sudah wujud!";
            } else {
                // INSERT
                $stmt = $connect->prepare("
                    INSERT INTO alumni (no_matrix, nama, emel, password, no_telefon, lokasi, program, batch, status_alumni) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'belum kemaskini')
                ");
                if($stmt->execute([$no_matrix, $nama, $emel, $password, $no_telefon, $lokasi, $program, $batch])) {
                    $_SESSION['success'] = "Alumni '$nama' berjaya ditambah! Password: alumni123";
                    header('Location: senarai.php');
                    exit();
                } else {
                    $error = "Gagal menambah alumni.";
                }
            }
        }
    }
}

include_once '../includes/header.php';
?>

<style>
/* Preview card styling */
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

.preview-empty .preview-value {
    color: #adb5bd;
    font-style: italic;
}
</style>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="header-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h3>Tambah Alumni Baru</h3>
            <p>Isi maklumat alumni</p>
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
                               placeholder="A22IP001" required>
                        <div class="form-hint">Gunakan huruf besar</div>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-person"></i> Nama Penuh 
                            <span class="required">*</span>
                        </label>
                        <input type="text" name="nama" class="form-control" id="nama" 
                               placeholder="Ali Bin Abu" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Emel 
                            <span class="required">*</span>
                        </label>
                        <input type="email" name="emel" class="form-control" id="emel" 
                               placeholder="ali@alumni.kvsa.edu" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-telephone"></i> No Telefon
                        </label>
                        <input type="tel" name="no_telefon" class="form-control" id="no_telefon" 
                               placeholder="012-3456789">
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
                                <option value="<?= htmlspecialchars($p['program']) ?>">
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
                            <option value="<?= $year ?>"><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Lokasi - GUNA INPUT TEXT BUKAN TEXTAREA -->
                <div class="form-row">
                    <div class="form-field full-width">
                        <label class="form-label">
                            <i class="bi bi-geo-alt"></i> Lokasi Semasa
                        </label>
                        <input type="text" name="lokasi" class="form-control" id="lokasi"
                            placeholder="Contoh: Johor Bahru/Johor (dalam Malaysia) atau Singapore (luar negara)"
                            value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>">
                        <div class="form-hint">
                            <i class="bi bi-info-circle"></i> 
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
                            <span class="preview-label">No Matriks:</span>
                            <span class="preview-value" id="preview_no_matrix">-</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Nama:</span>
                            <span class="preview-value" id="preview_nama">-</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Emel:</span>
                            <span class="preview-value" id="preview_emel">-</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Telefon:</span>
                            <span class="preview-value" id="preview_telefon">-</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Program:</span>
                            <span class="preview-value" id="preview_program">-</span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">Batch:</span>
                            <span class="preview-value" id="preview_batch">-</span>
                        </div>
                        <div class="preview-item full-width" id="preview_lokasi_container">
                            <span class="preview-label"><i class="bi bi-geo-alt"></i> Lokasi:</span>
                            <span class="preview-value" id="preview_lokasi">-</span>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="senarai.php" class="btn-back"><i class="bi bi-arrow-left"></i> Kembali</a>
                    <button type="submit" class="btn-save"><i class="bi bi-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Function to update all previews
function updatePreviews() {
    // No Matriks
    let noMatrix = document.getElementById('no_matrix').value;
    document.getElementById('preview_no_matrix').innerText = noMatrix || '-';
    
    // Nama
    let nama = document.getElementById('nama').value;
    document.getElementById('preview_nama').innerText = nama || '-';
    
    // Emel
    let emel = document.getElementById('emel').value;
    document.getElementById('preview_emel').innerText = emel || '-';
    
    // Telefon
    let telefon = document.getElementById('no_telefon').value;
    document.getElementById('preview_telefon').innerText = telefon || '-';
    
    // Lokasi - GUNA INPUT BUKAN TEXTAREA
    let lokasi = document.getElementById('lokasi').value;
    let previewLokasi = document.getElementById('preview_lokasi');
    let previewContainer = document.getElementById('preview_lokasi_container');
    if(lokasi) {
        previewLokasi.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + lokasi;
        previewContainer.classList.remove('preview-empty');
    } else {
        previewLokasi.innerHTML = '-';
        previewContainer.classList.add('preview-empty');
    }
    
    // Program
    let programOption = document.querySelector('input[name="program_option"]:checked').value;
    if(programOption === 'existing') {
        let select = document.querySelector('[name="program_existing"]');
        let text = select.options[select.selectedIndex]?.text || '-';
        document.getElementById('preview_program').innerText = text;
    } else {
        let newProgram = document.querySelector('[name="program_new"]').value;
        document.getElementById('preview_program').innerText = newProgram || 'Program Baru';
    }
    
    // Batch
    let batch = document.getElementById('batch').value;
    document.getElementById('preview_batch').innerText = batch || '-';
}

// Add event listeners to all inputs
document.getElementById('no_matrix').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
    updatePreviews();
});
document.getElementById('nama').addEventListener('input', updatePreviews);
document.getElementById('emel').addEventListener('input', updatePreviews);
document.getElementById('no_telefon').addEventListener('input', updatePreviews);
document.getElementById('lokasi').addEventListener('input', updatePreviews);  // ← FIXED: guna input bukan textarea
document.getElementById('batch').addEventListener('change', updatePreviews);

// Program option listeners
document.querySelectorAll('input[name="program_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if(this.value === 'existing') {
            document.getElementById('existing_program_div').style.display = 'block';
            document.getElementById('new_program_div').style.display = 'none';
            document.querySelector('[name="program_existing"]').required = true;
            document.querySelector('[name="program_new"]').required = false;
        } else {
            document.getElementById('existing_program_div').style.display = 'none';
            document.getElementById('new_program_div').style.display = 'block';
            document.querySelector('[name="program_existing"]').required = false;
            document.querySelector('[name="program_new"]').required = true;
        }
        updatePreviews();
    });
});

document.querySelector('[name="program_existing"]').addEventListener('change', updatePreviews);
document.querySelector('[name="program_new"]').addEventListener('input', updatePreviews);

// Initial update
updatePreviews();
</script>

<?php include_once '../includes/footer.php'; ?>