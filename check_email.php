<?php
include 'connection.php';
$emel = $_GET['emel'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-info">
            <h4>📧 Link Reset Dihantar!</h4>
            <p>Kami telah hantar link reset ke: <strong><?= htmlspecialchars($emel) ?></strong></p>
            <p>Sila check email anda (termasuk folder SPAM).</p>
            <p class="text-muted">Link reset sah selama 1 jam.</p>
            <hr>
            <a href="login.php" class="btn btn-primary">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>