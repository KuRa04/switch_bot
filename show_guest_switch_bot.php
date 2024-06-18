<?php
$managePassword = isset($_GET['mp']) ? $_GET['mp'] : '';

if (!$managePassword) {
  echo "No manage password provided!";
  exit;
}

if (isset($GET['data'])) {

  $encodedJsonContent = $_GET['data'];

  $jsonContent = urldecode($encodedJsonContent);

  echo "Received JSON Content" . htmlspecialchars($jsonContent) . "<br>";
  echo "Manage Password: " . htmlspecialchars($managePassword) . "<br>";

  $jsonData = json_decode($jsonContent, true);

  if (json_last_error() === JSON_ERROR_NONE) {
    echo "Decoded JSON Data: <br>";
    echo "Token: " . htmlspecialchars($jsonData['token']) . "<br>";
    echo "Guest Login Page URL: " . htmlspecialchars($jsonData['guest_login_page_url']) . "<br>";

    $password = $jsonData['password'];
    $bin_password = hex2bin($managePassword);
    $decrypt_password = $password . $bin_password;
    $dec = decrypt($jsonData['token'], $decrypt_password);
    $dec_json = json_decode($dec, true);
    echo $dec_json['token'];
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Display JSON Data</title>
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 8px;
    }

    th {
      background-color: #f2f2f2;
    }
  </style>
</head>

<body>
  <h1>JSON Data</h1>
</body>

</html>