<?php
session_start();
require_once '../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../index.php');
    exit();
}

$id = $_GET['id'] ?? '';
$module = $_GET['module'] ?? '';
$redirect = '';

if($module == 'alumni') {
    $redirect = 'alumni/senarai.php';
    $table = 'alumni';
    $id_field = 'alumni_id';
    // No related data check needed (alumni adalah data utama)
    
} elseif($module == 'ketua_program') {
    $redirect = 'ketua_program/senarai.php';
    $table = 'ketua_program';
    $id_field = 'kp_id';
    // No related data check needed
    
} else {
    // Module tidak dikenali - redirect ke dashboard
    header('Location: dashboard_kaunseling.php');
    exit();
}

// Delete
$stmt = $connect->prepare("DELETE FROM $table WHERE $id_field = ?");
if($stmt->execute([$id])) {
    $_SESSION['success'] = ucfirst($module) . " berjaya dipadam!";
} else {
    $_SESSION['error'] = "Gagal memadam " . $module . ".";
}

header('Location: ' . $redirect);
exit();
?>