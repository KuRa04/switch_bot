<?php
function make_secret($secret_key)
{
    return $secret_key;
}

function make_sign($secret_key, $token, $t, $nonce)
{
    return base64_encode(hash_hmac('sha256', $token . $t . $nonce, $secret_key, true));
}

function make_t()
{
    return round(microtime(true) * 1000);
}

function make_nonce()
{
    return bin2hex(random_bytes(16));
}

function get_device_list($token, $secret_key)
{
    $secret_key = make_secret($secret_key);
    $t = make_t();
    $nonce = make_nonce();
    $sign = make_sign($secret_key, $token, $t, $nonce);

    $url = "https://api.switch-bot.com/v1.1/devices";

    $headers = [
        "Authorization: $token",
        "sign: $sign",
        "t: $t",
        "nonce: $nonce",
        "Content-Type: application/json; charset=utf-8"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function get_device_status($data)
{
    $param_enc = $data['x'];
    $password = $data['p'];
    $deviceid = $data['d'];

    if (!$param_enc || !$password || !$deviceid) {
        return array("error" => "Parameters are not enough");
    }

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
        return array("error" => "Decryption failed or invalid token");
    }

    $invalid_devices = array();
    foreach (explode(",", $deviceid) as $device) {
        if (!in_array($device, $pickDevice)) {
            $invalid_devices[] = $device;
        }
    }

    if (count($invalid_devices) > 0) {
        return array("error" => "Device ID: " . implode(", ", $invalid_devices) . " is not accepted");
    }

    $device_status = array();
    foreach (explode(",", $deviceid) as $device) {
        $headers = array(
            "Authorization: $token",
            "sign: $sign",
            "t: $t",
            "nonce: $nonce",
            "Content-Type: application/json; charset=utf-8"
        );

        $geturl = "https://api.switch-bot.com/v1.1/devices/$device/status";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $geturl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode >= 200 && $httpcode < 300) {
            $res = json_decode($result, true);
            $device_status[$device] = $res;
        } else {
            return array("error" => "Failed to fetch device status for device ID: $device");
        }
    }

    return array(
        "device_status" => $device_status,
        "decrypted_data" => $dec_json
    );
}
