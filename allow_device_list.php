<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

  <title>Device List</title>
</head>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // ファイルがアップロードされたか確認
  if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] == UPLOAD_ERR_OK) {
    // ファイルタイプがJSONであることを確認
    $fileType = $_FILES['fileToUpload']['type'];
    if ($fileType == 'application/json') {
      // JSONファイルを読み込み
      $jsonContent = file_get_contents($_FILES['fileToUpload']['tmp_name']);
      // JSONをデコード
      $data = json_decode($jsonContent, true);
      if ($data === null) {
        echo "JSONファイルのデコードに失敗しました。";
      } else {
        // デコードされたデータを処理
        // 例: データを表示
        $auth_guest_token = $data['authGuestToken'];
        $password = $_POST['password'];

        $response = openssl_decrypt(base64_decode($auth_guest_token), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, 'iv12345678901234');
        $json_data = json_decode($response, true);
      }
    } else {
      echo "アップロードされたファイルはJSON形式ではありません。";
    }
  } else {
    echo "ファイルがアップロードされていません。";
  }
}
?>

<body>
  <h1>Device List</h1>
  <p id="get-status-loading"></p>
  <?php
  if (isset($json_data)) {
    echo "<table border='1'>";
    echo "<tr><th>Device ID</th><th>Device Name</th><th>Status</th><th>Command</th></tr>"; // Commands列を追加
    foreach ($json_data['deviceList'] as $device) {
      echo "<tr>";
      echo "<td>{$device['deviceId']}</td>";
      echo "<td><a href='allow_device_detail.php?" . "t=" . urlencode($json_data['token']) . "s=" . urlencode($json_data['secretKey']) . "&d=" . urlencode($device['deviceId']) . "'>{$device['deviceName']}</a></td>";
      echo "<td>";
      if (!empty($device['status'])) {
        echo "<p id='allowStatus" . htmlspecialchars($device['deviceId']) . "'></p>" ;
      }
      echo "</td>";
      echo "<td>";
      if (!empty($device['commands'])) {
        foreach ($device['commands'] as $key => $value) {
          if ($value) {
            echo htmlspecialchars($key) . "<br>"; // コマンドを表示
          }
        }
      }
      echo "</td>";
      echo "</tr>";
    }
    echo "</table>";
  }
  ?>
</body>
<script>
  function getAllowDeviceStatus() {
    const loadingElement = document.getElementById('get-status-loading');
    // innerHTMLを使用して、改行を<br>タグとして反映
    loadingElement.innerHTML = "status取得中...";
    const token = '<?php echo $json_data['token'] ?>'
    const secretKey = '<?php echo $json_data['secretKey'] ?>'
    const deviceList = JSON.parse('<?php echo addslashes(json_encode($json_data['deviceList'])) ?>');
    const data = {
      token: token,
      secretKey: secretKey,
      deviceList: deviceList
    };

    axios({
        method: "post",
        url: "https://watalab.info/lab/asakura/api/get_allow_device_list_status.php",
        data: JSON.stringify(data),
        headers: {
          "Content-Type": "application/json"
        },
      })
      .then(function(response) {
        console.log(response.data);
        const allowDeviceStatus = response.data;
        allowDeviceStatus.forEach(function(allowDevice) {
          let statusContent = ''
          if (typeof allowDevice.body === 'object') {
            // オブジェクトのキーと値を文字列に変換し、それらを改行で区切る
            const statusContent = Object.entries(allowDevice.body.status).map(([key, value]) => `${key}: ${value}`).join('<br>');
            // HTMLの要素を選択
            const statusElement = document.getElementById('allowStatus' + allowDevice.body.deviceId);
            // innerHTMLを使用して、改行を<br>タグとして反映
            statusElement.innerHTML = statusContent;
          }
        });
        loadingElement.innerHTML = "";
      })
      .catch(function(error) {
        console.error("Error: " + error);
      });
  }
  getAllowDeviceStatus();
</script>

</html>