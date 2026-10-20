const express = require('express');
const fs = require('fs');
const { exec } = require('child_process');
const path = require('path');
const app = express();

const PORT = 9000;
const HOST = '127.0.0.1'; // Ascolta ESCLUSIVAMENTE localmente per forzare il pivoting SSH

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.get('/', (req, res) => {
    res.send(`
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Internal System Script Editor</title>
    <!-- FIX: Caricamento corretto delle librerie e degli stili per l'IDE grafico CodeMirror -->
    <link rel="stylesheet" href="https://cloudflare.com">
    <link rel="stylesheet" href="https://cloudflare.com">
    <script src="https://cloudflare.com"></script>
    <script src="https://cloudflare.com"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #1e1e1e; color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #2d2d2d; padding: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        h2 { margin-top: 0; color: #007acc; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .auth-box { display: flex; gap: 15px; margin-bottom: 15px; background: #3c3c3c; padding: 15px; border-radius: 4px; align-items: center; }
        .auth-box input { background: #252526; border: 1px solid #555; color: #fff; padding: 6px; border-radius: 4px; }
        .CodeMirror { height: 380px; border-radius: 4px; font-size: 14px; border: 1px solid #444; }
        .btn { background-color: #0e639c; color: white; border: none; padding: 10px 20px; font-size: 15px; border-radius: 4px; cursor: pointer; margin-top: 15px; float: right; }
        #output { margin-top: 60px; background: #1e1e1e; border: 1px solid #444; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap; color: #a8ff60; display: none; }
    </style>
</head>
<body>
<div class="container">
    <h2>IDE Integrato di Manutenzione Core (Privilegi di Esecuzione: ROOT)</h2>
    <div class="auth-box">
        <label>Utente:</label><input type="text" id="username" value="iury">
        <label>Password:</label><input type="password" id="password" placeholder="Password di iury">
    </div>
    <textarea id="codeEditor">// IDE di Runtime di Sistema
const cp = require('child_process');
console.log('Eseguito da UID:', process.getuid());</textarea>
    <button class="btn" onclick="executeScript()">Esegui Script nel Core</button>
    <div id="output"></div>
</div>
<script>
    // Inizializzazione corretta dell'editor grafico con il tema dark Dracula
    var editor = CodeMirror.fromTextArea(document.getElementById("codeEditor"), {
        lineNumbers: true, mode: "javascript", theme: "dracula", tabSize: 4
    });
    function executeScript() {
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const code = editor.getValue();
        const out = document.getElementById('output');
        if(!password) { alert("Autenticazione richiesta!"); return; }
        out.style.display = "block"; out.innerHTML = "In esecuzione..."; out.style.color = "#f4f4f4";
        fetch('/run', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password, code })
        }).then(res => res.text()).then(data => { out.innerHTML = data; });
    }
</script>
</body>
</html>
    `);
});

app.post('/run', (req, res) => {
    const { username, password, code } = req.body;
    if (username !== 'iury' || password !== 'johnsucks1') {
        return res.status(401).send('[-] Autenticazione fallita per il contesto di root.');
    }
    const scriptPath = path.join(__dirname, 'executed_admin_script.js');
    fs.writeFile(scriptPath, code, (err) => {
        if (err) return res.status(500).send('Errore di IO.');
        exec(`node ${scriptPath}`, (error, stdout, stderr) => {
            try { fs.unlinkSync(scriptPath); } catch(e){}
            if (error) {
                res.send(`⚠️ Errore:\n${stderr || error.message}`);
            } else {
                res.send(`✅ Successo:\n${stdout}`);
            }
        });
    });
});

app.listen(PORT, HOST, () => {
    console.log(`[+] Server interno attivo su http://${HOST}:${PORT}`);
});
