<?php
session_start();
require_once '../../connection.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ketua_program') {
    header('Location: ../../index.php');
    exit();
}

$kod_program = $_SESSION['kod_program'] ?? '';
$filter_batch = $_GET['batch'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT a.no_matrix, a.nama, a.emel, a.no_telefon, a.lokasi, 
        b.tahun as batch_tahun, a.pekerjaan, a.tempat_kerja, a.jawatan, a.julat_gaji,
        a.status_kemaskini, a.tarikh_kemaskini
        FROM alumni a
        LEFT JOIN batch b ON a.batch_id = b.id
        WHERE a.kod_program = ?";
$params = [$kod_program];

if($filter_batch) {
    $sql .= " AND a.batch_id = ?";
    $params[] = $filter_batch;
}
if($filter_status) {
    $sql .= " AND a.status_kemaskini = ?";
    $params[] = $filter_status;
}
if($search) {
    $sql .= " AND (a.nama LIKE ? OR a.no_matrix LIKE ? OR a.emel LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}
$sql .= " ORDER BY a.created_at DESC";

$stmt = $connect->prepare($sql);
$stmt->execute($params);
$alumni = $stmt->fetchAll();

// Get program name
$stmt = $connect->prepare("SELECT nama_program FROM program WHERE kod_program = ?");
$stmt->execute([$kod_program]);
$nama_program = $stmt->fetchColumn();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=alumni_' . $nama_program . '_' . date('Y-m-d') . '.csv');
header('Cache-Control: max-age=0');

$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header row
fputcsv($output, [
    'No Matriks', 'Nama', 'Emel', 'No Telefon', 'lokasi', 
    'Batch', 'Pekerjaan', 'Tempat Kerja', 'Jawatan', 'Julat Gaji', 
    'Status', 'Tarikh Kemaskini'
]);

// Data rows
foreach($alumni as $a) {
    fputcsv($output, [
        $a['no_matrix'],
        $a['nama'],
        $a['emel'],
        $a['no_telefon'],
        $a['lokasi'],
        $a['batch_tahun'],
        $a['pekerjaan'],
        $a['tempat_kerja'],
        $a['jawatan'],
        $a['julat_gaji'],
        $a['status_kemaskini'] == 'kemaskini' ? 'Telah Kemaskini' : 'Belum Kemaskini',
        $a['tarikh_kemaskini']
    ]);
}

fclose($output);
exit();
?>