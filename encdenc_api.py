from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import json
import requests
from encdenc import encrypt, decrypt
from utils import make_secret, make_sign, make_t, make_nonce

app = FastAPI()

# CORS設定を追加
origins = [
    "http://localhost:8000",
    "http://127.0.0.1:8000",
]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# リクエストデータのモデル定義
class EncodeRequestData(BaseModel):
    t: str
    p: str
    d: str
    s: str
    desc: str
    st: str
    et: str
    managePassword: str
    version: str
    vender: str


class DecodeRequestData(BaseModel):
    x: str
    p: str
    d: str

# エンドポイントの定義
@app.post("/api/encode_token")
async def encode_token(data: EncodeRequestData):
    token = data.t
    password = data.p
    deviceid = data.d
    secret = data.s
    desc = data.desc
    start_time = data.st
    end_time = data.et
    managePassword = data.managePassword
    version = data.version
    vender = data.vender

    if not (token and password and deviceid):
        raise HTTPException(status_code=400, detail="Parameters are not enough")

    json_data = json.dumps({
        "token": token, 
        "pickDevice": deviceid.split(","), 
        "secret": secret, 
        "desc": desc, 
        "start_time": start_time, 
        "end_time": end_time,
        "managePassword": managePassword,
        "version": version,
        "vender": vender
    })
    
    try:
        enc = encrypt(json_data, password).decode('utf-8')
    except Exception as e:
        raise HTTPException(status_code=500, detail="Encryption failed")

    return {"enc": enc}

@app.post("/api/decode_token")
async def decode_token(data: DecodeRequestData):
    param_enc = data.x
    password = data.p
    deviceid = data.d

    if not (param_enc and password and deviceid):
        raise HTTPException(status_code=400, detail="Parameters are not enough")

    try:
        dec = decrypt(param_enc, password)
        dec_json = json.loads(dec)
        token = dec_json['token']
        pickDevice = dec_json['pickDevice']
        secret = dec_json['secret']

        secret_key = make_secret(secret)
        t = make_t()
        nonce = make_nonce()
        sign = make_sign(secret_key, token, t, nonce)
    except Exception as e:
        raise HTTPException(status_code=400, detail="Decryption failed or invalid token")

    # デバイスIDのバリデーション
    invalid_devices = [device for device in deviceid.split(",") if device not in pickDevice]
    if invalid_devices:
        raise HTTPException(status_code=403, detail=f"Device ID: {', '.join(invalid_devices)} is not accepted")

    headers = {
        "Authorization": token,
        "sign": sign,
        "t": t,
        "nonce": nonce,
        "Content-Type": "application/json; charset=utf-8"
    }

    # TODO 許可しているデバイスIDのみ取得なので、複数デバイスIDの場合は複数デバイスの情報を取得する
    geturl = "https://api.switch-bot.com/v1.1/devices"
    try:
        swres = requests.get(geturl, headers=headers)
        swres.raise_for_status()
    except requests.exceptions.RequestException as e:
        raise HTTPException(status_code=swres.status_code, detail=f"Failed to fetch device status: {str(e)}")

    res = swres.json()
    swproxy = {'deviceid': deviceid}
    res['swproxy'] = swproxy

    return res

if __name__ == '__main__':
    import uvicorn
    uvicorn.run(app, host='127.0.0.1', port=8000)
