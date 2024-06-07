<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

require_once 'encrypt_decrypt.php';  // encrypt, decrypt 関数が定義されているファイル
require_once 'api_utils.php';    // make_secret, make_sign, make_t, make_nonce 関数が定義されているファイル

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Content-Type: text/plain");
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$response = array();
if (isset($data['t']) && isset($data['p']) && isset($data['d'])) {
    // Encryption process
    $token = $data['t'];
    $password = $data['p'];
    $deviceid = $data['d'];
    $secret = $data['s'];
    $desc = $data['desc'];
    $start_time = $data['st'];
    $end_time = $data['et'];
    $managePassword = $data['managePassword'];
    $version = $data['version'];
    $vender = $data['vender'];

    if (!$token || !$password || !$deviceid) {
        $response = array("error" => "Parameters are not enough");
    } else {
        $json_data = json_encode(array(
            "token" => $token, 
            "pickDevice" => explode(",", $deviceid), 
            "secret" => $secret, 
            "desc" => $desc, 
            "start_time" => $start_time, 
            "end_time" => $end_time,
            "managePassword" => $managePassword,
            "version" => $version,
            "vender" => $vender
        ));
        
        try {
            $enc = encrypt($json_data, $password);
            $response = array("enc" => $enc);
        } catch (Exception $e) {
            $response = array("error" => "Encryption failed");
        }
    }
} elseif (isset($data['x']) && isset($data['p']) && isset($data['d'])) {
    // Decryption process
    $param_enc = $data['x'];
    $password = $data['p'];
    $deviceid = $data['d'];

    if (!$param_enc || !$password || !$deviceid) {
        $response = array("error" => "Parameters are not enough");
    } else {
        try {
            $dec = decrypt($param_enc, $password);
            $dec_json = json_decode($dec, true);
            $token = $dec_json['token'];
            $pickDevice = $dec_json['pickDevice'];
            $secret = $dec_json['secret'];

            $secret_key = make_secret($secret);
            $t = make_t();
            $nonce = make_nonce();
            $sign = make_sign($secret_key, $token, $t, $nonce);
        } catch (Exception $e) {
            $response = array("error" => "Decryption failed or invalid token");
        }

        if (isset($response['error'])) {
            // Already set error, do nothing
        } else {
            $invalid_devices = array();
            foreach (explode(",", $deviceid) as $device) {
                if (!in_array($device, $pickDevice)) {
                    $invalid_devices[] = $device;
                }
            }

            if (count($invalid_devices) > 0) {
                $response = array("error" => "Device ID: " . implode(", ", $invalid_devices) . " is not accepted");
            } else {
                $headers = array(
                    "Authorization: $token",
                    "sign: $sign",
                    "t: $t",
                    "nonce: $nonce",
                    "Content-Type: application/json; charset=utf-8"
                );

                $geturl = "https://api.switch-bot.com/v1.1/devices";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $geturl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpcode >= 200 && $httpcode < 300) {
                    $res = json_decode($result, true);
                    $res['swproxy'] = array('deviceid' => $deviceid);
                    $response = $res;
                } else {
                    $response = array("error" => "Failed to fetch device status");
                }
            }
        }
    }
} else {
    $response = array("error" => "Invalid parameters");
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response);
?>
