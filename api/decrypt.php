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

$encode_data = $data['x'];
$password = $data['p'];
$manage_password = $data['mp'];

$decrypt_password = $data['p'] . hex2bin($data['mp']);

$response = openssl_decrypt(base64_decode($encode_data), 'aes-256-cbc', $decrypt_password, OPENSSL_RAW_DATA, 'iv12345678901234');

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response);
