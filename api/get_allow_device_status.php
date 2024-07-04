<?php
require_once __DIR__ . '/../util/auth.php';

function get_allow_device_status()
{

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  $token = $data['token'];
  $secret_key = $data['secretKey'];
  $device_id = $data['deviceId'];

  $t = make_t();
  $nonce = make_nonce();
  $sign = make_sign($secret_key, $token, $t, $nonce);

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

  return json_decode($response, true);
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode(get_allow_device_status());
