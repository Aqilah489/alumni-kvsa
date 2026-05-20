<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ketua_program') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Dapatkan program KP dari session
$program_kp = $_SESSION['program'] ?? '';

if(empty($program_kp)) {
    $stmt = $connect->prepare("SELECT program FROM ketua_program WHERE emel = ?");
    $stmt->execute([$_SESSION['emel']]);
    $kp = $stmt->fetch();
    if($kp) {
        $program_kp = $kp['program'];
        $_SESSION['program'] = $program_kp;
    }
}

$id = $_GET['id'] ?? 0;

if($id > 0) {
    $stmt = $connect->prepare("
        SELECT 
            alumni_id,
            no_matrix,
            nama,
            emel,
            no_telefon,
            lokasi,
            program,
            batch,
            status_alumni,
            pekerjaan,
            tempat_kerja,
            jawatan,
            julat_gaji,
            institusi,
            bidang_pengajian,
            nama_perniagaan,
            bidang_perniagaan,
            DATE_FORMAT(tarikh_kemaskini, '%d/%m/%Y') as tarikh_kemaskini_formatted
        FROM alumni 
        WHERE alumni_id = ? AND program = ?
    ");
    $stmt->execute([$id, $program_kp]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($alumni) {
        header('Content-Type: application/json');
        echo json_encode($alumni);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Alumni not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
}
?>