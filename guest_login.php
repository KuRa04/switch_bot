<?php
session_start();

// mp取得完了
if (isset($_GET['mp'])) {
  $_SESSION['manage_password'] = $_GET['mp'];
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

  <script>
    document
      .getElementById("uploadForm")
      .addEventListener("submit", function(event) {
        const fileInput = document.getElementById("jsonFile");
        if (fileInput.files.length === 0) {
          alert("Please select a JSON file to upload.");
          event.preventDefault();
        }
      });
  </script>
</body>

</html>