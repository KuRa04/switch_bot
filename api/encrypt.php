<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header("Content-Type: text/plain");
  exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$manage_password = "KAIT2024";
$response = array();
//token, password, deviceListが存在する時。!emptyがわかりづらいので修正

// Encryption process
$token = $data['token'];
$secret_key = $data['secretKey'];
$password = $data['password'];
$desc = $data['description'];
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
    "description" => $desc,
    "startTime" => $start_time,
    "endTime" => $end_time,
    "version" => $version,
    "vendor" => $vendor,
    "deviceList" => $device_list
  ));

  try {
    $encrypt_password = $password . $manage_password;
    $encode_data = base64_encode(openssl_encrypt($json_data, 'aes-256-cbc', $encrypt_password, OPENSSL_RAW_DATA, 'iv12345678901234'));
    $response = array("encodeData" => $encode_data);
  } catch (Exception $e) {
    $response = array("error" => "Encryption failed");
  }
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response);
