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
    <link rel="stylesheet" href="login.css">
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
                        <form method="POST">
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
                        </form>
                        
                        <!-- Footer -->
                        <hr class="my-4">
                        <div class="text-center text-muted small">
                            <p class="mb-0">© 2024 Kolej Vokasional Shah Alam</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="login.js"></script>
</body>
</html>