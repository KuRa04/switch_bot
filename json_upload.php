<?php
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
    echo $data['token']; //権限付与トークンを表示

    // ここで$manage_passwordと$form_passwordを使用した処理を行う
  } else {
    die("Failed to upload JSON file.");
  }
} else {
  die("Invalid form submission.");
}

// セッションのクリーンアップ
unset($_SESSION['manage_password']);
