<?php
require_once '../util/auth.php';

function get_allow_device_list_status()
{

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  $token = $data['token'];
  $secret_key = $data['secretKey'];
  $device_list = $data['deviceList'];

  $t = make_t();
  $nonce = make_nonce();
  $sign = make_sign($secret_key, $token, $t, $nonce);

  $statuses = [];

  foreach ($device_list as $device) {
    $device_id = $device['deviceId'];
    $url = "https://api.switch-bot.com/v1.1/devices/$device_id/status";

    $headers = [
      "Authorization: $token",
      "sign: $sign",
      "t: $t",
      "nonce: $nonce",
      "Content-Type: application/json; charset=utf-8"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    $device_status = json_decode($response, true);

    // payloadのstatusのvalueがtrueのkeyを抽出
    $trueStatusKeys = array_keys(array_filter($device['status'], function ($value) {
      return $value === true;
    }));

    // responseの同じkey名のみを含む新しいオブジェクトを作成
    $filteredStatus = [
      "statusCode" => $device_status['statusCode'],
      "body" => [
        "deviceId" => $device["deviceId"],
      ],
      "message" => $device_status['message']
    ];

    foreach ($trueStatusKeys as $key) {
      if (array_key_exists($key, $device_status['body'])) {
        $filteredStatus['body']['status'][$key] = $device_status['body'][$key];
      }
    }

    $statuses[] = $filteredStatus;
  }

  return $statuses;
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode(get_allow_device_list_status());
