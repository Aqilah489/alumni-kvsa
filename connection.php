<?php
// Mula fail PHP

try {
    // Cuba jalankan code dalam block ni
    // Kalau ada error, dia akan lompat ke catch

    $connect = new PDO("mysql:host=localhost;dbname=alumni_kvsa", "root", "");
    // Buat connection ke database guna PDO
    // mysql:host=localhost → server database (biasanya localhost)
    // dbname=alumni_kvsa → nama database kau
    // "root" → username database
    // "" → password (kosong untuk localhost biasanya)

    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set mode error kepada exception
    // Maksudnya: kalau ada error (contoh query salah),
    // PHP akan terus bagi error yang jelas (senang debug)

    $connect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    if($connect){
        //echo "Connected";
    }
    // Set cara ambil data dari database
    // FETCH_ASSOC → data akan jadi array ikut nama column
    // Contoh: $data['nama'] instead of $data[0]

    // echo "Connected!";
    // Optional: boleh guna untuk test connection berjaya ke tak

} catch(PDOException $e) {
    // Kalau ada error dalam try tadi, code akan masuk sini

    die("Connection failed: " . $e->getMessage());
    // Hentikan program dan paparkan mesej error
    // $e->getMessage() → ambil detail error sebenar
}
?>
