<?php
session_start();

// mp取得完了
if (isset($_GET['mp'])) {
  $_SESSION['manage_password'] = $_GET['mp'];
}

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
        $manage_password = $_SESSION['manage_password'];
        $decrypt_password = $password . hex2bin($manage_password);

        $response = openssl_decrypt(base64_decode($auth_guest_token), 'aes-256-cbc', $decrypt_password, OPENSSL_RAW_DATA, 'iv12345678901234');

        $_SESSION['response'] = json_encode($response);
        echo json_encode($response);
      }
    } else {
      echo "アップロードされたファイルはJSON形式ではありません。";
    }
  } else {
    echo "ファイルがアップロードされていません。";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login and Upload JSON</title>
</head>

<body>
  <h1>Login Page</h1>
  <form id="uploadForm" action="allow_device_list.php" method="post" enctype="multipart/form-data">
    <label>所有者パスワード：</label><input type="text" name="password" size="100" /><br />
    <label for="jsonFile">Upload JSON File:</label>
    <input type="file" name="fileToUpload" id="fileToUpload">
    <button type="submit">Next</button>
  </form>
</body>

</html>