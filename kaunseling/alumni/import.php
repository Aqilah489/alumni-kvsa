<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

$page_title = 'Import Alumni';
$page_css = 'alumni';
$error = '';
$success = '';
$inserted = 0;
$failed = 0;
$failed_list = [];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    if($file['error'] != 0) {
        $error = "Ralat upload file.";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if($ext != 'csv') {
            $error = "Format file mesti .csv";
        } else {
            $handle = fopen($file['tmp_name'], 'r');
            // Skip header row
            $header = fgetcsv($handle);
            
            $row_num = 1;
            while(($data = fgetcsv($handle)) !== false) {
                $row_num++;
                $no_matrix = strtoupper(trim($data[0] ?? ''));
                $nama = ucwords(strtolower(trim($data[1] ?? '')));
                $emel = strtolower(trim($data[2] ?? ''));
                $batch_tahun = trim($data[3] ?? '');
                $program = ucwords(strtolower(trim($data[4] ?? '')));
                $no_telefon = trim($data[5] ?? '');
                $lokasi = trim($data[6] ?? '');
                
                // Validation
                if(empty($no_matrix) || empty($nama) || empty($emel) || empty($batch_tahun) || empty($program)) {
                    $failed++;
                    $failed_list[] = "Baris $row_num: Data tidak lengkap (No Matriks, Nama, Emel, Batch, Program wajib)";
                    continue;
                }
                
                // Check duplicate no_matrix
                $check = $connect->prepare("SELECT * FROM alumni WHERE no_matrix = ?");
                $check->execute([$no_matrix]);
                if($check->fetch()) {
                    $failed++;
                    $failed_list[] = "Baris $row_num: No matriks '$no_matrix' sudah wujud";
                    continue;
                }
                
                // Check duplicate emel
                $check = $connect->prepare("SELECT * FROM alumni WHERE emel = ?");
                $check->execute([$emel]);
                if($check->fetch()) {
                    $failed++;
                    $failed_list[] = "Baris $row_num: Emel '$emel' sudah wujud";
                    continue;
                }
                
                $password = password_hash('alumni123', PASSWORD_DEFAULT);
                
                // Insert only basic info, status default 'belum kemaskini'
                $stmt = $connect->prepare("
                    INSERT INTO alumni (
                        no_matrix, nama, emel, password, no_telefon, lokasi, 
                        program, batch, status_alumni
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, 
                        ?, ?, 'belum kemaskini'
                    )
                ");
                
                if($stmt->execute([
                    $no_matrix, $nama, $emel, $password, $no_telefon, $lokasi,
                    $program, $batch_tahun
                ])) {
                    $inserted++;
                } else {
                    $failed++;
                    $failed_list[] = "Baris $row_num: Gagal insert ke database";
                }
            }
            fclose($handle);
            
            if($inserted > 0) {
                $success = "✅ $inserted alumni berjaya diimport. Password: alumni123";
            }
            if($failed > 0) {
                $error = "❌ $failed rekod gagal diimport.";
            }
        }
    }
}

include_once '../includes/header.php';
?>

<style>
/* Import page styles */
.import-container {
    max-width: 900px;
    margin: 0 auto;
}

/* Form Card */
.form-card {
    margin-bottom: 20px;
}

.form-body {
    padding: 25px;
}

/* Format Guide */
.format-guide {
    background: #f8fafc;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 25px;
    border: 1px solid #e2e8f0;
}

.format-guide h4 {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.format-table {
    width: 100%;
    font-size: 0.7rem;
    border-collapse: collapse;
    margin-bottom: 15px;
}

.format-table th,
.format-table td {
    padding: 8px 8px;
    text-align: left;
    border: 1px solid #e2e8f0;
}

.format-table th {
    background: #f1f5f9;
    font-weight: 600;
    white-space: nowrap;
}

.format-table td {
    white-space: nowrap;
}

.format-table .example-row td {
    background: #fef5e7;
}

.format-note {
    margin-top: 15px;
    padding: 12px 15px;
    background: #e3f2fd;
    border-radius: 10px;
    font-size: 0.7rem;
    color: #0c5460;
}

.format-note ul {
    margin: 0px 0 0 0px;
    padding-left: 0;
}

.format-note li {
    margin-bottom: 3px;
}

/* File Input Area */
.file-input-area {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 30px 20px;
    text-align: center;
    margin: 20px 0 25px 0;
    cursor: pointer;
    transition: all 0.2s;
}

.file-input-area:hover {
    border-color: #28a745;
    background: #d4edda;
}

.file-input-area i {
    font-size: 2rem;
    color: #94a3b8;
    margin-bottom: 8px;
}

.file-input-area p {
    font-size: 0.8rem;
    color: #475569;
    margin: 0;
}

.file-input-area .file-hint {
    font-size: 0.65rem;
    color: #94a3b8;
    margin-top: 5px;
}

.file-input-area.file-selected {
    border-color: #28a745;
    background: #d4edda;
}

.file-input-area.file-selected i {
    color: #28a745;
}

.file-name {
    margin-top: 8px;
    font-size: 0.7rem;
    color: #28a745;
    font-weight: 500;
}

/* Import Actions */
.import-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn-import-submit {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-import-submit:hover {
    background: #218838;
    transform: translateY(-1px);
}

.btn-download-template {
    background: #17a2b8;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-download-template:hover {
    background: #138496;
    transform: translateY(-1px);
    color: white;
}

.btn-back {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-back:hover {
    background: #e9ecef;
}

/* Alerts */
.alert-custom {
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 10px;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-danger {
    background: #fee2e2;
    color: #e74c3c;
    border: 1px solid #fccaca;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-warning-custom {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

/* Header import */
.import-header {
    background: #28a745 !important;
}

/* Responsive */
@media (max-width: 768px) {
    .format-table {
        display: block;
        overflow-x: auto;
    }
    
    .import-actions {
        flex-direction: column;
    }
    
    .btn-import-submit,
    .btn-download-template,
    .btn-back {
        width: 100%;
        justify-content: center;
    }
    
    .form-body {
        padding: 20px;
    }
}
</style>

<div class="import-container">
    <div class="form-card">
        <div class="form-header import-header">
            <div class="header-icon">
                <i class="bi bi-file-spreadsheet-fill"></i>
            </div>
            <h3>Import Alumni dari CSV</h3>
            <p>Muat naik fail CSV untuk tambah alumni beramai-ramai</p>
        </div>
        
        <div class="form-body">
            <?php if($error): ?>
                <div class="alert-custom alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert-custom alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($failed_list)): ?>
                <div class="alert-custom alert-warning-custom">
                    <i class="bi bi-info-circle-fill"></i>
                    <div style="flex: 1">
                        <strong>Detail Error (<?= count($failed_list) ?> rekod gagal):</strong><br>
                        <ul style="margin: 5px 0 0 20px; font-size: 0.65rem;">
                            <?php foreach(array_slice($failed_list, 0, 10) as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                            <?php if(count($failed_list) > 10): ?>
                                <li>... dan <?= count($failed_list) - 10 ?> error lain</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Format Guide -->
            <div class="format-guide">
                <h4><i class="bi bi-table"></i> Format CSV:</h4>
                <table class="format-table">
                    <thead>
                        <tr>
                            <th>Column 1</th><th>Column 2</th><th>Column 3</th>
                            <th>Column 4</th><th>Column 5</th><th>Column 6</th><th>Column 7</th>
                        </thead>
                        <tbody>
                            <tr style="background: #f1f5f9;">
                                <th>No Matriks</th>
                                <th>Nama</th>
                                <th>Emel</th>
                                <th>Batch</th>
                                <th>Program</th>
                                <th>No Telefon</th>
                                <th>Lokasi</th>
                             </tr>
                            <tr class="example-row">
                                <td>A221P001</td>
                                <td>Ali Bin Abu</td>
                                <td>ali@alumni.kvsa.edu</td>
                                <td>2024</td>
                                <td>Dpl Teknologi Komputeran</td>
                                <td>012-3456789</td>
                                <td>Johor Bahru/Johor</td>
                             </tr>
                        </tbody>
                    </table>
                <div class="format-note">
                    <div>
                        <strong>Nota Format CSV:</strong>
                        <ul>
                            <li>Baris pertama (header) akan diabaikan semasa import</li>
                            <li>Wajib diisi: <strong>No Matriks, Nama, Emel, Batch, Program</strong></li>
                            <li>Lokasi: format <strong>Daerah/Negeri</strong> (contoh: Johor Bahru/Johor) atau <strong>Bandar/Negara</strong> (luar negara)</li>
                            <li>Password default untuk semua alumni: <strong>alumni123</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="importForm">
                <!-- File Input Area -->
                <div class="file-input-area" id="fileInputArea" onclick="document.getElementById('csvFile').click()">
                    <i class="bi bi-cloud-upload" id="uploadIcon"></i>
                    <p id="uploadText">Klik atau seret fail CSV ke sini</p>
                    <div class="file-hint">Maksimum 5MB, format .csv</div>
                    <input type="file" name="csv_file" id="csvFile" accept=".csv" required style="display: none">
                    <div class="file-name" id="fileName"></div>
                </div>
                
                <div class="import-actions">
                    <a href="senarai.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn-import-submit" id="importBtn">
                        <i class="bi bi-upload"></i> Import
                    </button>
                    <a href="template_alumni.csv" download class="btn-download-template">
                        <i class="bi bi-download"></i> Download Template
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// File input preview
const csvFile = document.getElementById('csvFile');
const fileNameSpan = document.getElementById('fileName');
const fileInputArea = document.getElementById('fileInputArea');
const uploadIcon = document.getElementById('uploadIcon');
const uploadText = document.getElementById('uploadText');

csvFile.addEventListener('change', function(e) {
    const file = e.target.files[0];
    
    if(file) {
        fileNameSpan.innerHTML = '<i class="bi bi-check-circle-fill"></i> ' + file.name;
        fileInputArea.classList.add('file-selected');
        uploadIcon.className = 'bi bi-file-earmark-spreadsheet-fill';
        uploadText.innerHTML = 'File sedia untuk diimport';
    } else {
        fileNameSpan.innerHTML = '';
        fileInputArea.classList.remove('file-selected');
        uploadIcon.className = 'bi bi-cloud-upload';
        uploadText.innerHTML = 'Klik atau seret fail CSV ke sini';
    }
});

// Drag and drop
const dropZone = document.getElementById('fileInputArea');

dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.style.borderColor = '#28a745';
    this.style.background = '#d4edda';
});

dropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.style.borderColor = '#cbd5e1';
    this.style.background = '#f8fafc';
});

dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.borderColor = '#cbd5e1';
    this.style.background = '#f8fafc';
    
    const file = e.dataTransfer.files[0];
    if(file && file.name.endsWith('.csv')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        csvFile.files = dt.files;
        
        // Trigger change event
        const event = new Event('change', { bubbles: true });
        csvFile.dispatchEvent(event);
    } else {
        alert('Sila pilih fail CSV sahaja!');
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>