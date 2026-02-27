<?php
// Разрешаем запросы с фронтенда
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Обработка Preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = file_get_contents('php://input');
if (!$data) {
    http_response_code(400);
    echo json_encode(["detail" => "Пустой запрос"]);
    exit();
}

// Отправляем JSON в Python-бэкенд
$python_backend_url = 'http://85.215.137.163:15241/api/pay';

$ch = curl_init($python_backend_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
));
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Проверка доступности Python-сервера
if ($response === false) {
    http_response_code(502);
    echo json_encode(["detail" => "Ошибка соединения с сервером оплат.", "error" => $curl_error]);
    exit();
}

http_response_code($http_code);
echo $response;
?>
