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
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script type="text/javascript" src="switchbot_api.js"></script>
</head>

<body class="device-list-body">
  <h2 class="page-title">SwitchBotのTokenとSecretKey</h2>
  <p class="form-group"><label for="token" class="form-label">SwitchBot APIのtoken：</label><input type="text" name="token" id="token" class="form-control" value="<?php echo htmlspecialchars($_POST['token'], ENT_QUOTES, 'UTF-8'); ?>" size="100" readonly /></p>
  <p class="form-group"><label for="secretKey" class="form-label">SwitchBot APIのsecret：</label><input type="text" name="secretKey" id="secretKey" class="form-control" value="<?php echo htmlspecialchars($_POST['secretKey'], ENT_QUOTES, 'UTF-8'); ?>" size="100" readonly /></p>

  <h2 class="section-heading">デバイスリスト</h2>
  <div id="deviceListContainer" class="device-list-container"></div>

  <div class="form-group">
    <label for="description" class="form-label">説明:</label>
    <input type="text" name="description" id="description" class="form-control" size="100" value="" />
  </div>

  <div class="form-group">
    <p class="form-label">有効期間</p>
    <label for="startTime" class="form-label">開始:</label>
    <input type="date" name="startTime" id="startTime" class="form-control" size="100" value="" />
    <label for="endTime" class="form-label">終了:</label>
    <input type="date" name="endTime" id="endTime" class="form-control" size="100" value="" />
  </div>

  <div class="form-group">
    <label for="version" class="form-label">version</label>
    <select name="version" id="version" class="form-control">
      <option value="v1.0">v1.0</option>
      <option value="v1.1" selected>v1.1</option>
    </select>
  </div>

  <div class="form-group">
    <label for="vendor" class="form-label">vendor</label>
    <select name="vendor" id="vendor" class="form-control">
      <option value="switchbot" selected>switchbot</option>
      <option value="tp-link">tp-link</option>
    </select>
  </div>

  <div class="form-group">
    <label for="password" class="form-label">SwitchBotプロクシの利用パスワード：</label>
    <input type="text" name="password" id="password" class="form-control" />
  </div>

  <div id="errorMessages" class="error-messages"></div>

  <input type="button" value="暗号化" class="button" onclick="clickBtnEnc()" />

  <div class="form-group">
    <label for="authGuestToken" class="form-label">暗号化データ</label>
    <textarea id="authGuestToken" class="form-control textarea" cols="100" rows="5" readonly></textarea>
  </div>

  <div class="form-group">
    <label for="decodeData" class="form-label">復号化</label>
    <input type="button" value="復号化して確認" class="button" onclick="clickBtnDec()" />
    <textarea id="decodeData" class="form-control textarea" cols="100" rows="10" readonly></textarea>
  </div>

  <button class="button button-download" onclick="jsonDownload()">jsonダウンロード</button>
  <p class="footer"><small>&copy; 2023 watalab.info</small></p>
</body>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    getDeviceList('<?php echo htmlspecialchars($_POST['token'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($_POST['secretKey'], ENT_QUOTES, 'UTF-8'); ?>');
  });
</script>

</html>