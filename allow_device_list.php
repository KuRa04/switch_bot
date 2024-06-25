<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Device List</title>
</head>

<body>
  <?php
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ファイルがアップロードされたか確認
    if (isset($_FILES["fileToUpload"]["tmp_name"]) && is_uploaded_file($_FILES["fileToUpload"]["tmp_name"])) {
      // JSONファイルの内容を読み込む
      $jsonContent = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
      // JSONをデコードしてPHPの変数に変換
      $data = json_decode($jsonContent, true);

      // ここで$dataを使用して必要な処理を行う
      // 例: データの検証や表示
      echo "<pre>";
      print_r($data); // デバッグ用にデータを表示
      echo "</pre>";
    } else {
      echo "ファイルのアップロードに失敗しました。";
    }
  }
  ?>
</body>

</html>