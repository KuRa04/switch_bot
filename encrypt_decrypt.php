<?php
function encrypt($data, $password) {
    // 暗号化処理をここに実装します
    return base64_encode(openssl_encrypt($data, 'aes-256-cbc', $password, OPENSSL_RAW_DATA, 'iv12345678901234'));
}

function decrypt($data, $password) {
    // 復号化処理をここに実装します
    return openssl_decrypt(base64_decode($data), 'aes-256-cbc', $password, OPENSSL_RAW_DATA, 'iv12345678901234');
}
?>