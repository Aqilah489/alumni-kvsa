<?php
session_start();
require_once 'connection.php';

// Manual include PHPMailer
require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: lupa_password.php');
    exit();
}

$emel = strtolower(trim($_POST['emel']));
$found = false;
$user_data = null;
$user_table = '';
$user_role = '';
$user_id_column = '';

// Table configuration
$table_config = [
    'alumni' => [
        'table' => 'alumni',
        'role' => 'alumni',
        'id_column' => 'alumni_id',
        'name_column' => 'nama'
    ],
    'kaunseling' => [
        'table' => 'kaunseling',
        'role' => 'kaunseling',
        'id_column' => 'kaunseling_id',
        'name_column' => 'nama'
    ],
    'ketua_program' => [
        'table' => 'ketua_program',
        'role' => 'ketua_program',
        'id_column' => 'kp_id',
        'name_column' => 'nama'
    ]
];

foreach($table_config as $config) {
    $stmt = $connect->prepare("SELECT * FROM {$config['table']} WHERE emel = ?");
    $stmt->execute([$emel]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user) {
        $found = true;
        $user_data = $user;
        $user_table = $config['table'];
        $user_role = $config['role'];
        $user_id_column = $config['id_column'];
        $user_name_column = $config['name_column'];
        $user_id = $user[$user_id_column];
        $user_name = $user[$user_name_column];
        break;
    }
}

if($found) {
    // Generate reset token (lebih pendek untuk senang copy)
    $token = bin2hex(random_bytes(16)); // 32 characters sahaja
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Check and add columns for alumni if needed
    if($user_table == 'alumni') {
        $check = $connect->query("SHOW COLUMNS FROM alumni LIKE 'reset_token'");
        if($check->rowCount() == 0) {
            $connect->exec("ALTER TABLE alumni ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
            $connect->exec("ALTER TABLE alumni ADD COLUMN reset_expires DATETIME DEFAULT NULL");
        }
    }
    
    // Save token to database
    $stmt = $connect->prepare("UPDATE $user_table SET reset_token = ?, reset_expires = ? WHERE $user_id_column = ?");
    $stmt->execute([$token, $expires, $user_id]);
    
    // SMTP settings
    $smtp_username = 'qamarina202@gmail.com';     // GANTI
    $smtp_password = 'btms wwha xoig mosi';         // GANTI
    
    $reset_page_link = "http://localhost/Sistem Penjejakan Alumni/reset_password.php";
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom($smtp_username, 'Unit Alumni KV Shah Alam');
        $mail->addAddress($emel, $user_name);
        $mail->isHTML(true);
        
        $role_display = ucfirst($user_role);
        $mail->Subject = "Reset Password - Sistem Alumni KV Shah Alam ($role_display)";
        
        // EMAIL DENGAN TOKEN (boleh copy & paste)
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <div style='max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #667eea;'>Reset Password</h2>
                <p>Assalamualaikum <strong>$user_name</strong>,</p>
                <p>Kami menerima permintaan untuk reset password akaun <strong>$role_display</strong> anda.</p>
                
                <div style='background: #f0f0f0; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center;'>
                    <p style='margin: 0; font-size: 12px; color: #666;'>KOD RESET PASSWORD ANDA:</p>
                    <p style='font-size: 24px; font-weight: bold; letter-spacing: 2px; margin: 10px 0; font-family: monospace;'>$token</p>
                </div>
                
                <p><strong>Cara reset password:</strong></p>
                <ol>
                    <li>Pergi ke halaman reset password: <br>
                        <a href='$reset_page_link' style='color: #667eea;'>$reset_page_link</a>
                    </li>
                    <li>Masukkan kod: <strong>$token</strong></li>
                    <li>Masukkan password baru anda</li>
                </ol>
                
                <p style='margin-top: 20px; font-size: 12px; color: #666;'>⚠️ Kod ini akan tamat dalam <strong>1 jam</strong>.</p>
                <p>Jika anda tidak meminta reset password, abaikan email ini.</p>
                <hr>
                <p style='font-size: 11px; color: #999;'>Unit Alumni KV Shah Alam</p>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Reset Password\n\nKod Reset Anda: $token\n\nCara reset:\n1. Buka http://localhost/Sistem Penjejakan Alumni/reset_password.php\n2. Masukkan kod: $token\n3. Masukkan password baru\n\nKod tamat dalam 1 jam.";
        
        $mail->send();
        $_SESSION['success'] = "Kod reset password telah dihantar ke emel anda. Sila semak inbox.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal menghantar email. Sila cuba lagi.";
        error_log("Email error: " . $mail->ErrorInfo);
    }
} else {
    $_SESSION['success'] = "Jika emel wujud, kod reset telah dihantar.";
}

header('Location: lupa_password.php');
exit();
?>