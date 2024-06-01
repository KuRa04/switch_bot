#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import json
import sys
import cgi

from encdenc import decrypt
form = cgi.FieldStorage()


deviceid=""
param_enc=""
ctrl=""
password=""


if 'x' in form:
    param_enc = form['x'].value
    print(f'param_enc: {param_enc}')

if 'p' in form:
    password = form['p'].value
    # print(f'password: {password}')
if 'd' in form:
    deviceid = form['d'].value
    # print(f'deviceid: {deviceid}')

if not ( param_enc and password and deviceid ):
    print("Parameters is NOT enough")
    print(form)
    sys.exit(1)

token = ""

dec = decrypt(param_enc, password)

dec_json = json.loads(dec)
token = dec_json['token']
pickDevice = dec_json['pickDevice']


url="https://api.switch-bot.com/v1.1/devices/"

if not deviceid in pickDevice:
    print ("Content-type: plain/text; charset=UTF-8\n")
    print('Sorry, deviceid: {deviceid} is NOT accepted.')
    sys.exit(1)

print ("Content-type: application/json; char- set=UTF-8\n")

HEADERS = {
    'Authorization': token,
    'Content-Type': 'application/json; charset=utf8'
}

geturl="https://api.switch-bot.com/v1.1/devices/"+deviceid+"/status"

swres=requests.get(geturl, headers=HEADERS)

res=swres.json()
swproxy={}
swproxy['deviceid']=deviceid
res['swproxy']=swproxy

json_str = print(json.dumps(res, indent=2, ensure_ascii=False))
print(HttpResponse(json_str))



