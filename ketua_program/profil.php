<?php
session_start();
require_once __DIR__ . '/../connection.php';

// ✅ BETUL: Check role ketua_program
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ketua_program') {
    header('Location: ../index.php');
    exit();
}

$page_title = 'Profil Saya';
$page_css = 'batch';  // atau 'alumni' ikut css awak
$error = '';
$success = '';

// Get user data dari table ketua_program
$stmt = $connect->prepare("SELECT * FROM ketua_program WHERE kp_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama']);
    $emel = trim($_POST['emel']);
    
    if(empty($nama) || empty($emel)) {
        $error = "Nama dan emel wajib diisi!";
    } else {
        // Update table ketua_program
        $stmt = $connect->prepare("UPDATE ketua_program SET nama = ?, emel = ? WHERE kp_id = ?");
        if($stmt->execute([$nama, $emel, $_SESSION['user_id']])) {
            $_SESSION['nama'] = $nama;
            $_SESSION['emel'] = $emel;
            $success = "Profil berjaya dikemaskini!";
            $user['nama'] = $nama;
            $user['emel'] = $emel;
        } else {
            $error = "Gagal mengemaskini profil.";
        }
    }
}

// ✅ Guna header_kp.php
include_once 'includes/header_kp.php';
?>
<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="header-icon">
                <i class="bi bi-person-circle"></i>
            </div>
            <h3>Profil Saya</h3>
            <p>Kemaskini maklumat peribadi anda</p>
        </div>
        
        <div class="form-body">
            <?php if($error): ?>
                <div class="alert-custom alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert-custom alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= $success ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Nama Penuh <span class="required">*</span></label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Emel <span class="required">*</span></label>
                        <input type="email" name="emel" class="form-control" value="<?= htmlspecialchars($user['emel']) ?>" required>
                    </div>
                </div>
                
                
                <div class="form-actions">
                    <a href="dashboard.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
            
            <hr>
            <div class="text-center">
                <a href="tukar_password.php" class="btn-add" style="background: #17a2b8;">
                    <i class="bi bi-key"></i> Tukar Kata Laluan
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer_kp.php'; ?>