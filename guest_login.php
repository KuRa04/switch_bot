<?php
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login and Upload JSON</title>
</head>

<body class="login-body">
  <h2 class="login-title">メンバーのログインページ</h2>
  <div class="container">
    <?php if ($error == 1) : ?>
      <p class="error-message">パスワードが間違っています。</p>
    <?php elseif ($error == 2) : ?>
      <p class="error-message">利用開始日より前の日付です。</p>
    <?php elseif ($error == 3) : ?>
      <p class="error-message">有効期限が切れています。</p>
    <?php endif; ?>
    <form id="uploadForm" class="login-form" action="allow_device_list.php" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label for="password" class="form-label">所有者パスワード:</label>
        <input type="text" name="password" id="password" class="form-control" />
      </div>
      <div class="form-group">
        <label for="fileToUpload" class="form-label">ログイン用JSONファイル:</label>
        <input type="file" name="fileToUpload" id="fileToUpload" class="form-control-file">
      </div>
      <button type="submit" class="submit-button">ログイン</button>
    </form>
  </div>
</body>
<style>
  .login-body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f4f4;
    padding: 20px;
  }

  .container {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: 20px auto;
  }

  .error-message {
    color: red;
    margin-bottom: 20px;
  }

  .login-title {
    color: #333;
    text-align: center;
    margin-bottom: 20px;
  }

  .login-form {
    display: flex;
    flex-direction: column;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    margin-bottom: 5px;
    color: #333;
  }

  .form-control,
  .form-control-file {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
  }

  .submit-button {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }

  .submit-button:hover {
    background-color: #0056b3;
  }
</style>

</html>