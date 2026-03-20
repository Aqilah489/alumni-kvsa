<?php
// logout.php - SUPER SIMPLE
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url=http://localhost/Sistem%20Penjejakan%20Alumni/index.php">
</head>
<body>
    <script>
        window.location.replace('http://localhost/Sistem%20Penjejakan%20Alumni/index.php');
    </script>
</body>
</html>