<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check login & role
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'alumni') {
    header('Location: ../login.php');
    exit();
}

$alumni_id = $_SESSION['user_id'];

// Get alumni data
$stmt = $connect->prepare("
    SELECT a.*, p.nama_program, b.nama_batch, b.tahun_grad
    FROM alumni a
    LEFT JOIN program p ON a.kod_program = p.kod_program
    LEFT JOIN batch b ON a.batch_id = b.id
    WHERE a.alumni_id = ?
");
$stmt->execute([$alumni_id]);
$alumni = $stmt->fetch();

// Calculate profile completion percentage
$fields = ['no_telefon', 'alamat', 'pekerjaan', 'tempat_kerja', 'jawatan', 'julat_gaji'];
$filled = 0;
foreach($fields as $field) {
    if(!empty($alumni[$field])) $filled++;
}
$completion_percent = round($filled / count($fields) * 100);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Alumni KVSA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #1e3c72;
            --primary-dark: #2a5298;
            --accent: #3498db;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #e74c3c;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --bg-light: #f8f9fa;
            --border: #e9ecef;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fb;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header i {
            font-size: 2rem;
        }
        
        .sidebar-header h3 {
            font-size: 1.1rem;
            margin: 8px 0 5px;
        }
        
        .sidebar-header p {
            font-size: 0.7rem;
            opacity: 0.7;
        }
        
        .nav-group {
            padding: 0 15px;
            margin: 20px 0;
        }
        
        .nav-group label {
            font-size: 0.65rem;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            display: block;
            margin-bottom: 8px;
            padding-left: 10px;
        }
        
        .nav-group a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 4px;
            font-size: 0.85rem;
        }
        
        .nav-group a:hover, .nav-group a.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        
        .nav-group a i {
            width: 24px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 20px 25px;
            min-height: 100vh;
        }
        
        /* Header */
        .simple-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: white;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-area i {
            font-size: 2rem;
            color: var(--primary);
        }
        
        .logo-area h4 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            color: var(--primary);
        }
        
        .logo-area p {
            font-size: 0.7rem;
            margin: 0;
            color: var(--text-light);
        }
        
        .user-area {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 30px;
        }
        
        .user-area:hover {
            background: var(--bg-light);
        }
        
        .user-area span {
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .user-area i {
            font-size: 1.5rem;
            color: var(--primary-dark);
        }
        
        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 25px 30px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        
        .welcome-text h2 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .welcome-text p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.85rem;
        }
        
        .status-badge {
            background: rgba(255,255,255,0.2);
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 0.75rem;
        }
        
        /* Profile Cards */
        .profile-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .info-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid var(--border);
        }
        
        .card-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border-bottom: 1px solid var(--border);
        }
        
        .card-header h3 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            color: var(--primary);
        }
        
        .card-header h3 i {
            margin-right: 8px;
            color: #667eea;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .info-label {
            width: 100px;
            font-size: 0.75rem;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .info-value {
            flex: 1;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .info-value i {
            color: var(--text-light);
            margin-right: 5px;
        }
        
        .empty-value {
            color: #adb5bd;
            font-style: italic;
        }
        
        .fill-btn {
            background: none;
            border: none;
            color: var(--accent);
            font-size: 0.7rem;
            margin-left: 10px;
            cursor: pointer;
        }
        
        /* Progress Card */
        .progress-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid var(--border);
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .progress-header h4 {
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
            color: var(--text-dark);
        }
        
        .progress-percent {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--accent);
        }
        
        .progress-bar-custom {
            background: #e9ecef;
            border-radius: 20px;
            height: 8px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        
        .progress-fill-custom {
            background: linear-gradient(90deg, #667eea, #764ba2);
            height: 100%;
            border-radius: 20px;
            transition: width 0.5s ease;
        }
        
        .progress-message {
            font-size: 0.7rem;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }
        
        .btn-outline-custom {
            background: white;
            border: 1px solid var(--border);
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-outline-custom:hover {
            background: var(--bg-light);
            border-color: var(--accent);
        }
        
        /* Footer */
        .footer {
            margin-top: 20px;
            padding: 15px 20px;
            background: white;
            border-radius: 12px;
            text-align: center;
            font-size: 0.7rem;
            color: var(--text-light);
            border: 1px solid var(--border);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: -260px;
                transition: left 0.3s ease;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .profile-cards {
                grid-template-columns: 1fr;
            }
            
            .welcome-banner {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .action-buttons {
                justify-content: center;
            }
        }
        
        .menu-toggle-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary);
            cursor: pointer;
            margin-right: 10px;
        }
        
        @media (max-width: 768px) {
            .menu-toggle-btn {
                display: block;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header text-center">
        <i class="bi bi-mortarboard"></i>
        <h3>Alumni KVSA</h3>
        <p>Alumni Panel</p>
    </div>
    
    <nav>
        <div class="nav-group">
            <label>MAIN</label>
            <a href="#" class="active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="profil.php">
                <i class="bi bi-person-circle"></i> Profil
            </a>
        </div>
        
        <div class="nav-group">
            <label>ACCOUNT</label>
            <a href="tukar_password.php">
                <i class="bi bi-key"></i> Tukar Password
            </a>
            <a href="../logout.php">
                <i class="bi bi-box-arrow-right"></i> Log Keluar
            </a>
        </div>
    </nav>
</aside>

<!-- Main Content -->
<main class="main-content">
    <!-- Header Simple -->
    <div class="simple-header">
        <div class="logo-area">
            <i class="bi bi-mortarboard"></i>
            <div>
                <h4>Kolej Vokasional Shah Alam</h4>
                <p>Sistem Penjejakan Alumni | Alumni</p>
            </div>
        </div>
        <div class="user-area">
            <span><?= htmlspecialchars($alumni['nama'] ?? 'Alumni') ?></span>
            <i class="bi bi-person-circle"></i>
        </div>
    </div>
    
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>Selamat Datang, <?= htmlspecialchars($alumni['nama']) ?>! 👋</h2>
            <p>Berikut adalah ringkasan maklumat profil anda</p>
        </div>
        <div class="status-badge">
            <i class="bi bi-<?= $alumni['status_kemaskini'] == 'belum' ? 'exclamation-triangle' : 'check-circle' ?>"></i>
            <?= $alumni['status_kemaskini'] == 'belum' ? 'Perlu Kemaskini' : 'Profil Lengkap' ?>
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
                            <button class="fill-btn" onclick="location.href='kemaskini_profil.php'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Program</div>
                    <div class="info-value"><i class="bi bi-mortarboard"></i> <?= htmlspecialchars($alumni['nama_program']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Batch</div>
                    <div class="info-value"><i class="bi bi-calendar"></i> <?= htmlspecialchars($alumni['nama_batch']) ?> (<?= $alumni['tahun_grad'] ?? '-' ?>)</div>
                </div>
            </div>
        </div>
        
        <!-- Maklumat Pekerjaan Card -->
        <div class="info-card">
            <div class="card-header">
                <h3><i class="bi bi-briefcase"></i> Maklumat Pekerjaan</h3>
            </div>
            <div class="card-body">
                <div class="info-row">
                    <div class="info-label">Pekerjaan</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['pekerjaan'])): ?>
                            <i class="bi bi-briefcase"></i> <?= htmlspecialchars($alumni['pekerjaan']) ?>
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini_profil.php#pekerjaan'">+ Isi</button>
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
                            <button class="fill-btn" onclick="location.href='kemaskini_profil.php#tempat_kerja'">+ Isi</button>
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
                            <button class="fill-btn" onclick="location.href='kemaskini_profil.php#jawatan'">+ Isi</button>
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
                            <button class="fill-btn" onclick="location.href='kemaskini_profil.php#julat_gaji'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Alamat</div>
                    <div class="info-value">
                        <?php if(!empty($alumni['alamat'])): ?>
                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars(substr($alumni['alamat'], 0, 50)) ?>...
                        <?php else: ?>
                            <span class="empty-value"><i class="bi bi-question-circle"></i> Belum diisi</span>
                            <button class="fill-btn" onclick="location.href='kemaskini_profil.php#alamat'">+ Isi</button>
                        <?php endif; ?>
                    </div>
                </div>
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
                <span>Lengkapkan maklumat pekerjaan dan alamat anda untuk profil yang lebih lengkap.</span>
            <?php else: ?>
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>Profil anda sudah lengkap! Terima kasih.</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons">
        <button class="btn-primary-custom" onclick="location.href='kemaskini_profil.php'">
            <i class="bi bi-pencil-square"></i> Kemaskini Profil
        </button>
        <button class="btn-outline-custom" onclick="location.href='tukar_password.php'">
            <i class="bi bi-key"></i> Tukar Password
        </button>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <i class="bi bi-c-circle"></i> <?= date('Y') ?> Kolej Vokasional Shah Alam. Hak Cipta Terpelihara.
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }
    
    const header = document.querySelector('.simple-header');
    if (header && window.innerWidth <= 768) {
        const menuBtn = document.createElement('button');
        menuBtn.innerHTML = '<i class="bi bi-list"></i>';
        menuBtn.className = 'menu-toggle-btn';
        menuBtn.onclick = toggleSidebar;
        header.insertBefore(menuBtn, header.firstChild);
    }
    
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth <= 768 && sidebar && !sidebar.contains(event.target) && !event.target.closest('.menu-toggle-btn')) {
            sidebar.classList.remove('active');
        }
    });
    
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('active');
        }
    });
</script>
</body>
</html>