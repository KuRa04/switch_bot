<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header("Content-Type: text/plain");
  exit();
}
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <title>SwitchBotプロクシ（暗号化）</title>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script type="text/javascript" src="switchbot_api.js"></script>
</head>

<body class="device-list-body">
  <p id="getDeviceListLoading"></p>
  <div id="container" class="container">
    <h2 class="page-title">権限管理画面</h2>
    <p class="form-group"><label for="token" class="form-label">SwitchBotAPIのToken:</label><input type="text" name="token" id="token" class="form-control" value="<?php echo htmlspecialchars($_POST['token'], ENT_QUOTES, 'UTF-8'); ?>" size="100" readonly /></p>
    <p class="form-group"><label for="secretKey" class="form-label">SwitchBotAPIのSecretKey:</label><input type="text" name="secretKey" id="secretKey" class="form-control" value="<?php echo htmlspecialchars($_POST['secretKey'], ENT_QUOTES, 'UTF-8'); ?>" size="100" readonly /></p>

    <label for="description" class="form-label">デバイス一覧:</label>
    <div id="deviceListContainer" class="device-list-container"></div>

    <div class="form-group">
      <label for="description" class="form-label">説明:</label>
      <input type="text" name="description" id="description" class="form-control" size="100" value="" />
    </div>

    <div class="form-group">
      <div class="date-range">
        <div class="date-range-item">
          <label for="startTime" class="form-label">利用開始日:</label>
          <input type="date" name="startTime" id="startTime" class="form-control" value="" />
        </div>
        <div class="date-range-item">
          <label for="endTime" class="form-label">利用終了日:</label>
          <input type="date" name="endTime" id="endTime" class="form-control" value="" />
        </div>
      </div>
    </div>

    <div class="form-group">
      <label for="version" class="form-label">version:</label>
      <select name="version" id="version" class="form-control">
        <option value="v1.0">v1.0</option>
        <option value="v1.1" selected>v1.1</option>
      </select>
    </div>

    <div class="form-group">
      <label for="vendor" class="form-label">vendor:</label>
      <select name="vendor" id="vendor" class="form-control">
        <option value="switchbot" selected>switchbot</option>
        <option value="tp-link">tp-link</option>
      </select>
    </div>

    <div class="form-group">
      <label for="password" class="form-label">所有者パスワード:</label>
      <input type="text" name="password" id="password" class="form-control" />
    </div>

    <div id="errorMessages" class="error-messages"></div>

    <div class="form-group">
      <button type="button" class="button button-encrypt" onclick="clickBtnEnc()">暗号化</button>
      <textarea id="authGuestToken" class="form-control textarea" cols="100" rows="5" readonly></textarea>
    </div>

    <div class="form-group">
      <button type="button" id="decode-button" class="button button-decrypt" onclick="clickBtnDec()">復号化</button>
      <textarea id="decodeData" class="form-control textarea" cols="100" rows="10" readonly></textarea>
    </div>

    <button id="json-download-button" class="button button-download" onclick="jsonDownload()">jsonダウンロード</button>
    <p class="footer"><small>&copy; 2023 watalab.info</small></p>
  </div>
</body>
<script>
  document.addEventListener('DOMContentLoaded', async function() {
    const container = document.getElementById('container');
    container.style.display = 'none';
    await getDeviceList('<?php echo htmlspecialchars($_POST['token'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($_POST['secretKey'], ENT_QUOTES, 'UTF-8'); ?>');
    container.style.display = '';

    const decodeButton = document.getElementById('decode-button');
    decodeButton.disabled = true;
    const jsonDownloadButton = document.getElementById('json-download-button');
    jsonDownloadButton.disabled = true;
  });
</script>
<style>
  .device-list-body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f4f4;
    padding: 20px;
  }

  .page-title,
  .section-heading {
    color: #333;
    margin-bottom: 20px;
  }

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

  .form-group {
    margin-bottom: 20px;
  }

  .form-control {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
  }

  .form-label {
    display: block;
    color: #333;
    font-weight: bold;
  }

  .date-range {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .date-range-item {
    flex: 1;
    margin-right: 20px;
  }

  .date-range-item:last-child {
    margin-right: 0;
  }

  .device-list-container {
    margin-bottom: 20px;
  }

  .button-encrypt,
  .button-decrypt {
    width: auto;
    padding: 10px 20px;
    margin-top: 10px;
  }

  .button-encrypt {
    background-color: #007bff;
    color: white;
  }

  .button-decrypt {
    background-color: #28a745;
    color: white;
  }

  .button-decrypt:disabled,
  .button-download:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .button,
  .button-download {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    display: inline-block;
    margin: 5px 0;
  }

  .button-download {
    background-color: #28a745;
  }

  .button:hover,
  .button-download:hover {
    opacity: 0.8;
  }


  .textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin: 10px 0;
    resize: vertical;
  }

  .error-messages {
    color: red;
    margin-bottom: 20px;
  }

  .footer {
    margin-top: 20px;
    text-align: center;
    color: #777;
  }
</style>

</html>