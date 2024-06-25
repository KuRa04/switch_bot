<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Device List</title>
</head>

<body>
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
</body>

</html>