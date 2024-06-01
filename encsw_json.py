from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import base64
import json

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

class RequestData(BaseModel):
    d: str
    p: str
    t: str

def encrypt(data: str, password: str) -> str:
    # ダミーの暗号化関数
    return base64.b64encode(data.encode('utf-8')).decode('utf-8')

@app.post("/api/encode_token")
async def encode_token(data: RequestData):
    ret = {}

    # デバッグ用ログ
    print(f"Received data: {data}")

    if not data.d or not data.p or not data.t:
        ret["result"] = "not enough param error"
        return ret

    ret["d"] = data.d
    ret["p"] = data.p
    ret["t"] = data.t

    json_data = json.dumps({"token": data.t, "pickDevice": data.d.split(",")})
    ret["data"] = json_data

    enc = encrypt(json_data, data.p)
    ret["enc"] = enc

    ret["result"] = "ok"
    return ret
