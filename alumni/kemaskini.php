<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check login & role
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'alumni') {
    header('Location: ../index.php');
    exit();
}

$alumni_id = $_SESSION['user_id'];

// Get alumni data
$stmt = $connect->prepare("SELECT * FROM alumni WHERE alumni_id = ?");
$stmt->execute([$alumni_id]);
$alumni = $stmt->fetch();

if(!$alumni) {
    header('Location: ../index.php');
    exit();
}

$page_title = 'Kemaskini Profil';
$page_css = 'alumni';
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emel = strtolower(trim($_POST['emel']));
    $no_telefon = trim($_POST['no_telefon'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $status_alumni = $_POST['status_alumni'] ?? '';
    
    // Fields based on status
    $pekerjaan = null;
    $tempat_kerja = null;
    $jawatan = null;
    $julat_gaji = null;
    $institusi = null;
    $bidang_pengajian = null;
    $nama_perniagaan = null;
    $bidang_perniagaan = null;
    
    if($status_alumni == 'bekerja') {
        $pekerjaan = trim($_POST['pekerjaan'] ?? '');
        $tempat_kerja = trim($_POST['tempat_kerja'] ?? '');
        $jawatan = trim($_POST['jawatan'] ?? '');
        $julat_gaji = trim($_POST['julat_gaji'] ?? '');
    } elseif($status_alumni == 'sambung belajar') {
        $institusi = trim($_POST['institusi'] ?? '');
        $bidang_pengajian = trim($_POST['bidang_pengajian'] ?? '');
    } elseif($status_alumni == 'usahawan') {
        $nama_perniagaan = trim($_POST['nama_perniagaan'] ?? '');
        $bidang_perniagaan = trim($_POST['bidang_perniagaan'] ?? '');
    }
    
    // Check duplicate emel (except current)
    $check = $connect->prepare("SELECT * FROM alumni WHERE emel = ? AND alumni_id != ?");
    $check->execute([$emel, $alumni_id]);
    if($check->fetch()) {
        $error = "Emel sudah digunakan oleh alumni lain!";
    } else {
        // Update database
        $stmt = $connect->prepare("
            UPDATE alumni SET 
                emel = ?,
                no_telefon = ?,
                lokasi = ?,
                status_alumni = ?,
                pekerjaan = ?,
                tempat_kerja = ?,
                jawatan = ?,
                julat_gaji = ?,
                institusi = ?,
                bidang_pengajian = ?,
                nama_perniagaan = ?,
                bidang_perniagaan = ?,
                tarikh_kemaskini = CURDATE()
            WHERE alumni_id = ?
        ");
        
        if($stmt->execute([$emel, $no_telefon, $lokasi, $status_alumni, 
                          $pekerjaan, $tempat_kerja, $jawatan, $julat_gaji,
                          $institusi, $bidang_pengajian, $nama_perniagaan, $bidang_perniagaan,
                          $alumni_id])) {
            $_SESSION['emel'] = $emel;
            $_SESSION['success'] = "Profil berjaya dikemaskini!";
            header('Location: profil.php');
            exit();
        } else {
            $error = "Gagal mengemaskini profil. Sila cuba lagi.";
        }
    }
}

include_once 'includes/header_alumni.php';
?>

<style>
/* Dynamic fields styling */
.dynamic-fields {
    transition: all 0.3s ease;
    margin-top: 10px;
}

.info-box {
    background: #e3f2fd;
    border-radius: 12px;
    padding: 15px;
    margin: 20px 0;
    border: 1px solid #b8e1fc;
}

.info-box-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-weight: 600;
    color: #0c5460;
}

.info-box-content ul {
    margin: 0;
    padding-left: 20px;
    font-size: 0.7rem;
    color: #0c5460;
}

.info-box-content li {
    margin-bottom: 4px;
}

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
    padding: 10px 12px;
    background: white;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.preview-item.status-item {
    grid-column: span 2;
}

.preview-dynamic {
    grid-column: span 2;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.preview-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.preview-value {
    font-size: 0.8rem;
    font-weight: 500;
    color: #2c3e50;
    word-break: break-word;
    text-align: right;
    max-width: 60%;
}
</style>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="header-icon">
                <i class="bi bi-pencil-square"></i>
            </div>
            <h3>Kemaskini Profil</h3>
            <p>Kemaskini maklumat peribadi dan pekerjaan anda</p>
        </div>
        
        <div class="form-body">
            <?php if($error): ?>
                <div class="alert-custom alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="profileForm">
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-person"></i> Nama Penuh
                        </label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($alumni['nama']) ?>" disabled>
                        <div class="form-hint">Nama tidak boleh ditukar</div>
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Emel
                            <span class="required">*</span>
                        </label>
                        <input type="email" name="emel" class="form-control" id="emel"
                               value="<?= htmlspecialchars($alumni['emel']) ?>" required>
                        <div class="form-hint">Emel akan digunakan untuk login</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-telephone"></i> No Telefon
                        </label>
                        <input type="tel" name="no_telefon" class="form-control" id="no_telefon"
                               value="<?= htmlspecialchars($alumni['no_telefon'] ?? '') ?>"
                               placeholder="012-3456789">
                    </div>
                    <div class="form-field">
                        <label class="form-label">
                            <i class="bi bi-person"></i> Status Alumni
                            <span class="required">*</span>
                        </label>
                        <select name="status_alumni" id="status_alumni" class="form-select" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="bekerja" <?= $alumni['status_alumni'] == 'bekerja' ? 'selected' : '' ?>>Bekerja</option>
                            <option value="sambung belajar" <?= $alumni['status_alumni'] == 'sambung belajar' ? 'selected' : '' ?>>Sambung Belajar</option>
                            <option value="usahawan" <?= $alumni['status_alumni'] == 'usahawan' ? 'selected' : '' ?>>Usahawan</option>
                            <option value="belum bekerja" <?= $alumni['status_alumni'] == 'belum bekerja' ? 'selected' : '' ?>>Belum Bekerja</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Fields for BEKERJA -->
                <div id="bekerja_fields" class="dynamic-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-field">
                            <label class="form-label">
                                <i class="bi bi-briefcase"></i> Pekerjaan
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="pekerjaan" id="pekerjaan" class="form-control" 
                                   value="<?= htmlspecialchars($alumni['pekerjaan'] ?? '') ?>"
                                   placeholder="Contoh: Software Engineer">
                        </div>
                        <div class="form-field">
                            <label class="form-label">
                                <i class="bi bi-building"></i> Tempat Kerja
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="tempat_kerja" id="tempat_kerja" class="form-control" 
                                   value="<?= htmlspecialchars($alumni['tempat_kerja'] ?? '') ?>"
                                   placeholder="Contoh: XYZ Sdn Bhd">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-field">
                            <label class="form-label">
                                <i class="bi bi-person-badge"></i> Jawatan
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="jawatan" id="jawatan" class="form-control" 
                                   value="<?= htmlspecialchars($alumni['jawatan'] ?? '') ?>"
                                   placeholder="Contoh: Senior Developer">
                        </div>
                        <div class="form-field">
                            <label class="form-label">
                                <i class="bi bi-cash-stack"></i> Julat Gaji
                            </label>
                            <select name="julat_gaji" id="julat_gaji" class="form-select">
                                <option value="">-- Pilih Julat Gaji --</option>
                                <option value="RM1000 - RM2000" <?= ($alumni['julat_gaji'] ?? '') == 'RM1000 - RM2000' ? 'selected' : '' ?>>RM1000 - RM2000</option>
                                <option value="RM2001 - RM3000" <?= ($alumni['julat_gaji'] ?? '') == 'RM2001 - RM3000' ? 'selected' : '' ?>>RM2001 - RM3000</option>
                                <option value="RM3001 - RM4000" <?= ($alumni['julat_gaji'] ?? '') == 'RM3001 - RM4000' ? 'selected' : '' ?>>RM3001 - RM4000</option>
                                <option value="RM4001 - RM5000" <?= ($alumni['julat_gaji'] ?? '') == 'RM4001 - RM5000' ? 'selected' : '' ?>>RM4001 - RM5000</option>
                                <option value="RM5001 - RM6000" <?= ($alumni['julat_gaji'] ?? '') == 'RM5001 - RM6000' ? 'selected' : '' ?>>RM5001 - RM6000</option>
                                <option value="RM6001 - RM7000" <?= ($alumni['julat_gaji'] ?? '') == 'RM6001 - RM7000' ? 'selected' : '' ?>>RM6001 - RM7000</option>
                                <option value="RM7001 - RM8000" <?= ($alumni['julat_gaji'] ?? '') == 'RM7001 - RM8000' ? 'selected' : '' ?>>RM7001 - RM8000</option>
                                <option value="RM8001 - RM9000" <?= ($alumni['julat_gaji'] ?? '') == 'RM8001 - RM9000' ? 'selected' : '' ?>>RM8001 - RM9000</option>
                                <option value="RM9001 - RM10000" <?= ($alumni['julat_gaji'] ?? '') == 'RM9001 - RM10000' ? 'selected' : '' ?>>RM9001 - RM10000</option>
                                <option value="RM10001 ke atas" <?= ($alumni['julat_gaji'] ?? '') == 'RM10001 ke atas' ? 'selected' : '' ?>>RM10001 ke atas</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Fields for SAMBUNG BELAJAR -->
                <div id="sambung_belajar_fields" class="dynamic-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-field">
                            <label class="form-label">
                                <i class="bi bi-building"></i> Institusi Pengajian
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="institusi" id="institusi" class="form-control" 
                                   value="<?= htmlspecialchars($alumni['institusi'] ?? '') ?>"
                                   placeholder="Nama universiti/kolej">
                        </div>
                        <div class="form-field">
                            <label class="form-label">
                                <i class="bi bi-book"></i> Bidang Pengajian
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="bidang_pengajian" id="bidang_pengajian" class="form-control" 
                                   value="<?= htmlspecialchars($alumni['bidang_pengajian'] ?? '') ?>"
                                   placeholder="Contoh: Ijazah Sarjana Muda Teknologi Maklumat">
                        </div>
                    </div>
                </div>

                <!-- Dynamic Fields for USAHAWAN -->
                <div id="usahawan_fields" class="dynamic-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-field">
                            <label class="form-label">
                                <i class="bi bi-shop"></i> Nama Perniagaan
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="nama_perniagaan" id="nama_perniagaan" class="form-control" 
                                   value="<?= htmlspecialchars($alumni['nama_perniagaan'] ?? '') ?>"
                                   placeholder="Nama syarikat/perniagaan">
                        </div>
                        <div class="form-field">
                            <label class="form-label">
                                <i class="bi bi-graph-up"></i> Bidang Perniagaan
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="bidang_perniagaan" id="bidang_perniagaan" class="form-control" 
                                   value="<?= htmlspecialchars($alumni['bidang_perniagaan'] ?? '') ?>"
                                   placeholder="Contoh: Peruncitan, Teknologi, Makanan">
                        </div>
                    </div>
                </div>

                <!-- Lokasi -->
                <div class="form-row">
                    <div class="form-field full-width">
                        <label class="form-label">
                            <i class="bi bi-geo-alt"></i> Lokasi Semasa
                        </label>
                        <input type="text" name="lokasi" class="form-control" id="lokasi"
                               value="<?= htmlspecialchars($alumni['lokasi'] ?? '') ?>"
                               placeholder="Contoh: Johor Bahru/Johor (dalam Malaysia) atau Singapore (luar negara)">
                        <div class="form-hint">
                            <i class="bi bi-info-circle"></i> 
                            Format: <strong>Daerah/Negeri</strong> (dalam Malaysia) atau <strong>Bandar/Negara</strong> (luar negara)
                        </div>
                    </div>
                </div>

                <!-- Info Penting -->
                <div class="info-box">
                    <div class="info-box-header">
                        <i class="bi bi-info-circle"></i>
                        <span>Info Penting</span>
                    </div>
                    <div class="info-box-content">
                        <ul>
                            <li>Nama tidak boleh ditukar. Sila hubungi kaunseling jika perlu.</li>
                            <li>Emel akan digunakan untuk login. Pastikan emel yang aktif.</li>
                            <li>Maklumat yang lengkap membantu kami menjejak alumni dengan lebih baik.</li>
                        </ul>
                    </div>
                </div>

                <!-- Preview Card -->
                <div class="preview-card">
                    <div class="preview-header">
                        <i class="bi bi-eye"></i>
                        <span>PREVIEW PROFIL</span>
                    </div>
                    <div class="preview-grid" id="previewGrid">
                        <div class="preview-item">
                            <span class="preview-label">NAMA</span>
                            <span class="preview-value" id="preview_nama"><?= htmlspecialchars($alumni['nama']) ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">EMEL</span>
                            <span class="preview-value" id="preview_emel"><?= htmlspecialchars($alumni['emel']) ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">TELEFON</span>
                            <span class="preview-value" id="preview_telefon"><?= htmlspecialchars($alumni['no_telefon'] ?? '-') ?></span>
                        </div>
                        <div class="preview-item">
                            <span class="preview-label">LOKASI</span>
                            <span class="preview-value" id="preview_lokasi"><?= htmlspecialchars($alumni['lokasi'] ?? '-') ?></span>
                        </div>
                        <div class="preview-item status-item">
                            <span class="preview-label">STATUS</span>
                            <span class="preview-value" id="preview_status">
                                <?php 
                                    $status_map = [
                                        'bekerja' => 'Bekerja',
                                        'sambung belajar' => 'Sambung Belajar',
                                        'usahawan' => 'Usahawan',
                                        'belum bekerja' => 'Belum Bekerja'
                                    ];
                                    echo $status_map[$alumni['status_alumni']] ?? '-';
                                ?>
                            </span>
                        </div>
                        <div class="preview-item preview-dynamic" id="preview_dynamic_1" style="display: none;">
                            <!-- Dynamic content based on status -->
                        </div>
                        <div class="preview-item preview-dynamic" id="preview_dynamic_2" style="display: none;">
                            <!-- Dynamic content based on status -->
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="profil.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Function to toggle fields based on status
function toggleFields() {
    const status = document.getElementById('status_alumni').value;
    
    // Hide all dynamic fields
    document.getElementById('bekerja_fields').style.display = 'none';
    document.getElementById('sambung_belajar_fields').style.display = 'none';
    document.getElementById('usahawan_fields').style.display = 'none';
    
    // Remove required attributes
    document.getElementById('pekerjaan')?.removeAttribute('required');
    document.getElementById('tempat_kerja')?.removeAttribute('required');
    document.getElementById('jawatan')?.removeAttribute('required');
    document.getElementById('institusi')?.removeAttribute('required');
    document.getElementById('bidang_pengajian')?.removeAttribute('required');
    document.getElementById('nama_perniagaan')?.removeAttribute('required');
    document.getElementById('bidang_perniagaan')?.removeAttribute('required');
    
    // Show relevant fields
    if(status === 'bekerja') {
        document.getElementById('bekerja_fields').style.display = 'block';
        document.getElementById('pekerjaan').required = true;
        document.getElementById('tempat_kerja').required = true;
        document.getElementById('jawatan').required = true;
    } else if(status === 'sambung belajar') {
        document.getElementById('sambung_belajar_fields').style.display = 'block';
        document.getElementById('institusi').required = true;
        document.getElementById('bidang_pengajian').required = true;
    } else if(status === 'usahawan') {
        document.getElementById('usahawan_fields').style.display = 'block';
        document.getElementById('nama_perniagaan').required = true;
        document.getElementById('bidang_perniagaan').required = true;
    }
    
    updatePreview();
}

// Update preview based on status
function updatePreview() {
    const status = document.getElementById('status_alumni').value;
    const statusText = document.querySelector(`#status_alumni option[value="${status}"]`)?.textContent || '-';
    
    // Update status preview
    document.getElementById('preview_status').innerText = statusText;
    
    // Hide all dynamic previews first
    document.getElementById('preview_dynamic_1').style.display = 'none';
    document.getElementById('preview_dynamic_2').style.display = 'none';
    
    if(status === 'bekerja') {
        const pekerjaan = document.getElementById('pekerjaan')?.value || '-';
        const tempatKerja = document.getElementById('tempat_kerja')?.value || '-';
        const jawatan = document.getElementById('jawatan')?.value || '-';
        const julatGaji = document.getElementById('julat_gaji')?.options[document.getElementById('julat_gaji')?.selectedIndex]?.text || '-';
        
        document.getElementById('preview_dynamic_1').innerHTML = `
            <span class="preview-label">PEKERJAAN</span>
            <span class="preview-value">${pekerjaan}</span>
        `;
        document.getElementById('preview_dynamic_2').innerHTML = `
            <span class="preview-label">TEMPAT KERJA / JAWATAN</span>
            <span class="preview-value">${tempatKerja} | ${jawatan} (${julatGaji})</span>
        `;
        document.getElementById('preview_dynamic_1').style.display = 'flex';
        document.getElementById('preview_dynamic_2').style.display = 'flex';
    } 
    else if(status === 'sambung belajar') {
        const institusi = document.getElementById('institusi')?.value || '-';
        const bidang = document.getElementById('bidang_pengajian')?.value || '-';
        
        document.getElementById('preview_dynamic_1').innerHTML = `
            <span class="preview-label">INSTITUSI</span>
            <span class="preview-value">${institusi}</span>
        `;
        document.getElementById('preview_dynamic_2').innerHTML = `
            <span class="preview-label">BIDANG PENGAJIAN</span>
            <span class="preview-value">${bidang}</span>
        `;
        document.getElementById('preview_dynamic_1').style.display = 'flex';
        document.getElementById('preview_dynamic_2').style.display = 'flex';
    } 
    else if(status === 'usahawan') {
        const namaPerniagaan = document.getElementById('nama_perniagaan')?.value || '-';
        const bidangPerniagaan = document.getElementById('bidang_perniagaan')?.value || '-';
        
        document.getElementById('preview_dynamic_1').innerHTML = `
            <span class="preview-label">NAMA PERNIAGAAN</span>
            <span class="preview-value">${namaPerniagaan}</span>
        `;
        document.getElementById('preview_dynamic_2').innerHTML = `
            <span class="preview-label">BIDANG PERNIAGAAN</span>
            <span class="preview-value">${bidangPerniagaan}</span>
        `;
        document.getElementById('preview_dynamic_1').style.display = 'flex';
        document.getElementById('preview_dynamic_2').style.display = 'flex';
    }
}

// Live preview for basic fields
document.getElementById('emel')?.addEventListener('keyup', function() {
    document.getElementById('preview_emel').innerText = this.value || '-';
});

document.getElementById('no_telefon')?.addEventListener('keyup', function() {
    document.getElementById('preview_telefon').innerText = this.value || '-';
});

// Live preview for lokasi
document.getElementById('lokasi')?.addEventListener('keyup', function() {
    document.getElementById('preview_lokasi').innerText = this.value || '-';
});

// Attach event listeners to dynamic fields
document.getElementById('pekerjaan')?.addEventListener('keyup', updatePreview);
document.getElementById('tempat_kerja')?.addEventListener('keyup', updatePreview);
document.getElementById('jawatan')?.addEventListener('keyup', updatePreview);
document.getElementById('julat_gaji')?.addEventListener('change', updatePreview);
document.getElementById('institusi')?.addEventListener('keyup', updatePreview);
document.getElementById('bidang_pengajian')?.addEventListener('keyup', updatePreview);
document.getElementById('nama_perniagaan')?.addEventListener('keyup', updatePreview);
document.getElementById('bidang_perniagaan')?.addEventListener('keyup', updatePreview);

// Status change event
document.getElementById('status_alumni')?.addEventListener('change', toggleFields);

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    toggleFields();
});
</script>

<?php include_once 'includes/footer_alumni.php'; ?>