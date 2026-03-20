<?php
// login.php - PHP Logic Only
include 'connection.php';
session_start();

// Kalau dah login, redirect ke dashboard
if(isset($_SESSION['user_id'])) {
    switch($_SESSION['role']) {
        case 'kaunseling':
            header('Location: kaunseling/dashboard_kaunseling.php');
            break;
        case 'ketua_program':
            header('Location: ketua_program/dashboard_kp.php');
            break;
        case 'alumni':
            header('Location: alumni/dashboard_alumni.php');
            break;
    }
    exit();
}

$error = '';

if(isset($_POST['login'])) {
    $emel = $_POST['emel'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if($role == 'kaunseling') {
        $sql = "SELECT * FROM kaunseling WHERE emel = ? AND status = 'aktif'";
        $redirect = 'kauseling/dashboard_kaunseling.php';
        $id_field = 'kaunseling_id';
    } 
    elseif($role == 'ketua_program') {
        $sql = "SELECT k.*, p.nama_program 
                FROM ketua_program k
                JOIN program p ON k.kod_program = p.kod_program
                WHERE k.emel = ? AND k.status = 'aktif'";
        $redirect = 'ketua_program/dashboard_kp.php';
        $id_field = 'kp_id';
    } 
    elseif($role == 'alumni') {
        $sql = "SELECT a.*, b.nama_batch, p.nama_program 
                FROM alumni a
                JOIN batch b ON a.batch_id = b.id
                JOIN program p ON a.kod_program = p.kod_program
                WHERE a.emel = ? AND a.status_hidup = 'hidup'";
        $redirect = 'alumni/dashboard_alumni.php';
        $id_field = 'alumni_id';
    } else {
        $error = "Sila pilih peranan yang betul";
    }
    
    if(empty($error)) {
        $stmt = $connect->prepare($sql);
        $stmt->execute([$emel]);
        $user = $stmt->fetch();
        echo "<!-- DEBUG: user = " . ($user ? 'JUMPA' : 'TAK JUMPA') . " -->";
        if($user) {
            echo "<!-- DEBUG: password_verify = " . (password_verify($password, $user['password']) ? 'TRUE' : 'FALSE') . " -->";
        }
        
        if($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user[$id_field];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['emel'] = $user['emel'];
            $_SESSION['role'] = $role;
            
            // Extra session untuk ketua_program
            if($role == 'ketua_program') {
                $_SESSION['kod_program'] = $user['kod_program'];
                $_SESSION['nama_program'] = $user['nama_program'];
            }
            
            // Extra session untuk alumni
            if($role == 'alumni') {
                $_SESSION['batch'] = $user['nama_batch'];
                $_SESSION['program'] = $user['nama_program'];
            }
            
            // Update last login
            $update = $connect->prepare("UPDATE $role SET last_login = NOW() WHERE $id_field = ?");
            $update->execute([$user[$id_field]]);
            
            header("Location: $redirect");
            exit();
        } else {
            $error = "Emel atau password salah!";
        }
    }
}

// Include HTML template
include 'index.php';
?>