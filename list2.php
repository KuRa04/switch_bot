<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8"/>
<title>SwitchBotプロクシ（暗号化）</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<style>
table {
    border-collapse: collapse;
}
th, td {
    padding: 5px 10px 5px 10px;
    border-width: 1px 0px;
    border-style: solid;
    border-collapse: collapse;
}
textarea#encdata {
    //display: none;
} 

</style>
</head>
<body>
<?php

// IoTのデバイスのリストを取得
function get_devicelist($token){
    // リクエストヘッダー 設定
    $headers = array(
        "Authorization: $token",
        "Content-Type: application/json; charset=utf8",
    );

    $url = "https://api.switch-bot.com/v1.0/devices";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_COOKIEJAR,      'cookie');
    curl_setopt($curl, CURLOPT_COOKIEFILE,     'tmp');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

    $output = curl_exec($curl);
    curl_close($curl);
    
    return( json_decode($output, true) );
}

$token = $_REQUEST['token'];

if(isset($token) && $token != ''){
    echo '<strong>token= [ '.$token.' ]'."</strong><br/><br/>";
    echo "SwitchBot APIにアクセスします<br/><hr/>";
    $data = get_devicelist( $token );
    echo "デバイスリストを取得しました<br/><hr/>";
} else {
    $output = "tokenが設定されていません";
    $data = json_decode("{}", true);
}
?>

<form name="device">

Device List:<br>
<table name="device">
<tr>
<th><input type="checkbox" name="deviceall" value=""></th>
<th>Device ID: </th>
<th>Device Name: </th>
<th>Device Type: </th>
<th>Enable Cloud Service: </th>
<th>Hub Device ID: </th>
<th>Master: </th>
</tr>

<?php
foreach ((array)$data['body']['deviceList'] as $device) {
    echo "<tr>\n";
    echo '<td><input type="checkbox" name="pick" value="' . $device['deviceId'] . '"></td>';
    echo "<td>" . $device['deviceId'] . "</td>\n";
    echo "<td>" . $device['deviceName'] . "</td>\n";
    echo "<td>" . $device['deviceType'] . "</td>\n";
    echo "<td>" . ($device['enableCloudService'] ? 'Yes' : 'No') . "</td>\n";
    echo "<td>" . $device['hubDeviceId'] . "</td>\n";
    echo "<td>" . ($device['master'] ? 'Yes' : 'No') . "</td>\n";
    echo "</tr>\n";
    //echo "\n";
}
?>

</table>
</form>
<br/>

<!-- // infraredRemoteListの表示 -->
<form name="remote">

Infrared Remote List:<br/>
<table name="remote">
<tr>
<th><input type="checkbox" name="remoteall" value=""></th>
<th>Device ID: </th>
<th>Device Name: </th>
<th>Remote Type: </th>
<th>Hub Device ID: </th>
</tr>

<?php
foreach ((array)$data['body']['infraredRemoteList'] as $remote) {
    echo "<tr>\n";
    echo '<td><input type="checkbox" name="pick" value="' . $remote['deviceId'] . '"></td>';
    echo "<td>" . $remote['deviceId'] . "</td>\n";
    echo "<td>" . $remote['deviceName'] . "</td>\n";
    echo "<td>" . $remote['remoteType'] . "</td>\n";
    echo "<td>" . $remote['hubDeviceId'] . "</td>\n";
    echo "</tr>\n";
    echo "\n";
}
?>

</table>
</form>
<br/>
<hr/>

<input type="button" value="　選択決定　" onclick="clickBtn()" />

<table>
<tr><td>選択されたDeviceID</td><td><b><span id="dlist"></span></b></td></tr>
<tr><td>選択されたInfrared DeviceID</td><td><b><span id="rlist"></span></b></td></tr>
</table>

<hr/>

token: <br/>
<input type="text" name="token" id="token" size="100" value="<?php echo $token ?>"/><br/>

SwitchBotプロクシの利用パスワード：<br/>
<input type="text" name="password" id="password"/><br/>
<input type="button" value="　暗号化　" onclick="clickBtnEnc()" />

<br/><br/>
<script type="text/javascript" src="sw.js"></script>

暗号化データ<br/>
<textarea id="encdata" cols="100" rows="5" readonly></textarea>
<hr/>
復号化の確認<br/>
<input type="button" value="　復号化して確認　" onclick="clickBtnDec()" /><br/>
<textarea id="decdata" cols="100" rows="10" readonly></textarea>
<hr/>
<p><small>&copy; 2023 watalab.info</small></p>

</body>
</html>
