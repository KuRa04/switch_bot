<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

require_once 'api_utils.php';    // make_secret, make_sign, make_t, make_nonce 関数が定義されているファイル
require_once 'encrypt_decrypt.php';    // encrypt, decrypt 関数が定義されているファイル

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
    $response = get_device_status($data);
} else {
    $response = array("error" => "Invalid parameters");
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response);
