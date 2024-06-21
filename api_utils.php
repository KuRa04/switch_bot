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
