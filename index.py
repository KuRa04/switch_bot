import time
import hashlib
import hmac
import base64
import uuid
import requests
import pprint
import json
import sys
import logging

def make_secret(secret_key):
    secret_key = bytes(secret_key, 'utf-8')
    return secret_key

def make_sign(secret_key, t, nonce):
    string_to_sign = '{}{}{}'.format(token, t, nonce)
    string_to_sign = bytes(string_to_sign, 'utf-8')
    sign = base64.b64encode(hmac.new(secret_key, msg=string_to_sign, digestmod=hashlib.sha256).digest())
    return sign

def make_t():
    t = int(round(time.time() * 1000))
    return str(t)

def make_nonce():
    nonce = str(uuid.uuid4())
    return nonce

if len(sys.argv) != 3:
    print(json.dumps({"error": "引数が不正です"}))
    sys.exit(1)


#SwitchBotアプリから取得
token = sys.argv[1]
secret_key = sys.argv[2]

#Requestパラメータ作成
secret_key = make_secret(secret_key)
t = make_t()
nonce = make_nonce()
sign = make_sign(secret_key, t, nonce)

#URL指定
url = "https://api.switch-bot.com/v1.1/devices"

#APIheader作成
headers = {
    "Authorization": token,
    "sign": sign,
    "t": t,
    "nonce": nonce,
    "Content-Type": "application/json; charset=utf-8"
}

#requests処理
response = requests.get(url,headers=headers)

print(json.dumps(response.json()))
