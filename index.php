<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PASTIKAN connection.php path BETUL
require_once __DIR__ . '/connection.php';

// Headers anti-cache
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$error = '';

// ============================================
// PROSES LOGIN
// ============================================
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $emel = $_POST['emel'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    
    // Tentukan table berdasarkan role
    if($role == 'kaunseling') {
        $table = 'kaunseling';
        $id_field = 'kaunseling_id';
        $redirect = 'kaunseling/dashboard_kaunseling.php';
    }
    elseif($role == 'ketua_program') {
        $table = 'ketua_program';
        $id_field = 'kp_id';
        $redirect = 'ketua_program/dashboard.php';
    }
    elseif($role == 'alumni') {
        $table = 'alumni';
        $id_field = 'alumni_id';
        $redirect = 'alumni/dashboard.php';
    }
    else {
        $error = "Sila pilih role yang sah!";
    }
    
    // Jika role sah, teruskan
    if(isset($table) && !empty($table)) {
        
        // Cari user
        $stmt = $connect->prepare("SELECT * FROM $table WHERE emel = ?");
        $stmt->execute([$emel]);
        $user = $stmt->fetch();
        
        // Verify password
        if($user && password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user[$id_field];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['emel'] = $user['emel'];
            $_SESSION['role'] = $role;
            
            // Untuk KP, simpan kod_program juga
            if($role == 'ketua_program') {
                $_SESSION['kod_program'] = $user['kod_program'];
            }
            
            header("Location: $redirect");
            exit();
        } else {
            $error = "Emel atau password salah!";
        }
    }
}

// Redirect kalau dah login
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'kaunseling') {
        header('Location: kaunseling/dashboard_kaunseling.php');
        exit();
    }
    elseif($_SESSION['role'] == 'ketua_program') {
        header('Location: ketua_program/dashboard.php');
        exit();
    }
    elseif($_SESSION['role'] == 'alumni') {
        header('Location: alumni/dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alumni KVSA</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card shadow-lg">
                    
                    <!-- Header -->
                    <div class="login-header">
                        <i class="bi bi-mortarboard" style="font-size: 3rem;"></i>
                        <h3 class="mt-3">Alumni KVSA</h3>
                        <p class="mb-0">Sistem Penjejakan Alumni</p>
                    </div>
                    
                    <!-- Body -->
                    <div class="login-body">
                        
                        <!-- Error Message -->
                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i> <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Login Form -->
                        <form method="POST" action="">
                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Emel
                                </label>
                                <input type="email" 
                                       name="emel" 
                                       class="form-control" 
                                       placeholder="nama@email.com" 
                                       value="<?= isset($_POST['emel']) ? htmlspecialchars($_POST['emel']) : '' ?>"
                                       required>
                            </div>
                            
                            <!-- Password -->
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-lock me-2"></i>Kata Laluan
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="form-control" 
                                           placeholder="********" 
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Role Selection -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-person-badge me-2"></i>Log Masuk Sebagai
                                </label>
                                <select name="role" class="form-select" required>
                                    <option value="">-- Sila Pilih --</option>
                                    <option value="alumni" <?= (isset($_POST['role']) && $_POST['role'] == 'alumni') ? 'selected' : '' ?>>Alumni</option>
                                    <option value="ketua_program" <?= (isset($_POST['role']) && $_POST['role'] == 'ketua_program') ? 'selected' : '' ?>>Ketua Program</option>
                                    <option value="kaunseling" <?= (isset($_POST['role']) && $_POST['role'] == 'kaunseling') ? 'selected' : '' ?>>Kaunseling</option>
                                </select>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" name="login" class="btn btn-login w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Log Masuk
                            </button>

                            <!-- Link Lupa Password -->
                            <div class="d-flex justify-content-between mt-3">
                                <a href="reset_password_manual.php" class="text-decoration-none small">
                                    <i class="bi bi-question-circle"></i> Lupa Password?
                                </a>
                                <!-- Boleh tambah link lain kalau perlu -->
                                <span></span>
                            </div>
                        </form>
                        
                        <!-- Footer -->
                        <hr class="my-4">
                        <div class="text-center text-muted small">
                            <p class="mb-0">© 2026 Kolej Vokasional Shah Alam</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/login.js"></script>
</body>
</html>