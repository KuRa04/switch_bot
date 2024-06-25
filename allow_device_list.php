<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Device List</title>
</head>

<body>
  <?php
  session_start();

  // セッションからデータを取得
  $response = isset($_SESSION['response']) ? $_SESSION['response'] : 'データがありません。';

  // セッションのクリア
  unset($_SESSION['response']);

  // データの表示
  echo "<h1>Response Data</h1>";
  echo "<p>" . htmlspecialchars($response) . "</p>";
  ?>
</body>

</html>