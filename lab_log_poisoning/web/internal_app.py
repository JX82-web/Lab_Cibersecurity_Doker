import os
import sys
import io
from flask import Flask, render_template_string, request, redirect, make_response, jsonify

app = Flask(__name__)
app.config['SECRET_KEY'] = 'super_secure_debug_key_2026'

# Credenziali allineate al tuo Dockerfile
VALID_USER = "michael"
VALID_PASSWORD = "endlesslove"

HTML_TEMPLATE = """
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Internal Debug Console</title>
    <style>
        body { font-family: 'Courier New', monospace; background-color: #121212; color: #00ff00; padding: 20px; }
        .container { max-width: 600px; margin: 50px auto; background: #1e1e1e; padding: 20px; border-radius: 5px; border: 1px solid #333; }
        input[type="text"], input[type="password"] { width: 95%; padding: 10px; margin: 10px 0; background: #2a2a2a; border: 1px solid #444; color: #00ff00; }
        button { padding: 10px 20px; background: #00ff00; color: #000; border: none; cursor: pointer; font-weight: bold; }
        #console { background: #000; border: 1px solid #333; padding: 15px; height: 350px; overflow-y: scroll; margin-bottom: 10px; white-space: pre-wrap; }
        .input-line { display: flex; }
        .prompt { color: #ff00ff; font-weight: bold; margin-right: 5px; }
        #cmd { background: transparent; border: none; color: #00ff00; font-family: inherit; font-size: inherit; flex-grow: 1; outline: none; }
        .error { color: #ff3333; }
    </style>
</head>
<body>

    {% if not logged_in %}
    <!-- INTERFACCIA DI LOGIN -->
    <div class="container">
        <h2>🔒 Autenticazione Richiesta</h2>
        {% if error %} <p class="error">{{ error }}</p> {% endif %}
        <form method="POST" action="/login">
            <label>Username:</label><br>
            <input type="text" name="username" required autocomplete="off"><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br>
            <button type="submit">Accedi</button>
        </form>
    </div>
    {% else %}
    <!-- INTERFACCIA CONSOLE INTERATTIVA -->
    <div class="container" style="max-width: 800px;">
        <h2>🐍 Python 3.13 Interactive IDLE (Web Mode)</h2>
        <p>Benvenuto Michael. Digita i comandi Python e premi Invio.</p>
        <div id="console">Benvenuto nell'IDLE interna.&#10;&gt;&gt;&gt; </div>
        <div class="input-line">
            <span class="prompt">&gt;&gt;&gt;</span>
            <input type="text" id="cmd" autofocus autocomplete="off">
        </div>
        <br>
        <a href="/logout" style="color: #ff3333; text-decoration: none;">[ Logout ]</a>
    </div>

    <script>
        var consoleDiv = document.getElementById('console');
        var inputCmd = document.getElementById('cmd');

        inputCmd.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                var command = inputCmd.value;
                if(command.trim() !== "") {
                    // Mostra immediatamente il comando digitato nella console web
                    consoleDiv.innerHTML += command + "\\n";
                    
                    // Invia il comando al backend tramite una normale richiesta HTTP POST (AJAX)
                    fetch('/execute', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ cmd: command })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Aggiunge l'output restituito da Python alla console web
                        consoleDiv.innerHTML += data.output;
                        consoleDiv.scrollTop = consoleDiv.scrollHeight;
                    })
                    .catch(err => {
                        consoleDiv.innerHTML += "Errore di connessione col server.\\n&gt;&gt;&gt; ";
                    });
                }
                inputCmd.value = "";
                consoleDiv.scrollTop = consoleDiv.scrollHeight;
            }
        });
    </script>
    {% endif %}

</body>
</html>
"""

# Dizionario per mantenere persistenti le variabili create dall'utente durante la sessione
interactive_locals = {"__name__": "__main__", "__doc__": None}

@app.route('/')
def index():
    auth_cookie = request.cookies.get('authenticated')
    if auth_cookie == 'true':
        return render_template_string(HTML_TEMPLATE, logged_in=True)
    return render_template_string(HTML_TEMPLATE, logged_in=False)

@app.route('/login', methods=['POST'])
def login():
    username = request.form.get('username')
    password = request.form.get('password')

    if username == VALID_USER and password == VALID_PASSWORD:
        response = make_response(redirect('/'))
        response.set_cookie('authenticated', 'true', httponly=True)
        return response
    return render_template_string(HTML_TEMPLATE, logged_in=False, error="Credenziali errate!")

@app.route('/logout')
def logout():
    response = make_response(redirect('/'))
    response.delete_cookie('authenticated')
    return response

# Gestore dell'esecuzione dei comandi tramite HTTP POST standard
@app.route('/execute', methods=['POST'])
def execute():
    auth_cookie = request.cookies.get('authenticated')
    if auth_cookie != 'true':
        return jsonify({'output': "Sessione non valida. Effettua il login.\\n>>> "})

    data = request.get_json() or {}
    command = data.get('cmd', '')

    # Cattura gli output inviati a stdout/stderr
    old_stdout = sys.stdout
    old_stderr = sys.stderr
    redirected_output = io.StringIO()
    sys.stdout = redirected_output
    sys.stderr = redirected_output

    try:
        # Tenta prima la valutazione come espressione singola (eval) per mostrare i risultati immediati
        try:
            compiled_code = compile(command, '<web_backend>', 'eval')
            result = eval(compiled_code, interactive_locals)
            if result is not None:
                print(repr(result))
        except SyntaxError:
            # Se è un'istruzione complessa (es. assegnamento o import), usa exec
            compiled_code = compile(command, '<web_backend>', 'exec')
            exec(compiled_code, interactive_locals)
    except Exception as e:
        print(f"Errore: {e}")

    # Ripristina i canali standard di sistema
    sys.stdout = old_stdout
    sys.stderr = old_stderr

    # Estrae l'output generato dall'esecuzione del codice
    output_text = redirected_output.getvalue()
    return jsonify({'output': output_text + "&gt;&gt;&gt; "})

if __name__ == '__main__':
    # Avvia l'applicazione usando il server standard Flask (Werkzeug) sulla porta 8888
    app.run(host='127.0.0.1', port=8888, debug=False)
