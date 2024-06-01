import time
import requests
import json
import sys
import logging
from utils import make_secret, make_sign, make_t, make_nonce

if len(sys.argv) != 3:
    print(json.dumps({"error": "引数が不正です"}))
    sys.exit(1)

# SwitchBotアプリから取得
token = sys.argv[1]
secret_key = sys.argv[2]

# Requestパラメータ作成
secret_key = make_secret(secret_key)
t = make_t()
nonce = make_nonce()
sign = make_sign(secret_key, token, t, nonce)  # token を引数として渡す

# URL指定
url = "https://api.switch-bot.com/v1.1/devices"

# APIheader作成
headers = {
    "Authorization": token,
    "sign": sign,
    "t": t,
    "nonce": nonce,
    "Content-Type": "application/json; charset=utf-8"
}

# requests処理
response = requests.get(url, headers=headers)

print(json.dumps(response.json()))
