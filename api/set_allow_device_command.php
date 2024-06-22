<?php
require_once '../util/auth.php';

function set_allow_device_command()
{

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  $token = $data['token'];
  $secret_key = $data['secretKey'];
  $device_id = $data['deviceId'];
  $command = $data['commands'];

  $commandFormatted = [
    "command" => $command['command'],
    "parameter" => $command['parameter'],
    "commandType" => $command['commandType']
  ];

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
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($commandFormatted));
  $response = curl_exec($ch);
  curl_close($ch);

  return $response;
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode(set_allow_device_command());
