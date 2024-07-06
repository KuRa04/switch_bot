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

function encrypt()
{
  $data = json_decode(file_get_contents('php://input'), true);
  $response = array();

  $token = $data['token'];
  $secret_key = $data['secretKey'];
  $password = $data['password'];
  $description = $data['description'];
  $start_time = $data['startTime'];
  $end_time = $data['endTime'];
  $version = $data['version'];
  $vendor = $data['vendor'];
  $device_list = $data['deviceList'];

  if (!$token || !$password || !$device_list) {
    $response = array("error" => "Parameters are not enough");
  } else {
    $json_data = json_encode(array(
      "token" => $token,
      "secretKey" => $secret_key,
      "description" => $description,
      "startTime" => $start_time,
      "endTime" => $end_time,
      "version" => $version,
      "vendor" => $vendor,
      "deviceList" => $device_list
    ));

    try {
      $encrypt_password = $password . MANAGE_PASSWORD; // MANAGE_PASSWORDはconstants/constants.phpに定義
      $auth_guest_token = base64_encode(openssl_encrypt($json_data, 'aes-256-cbc', $encrypt_password, OPENSSL_RAW_DATA, 'iv12345678901234'));
      $response = array("authGuestToken" => $auth_guest_token);
    } catch (Exception $e) {
      $response = array("error" => "Encryption failed");
    }
  }

  return $response;
}

echo json_encode(encrypt());
