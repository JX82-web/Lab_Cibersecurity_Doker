import hashlib
from flask import Flask, request, render_template_string

app = Flask(__name__)

PASSWORD_PLAIN = "colinasdeplata801"

LOGIN_HTML = """<!doctype html>
<html>
<head>
    <title>Internal Management</title>
    <style>
        body { font-family: monospace; background: #1a1a2e; color: #e2e2e2; padding: 50px; text-align: center; }
        .box { border: 1px solid #162447; padding: 30px; display: inline-block; background: #1f4068; border-radius: 5px; }
        input, button { display: block; margin: 10px auto; padding: 10px; background: #162447; color: #fff; border: 1px solid #0f4c75; font-family: monospace; }
        button { background: #e43f5a; cursor: pointer; font-weight: bold; }
        .err { color: #e43f5a; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Internal Admin Portal</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        {% if error %}<p class="err">{{ error }}</p>{% endif %}
    </div>
</body>
</html>
"""

CONSOLE_HTML = """<!doctype html>
<html>
<head>
    <title>Python Management Console</title>
    <style>
        body { font-family: monospace; background: #1b1b1b; color: #00ff00; padding: 30px; }
        .console { border: 1px solid #333; background: #000; padding: 20px; box-shadow: 0 0 10px #00ff00; }
        textarea { width: 100%; height: 150px; background: #111; color: #00ff00; border: 1px solid #333; font-family: monospace; font-size: 16px; padding: 10px; resize: none; }
        input[type="submit"] { background: #00ff00; color: #000; border: none; padding: 10px 20px; font-family: monospace; font-weight: bold; cursor: pointer; margin-top: 10px; }
        pre { background: #111; padding: 15px; border: 1px solid #222; white-space: pre-wrap; word-wrap: break-word; color: #ffffff; }
    </style>
</head>
<body>
    <div class="console">
        <h2>Interactive Python 3 Console (Root Context)</h2>
        <form method="POST" action="/console">
            <input type="hidden" name="token" value="{{ token }}">
            <textarea name="code" placeholder="print('Hello World')"></textarea>
            <input type="submit" value="Execute">
        </form>
        {% if output %}
        <h3>Execution Output:</h3>
        <pre>{{ output }}</pre>
        {% endif %}
    </div>
</body>
</html>
"""

def verify_token(username, password):
    if username != "richard":
        return False
    return password == PASSWORD_PLAIN

@app.route("/", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        username = request.form.get("username", "")
        password = request.form.get("password", "")
        if verify_token(username, password):
            token = hashlib.md5(f"{username}:{PASSWORD_PLAIN}".encode()).hexdigest()
            return render_template_string(CONSOLE_HTML, token=token, output="")
        return render_template_string(LOGIN_HTML, error="Invalid credentials")
    return render_template_string(LOGIN_HTML, error=None)

@app.route("/console", methods=["POST"])
def console():
    token = request.form.get("token", "")
    expected_token = hashlib.md5(b"richard:colinasdeplata801").hexdigest()
    
    if token != expected_token:
        return "Unauthorized", 401
        
    code = request.form.get("code", "")
    
    import sys
    import io
    
    old_stdout = sys.stdout
    old_stderr = sys.stderr
    redirected_output = sys.stdout = io.StringIO()
    redirected_error = sys.stderr = io.StringIO()
    
    try:
        exec(code, {'__builtins__': __builtins__}, {})
    except Exception as e:
        print(f"Error: {e}")
        
    output = redirected_output.getvalue() + redirected_error.getvalue()
    
    sys.stdout = old_stdout
    sys.stderr = old_stderr
    
    return render_template_string(CONSOLE_HTML, token=token, output=output)

if __name__ == "__main__":
    app.run(host="127.0.0.1", port=5000, debug=False)
