<?php
require_once __DIR__ . '/../util/auth.php';
require_once __DIR__ . '/../constants/constants.php';


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

function get_allow_device_status($token, $secret_key)
{

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

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
$decrypt_data = allow_device_decrypt();
echo json_encode(get_allow_device_status($decrypt_data['token'], $decrypt_data['secretKey']));
