<?php
// Разрешаем запросы с твоего сайта
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Получаем данные от браузера
$data = file_get_contents('php://input');

// Отправляем их на твой Python-бэкенд (под капотом, браузер этого не видит)
$ch = curl_init('http://85.215.137.163:15241/api/get_payment_url');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

// Получаем ответ от Python и отдаем его браузеру
$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>