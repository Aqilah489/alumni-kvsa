<?php
// reset_password_manual.php - GUNA UNTUK RESET PASSWORD
require_once 'connection.php';

$password_baru = 'password123';
$hash = password_hash($password_baru, PASSWORD_DEFAULT);

echo "<h2>🔐 Reset Password Manual</h2>";

// Reset semua table
$connect->exec("UPDATE kaunseling SET password = '$hash'");
echo "✅ Kaunseling direset<br>";

$connect->exec("UPDATE ketua_program SET password = '$hash'");
echo "✅ Ketua Program direset<br>";

$connect->exec("UPDATE alumni SET password = '$hash'");
echo "✅ Alumni direset<br>";

echo "<hr>";
echo "Semua user password: <strong>$password_baru</strong><br>";
echo "<a href='index.php'>Login Sekarang</a>";
?>