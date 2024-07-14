<?php
require_once __DIR__ . '/../util/auth.php';
require_once __DIR__ . '/../constants/constants.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
header("Content-Type: application/json; charset=UTF-8");

function allow_device_decrypt()
{
  $data = json_decode(file_get_contents('php://input'), true);
  $response = array();

  $auth_guest_token = $data['authGuestToken'];
  $password = $data['password'];
  $decrypt_password = $password . MANAGE_PASSWORD;

  $decrypt_data = openssl_decrypt(base64_decode($auth_guest_token), 'aes-256-cbc', $decrypt_password, OPENSSL_RAW_DATA, 'iv12345678901234');
  $response = json_decode($decrypt_data, true);

  return $response;
}

function operate_command($token, $secret_key)
{

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);
  $device_id = $data['deviceId'];
  $command = $data['commands'];

  $t = make_t();
  $nonce = make_nonce();
  $sign = make_sign($secret_key, $token, $t, $nonce);

  $url = "https://api.switch-bot.com/v1.1/devices/$device_id/commands";

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
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($command, true));
  $response = curl_exec($ch);
  curl_close($ch);

  $decoded_response = json_decode($response, true);

  // 必要な情報を抽出
  $statusCode = $decoded_response['statusCode'];
  $power = $decoded_response['body']['items'][0]['status']['power'];

  // statusCodeとpowerのみを含む配列を返す
  return [
    'statusCode' => $statusCode,
    'power' => $power
  ];
}
$decrypt_data = allow_device_decrypt();
echo json_encode(operate_command($decrypt_data['token'], $decrypt_data['secretKey']));
