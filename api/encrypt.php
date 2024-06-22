<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header("Content-Type: text/plain");
  exit();
}

$data = json_decode(file_get_contents('php://input'), true);

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
$vender = $data['vender'];
$device_list = $data['deviceList'];

if (!$token || !$password || !$device_list) {
  $response = array("error" => "Parameters are not enough");
} else {
  $json_data = json_encode(array(
    "token" => $token,
    "secret_key" => $secret_key,
    "desc" => $desc,
    "start_time" => $start_time,
    "end_time" => $end_time,
    "version" => $version,
    "vender" => $vender,
    "device_list" => $device_list
  ));

  $manage_password = bin2hex(random_bytes(16));
  $bin_password = hex2bin($manage_password);
  $encryption_password = $password . $bin_password;

  $guest_login_page_url = "https://watalab.info/lab/asakura/guest_login.php?mp=$manage_password";

  try {
    $enc = base64_encode(openssl_encrypt($json_data, 'aes-256-cbc', $encryption_password, OPENSSL_RAW_DATA, 'iv12345678901234'));
    $response = array("enc" => $enc, "guest_login_page_url" => $guest_login_page_url);
  } catch (Exception $e) {
    $response = array("error" => "Encryption failed");
  }
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response);
