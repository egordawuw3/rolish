<?php
// Обязательные заголовки, чтобы браузер не выдавал ошибку 405 и CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Если браузер делает предварительный запрос (OPTIONS), отдаем ему "ОК" и завершаем скрипт
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Получаем данные от браузера
$data = file_get_contents('php://input');

if (!$data) {
    http_response_code(400);
    echo json_encode(["detail" => "Пустой запрос"]);
    exit();
}

// Отправляем их на твой Python-бэкенд
$ch = curl_init('http://85.215.137.163:15241/api/pay');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
));
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

// Получаем ответ от Python
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Если Питон недоступен
if ($response === false) {
    http_response_code(502);
    echo json_encode(["detail" => "Ошибка соединения с сервером оплат."]);
    exit();
}

// Отдаем ответ браузеру с правильным кодом (200, 400 или 500)
http_response_code($http_code);
echo $response;
?>
