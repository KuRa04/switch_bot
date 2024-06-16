<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>SwitchBotプロクシ（暗号化）</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <style>
        table {
            border-collapse: collapse;
        }

        th,
        td {
            padding: 5px 10px 5px 10px;
            border-width: 1px 0px;
            border-style: solid;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <?php
    require_once 'api_utils.php';

    if (!isset($_POST['token']) || !isset($_POST['secret'])) {
        echo json_encode(["error" => "引数が不正です"]);
        exit(1);
    }

    // SwitchBotアプリから取得
    $token = $_POST['token'];
    $secret_key = $_POST['secret'];

    // Requestパラメータ作成
    $secret_key = make_secret($secret_key);
    $t = make_t();
    $nonce = make_nonce();
    $sign = make_sign($secret_key, $token, $t, $nonce); // token を引数として渡す

    // URL指定
    $url = "https://api.switch-bot.com/v1.1/devices";

    // APIheader作成
    $headers = [
        "Authorization: $token",
        "sign: $sign",
        "t: $t",
        "nonce: $nonce",
        "Content-Type: application/json; charset=utf-8"
    ];

    // cURL処理
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    $device_list = json_decode($response, true);

    //TODO: コマンド操作があるデバイスをアプリに登録する
    //device_listのmockを作成
    $mock_device_list = [
        "body" => [
            "deviceList" => [
                [
                    "deviceId" => "1",
                    "deviceName" => "device1",
                    "deviceType" => "Bot",
                    "enableCloudService" => true,
                    "hubDeviceId" => "hubDeviceId1"
                ],
                [
                    "deviceId" => "2",
                    "deviceName" => "device2",
                    "deviceType" => "Curtain",
                    "enableCloudService" => false,
                    "hubDeviceId" => "hubDeviceId2"
                ],
                [
                    "deviceId" => "3",
                    "deviceName" => "device3",
                    "deviceType" => "Humidifier",
                    "enableCloudService" => true,
                    "hubDeviceId" => "hubDeviceId3"
                ]
            ]
        ]
    ];

    //command一覧を取得し、$device_listに追加する
    // CSVファイルを開き、その内容を配列に読み込む
    $csv = array_map('str_getcsv', file('command_type_table.csv'));
    array_walk($csv, function (&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    });
    array_shift($csv); // remove column header

    // $mock_device_list_add_commandsを$mock_device_listのコピーとして初期化
    $mock_device_list_add_commands = $mock_device_list;

    // $mock_device_listをループし、各deviceTypeに対して以下の操作を行う
    // deviceListの各デバイスに対してループを行う
    foreach ($mock_device_list_add_commands['body']['deviceList'] as $index => $device) {
        // CSV配列をループし、deviceTypeが一致する行を見つける
        foreach ($csv as $row) {
            if ($device['deviceType'] == $row['deviceType']) {                // 一致する行が見つけられたら、その行の「Command,command parameter,Description」を該当するdeviceTypeの配列に追加する
                $mock_device_list_add_commands['body']['deviceList'][$index]['commands'][] = [
                    'command' => $row['command'],
                    'commandParameter' => $row['commandParameter'],
                    'description' => $row['description']
                ];
            }
        }
    }

    $csv = array_map('str_getcsv', file('status_type_table.csv'));
    array_walk($csv, function (&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    });
    array_shift($csv); // remove column header

    foreach ($mock_device_list_add_commands['body']['deviceList'] as $index => $device) {
        // CSV配列をループし、deviceTypeが一致する行を見つける
        foreach ($csv as $row) {
            if ($device['deviceType'] == $row['deviceType']) {                // 一致する行が見つけられたら、その行の「Command,command parameter,Description」を該当するdeviceTypeの配列に追加する
                $mock_device_list_add_commands['body']['deviceList'][$index]['status'][] = [
                    'key' => $row['key'],
                    'deviceType' => $row['deviceType'],
                    'description' => $row['description']
                ];
            }
        }
    }

    ?>

    <form name="device">
        <label>Device List:</label><br>
        <table name="device">
            <tr>
                <th><input type="checkbox" name="deviceall" value=""></th>
                <th>Device ID: </th>
                <th>Device Name: </th>
                <th>Device Type: </th>
                <th>Enable Cloud Service: </th>
                <th>Hub Device ID: </th>
                <th>status: </th>
                <th>command: </th>
            </tr>
            <?php
            foreach ((array)$mock_device_list_add_commands['body']['deviceList'] as $device) {
                echo "<tr>\n";
                echo '<td><input type="checkbox" name="pick" value="' . $device['deviceId'] . '"></td>';
                echo "<td>" . $device['deviceId'] . "</td>\n";
                echo "<td>" . $device['deviceName'] . "</td>\n";
                echo "<td>" . $device['deviceType'] . "</td>\n";
                echo "<td>" . ($device['enableCloudService'] ? 'Yes' : 'No') . "</td>\n";
                echo "<td>" . $device['hubDeviceId'] . "</td>\n";
                echo "<td>";
                foreach ($device['status'] as $status) {
                    echo "<input type='checkbox' name='" . $device['deviceId'] . $status['key'] . "' value='" . $device['deviceId'] . "/" . $device['deviceType'] . "/" . $device['deviceName'] . "/" . "status" . "/" . $status['key'] . "' onclick='onCheckboxClick(this)'> " . $status['key'] . "<br>";
                }
                echo "</td>\n";
                echo "<td>";
                foreach ($device['commands'] as $command) {
                    echo "<input type='checkbox' name='" . $device['deviceId'] . $command['command'] . "' value='" . $device['deviceId'] . "/" . $device['deviceType'] . "/" . $device['deviceName'] . "/" . "command" . "/" . $command['command'] . "' onclick='onCheckboxClick(this)'> " . $command['command'] . "<br>";
                }
                echo "</td>\n";
                echo "</tr>\n";
                echo "\n";
            }
            ?>
        </table>
    </form>
    <br />

    <!-- // infraredRemoteListの表示 -->
    <form name="remote">
        <label>Infrared Remote List:</label><br>
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
    <br />
    <hr />
    <input type="button" value="　選択決定　" onclick="clickBtn()" />

    <table>
        <tr>
            <td>選択されたDevice</td>
            <td><b><span id="dlist"></span></b></td>
        </tr>
        <tr>
            <td>選択されたInfrared DeviceID</td>
            <td><b><span id="rlist"></span></b></td>
        </tr>
    </table>
    <hr />

    <label>token:</label><br />
    <input type="text" name="token" id="token" size="100" value="<?php echo $token ?>" /><br />
    <label>secret:</label><br />
    <input type="text" name="secret" id="secret" size="100" value="<?php echo $secret_key ?>" /><br />
    <label>説明:</label><br />
    <input type="text" name="description" id="description" size="100" value="" /><br />
    <p>有効期間</p>
    <label>開始:</label>
    <input type="date" name="startTime" id="startTime" size="100" value="" /><br />
    <label>終了:</label>
    <input type="date" name="endTime" id="endTime" size="100" value="" /><br /><br />
    <label>version</label><br />
    <select name="version" id="version">
        <option value="v1.0">v1.0</option>
        <option value="v1.1">v1.1</option>
    </select><br />
    <label>vender</label><br />
    <select name="vender" id="vender">
        <option value="switchbot">switchbot</option>
        <option value="tp-link">tp-link</option>
    </select><br />
    <br />
    <label>SwitchBotプロクシの利用パスワード：</label><br />
    <input type="text" name="password" id="password" /><br /><br />

    <input type="button" value="　暗号化　" onclick="clickBtnEnc()" /><br /><br />
    <script type="text/javascript" src="sw.js"></script>

    <label>暗号化データ</label><br />
    <textarea id="encdata" cols="100" rows="5" readonly></textarea><br />
    <label>配布用URL</label><br />
    <textarea id="guest_login_page_url" cols="100" rows="1" readonly></textarea>

    <hr />
    <label>復号化の確認</label><br />
    <input type="button" value="　復号化して確認　" onclick="clickBtnDec()" /><br />
    <textarea id="decdata" cols="100" rows="10" readonly></textarea>
    <hr />
    <button onclick="jsonDownload()">jsonダウンロード</button>
    <p><small>&copy; 2023 watalab.info</small></p>
</body>

</html>