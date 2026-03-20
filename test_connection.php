<?php
// test_connection.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

// 1. Check file wujud
if(file_exists('connection.php')) {
    echo "✅ File connection.php wujud<br>";
    include 'connection.php';
} else {
    echo "❌ File connection.php TAK wujud dalam folder includes/<br>";
    die("Check path: connection.php");
}

// 2. Check variable $connect wujud
if(isset($connect)) {
    echo "✅ Variable \$connect wujud<br>";
} else {
    echo "❌ Variable \$connect TAK wujud<br>";
    die("Connection maybe failed silently");
}

// 3. Check connection status
try {
    $query = $connect->query("SELECT 1");
    echo "✅ Connection active - boleh query<br>";
} catch(Exception $e) {
    echo "❌ Connection not active: " . $e->getMessage() . "<br>";
}

// 4. Check database & tables
echo "<h3>Database Info:</h3>";

// Check database name
$db_name = $connect->query("SELECT DATABASE() as db")->fetch();
echo "📊 Database: " . $db_name['db'] . "<br>";

// List all tables
$tables = $connect->query("SHOW TABLES")->fetchAll();
if(count($tables) > 0) {
    echo "📋 Tables dalam database:<br>";
    echo "<ul>";
    foreach($tables as $table) {
        // Get row count for each table
        $table_name = current($table);
        $count = $connect->query("SELECT COUNT(*) as total FROM $table_name")->fetch();
        echo "<li> $table_name - " . $count['total'] . " records</li>";
    }
    echo "</ul>";
} else {
    echo "❌ Tiada tables dalam database<br>";
}

// 5. Test login dengan credential test
echo "<h3>Login Test:</h3>";

// Try kaunseling first
$test_emel = "qamarina202@gmail.com";
$stmt = $connect->prepare("SELECT * FROM kaunseling WHERE emel = ?");
$stmt->execute([$test_emel]);
$kaunseling = $stmt->fetch();

if($kaunseling) {
    echo "✅ Alumni ditemui: " . $kaunseling['nama'] . "<br>";
} else {
    echo "❌ Alumni dengan emel $test_emel tak wujud<br>";
}

// Show PHP info
echo "<h3>PHP Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Drivers: " . implode(', ', PDO::getAvailableDrivers()) . "<br>";
?>