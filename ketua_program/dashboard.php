//ketua program
<?php
// ketua_program/dashboard.php
session_start();

// Force no cache
header("Cache-Control: no-cache, no-store, must-revalidate, private");
header("Pragma: no-cache");
header("Expires: 0");

// Check login
if(!isset($_SESSION['user_id'])) {
    echo "<script>window.location.replace('index.php');</script>";
    exit();
}
?>
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>📊 DASHBOARD KAUNSELING</h2>";

if(!isset($_SESSION['user_id'])) {
    echo "❌ TAK LOGIN! Redirect balik...";
    header('Location: index.php');
    exit();
}

echo "✅ User ID: " . $_SESSION['user_id'] . "<br>";
echo "✅ Nama: " . $_SESSION['nama'] . "<br>";
echo "✅ Emel: " . $_SESSION['emel'] . "<br>";
echo "✅ Role: " . $_SESSION['role'] . "<br>";

echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo '<a href="../logout.php">Logout</a>';
?>