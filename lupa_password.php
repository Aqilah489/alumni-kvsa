<?php
session_start();
require_once 'connection.php';

$page_title = 'Lupa Password';
$error = '';
$success = '';

// Determine which header to use (no sidebar)
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - KVSA Alumni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .reset-container {
            max-width: 450px;
            width: 100%;
            margin: 20px;
        }
        .reset-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .reset-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .reset-header i {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .reset-header h3 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        .reset-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            display: block;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.85rem;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(102,126,234,0.3);
        }
        .alert {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.75rem;
        }
        .alert-danger {
            background: #fee2e2;
            color: #e74c3c;
            border: 1px solid #fccaca;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.75rem;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="reset-container">
    <div class="reset-card">
        <div class="reset-header">
            <i class="bi bi-envelope-paper-fill"></i>
            <h3>Lupa Password?</h3>
            <p>Masukkan emel anda untuk reset password</p>
        </div>
        <div class="reset-body">
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <form method="POST" action="proses_lupa_password.php">
                <div class="form-group">
                    <label><i class="bi bi-envelope"></i> Emel</label>
                    <input type="email" name="emel" class="form-control" required 
                           placeholder="Masukkan emel anda">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-send"></i> Hantar Link Reset
                </button>
            </form>
            <div class="back-link">
                <a href="index.php"><i class="bi bi-arrow-left"></i> Kembali ke Log Masuk</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>