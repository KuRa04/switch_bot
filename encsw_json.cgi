#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import json
import sys
import cgi

from encdenc import encrypt

form = cgi.FieldStorage()

print('Content-type: application/json; charset=UTF-8')
print('')

ret = {}

if 'd' in form:
    devices = form['d'].value
    ret["d"] = devices

if 'p' in form:
    password = form['p'].value
    ret["p"] = password

if 't' in form:
    token = form['t'].value
    ret["t"] = token

if not set(['d','p','t']) <= set(form):
    ret["result"]="not enough param error"
    print(ret)
    sys.exit(1)

data = '{"token":"'+token+'","pickDevice":['+devices+']}'
ret["data"]=data

enc = encrypt(data, password).decode('utf-8')
ret["enc"]=enc

ret["result"]="ok"
print(json.dumps(ret))
