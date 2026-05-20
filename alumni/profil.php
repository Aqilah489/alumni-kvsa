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
$stmt = $connect->prepare("
    SELECT a.* 
    FROM alumni a
    WHERE a.alumni_id = ?
");
$stmt->execute([$alumni_id]);
$alumni = $stmt->fetch();

// Calculate profile completion percentage - based on relevant fields for each status
function calculateCompletion($alumni) {
    $status = $alumni['status_alumni'];
    
    // Jika status 'belum kemaskini', terus return 0%
    if($status == 'belum kemaskini') {
        return 0;
    }
    
    // Field asas untuk semua status (wajib)
    $fields = ['no_telefon', 'lokasi'];
    
    // Tambah field berdasarkan status
    if($status == 'bekerja') {
        $fields = array_merge($fields, ['pekerjaan', 'tempat_kerja', 'jawatan']);
    } elseif($status == 'sambung belajar') {
        $fields = array_merge($fields, ['institusi', 'bidang_pengajian']);
    } elseif($status == 'usahawan') {
        $fields = array_merge($fields, ['nama_perniagaan', 'bidang_perniagaan']);
    }
    
    $filled = 0;
    foreach($fields as $field) {
        if(!empty($alumni[$field])) $filled++;
    }
    $total = count($fields);
    
    // Kalau tiada field, return 0
    if($total == 0) return 0;
    
    return round($filled / $total * 100);
}
$completion_percent = calculateCompletion($alumni);

$page_title = 'Profil Saya';
$page_css = 'dashboard_alumni';

include_once 'includes/header_alumni.php';
?>

<style>
/* Additional styles for profile */
.welcome-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 25px 30px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.welcome-text h2 {
    font-size: 1.3rem;
    margin-bottom: 5px;
}

.welcome-text p {
    font-size: 0.8rem;
    opacity: 0.9;
}

.status-badge {
    background: rgba(255,255,255,0.2);
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge i {
    margin-right: 5px;
}

.profile-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

.info-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.card-header {
    background: #f8fafc;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
}

.card-header h3 {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
    color: #2c3e50;
}

.card-header h3 i {
    margin-right: 8px;
    color: #667eea;
}

.card-body {
    padding: 15px 20px;
}

.info-row {
    display: flex;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.info-label {
    width: 120px;
    font-size: 0.7rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    flex-shrink: 0;
}

.info-value {
    flex: 1;
    font-size: 0.8rem;
    font-weight: 500;
    color: #2c3e50;
    word-break: break-word;
}

.info-value i {
    margin-right: 5px;
    color: #667eea;
}

.empty-value {
    color: #adb5bd;
    font-style: italic;
}

.fill-btn {
    background: none;
    border: none;
    color: #28a745;
    font-size: 0.7rem;
    margin-left: 8px;
    cursor: pointer;
}

.fill-btn:hover {
    text-decoration: underline;
}

.progress-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #e9ecef;
    margin-bottom: 25px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.progress-header h4 {
    font-size: 0.85rem;
    font-weight: 600;
    margin: 0;
}

.progress-percent {
    font-size: 0.8rem;
    font-weight: 600;
    color: #667eea;
}

.progress-bar-custom {
    height: 8px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill-custom {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    border-radius: 10px;
    transition: width 0.3s;
}

.progress-message {
    font-size: 0.7rem;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 20px;
}

/* Button Kemaskini Profil */
.btn-kemaskini {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-kemaskini:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(102,126,234,0.3);
}

/* Button Tukar Kata Laluan */
.btn-tukar-password {
    background: #17a2b8;
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-tukar-password:hover {
    background: #138496;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .profile-cards {
        grid-template-columns: 1fr;
    }
    
    .welcome-banner {
        flex-direction: column;
        text-align: center;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .action-buttons button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-text">
        <h2>Selamat Datang, <?= htmlspecialchars($alumni['nama']) ?>! 👋</h2>
        <p>Berikut adalah ringkasan maklumat profil anda</p>
    </div>
    <div class="status-badge">
        <i class="bi bi-<?= $alumni['status_alumni'] == 'belum kemaskini' ? 'exclamation-triangle' : 'check-circle' ?>"></i>
        <?php 
        $status_labels = [
            'bekerja' => 'Bekerja',
            'sambung belajar' => 'Sambung Belajar', 
            'usahawan' => 'Usahawan',
            'belum bekerja' => 'Belum Bekerja',
            'belum kemaskini' => 'Perlu Kemaskini'
        ];
        echo $status_labels[$alumni['status_alumni']] ?? 'Profil Lengkap';
        ?>
    </div>
</div>

<!-- Profile Cards - 2 Columns -->
<div class="profile-cards">
    <!-- Maklumat Asas Card -->
    <div class="info-card">
        <div class="card-header">
            <h3><i class="bi bi-person-badge"></i> Maklumat Asas</h3>
        </div>
        <div class="card-body">
            <div class="info-row">
                <div class="info-label">Nama Penuh</div>
                <div class="info-value"><i class="bi bi-person"></i> <?= htmlspecialchars($alumni['nama']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">No. Matriks</div>
                <div class="info-value"><i class="bi bi-upc-scan"></i> <?= htmlspecialchars($alumni['no_matrix']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Emel</div>
                <div class="info-value"><i class="bi bi-envelope"></i> <?= htmlspecialchars($alumni['emel']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Telefon</div>
                <div class="info-value">
                    <?php if(!empty($alumni['no_telefon'])): ?>
                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($alumni['no_telefon']) ?>
                    <?php else: ?>
                        <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                        <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Program</div>
                <div class="info-value"><i class="bi bi-mortarboard"></i> <?= htmlspecialchars($alumni['program']) ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Batch</div>
                <div class="info-value"><i class="bi bi-calendar"></i> <?= $alumni['batch'] ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Lokasi</div>
                <div class="info-value">
                    <?php if(!empty($alumni['lokasi'])): ?>
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($alumni['lokasi']) ?>
                    <?php else: ?>
                        <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                        <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Maklumat Pekerjaan/Pengajian/Perniagaan Card - Dynamic based on status -->
    <div class="info-card">
        <div class="card-header">
            <h3>
                <?php 
                if($alumni['status_alumni'] == 'bekerja') {
                    echo '<i class="bi bi-briefcase"></i> Maklumat Pekerjaan';
                } elseif($alumni['status_alumni'] == 'sambung belajar') {
                    echo '<i class="bi bi-book"></i> Maklumat Pengajian';
                } elseif($alumni['status_alumni'] == 'usahawan') {
                    echo '<i class="bi bi-shop"></i> Maklumat Perniagaan';
                } else {
                    echo '<i class="bi bi-info-circle"></i> Maklumat Status';
                }
                ?>
            </h3>
        </div>
        <div class="card-body">
            <div class="info-row">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <?php
                    switch($alumni['status_alumni']) {
                        case 'bekerja':
                            echo '<i class="bi bi-briefcase"></i> Bekerja';
                            break;
                        case 'sambung belajar':
                            echo '<i class="bi bi-book"></i> Sambung Belajar';
                            break;
                        case 'usahawan':
                            echo '<i class="bi bi-shop"></i> Usahawan';
                            break;
                        case 'belum bekerja':
                            echo '<i class="bi bi-question-circle"></i> Belum Bekerja';
                            break;
                        case 'belum kemaskini':
                            echo '<i class="bi bi-clock-history"></i> Perlu Kemaskini';
                            break;
                        default:
                            echo '<span class="empty-value">Belum diisi</span>';
                    }
                    ?>
                </div>
            </div>
            
            <?php if($alumni['status_alumni'] == 'bekerja'): ?>
                <!-- Display for BEKERJA -->
                <div class="info-row">
                    <div class="info-label">Pekerjaan</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['pekerjaan'])): ?>
                            <i class="bi bi-briefcase"></i> <?= htmlspecialchars($alumni['pekerjaan']) ?>
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tempat Kerja</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['tempat_kerja'])): ?>
                            <i class="bi bi-building"></i> <?= htmlspecialchars($alumni['tempat_kerja']) ?>
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jawatan</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['jawatan'])): ?>
                            <i class="bi bi-person-badge"></i> <?= htmlspecialchars($alumni['jawatan']) ?>
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Julat Gaji</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['julat_gaji'])): ?>
                            <i class="bi bi-cash"></i> <?= htmlspecialchars($alumni['julat_gaji']) ?>
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif($alumni['status_alumni'] == 'sambung belajar'): ?>
                <!-- Display for SAMBUNG BELAJAR -->
                <div class="info-row">
                    <div class="info-label">Institusi</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['institusi'])): ?>
                            <i class="bi bi-building"></i> <?= htmlspecialchars($alumni['institusi']) ?>
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Bidang Pengajian</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['bidang_pengajian'])): ?>
                            <i class="bi bi-book"></i> <?= htmlspecialchars($alumni['bidang_pengajian']) ?>
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif($alumni['status_alumni'] == 'usahawan'): ?>
                <!-- Display for USAHAWAN -->
                <div class="info-row">
                    <div class="info-label">Perniagaan</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['nama_perniagaan'])): ?>
                            <i class="bi bi-shop"></i> <?= htmlspecialchars($alumni['nama_perniagaan']) ?>
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Bidang Perniagaan</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['bidang_perniagaan'])): ?>
                            <i class="bi bi-graph-up"></i> <?= htmlspecialchars($alumni['bidang_perniagaan']) ?>
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini.php'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif($alumni['status_alumni'] == 'belum bekerja'): ?>
                <!-- Display for BELUM BEKERJA -->
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <i class="bi bi-clock"></i> Sedang mencari peluang pekerjaan
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Maklumat</div>
                    <div class="info-value">
                        <span class="empty-value">Tiada maklumat pekerjaan</span>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Display for BELUM KEMASKINI or others -->
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <i class="bi bi-exclamation-triangle"></i> Profil belum lengkap
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tindakan</div>
                    <div class="info-value">
                        <button class="fill-btn" onclick="location.href='kemaskini.php'" style="background: #667eea; color: white; padding: 5px 12px; border-radius: 6px;">
                            ✏️ Kemaskini Sekarang
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Progress Card -->
<div class="progress-card">
    <div class="progress-header">
        <h4><i class="bi bi-pie-chart"></i> Status Kelengkapan Profil</h4>
        <span class="progress-percent"><?= $completion_percent ?>% Lengkap</span>
    </div>
    <div class="progress-bar-custom">
        <div class="progress-fill-custom" style="width: <?= $completion_percent ?>%"></div>
    </div>
    <div class="progress-message">
        <?php if($completion_percent < 100): ?>
            <i class="bi bi-info-circle text-warning"></i>
            <span>Lengkapkan maklumat anda untuk profil yang lebih lengkap.</span>
        <?php else: ?>
            <i class="bi bi-check-circle-fill text-success"></i>
            <span>Profil anda sudah lengkap! Terima kasih.</span>
        <?php endif; ?>
    </div>
</div>

<!-- Action Buttons -->
<div class="action-buttons">
    <button class="btn-kemaskini" onclick="location.href='kemaskini.php'">
        <i class="bi bi-pencil-square"></i> Kemaskini Profil
    </button>
    <button class="btn-tukar-password" onclick="location.href='tukar_password.php'">
        <i class="bi bi-key"></i> Tukar Kata Laluan
    </button>
</div>

<?php include_once 'includes/footer_alumni.php'; ?>