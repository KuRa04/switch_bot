import time
import hashlib
import hmac
import base64
import uuid
import requests
import pprint

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

#SwitchBotアプリから取得
secret_key = "7594f1b4ef40a81c47a3c02dab055f2b"
token = "5e084039a332385fce4821760d17856e238e10fe942271cce54b54d6eacecc14dc0f603e5b19df79bc49f5071c795e2f"

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

pprint.pprint(response.json())
