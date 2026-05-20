<?php
// ollama_api.php - API untuk panggil Ollama dari PHP

header('Content-Type: application/json');

// Get question from POST
$question = $_POST['question'] ?? '';

if(empty($question)) {
    echo json_encode(['response' => 'Sila taip soalan.']);
    exit();
}

// Call Ollama API
$url = 'http://localhost:11434/api/generate';

$data = [
    'model' => 'llama3.2:3b',
    'prompt' => "Anda adalah pembantu untuk sistem alumni Kolej Vokasional Shah Alam. 
                 Berikan jawapan ringkas (maksimum 3 ayat) dalam Bahasa Malaysia.
                 Soalan: $question",
    'stream' => false
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30 saat timeout

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if($httpCode !== 200) {
    echo json_encode(['response' => 'Maaf, AI sedang sibuk. Sila cuba sebentar lagi.']);
    exit();
}

$result = json_decode($response, true);
$answer = $result['response'] ?? 'Maaf, saya tidak dapat memproses soalan anda.';

echo json_encode(['response' => $answer]);
?>