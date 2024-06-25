<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
  <?php
  if (isset($json_data)) {
    echo "<h1>Device List</h1>";
    echo "<table border='1'>";
    echo "<tr><th>Device ID</th><th>Device Name</th><th>Status</th><th>Command</th></tr>"; // Commands列を追加
    foreach ($json_data['deviceList'] as $device) {
      echo "<tr>";
      echo "<td>{$device['deviceId']}</td>";
      echo "<td><a>{$device['deviceName']}</a></td>";
      echo "<td>";
      if (!empty($device['status'])) {
        foreach ($device['status'] as $key => $value) {
          if ($value) {
            echo htmlspecialchars($key) . "<br>"; // コマンドを表示
          }
        }
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
  //jsにPHPの変数を渡す方法
  console.log('<?php echo $json_data['token'] ?>');
</script>

</html>