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

function handleFileUpload()
{
  if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] != UPLOAD_ERR_OK) {
    return "ファイルがアップロードされていません。";
  }
  if ($_FILES['fileToUpload']['type'] != 'application/json') {
    return "アップロードされたファイルはJSON形式ではありません。";
  }
  return null;
}

function decodeJsonFile($tmpName)
{
  $jsonContent = file_get_contents($tmpName);
  $data = json_decode($jsonContent, true);
  if ($data === null) {
    return "JSONファイルのデコードに失敗しました。";
  }
  return $data;
}

function decryptAuthToken($authGuestToken, $password)
{
  $decrypt_password = $password . MANAGE_PASSWORD;
  $response = openssl_decrypt(base64_decode($authGuestToken), 'aes-256-cbc', $decrypt_password, OPENSSL_RAW_DATA, 'iv12345678901234');
  if (!$response) {
    return false;
  }
  return json_decode($response, true);
}

function validateAccessPeriod($json_data)
{
  if (isset($json_data['startTime']) && isset($json_data['endTime'])) {
    $current_date = new DateTime();
    $start_time = DateTime::createFromFormat('Y-m-d', $json_data['startTime']);
    $end_time = DateTime::createFromFormat('Y-m-d', $json_data['endTime']);

    if ($start_time > $current_date) {
      return 2;
    }
    if ($end_time < $current_date) {
      return 3;
    }
  }
  return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fileUploadError = handleFileUpload();
  if ($fileUploadError) {
    echo $fileUploadError;
    exit;
  }

  $data = decodeJsonFile($_FILES['fileToUpload']['tmp_name']);
  if (is_string($data)) {
    echo $data;
    exit;
  }

  $json_data = decryptAuthToken($data['authGuestToken'], $_POST['password']);
  if (!$json_data) {
    header('Location: guest_login.php?error=1');
    exit;
  }

  $accessError = validateAccessPeriod($json_data);
  if ($accessError) {
    header('Location: guest_login.php?error=' . $accessError);
    exit;
  }
}
?>

<body>
  <h1>Device List</h1>
  <p id="get-status-loading"></p>
  <div id="deviceListContainer"></div>
</body>
<script>
  document.addEventListener('DOMContentLoaded', async function() {
    await printAllowDeviceTable(<?php echo json_encode($json_data) ?>);
  });
</script>
<style>
  .device-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .device-table th,
  .device-table td {
    border-right: 1px solid #eaeaea;
    border-bottom: 1px solid #eaeaea;
    padding: 10px;
    text-align: left;
  }

  .device-table th:first-child,
  .device-table td:first-child {
    border-left: 1px solid #eaeaea;
  }

  .device-table th {
    background-color: #3498db;
    color: white;
    font-weight: bold;
  }

  .device-table tr:nth-child(even) {
    background-color: #f9f9f9;
  }

  .device-table tr:hover {
    background-color: #ecf0f1;
  }

  .device-table input[type="checkbox"] {
    margin-right: 10px;
    cursor: pointer;
  }
</style>

</html>