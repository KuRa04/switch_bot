<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script type="text/javascript" src="switchbot_api.js"></script>
  <title>Device List</title>
</head>

<?php
require_once __DIR__ . '/constants/constants.php';

function handleFileUpload()
{
  if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] != UPLOAD_ERR_OK) {
    return "ファイルがアップロードされていません。";
  }
  if ($_FILES['fileToUpload']['type'] != 'application/json') {
    return "アップロードされたファイルはJSON形式ではありません。";
  }
  return null;
}

function decodeJsonFile($tmpName)
{
  $jsonContent = file_get_contents($tmpName);
  $data = json_decode($jsonContent, true);
  if ($data === null) {
    return "JSONファイルのデコードに失敗しました。";
  }
  return $data;
}

function get_allow_decrypt($authGuestToken, $password)
{
  $url = "https://watalab.info/lab/asakura/api/allow_decrypt.php";

  $headers = [
    "Content-Type: application/json; charset=utf-8"
  ];

  $data = [
    "authGuestToken" => $authGuestToken,
    "password" => $password
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, true));
  $response = curl_exec($ch);
  curl_close($ch);

  if (!$response) {
    return false;
  }

  return json_decode($response, true);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fileUploadError = handleFileUpload();
  if ($fileUploadError) {
    echo $fileUploadError;
    exit;
  }

  $data = decodeJsonFile($_FILES['fileToUpload']['tmp_name']);
  if (is_string($data)) {
    echo $data;
    exit;
  }

  $allow_device_list = get_allow_decrypt($data['authGuestToken'], $_POST['password']);
}
?>

<body class="device-list-body">
  <p id="get-status-loading"></p>
  <div id="container" class="container">
    <h2>デバイス一覧</h2>
    <?php
    $tableHtml = '<table class="device-table"><tr><th>Device ID</th><th>Device Name</th><th>Status</th><th>Command</th></tr>';

    foreach ($allow_device_list['deviceList'] as $device) {
      $tableHtml .= "<tr>";
      $tableHtml .= "<td>{$device['deviceId']}</td>";
      $tableHtml .= "<td>{$device['deviceName']}</td>";
      $tableHtml .= "<td>";
      if (isset($device['status'])) {
        foreach ($device['status'] as $key => $value) {
          if ($value) {
            $tableHtml .= "<p id='allowStatus{$device['deviceId']}{$key}'>{$key}</p>";
          }
        }
        $tableHtml .= "<button id='{$device['deviceId']}-button' class='button-command' onClick=\"getStatus('{$data['authGuestToken']}', '{$_POST['password']}', '{$device['deviceId']}')\">ステータスを更新</button><br>";
      }
      $tableHtml .= "</td>";
      $tableHtml .= "<td>";
      if (isset($device['commands'])) {
        foreach ($device['commands'] as $key => $value) {
          if ($value) {
            $tableHtml .= "<button id='{$device['deviceId']}-{$key}' class='button-command' value='{$key}' onClick=\"operateSwitch('{$data['authGuestToken']}', '{$_POST['password']}', '{$device['deviceId']}', '{$key}')\">{$key}</button><br>";
          }
        }
      }
      $tableHtml .= "</td>";
      $tableHtml .= "</tr>";
    }
    $tableHtml .= "</table>";
    echo $tableHtml;

    ?>
  </div>
</body>
<style>
  .device-list-body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f4f4;
    padding: 20px;
  }

  .device-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .device-table th,
  .device-table td {
    border-right: 1px solid #eaeaea;
    border-bottom: 1px solid #eaeaea;
    padding: 10px;
    text-align: left;
  }

  .device-table th:first-child,
  .device-table td:first-child {
    border-left: 1px solid #eaeaea;
  }

  .device-table th {
    background-color: #3498db;
    color: white;
    font-weight: bold;
  }

  .device-table tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  .device-table tr:hover {
    background-color: #ecf0f1;
  }

  .device-table input[type="checkbox"] {
    margin-right: 10px;
    cursor: pointer;
  }

  .button-command {
    background-color: #4CAF50;
    border: none;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease;
  }

  .button-command:hover {
    background-color: #45a049;
  }
</style>

</html>