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

<body>
  <?php if ($error == 1) : ?>
    <p style="color: red;">パスワードが間違っています。</p>
  <?php elseif ($error == 2) : ?>
    <p style="color: red;">有効期限より前の日付です。 </p>
  <?php elseif ($error == 3) : ?>
    <p style="color: red;">有効期限が切れています。</p>
  <?php endif; ?>
  <h1>Login Page</h1>
  <form id="uploadForm" action="allow_device_list.php" method="post" enctype="multipart/form-data">
    <label>所有者パスワード：</label><input type="text" name="password" size="100" /><br />
    <label for="jsonFile">Upload JSON File:</label>
    <input type="file" name="fileToUpload" id="fileToUpload">
    <button type="submit">Next</button>
  </form>
</body>

</html>