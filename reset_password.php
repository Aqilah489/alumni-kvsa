<?php
session_start();
require_once 'connection.php';

$error = '';
$success = '';
$show_form = true;
$token_verified = false;
$user_data = null;
$user_table = '';
$user_id_column = '';
$user_id = null;

// Table configuration
$table_config = [
    'alumni' => ['table' => 'alumni', 'id_column' => 'alumni_id'],
    'kaunseling' => ['table' => 'kaunseling', 'id_column' => 'kaunseling_id'],
    'ketua_program' => ['table' => 'ketua_program', 'id_column' => 'kp_id']
];

// Step 1: Verify token (if POST with token)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token'])) {
    $token = trim($_POST['token']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Cari token dalam semua table
    foreach($table_config as $role => $config) {
        $table = $config['table'];
        $id_col = $config['id_column'];
        
        $stmt = $connect->prepare("SELECT * FROM $table WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if($user) {
            $token_verified = true;
            $user_data = $user;
            $user_table = $table;
            $user_id_column = $id_col;
            $user_id = $user[$id_col];
            break;
        }
    }
    
    if(!$token_verified) {
        $error = "Kod reset password tidak sah atau sudah tamat tempoh.";
    } elseif(strlen($password) < 6) {
        $error = "Password mesti sekurang-kurangnya 6 aksara.";
    } elseif($password !== $confirm_password) {
        $error = "Password tidak sama.";
    } else {
        // Reset password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $connect->prepare("UPDATE $user_table SET password = ?, reset_token = NULL, reset_expires = NULL WHERE $user_id_column = ?");
        $stmt->execute([$hashed_password, $user_id]);
        
        $success = "Password berjaya ditukar! Sila log masuk.";
        $show_form = false;
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KVSA Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-container { max-width: 450px; width: 100%; margin: 20px; }
        .reset-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .reset-header { background: linear-gradient(135deg, #667eea, #764ba2); padding: 30px; text-align: center; color: white; }
        .reset-header i { font-size: 3rem; margin-bottom: 10px; }
        .reset-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 5px; }
        .form-control { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; }
        .btn-submit { width: 100%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; }
        .alert { padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.75rem; }
        .alert-danger { background: #fee2e2; color: #e74c3c; }
        .alert-success { background: #d4edda; color: #155724; }
        .token-box { background: #f0f0f0; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px; }
        .token-code { font-size: 20px; font-weight: bold; letter-spacing: 2px; font-family: monospace; }
    </style>
</head>
<body>
<div class="reset-container">
    <div class="reset-card">
        <div class="reset-header">
            <i class="bi bi-key-fill"></i>
            <h3>Reset Password</h3>
        </div>
        <div class="reset-body">
            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <div class="text-center mt-3">
                    <a href="index.php" class="btn-submit" style="display: inline-block; text-decoration: none;">Log Masuk</a>
                </div>
            <?php endif; ?>
            
            <?php if($show_form && !$success): ?>
                <form method="POST">
                    <div class="form-group">
                        <label><i class="bi bi-key"></i> Kod Reset Password</label>
                        <input type="text" name="token" class="form-control" required 
                               placeholder="Masukkan kod dari email">
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Ulang Password Baru</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn-submit">Reset Password</button>
                </form>
                
                <div class="text-center mt-3">
                    <a href="lupa_password.php" style="color: #667eea; text-decoration: none; font-size: 0.75rem;">
                        <i class="bi bi-arrow-left"></i> Tak dapat kod? Cuba lagi
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>