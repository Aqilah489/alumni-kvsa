<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

$kod = $_GET['id'] ?? '';
if(empty($kod)) {
    header('Location: senarai.php');
    exit();
}

// Check related data
$check_alumni = $connect->prepare("SELECT COUNT(*) FROM alumni WHERE kod_program = ?");
$check_alumni->execute([$kod]);
$alumni_count = $check_alumni->fetchColumn();

$check_batch = $connect->prepare("SELECT COUNT(*) FROM batch WHERE kod_program = ?");
$check_batch->execute([$kod]);
$batch_count = $check_batch->fetchColumn();

$check_kp = $connect->prepare("SELECT COUNT(*) FROM ketua_program WHERE kod_program = ?");
$check_kp->execute([$kod]);
$kp_count = $check_kp->fetchColumn();

$page_title = 'Padam Program';
$page_css = 'program';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm'])) {
    $stmt = $connect->prepare("DELETE FROM program WHERE kod_program = ?");
    if($stmt->execute([$kod])) {
        $_SESSION['success'] = "Program '$kod' berjaya dipadam!";
        header('Location: senarai.php');
        exit();
    } else {
        $error = "Gagal memadam program.";
    }
}

include_once '../includes/header.php';
?>

<div class="confirm-card">
    <div class="confirm-header">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <h3>Padam Program</h3>
    </div>
    <div class="confirm-body">
        <?php if($alumni_count > 0 || $batch_count > 0 || $kp_count > 0): ?>
            <div class="warning-list">
                <i class="bi bi-warning"></i> <strong>Perhatian!</strong>
                <ul>
                    <?php if($alumni_count > 0): ?>
                        <li><?= $alumni_count ?> alumni</li>
                    <?php endif; ?>
                    <?php if($batch_count > 0): ?>
                        <li><?= $batch_count ?> batch</li>
                    <?php endif; ?>
                    <?php if($kp_count > 0): ?>
                        <li><?= $kp_count ?> ketua program</li>
                    <?php endif; ?>
                </ul>
                <strong>Semua data ini akan turut dipadam!</strong>
            </div>
        <?php endif; ?>
        
        <p>Adakah anda pasti untuk memadam program <strong><?= htmlspecialchars($kod) ?></strong>?</p>
        <p class="text-danger">Tindakan ini tidak boleh dibatalkan.</p>
        
        <form method="POST">
            <div class="confirm-actions">
                <a href="senarai.php" class="btn-cancel">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" name="confirm" class="btn-danger-custom">
                    <i class="bi bi-trash"></i> Ya, Padam
                </button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>