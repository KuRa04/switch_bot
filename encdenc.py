#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from Crypto.Cipher import AES
from Crypto.Hash import SHA256
from Crypto import Random
import base64

def create_aes(password, iv):
  sha = SHA256.new()
  sha.update(password.encode())
  key = sha.digest()
  return AES.new(key, AES.MODE_CFB, iv)

def encrypt(decrypted_data, password):
  iv = Random.new().read(AES.block_size)
  iv = iv + create_aes(password, iv).encrypt(decrypted_data.encode('utf-8'))
  return base64.b64encode(iv, altchars=b'-:')
 
def decrypt(encrypted_data, password):
  encrypted_data = base64.b64decode(encrypted_data, altchars=b'-:')
  iv, cipher = encrypted_data[:AES.block_size], encrypted_data[AES.block_size:]
  encoded = create_aes(password, iv).decrypt(cipher).decode('utf-8')
  return encoded


