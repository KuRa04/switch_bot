<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header("Content-Type: text/plain");
  exit();
}

require_once './util/auth.php';

function get_device_list()
{
  $token = $_POST['token'];
  $secret_key = $_POST['secretKey'];

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
$commandCsv = readCsvToArray('./csv/command_type_table.csv');
$statusCsv = readCsvToArray('./csv/status_type_table.csv');

// $device_list_add_commandsを$device_listのコピーとして初期化
$device_list = json_decode(get_device_list(), true);

// コマンドデータを追加
addDataToDeviceList($device_list, $commandCsv, 'command');
// ステータスデータを追加
addDataToDeviceList($device_list, $statusCsv, 'status');

$decode_device_list = json_decode(json_encode($device_list), true);
?>


<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <title>SwitchBotプロクシ（暗号化）</title>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script type="text/javascript" src="switchbot_api.js"></script>
</head>

<body>
  <h2>SwitchBotのTokenとSecretKey</h2>
  <p><label for="token">SwitchBot APIのtoken：</label><input type="text" name="token" id="token" value="<?php echo htmlspecialchars($_POST['token'], ENT_QUOTES, 'UTF-8'); ?>" size="100" readonly /></p>
  <p><label for="secretKey">SwitchBot APIのsecret：</label><input type="text" name="secretKey" id="secretKey" value="<?php echo htmlspecialchars($_POST['secretKey'], ENT_QUOTES, 'UTF-8'); ?>" size="100" readonly /></p>

  <h2>デバイスリスト</h2>
  <div id="deviceListContainer">' . $table . '</div>

  <label>説明:</label><br />
  <input type="text" name="description" id="description" size="100" value="" /><br />
  <p>有効期間</p>
  <label>開始:</label>
  <input type="date" name="startTime" id="startTime" size="100" value="" /><br />
  <label>終了:</label>
  <input type="date" name="endTime" id="endTime" size="100" value="" /><br /><br />
  <label>version</label><br />
  <select name="version" id="version">
    <option value="v1.0">v1.0</option>
    <option value="v1.1" selected>v1.1</option>
  </select><br />
  <label>vendor</label><br />
  <select name="vendor" id="vendor">
    <option value="switchbot" selected>switchbot</option>
    <option value="tp-link">tp-link</option>
  </select><br />
  <br />
  <label>SwitchBotプロクシの利用パスワード：</label><br />
  <input type="text" name="password" id="password" /><br /><br />

  <div id="errorMessages" style="color: red"></div>
  <br />

  <input type="button" value="暗号化" onclick="clickBtnEnc()" /><br /><br />

  <label>暗号化データ</label><br />
  <textarea id="encodeData" cols="100" rows="5" readonly></textarea><br />

  <hr />
  <label>復号化の確認</label><br />
  <input type="button" value="復号化して確認" onclick="clickBtnDec()" /><br />
  <textarea id="decodeData" cols="100" rows="10" readonly></textarea>
  <hr />
  <button onclick="jsonDownload()">jsonダウンロード</button>
  <p><small>&copy; 2023 watalab.info</small></p>
</body>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    getDeviceList('<?php echo htmlspecialchars($_POST['token'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($_POST['secretKey'], ENT_QUOTES, 'UTF-8'); ?>');
  });
</script>

</html>