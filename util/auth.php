<?php
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
