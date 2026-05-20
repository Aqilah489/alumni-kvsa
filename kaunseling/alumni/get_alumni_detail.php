<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$id = $_GET['id'] ?? 0;

if($id > 0) {
    // Query dari table alumni (struktur baru)
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
            tarikh_kemaskini,
            created_at
        FROM alumni 
        WHERE alumni_id = ?
    ");
    $stmt->execute([$id]);
    $alumni = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($alumni) {
        // Mapping status untuk paparan
        $status_labels = [
            'bekerja' => 'Bekerja',
            'sambung belajar' => 'Sambung Belajar',
            'usahawan' => 'Usahawan',
            'belum bekerja' => 'Belum Bekerja',
            'belum kemaskini' => 'Belum Kemaskini'
        ];
        
        $alumni['status_label'] = $status_labels[$alumni['status_alumni']] ?? 'Tiada Status';
        
        // Tentukan sama ada profil lengkap atau tidak
        $alumni['is_complete'] = ($alumni['status_alumni'] != 'belum kemaskini');
        
        // Maklumat dynamic berdasarkan status - UNTUK PAPARAN YANG RELEVAN SAHAJA
        $dynamic_info = [];
        $work_info = [];
        
        if($alumni['status_alumni'] == 'bekerja') {
            $work_info = [
                'pekerjaan' => $alumni['pekerjaan'] ?? '-',
                'tempat_kerja' => $alumni['tempat_kerja'] ?? '-',
                'jawatan' => $alumni['jawatan'] ?? '-',
                'julat_gaji' => $alumni['julat_gaji'] ?? '-'
            ];
        } elseif($alumni['status_alumni'] == 'sambung belajar') {
            $work_info = [
                'institusi' => $alumni['institusi'] ?? '-',
                'bidang_pengajian' => $alumni['bidang_pengajian'] ?? '-'
            ];
        } elseif($alumni['status_alumni'] == 'usahawan') {
            $work_info = [
                'nama_perniagaan' => $alumni['nama_perniagaan'] ?? '-',
                'bidang_perniagaan' => $alumni['bidang_perniagaan'] ?? '-'
            ];
        }
        
        $alumni['work_info'] = $work_info;
        $alumni['has_work_info'] = !empty(array_filter($work_info, function($val) { return $val != '-'; }));
        
        // Format tarikh
        $alumni['tarikh_kemaskini_formatted'] = $alumni['tarikh_kemaskini'] ? date('d/m/Y', strtotime($alumni['tarikh_kemaskini'])) : '-';
        
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