<?php
session_start();
require_once 'connection.php';

if(isset($_POST['hantar'])) {
    $emel = $_POST['emel'];
    
    // Check emel wujud tak dalam mana-mana table
    $tables = ['kaunseling', 'ketua_program', 'alumni'];
    $found = false;
    
    foreach($tables as $table) {
        $status = ($table == 'alumni') ? 'status_hidup = "hidup"' : 'status = "aktif"';
        $check = $pdo->prepare("SELECT * FROM $table WHERE emel = ? AND $status");
        $check->execute([$emel]);
        if($check->fetch()) {
            $found = true;
            break;
        }
    }
    
    if($found) {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Simpan dalam table password_resets
        $pdo->prepare("INSERT INTO password_resets (emel, token, expiry) VALUES (?, ?, ?)")
            ->execute([$emel, $token, $expiry]);
        
        // Redirect ke page "Check email" (jangan bagi link kat sini)
        header("Location: check_email.php?emel=" . urlencode($emel));
        exit();
    } else {
        $error = "E-mel tidak dijumpai!";
    }
}
?>
<!-- Form HTML -->
<form method="POST">
    <input type="email" name="emel" placeholder="Masukkan emel" required>
    <button type="submit" name="hantar">Hantar Link Reset</button>
</form>