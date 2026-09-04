#!/usr/bin/env python3
"""
Minimal fake "cloud instance metadata" endpoint, styled after the AWS EC2 IMDSv1 shape.
The credentials below are fake and only ever exist inside this container's Docker-internal
network.
"""
import http.server
import json

FAKE_ACCESS_KEY = "AKIAFAKERAMPARTDEMO"
FAKE_SECRET_KEY = "fakeSecretRampartWorkshopDoNotUseThisIsNotReal1234"

ROLE_NAME = "rampart-app-role"

ROUTES = {
    "/": "latest/\n",
    "/latest/": "meta-data/\n",
    "/latest/meta-data/": "iam/\ninstance-id\nlocal-hostname\n",
    "/latest/meta-data/instance-id": "i-0fakeinstance00000\n",
    "/latest/meta-data/local-hostname": "rampart-app.internal\n",
    "/latest/meta-data/iam/": "security-credentials/\n",
    "/latest/meta-data/iam/security-credentials/": f"{ROLE_NAME}\n",
    f"/latest/meta-data/iam/security-credentials/{ROLE_NAME}": json.dumps({
        "Code": "Success",
        "Type": "AWS-HMAC",
        "AccessKeyId": FAKE_ACCESS_KEY,
        "SecretAccessKey": FAKE_SECRET_KEY,
        "Token": "FAKE-SESSION-TOKEN-FOR-WORKSHOP-DEMO-ONLY",
        "Expiration": "2099-01-01T00:00:00Z",
    }, indent=2) + "\n",
}


class Handler(http.server.BaseHTTPRequestHandler):
    def do_GET(self):
        body = ROUTES.get(self.path)

        if body is None:
            self.send_response(404)
            self.send_header("Content-Type", "text/plain")
            self.end_headers()
            self.wfile.write(b"404 not found\n")
            return

        self.send_response(200)
        self.send_header("Content-Type", "text/plain")
        self.end_headers()
        self.wfile.write(body.encode())

    def log_message(self, format, *args):
        pass


if __name__ == "__main__":
    http.server.HTTPServer(("0.0.0.0", 80), Handler).serve_forever()
