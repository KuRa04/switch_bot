<?php
require_once '../util/auth.php';

function get_device_list()
{

  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  $token = $data['token'];
  $secret_key = $data['secretKey'];

  $t = make_t();
  $nonce = make_nonce();
  $sign = make_sign($secret_key, $token, $t, $nonce);

  $url = "https://api.switch-bot.com/v1.1/devices";

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
  return $response;
}

function readCsvToArray($filename)
{
  $csv = array_map('str_getcsv', file($filename));
  array_walk($csv, function (&$a) use ($csv) {
    $a = array_combine($csv[0], $a);
  });
  array_shift($csv); // remove column header
  return $csv;
}

function addDataToDeviceList(&$deviceList, $csv, $dataType)
{
  foreach ($deviceList['body']['deviceList'] as $index => $device) {
    foreach ($csv as $row) {
      if ($device['deviceType'] == $row['deviceType']) {
        if ($dataType == 'command') {
          $deviceList['body']['deviceList'][$index]['commands'][] = [
            'command' => $row['command'],
            'commandParameter' => $row['commandParameter'],
            'description' => $row['description']
          ];
        } elseif ($dataType == 'status' && stripos($row['key'], 'device') === false) {
          $deviceList['body']['deviceList'][$index]['status'][] = [
            'key' => $row['key'],
            'deviceType' => $row['deviceType'],
            'description' => $row['description']
          ];
        }
      }
    }
  }
}

// CSVファイルからデータを読み込む
$commandCsv = readCsvToArray('../csv/command_type_table.csv');
$statusCsv = readCsvToArray('../csv/status_type_table.csv');

// $device_list_add_commandsを$device_listのコピーとして初期化
$device_list = json_decode(get_device_list(), true);

// コマンドデータを追加
addDataToDeviceList($device_list, $commandCsv, 'command');
// ステータスデータを追加
addDataToDeviceList($device_list, $statusCsv, 'status');

header('Content-Type: application/json; charset=utf-8');
echo json_encode($device_list);
