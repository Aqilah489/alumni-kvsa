<?php
// header.php - Header untuk kaunseling module
if(!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Dashboard' ?> - Alumni KVSA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <?php
// header.php - relative dari root
if(isset($page_css)): ?>
    <link rel="stylesheet" href="/Sistem%20Penjejakan%20Alumni/css/<?= $page_css ?>.css?v=<?= time() ?>">
<?php else: ?>
    <link rel="stylesheet" href="/Sistem%20Penjejakan%20Alumni/css/dashboard_kaunseling.css?v=<?= time() ?>">
<?php endif; ?>
</head>
<body>

<?php include_once 'sidebar.php'; ?>

<main class="main-content">
    <!-- Header Simple -->
    <div class="simple-header">
        <div class="logo-area">
            <i class="bi bi-mortarboard"></i>
            <div>
                <h4>Kolej Vokasional Shah Alam</h4>
                <p>Sistem Penjejakan Alumni | Kaunseling</p>
            </div>
        </div>
        <div class="user-area">
            <span><?= htmlspecialchars($_SESSION['nama'] ?? 'Kaunseling') ?></span>
            <i class="bi bi-person-circle"></i>
        </div>
    </div>