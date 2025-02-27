<?php
session_start();
header("Content-Type: application/json");

// 現在のタイムスタンプ
$currentTime = time();

// 最後のリクエストタイムスタンプを取得
$lastRequestTime = $_SESSION['last_request_time'] ?? 0;

// 3秒以内の��クエストは拒否
if ($currentTime - $lastRequestTime < 3) {
    echo json_encode(["error" => "3秒以内のリクエストは受け付けられません"]);
    exit;
}

// 現在のタイムスタンプを保存
$_SESSION['last_request_time'] = $currentTime;

// JSONデータの取得
$requestData = json_decode(file_get_contents("php://input"), true);

// 入力値の取得
$url = $requestData['url'] ?? '';
$method = strtoupper($requestData['method'] ?? 'GET');
$body = $requestData['body'] ?? '';

// URLが無い場合はエラー
if (empty($url)) {
    echo json_encode(["error" => "URLを入力してください"]);
    exit;
}

// cURL 初期化
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// POST/PUTならリクエストボディを送信
if (in_array($method, ['POST', 'PUT']) && !empty($body)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
}

// APIリクエスト実行
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// JSON整形
$responseData = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo json_encode(["status" => $httpCode, "response" => $responseData], JSON_PRETTY_PRINT);
} else {
    echo json_encode(["status" => $httpCode, "response" => $response], JSON_PRETTY_PRINT);
}
