<?php
// reset_pasti_jadi.php
require_once 'connection.php';

$emel = 'ali@alumni.kvsa.edu';
$password = 'password123';

// Generate hash baru
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>🔐 Reset Password</h2>";
echo "Email: $emel<br>";
echo "Password: $password<br>";
echo "Hash baru: $hash<br><br>";

// Update database
$stmt = $connect->prepare("UPDATE alumni SET password = ? WHERE emel = ?");
$result = $stmt->execute([$hash, $emel]);

if($result && $stmt->rowCount() > 0) {
    echo "✅ BERJAYA! Password telah diupdate.<br><br>";
    
    // Verify semula
    $check = $connect->prepare("SELECT * FROM alumni WHERE emel = ?");
    $check->execute([$emel]);
    $user = $check->fetch();
    
    if(password_verify($password, $user['password'])) {
        echo "✅ VERIFY OK! Password '$password' BETUL!<br>";
        echo "<a href='index.php'>➡️ Pergi Login</a>";
    } else {
        echo "❌ VERIFY GAGAL! Hash tak match.<br>";
    }
} else {
    echo "❌ GAGAL! Email tak jumpa.<br>";
}

echo "<hr>";
echo "<h3>Senarai Ketua Program:</h3>";
$users = $connect->query("SELECT emel, nama FROM alumni")->fetchAll();
foreach($users as $u) {
    echo "• {$u['emel']} - {$u['nama']}<br>";
}
?>