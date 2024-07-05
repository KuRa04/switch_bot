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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] == UPLOAD_ERR_OK) {
    $fileType = $_FILES['fileToUpload']['type'];
    if ($fileType == 'application/json') {
      $jsonContent = file_get_contents($_FILES['fileToUpload']['tmp_name']);
      $data = json_decode($jsonContent, true);
      if ($data === null) {
        echo "JSONファイルのデコードに失敗しました。";
      } else {
        $auth_guest_token = $data['authGuestToken'];
        $password = $_POST['password'];
        $decrypt_password = $password . MANAGE_PASSWORD;

        $response = openssl_decrypt(base64_decode($auth_guest_token), 'aes-256-cbc', $decrypt_password, OPENSSL_RAW_DATA, 'iv12345678901234');
        if (!$response) {
          header('Location: guest_login.php?error=1');
          exit;
        }
        $json_data = json_decode($response, true);

        if (isset($json_data['startTime']) && isset($json_data['endTime'])) {
          $current_date = new DateTime();
          $start_time = DateTime::createFromFormat('Y-m-d', $json_data['startTime']);
          $end_time = DateTime::createFromFormat('Y-m-d', $json_data['endTime']);

          if ($start_time > $current_date) {
            header('Location: guest_login.php?error=2');
            exit;
          }

          if ($end_time < $current_date) {
            header('Location: guest_login.php?error=3');
            exit;
          }
        }
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
</body>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    printAllowDeviceTable(<?php echo json_encode($json_data) ?>);
  });
</script>

</html>