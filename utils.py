import time
import hashlib
import hmac
import base64
import uuid

def make_secret(secret_key):
    """Convert a secret key to bytes."""
    return bytes(secret_key, 'utf-8')

def make_sign(secret_key, token, t, nonce):
    """Create a HMAC SHA256 signature."""
    string_to_sign = '{}{}{}'.format(token, t, nonce)
    string_to_sign = bytes(string_to_sign, 'utf-8')
    sign = base64.b64encode(hmac.new(secret_key, msg=string_to_sign, digestmod=hashlib.sha256).digest())
    return sign

def make_t():
    """Generate the current time in milliseconds."""
    t = int(round(time.time() * 1000))
    return str(t)

def make_nonce():
    """Generate a unique nonce using UUID4."""
    return str(uuid.uuid4())
