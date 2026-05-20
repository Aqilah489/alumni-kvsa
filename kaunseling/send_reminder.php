<?php
session_start();
require_once '../connection.php';

// ========== MANUAL INCLUDE PHPMailer (TANPA COMPOSER) ==========
// Pastikan folder PHPMailer ada di includes/
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'kaunseling') {
    header('Location: ../index.php');
    exit();
}

$alumni_id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? 'single';

// ========== SETTING EMAIL ANDA ==========
$smtp_username = 'qamarina202@gmail.com';      // GANTI
$smtp_password = 'btms wwha xoig mosi';          // GANTI

function sendReminderEmail($email, $nama, $smtp_username, $smtp_password) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_username;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom($smtp_username, 'Unit Alumni KV Shah Alam');
        $mail->addAddress($email, $nama);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Peringatan Kemaskini Data Alumni KV Shah Alam';
        
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family: Arial, sans-serif;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;">
                    <h2>📢 Peringatan Kemaskini Data Alumni</h2>
                </div>
                <div style="background: #f8fafc; padding: 25px; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 10px 10px;">
                    <p>Assalamualaikum dan salam sejahtera <strong>' . htmlspecialchars($nama) . '</strong>,</p>
                    <p>Pihak <strong>Kolej Vokasional Shah Alam</strong> memohon kerjasama tuan/puan untuk mengemaskini maklumat alumni.</p>
                    <p>Sila klik butang di bawah:</p>
                    <center>
                        <a href="http://localhost/Sistem Penjejakan Alumni/alumni/kemaskini.php" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;">
                            ✨ Kemaskini Profil Sekarang ✨
                        </a>
                    </center>
                    <p style="margin-top: 20px;">Terima kasih atas kerjasama tuan/puan.</p>
                    <p><strong>Unit Alumni KV Shah Alam</strong><br>Kolej Vokasional Shah Alam</p>
                </div>
                <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
                    <p>Email ini dihasilkan secara automatik. Sila jangan balas email ini.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        $mail->AltBody = "Assalamualaikum $nama,\n\nPihak KV Shah Alam memohon kerjasama tuan/puan untuk mengemaskini maklumat alumni.\n\nSila login ke sistem alumni untuk kemaskini.\n\nTerima kasih.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed to $email: " . $mail->ErrorInfo);
        return false;
    }
}

// Process request
if($action == 'single' && $alumni_id > 0) {
    $stmt = $connect->prepare("SELECT * FROM alumni WHERE alumni_id = ?");
    $stmt->execute([$alumni_id]);
    $alumni = $stmt->fetch();
    
    if($alumni) {
        if(sendReminderEmail($alumni['emel'], $alumni['nama'], $smtp_username, $smtp_password)) {
            $_SESSION['success'] = "✅ Peringatan berjaya dihantar kepada " . $alumni['nama'];
        } else {
            $_SESSION['error'] = "❌ Gagal menghantar peringatan kepada " . $alumni['nama'];
        }
    }
    header('Location: /Sistem Penjejakan Alumni/kaunseling/alumni/senarai.php');
    exit();
}
elseif($action == 'bulk') {
    $stmt = $connect->prepare("SELECT * FROM alumni WHERE status_alumni = 'belum kemaskini'");
    $stmt->execute();
    $alumni_list = $stmt->fetchAll();
    
    $sent = 0;
    $failed = 0;
    
    foreach($alumni_list as $alumni) {
        if(sendReminderEmail($alumni['emel'], $alumni['nama'], $smtp_username, $smtp_password)) {
            $sent++;
        } else {
            $failed++;
        }
        usleep(500000);
    }
    
    $_SESSION['success'] = "✅ Peringatan dihantar: $sent berjaya, $failed gagal";
    header('Location: /Sistem Penjejakan Alumni/kaunseling/alumni/senarai.php');
    exit();
}
else {
    $_SESSION['error'] = "❌ Invalid request";
    header('Location: /Sistem Penjejakan Alumni/kaunseling/alumni/senarai.php');
    exit();
}
?>