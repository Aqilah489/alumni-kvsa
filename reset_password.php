<?php
require_once 'connection.php';

$token = $_GET['token'] ?? '';
$error = '';

// Verify token
if($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets 
                           WHERE token = ? AND used = 0 AND expiry > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if(!$reset) {
        $error = "Link tidak sah atau telah luput!";
    }
} else {
    header('Location: lupa_password.php');
    exit();
}

if(isset($_POST['reset'])) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if($password != $confirm) {
        $error = "Password tidak sepadan!";
    } elseif(strlen($password) < 6) {
        $error = "Password minimum 6 aksara!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $emel = $reset['emel'];
        
        // Cari dalam table mana dan update
        $tables = ['kaunseling', 'ketua_program', 'alumni'];
        $updated = false;
        
        foreach($tables as $table) {
            $stmt = $pdo->prepare("UPDATE $table SET password = ? WHERE emel = ?");
            if($stmt->execute([$hash, $emel]) && $stmt->rowCount() > 0) {
                $updated = true;
                break;
            }
        }
        
        if($updated) {
            // Tandakan token dah guna
            $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?")
                ->execute([$token]);
            
            $success = "Password berjaya ditukar!";
        } else {
            $error = "Ralat! User tidak dijumpai.";
        }
    }
}
?>
<!-- Form HTML tukar password -->
<?php if($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
    <a href="login.php">Log Masuk</a>
<?php else: ?>
    <form method="POST">
        <input type="password" name="password" placeholder="Password baru" required>
        <input type="password" name="confirm_password" placeholder="Confirm password" required>
        <button type="submit" name="reset">Reset Password</button>
    </form>
<?php endif; ?>