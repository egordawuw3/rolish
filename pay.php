<?php
// === НАСТРОЙКИ ===
// Разрешаем запросы с любого домена. В боевом режиме лучше указать конкретный домен:
// header('Access-Control-Allow-Origin: https://paschail.ru');
header('Access-Control-Allow-Origin: *');
// Разрешаем методы POST и OPTIONS (для CORS Preflight)
header('Access-Control-Allow-Methods: POST, OPTIONS');
// Разрешаем заголовок Content-Type
header('Access-Control-Allow-Headers: Content-Type');
// Указываем, что ответ будет в формате JSON
header('Content-Type: application/json; charset=utf-8');

// === ОБРАБОТКА OPTIONS-ЗАПРОСА (CORS Preflight) ===
// Браузер отправляет этот запрос перед каждым POST.
// Если мы на него не ответим, будет ошибка 405 или CORS.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Отправляем "ОК"
    exit(); // Завершаем выполнение скрипта
}

// === ПОЛУЧЕНИЕ ДАННЫХ ОТ БРАУЗЕРА ===
// Получаем JSON-тело запроса от фронтенда
$data = file_get_contents('php://input');

// Проверяем, есть ли данные. Если нет, это некорректный запрос.
if (!$data) {
    http_response_code(400); // Bad Request
    echo json_encode(["detail" => "Пустой запрос от браузера."]);
    exit();
}

// === ОТПРАВКА ДАННЫХ В PYTHON-БЭКЕНД ===
// !!! ИСПРАВЛЕНО: Указан правильный эндпоинт /api/pay !!!
$python_backend_url = 'http://85.215.137.163:15241/api/pay'; 

$ch = curl_init($python_backend_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Возвращать ответ в виде строки
curl_setopt($ch, CURLOPT_POST, true);           // Метод POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);    // Передаем данные из браузера
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data) // Обязательно указывать длину контента
));
// Добавляем таймауты, чтобы избежать зависания скрипта
curl_setopt($ch, CURLOPT_TIMEOUT, 15);      // Максимальное время выполнения запроса 15 секунд
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Максимальное время установки соединения 5 секунд

// Выполняем cURL-запрос к Python-бэкенду
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код ответа от Python
$curl_error = curl_error($ch); // Получаем ошибку cURL, если была

curl_close($ch); // Закрываем cURL-сессию

// === ОБРАБОТКА ОТВЕТА (или ошибки) ===

// 1. Ошибка соединения с Python-бэкендом (например, Python-сервер выключен)
if ($response === false) {
    http_response_code(502); // Bad Gateway
    echo json_encode([
        "detail" => "Ошибка соединения с сервером оплат. Пожалуйста, попробуйте позже.",
        "error_details" => $curl_error // Дополнительная информация для отладки
    ]);
    exit();
}

// 2. Все прошло хорошо, возвращаем фронтенду ТОТ ЖЕ HTTP-статус и тело ответа, что и Python
http_response_code($http_code);
echo $response; // Отдаем ответ Python-сервера фронтенду

?>
