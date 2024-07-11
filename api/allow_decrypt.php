<?php
require_once __DIR__ . '/../constants/constants.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header("Content-Type: text/plain");
  exit();
}

function allow_device_decrypt()
{
  $data = json_decode(file_get_contents('php://input'), true);
  $response = array();

  $auth_guest_token = $data['authGuestToken'];
  $password = $data['password'];
  $decrypt_password = $password . MANAGE_PASSWORD;

  $decrypt_data = openssl_decrypt(base64_decode($auth_guest_token), 'aes-256-cbc', $decrypt_password, OPENSSL_RAW_DATA, 'iv12345678901234');
  $response = json_decode($decrypt_data, true);
  unset($response['token'], $response['secretKey']);

  return $response;
}

echo json_encode(allow_device_decrypt());
