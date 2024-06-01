from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import json
from encdenc import encrypt, decrypt
import requests

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

    if not (token and password and deviceid):
        raise HTTPException(status_code=400, detail="Parameters are not enough")

    json_data = json.dumps({"token": token, "pickDevice": deviceid.split(",")})
    
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
    except Exception as e:
        raise HTTPException(status_code=400, detail="Decryption failed or invalid token")

    if deviceid not in pickDevice:
        raise HTTPException(status_code=403, detail=f"Device ID: {deviceid} is not accepted")

    HEADERS = {
        'Authorization': token,
        'Content-Type': 'application/json; charset=utf-8'
    }

    geturl = f"https://api.switch-bot.com/v1.1/devices/{deviceid}/status"
    swres = requests.get(geturl, headers=HEADERS)
    
    if swres.status_code != 200:
        raise HTTPException(status_code=swres.status_code, detail="Failed to fetch device status")

    res = swres.json()
    swproxy = {'deviceid': deviceid}
    res['swproxy'] = swproxy

    return res

if __name__ == '__main__':
    import uvicorn
    uvicorn.run(app, host='127.0.0.1', port=8000)