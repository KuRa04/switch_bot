#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import cgi
import cgitb
import sys
from encdenc import encrypt
import json

cgitb.enable()

def print_cors_headers():
    print("Access-Control-Allow-Origin: http://localhost:8000")  # 特定のオリジンを許可
    print("Access-Control-Allow-Methods: POST, GET, OPTIONS")
    print("Access-Control-Allow-Headers: X-Requested-With, Content-Type")

def handle_options():
    print("Content-Type: text/plain")
    print_cors_headers()
    print()
    return

def handle_request():
    form = cgi.FieldStorage()

    print("Content-Type: application/json; charset=UTF-8")
    print_cors_headers()
    print()

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
        print(json.dumps(ret))
        sys.exit(1)

    data = '{"token":"'+token+'","pickDevice":['+devices+']}'
    ret["data"]=data

    enc = encrypt(data, password).decode('utf-8')
    ret["enc"]=enc

    ret["result"]="ok"
    print(json.dumps(ret))

if __name__ == '__main__':
    if os.environ['REQUEST_METHOD'] == 'OPTIONS':
        handle_options()
    else:
        handle_request()
