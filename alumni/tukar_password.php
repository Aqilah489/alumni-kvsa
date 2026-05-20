<?php
session_start();
require_once __DIR__ . '/../connection.php';

// Check login & role
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'alumni') {
    header('Location: ../index.php');
    exit();
}

$page_title = 'Tukar Kata Laluan';
$page_css = 'alumni';
$error = '';
$success = '';

// Get user info
$stmt = $connect->prepare("SELECT nama, emel FROM alumni WHERE alumni_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $password_confirm = $_POST['password_confirm'];
    
    // Get current password
    $stmt = $connect->prepare("SELECT password FROM alumni WHERE alumni_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current = $stmt->fetch();
    
    if(!password_verify($password_lama, $current['password'])) {
        $error = "Kata laluan lama tidak sesuai!";
    } elseif(strlen($password_baru) < 6) {
        $error = "Kata laluan baru mestilah sekurang-kurangnya 6 aksara!";
    } elseif($password_baru !== $password_confirm) {
        $error = "Kata laluan baru dan pengesahan tidak sepadan!";
    } else {
        $new_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $connect->prepare("UPDATE alumni SET password = ? WHERE alumni_id = ?");
        if($stmt->execute([$new_hash, $_SESSION['user_id']])) {
            $success = "Kata laluan berjaya ditukar! Sila login semula.";
            // Optional: logout user lepas tukar password
            // session_destroy();
            // header('Location: ../index.php');
            // exit();
        } else {
            $error = "Gagal menukar kata laluan.";
        }
    }
}

include_once 'includes/header_alumni.php';
?>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="header-icon">
                <i class="bi bi-key"></i>
            </div>
            <h3>Tukar Kata Laluan</h3>
            <p>Pastikan kata laluan anda selamat</p>
        </div>
        
        <div class="form-body">
            <!-- Info User -->
            <div class="info-user">
                <div class="info-user-item">
                    <i class="bi bi-person-circle"></i>
                    <div>
                        <strong><?= htmlspecialchars($user['nama']) ?></strong>
                        <small><?= htmlspecialchars($user['emel']) ?></small>
                    </div>
                </div>
                <div class="info-user-badge">
                    <span class="badge-role">Alumni</span>
                </div>
            </div>
            
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
                <div class="text-center mt-3">
                    <a href="../index.php" class="btn-back">Pergi ke Login</a>
                </div>
            <?php endif; ?>
            
            <?php if(!$success): ?>
            <form method="POST" id="passwordForm">
                <!-- Kata Laluan Lama -->
                <div class="form-row">
                    <div class="form-field full-width">
                        <label class="form-label">Kata Laluan Lama <span class="required">*</span></label>
                        <div class="password-wrapper">
                            <input type="password" name="password_lama" class="form-control" id="password_lama" required>
                            <button type="button" class="toggle-password" data-target="password_lama" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Kata Laluan Baru & Sahkan -->
                <div class="form-row">
                    <div class="form-field">
                        <label class="form-label">Kata Laluan Baru <span class="required">*</span></label>
                        <div class="password-wrapper">
                            <input type="password" name="password_baru" class="form-control" id="password_baru" required>
                            <button type="button" class="toggle-password" data-target="password_baru" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-hint">Minimum 6 aksara</div>
                        <div class="strength-meter">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Sahkan Kata Laluan Baru <span class="required">*</span></label>
                        <div class="password-wrapper">
                            <input type="password" name="password_confirm" class="form-control" id="password_confirm" required>
                            <button type="button" class="toggle-password" data-target="password_confirm" type="button">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="match-indicator" id="matchIndicator"></div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="profil.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn-save" id="submitBtn">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Password wrapper */
.password-wrapper {
    position: relative;
}

.password-wrapper .form-control {
    padding-right: 45px;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
}

.toggle-password:hover {
    color: #667eea;
}

/* Info User */
.info-user {
    background: #f8fafc;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #e9ecef;
}

.info-user-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.info-user-item i {
    font-size: 2rem;
    color: #667eea;
}

.info-user-item div {
    display: flex;
    flex-direction: column;
}

.info-user-item strong {
    font-size: 0.9rem;
    color: #2c3e50;
}

.info-user-item small {
    font-size: 0.7rem;
    color: #6c757d;
}

.badge-role {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 500;
}

/* Strength Meter */
.strength-meter {
    height: 4px;
    background: #e9ecef;
    border-radius: 4px;
    margin: 8px 0 4px;
    overflow: hidden;
}

.strength-bar {
    width: 0%;
    height: 100%;
    transition: width 0.3s;
}

.strength-weak .strength-bar {
    width: 33%;
    background: #dc3545;
}

.strength-medium .strength-bar {
    width: 66%;
    background: #ffc107;
}

.strength-strong .strength-bar {
    width: 100%;
    background: #28a745;
}

.strength-text {
    font-size: 0.65rem;
    margin-top: 4px;
    color: #6c757d;
}

/* Match Indicator */
.match-indicator {
    font-size: 0.65rem;
    margin-top: 6px;
}

.match-match {
    color: #28a745;
}

.match-not {
    color: #dc3545;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.btn-back {
    background: #f1f5f9;
    color: #475569;
    text-decoration: none;
    padding: 8px 20px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    border: 1px solid #e2e8f0;
}

.btn-back:hover {
    background: #e9ecef;
    color: #1e293b;
}

.btn-save {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    padding: 8px 24px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    cursor: pointer;
}

.btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(40,167,69,0.3);
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Responsive */
@media (max-width: 768px) {
    .info-user {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .info-user-item {
        flex-direction: column;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .form-actions a, .form-actions button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        let targetId = this.getAttribute('data-target');
        let input = document.getElementById(targetId);
        let icon = this.querySelector('i');
        
        if(input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
});

// Password strength checker
function checkStrength(password) {
    let strength = 0;
    let meter = document.querySelector('.strength-meter');
    let text = document.getElementById('strengthText');
    
    meter.classList.remove('strength-weak', 'strength-medium', 'strength-strong');
    
    if(password.length === 0) {
        text.innerHTML = '';
        return;
    }
    
    if(password.length >= 6) strength++;
    if(password.length >= 8) strength++;
    if(/[a-z]/.test(password)) strength++;
    if(/[A-Z]/.test(password)) strength++;
    if(/[0-9]/.test(password)) strength++;
    if(/[$@#&!]/.test(password)) strength++;
    
    if(strength <= 2) {
        meter.classList.add('strength-weak');
        text.innerHTML = '⚠️ Lemah - Gunakan huruf besar, kecil, nombor dan simbol';
    } else if(strength <= 4) {
        meter.classList.add('strength-medium');
        text.innerHTML = '⚠️ Sederhana - Tambah huruf besar dan simbol untuk lebih selamat';
    } else {
        meter.classList.add('strength-strong');
        text.innerHTML = '✅ Kuat - Kata laluan yang baik!';
    }
}

// Password match checker
function checkMatch() {
    let pwd = document.getElementById('password_baru').value;
    let confirm = document.getElementById('password_confirm').value;
    let indicator = document.getElementById('matchIndicator');
    let submitBtn = document.getElementById('submitBtn');
    
    if(confirm.length === 0) {
        indicator.innerHTML = '';
        submitBtn.disabled = false;
        return;
    }
    
    if(pwd === confirm) {
        indicator.innerHTML = '✅ Kata laluan sepadan';
        indicator.style.color = '#28a745';
        submitBtn.disabled = false;
    } else {
        indicator.innerHTML = '❌ Kata laluan tidak sepadan';
        indicator.style.color = '#dc3545';
        submitBtn.disabled = true;
    }
}

// Event listeners
document.getElementById('password_baru').addEventListener('keyup', function() {
    checkStrength(this.value);
    checkMatch();
});

document.getElementById('password_confirm').addEventListener('keyup', checkMatch);
</script>

<?php include_once 'includes/footer_alumni.php'; ?>