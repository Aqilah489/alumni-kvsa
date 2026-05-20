<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../../../index.php');
    exit();
}

// Helper function untuk format text
function formatNama($nama) {
    return ucwords(strtolower(trim($nama)));
}

function formatProgram($program) {
    return ucwords(strtolower(trim($program)));
}

function formatNoMatrix($no_matrix) {
    return strtoupper(trim($no_matrix));
}

function formatEmail($emel) {
    return strtolower(trim($emel));
}

// Get filter
$filter_program = $_GET['program'] ?? '';
$filter_batch = $_GET['batch'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build query - Dengan semua field termasuk sambung belajar & usahawan
$sql = "SELECT 
            a.no_matrix, 
            a.nama, 
            a.emel, 
            a.no_telefon, 
            a.lokasi, 
            a.program, 
            a.batch, 
            a.status_alumni,
            a.pekerjaan, 
            a.tempat_kerja, 
            a.jawatan, 
            a.julat_gaji,
            a.institusi,
            a.bidang_pengajian,
            a.nama_perniagaan,
            a.bidang_perniagaan,
            a.tarikh_kemaskini
        FROM alumni a
        WHERE 1=1";
$params = [];

if($filter_program) {
    $sql .= " AND a.program = ?";
    $params[] = $filter_program;
}
if($filter_batch) {
    $sql .= " AND a.batch = ?";
    $params[] = $filter_batch;
}
if($filter_status) {
    $sql .= " AND a.status_alumni = ?";
    $params[] = $filter_status;
}
$sql .= " ORDER BY a.created_at DESC";

$stmt = $connect->prepare($sql);
$stmt->execute($params);
$alumni = $stmt->fetchAll();

// Helper untuk dapatkan label status
function getStatusLabel($status) {
    switch($status) {
        case 'belum kemaskini':
            return 'Belum Kemaskini';
        case 'bekerja':
            return 'Bekerja';
        case 'sambung belajar':
            return 'Sambung Belajar';
        case 'usahawan':
            return 'Usahawan';
        case 'belum bekerja':
            return 'Belum Bekerja';
        default:
            return '-';
    }
}

// Helper untuk dapatkan maklumat dynamic berdasarkan status
function getDynamicInfo($alumni) {
    $status = $alumni['status_alumni'];
    
    if($status == 'bekerja') {
        return [
            'pekerjaan' => $alumni['pekerjaan'] ?? '',
            'tempat_kerja' => $alumni['tempat_kerja'] ?? '',
            'jawatan' => $alumni['jawatan'] ?? '',
            'julat_gaji' => $alumni['julat_gaji'] ?? ''
        ];
    } elseif($status == 'sambung belajar') {
        return [
            'institusi' => $alumni['institusi'] ?? '',
            'bidang_pengajian' => $alumni['bidang_pengajian'] ?? '',
            'pekerjaan' => '',
            'tempat_kerja' => '',
            'jawatan' => '',
            'julat_gaji' => ''
        ];
    } elseif($status == 'usahawan') {
        return [
            'nama_perniagaan' => $alumni['nama_perniagaan'] ?? '',
            'bidang_perniagaan' => $alumni['bidang_perniagaan'] ?? '',
            'pekerjaan' => '',
            'tempat_kerja' => '',
            'jawatan' => '',
            'julat_gaji' => ''
        ];
    } else {
        return [
            'pekerjaan' => '',
            'tempat_kerja' => '',
            'jawatan' => '',
            'julat_gaji' => ''
        ];
    }
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=alumni_' . date('Y-m-d') . '.csv');
header('Cache-Control: max-age=0');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 (fix Malay characters)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header row - in Malay (lebih lengkap)
fputcsv($output, [
    'No Matriks', 
    'Nama', 
    'Emel', 
    'No Telefon', 
    'Lokasi', 
    'Program', 
    'Batch', 
    'Status Alumni',
    'Pekerjaan', 
    'Tempat Kerja', 
    'Jawatan', 
    'Julat Gaji',
    'Institusi (Sambung Belajar)',
    'Bidang Pengajian (Sambung Belajar)',
    'Nama Perniagaan (Usahawan)',
    'Bidang Perniagaan (Usahawan)',
    'Tarikh Kemaskini'
]);

// Data rows
foreach($alumni as $a) {
    $dynamic = getDynamicInfo($a);
    
    fputcsv($output, [
        formatNoMatrix($a['no_matrix']),
        formatNama($a['nama']),
        formatEmail($a['emel']),
        $a['no_telefon'] ?? '',
        $a['lokasi'] ?? '',
        formatProgram($a['program']),
        $a['batch'],
        getStatusLabel($a['status_alumni']),
        $dynamic['pekerjaan'] ?? '',
        $dynamic['tempat_kerja'] ?? '',
        $dynamic['jawatan'] ?? '',
        $dynamic['julat_gaji'] ?? '',
        $dynamic['institusi'] ?? '',
        $dynamic['bidang_pengajian'] ?? '',
        $dynamic['nama_perniagaan'] ?? '',
        $dynamic['bidang_perniagaan'] ?? '',
        $a['tarikh_kemaskini'] ?? ''
    ]);
}

fclose($output);
exit();
?>