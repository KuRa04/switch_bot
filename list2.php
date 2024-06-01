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
    $pythonScriptPath = __DIR__ . '/index.py';

    $output = [];
    $return_variable = 0;
    exec("python3 $pythonScriptPath", $output, $return_variable);
    // JSONレスポンスを結合してデコード
    $deviceList = json_decode(implode("\n", $output), true);
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
        foreach ((array)$deviceList['body']['deviceList'] as $device) {
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
