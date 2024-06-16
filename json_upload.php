<?php
require_once 'encrypt_decrypt.php';    // encrypt, decrypt 関数が定義されているファイル

session_start();

// セッションからmanage_passwordを取得
if (isset($_SESSION['manage_password'])) {
  $manage_password = $_SESSION['manage_password'];
  // 必要に応じてここで$manage_passwordを処理する
} else {
  // エラーハンドリング: manage_passwordがない場合
  die("Manage password is missing.");
}

// フォームから送信されたデータを処理
if (isset($_POST['password']) && isset($_FILES['jsonFile'])) {
  $password = $_POST['password'];
  $jsonFile = $_FILES['jsonFile'];

  // jsonFileの処理
  if ($jsonFile['error'] == UPLOAD_ERR_OK) {
    $jsonContent = file_get_contents($jsonFile['tmp_name']);
    $data = json_decode($jsonContent, true);
    $auth_guest_token = $data['token']; //権限付与トークン
    $bin_password = hex2bin($manage_password);
    $decrypt_password = $password . $bin_password; //復号化に使用するパスワード

    //復号化処理
    $dec = decrypt($auth_guest_token, $decrypt_password);
    $dec_json = json_decode($dec, true);
    $token = $dec_json['token']; //元々のswitchbotAPIのトークン


    // ここで$manage_passwordと$form_passwordを使用した処理を行う
  } else {
    die("Failed to upload JSON file.");
  }
} else {
  die("Invalid form submission.");
}

// セッションのクリーンアップ
unset($_SESSION['manage_password']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login and Upload JSON</title>
</head>

<body>
  <?php
  $html = "<div>";
  foreach ($dec_json['device_list'] as $device) {
    $html .= "<h2>{$device['deviceType']}</h2>";
    $html .= "<h3>{$device['deviceName']}</h3>";
    $html .= "<div style='display: flex; align-items: baseline;'>";
    foreach (['status', 'commands'] as $category) {
      $html .= "<div style='margin-right: 10px;'>";
      $html .= "<h4 style='display: inline-block;'>{$category}</h4>";
      foreach ($device[$category] as $key => $value) {
        if ($value == 1) {
          $html .= "<p>{$key}</p>";
        }
      }
      $html .= "</div>";
    }
    $html .= "</div>";
  }
  $html .= "</div>";

  // HTMLを表示

  echo $html;
  ?>
</body>

</html>