<?php
function make_secret($secret_key) {
    // シークレットキーの生成処理をここに実装します
    return hash('sha256', $secret_key);
}

function make_sign($secret_key, $token, $t, $nonce) {
    // 署名の生成処理をここに実装します
    return base64_encode(hash_hmac('sha256', $token . $t . $nonce, $secret_key, true));
}

function make_t() {
    // タイムスタンプの生成処理をここに実装します
    return round(microtime(true) * 1000);
}

function make_nonce() {
    // ノンスの生成処理をここに実装します
    return bin2hex(random_bytes(16));
}
?>
