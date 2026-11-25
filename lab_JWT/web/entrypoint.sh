#!/bin/bash

set -e

echo "[+] Configurazione MySQL..."

# Assicura che la directory runtime esista
mkdir -p /run/mysqld
chown mysql:mysql /run/mysqld

# MySQL/MariaDB deve ascoltare SOLO su localhost
MYSQL_CONFIG="/etc/mysql/mariadb.conf.d/50-server.cnf"

if [ -f "$MYSQL_CONFIG" ]; then
    sed -i 's/^bind-address.*/bind-address = 127.0.0.1/' "$MYSQL_CONFIG"
else
    MYSQL_CONFIG="/etc/mysql/mysql.conf.d/mysqld.cnf"

    if [ -f "$MYSQL_CONFIG" ]; then
        sed -i 's/^bind-address.*/bind-address = 127.0.0.1/' "$MYSQL_CONFIG"
    fi
fi

echo "[+] Avvio MySQL..."

service mariadb start 2>/dev/null || service mysql start

echo "[+] Attendo MySQL..."

for i in {1..30}; do
    if mysqladmin ping --silent >/dev/null 2>&1; then
        break
    fi

    sleep 1
done

if ! mysqladmin ping --silent >/dev/null 2>&1; then
    echo "[!] MySQL non si è avviato."
    exit 1
fi

echo "[+] MySQL attivo."

# Password root
MYSQL_ROOT_PASSWORD="cybersecurity_pass_2026"

# Inizializzazione database solo la prima volta
if [ ! -f /var/lib/mysql/.lab_jwt_initialized ]; then

    echo "[+] Inizializzazione database del lab..."

    # Importa database e tabelle
    mysql -u root < /database/init.sql

    # Imposta la password per root
    mysql -u root <<SQL
ALTER USER 'root'@'localhost'
IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';

CREATE USER IF NOT EXISTS 'root'@'127.0.0.1'
IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';

GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1'
WITH GRANT OPTION;

FLUSH PRIVILEGES;
SQL

    touch /var/lib/mysql/.lab_jwt_initialized

    echo "[+] Database inizializzato."

else

    echo "[+] Database già inizializzato."

fi

# Generazione flag user.txt per l'utente robert
mkdir -p /home/robert

USER_FLAG=$(openssl rand -hex 16)

echo "$USER_FLAG" > /home/robert/user.txt

chown robert:robert /home/robert/user.txt
chmod 600 /home/robert/user.txt

# Generazione flag root.txt per root
ROOT_FLAG=$(openssl rand -hex 16)

echo "$ROOT_FLAG" > /root/root.txt

chmod 600 /root/root.txt

# ============================================================
# INTERNAL ROOT NODE.JS EDITOR
# ============================================================

mkdir -p /opt/internal_service

cat << 'EOF' > /opt/internal_service/app.js
const http = require('http');
const crypto = require('crypto');
const fs = require('fs');
const { execFile } = require('child_process');

const HOST = '127.0.0.1';
const PORT = 5555;
const ROBERT_PASSWORD = 'carmelo15';
const sessions = new Map();

const html = `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Northbridge JS Editor</title>
<style>
body{font-family:Arial,sans-serif;background:#111;color:#eee;margin:0}
.wrap{max-width:1100px;margin:40px auto;padding:24px}
.panel{background:#1b1b1b;border:1px solid #333;border-radius:8px;padding:20px}
input,textarea,button{box-sizing:border-box;width:100%;margin-top:8px;padding:10px;border-radius:5px;border:1px solid #444;background:#0d0d0d;color:#eee}
textarea{min-height:360px;font-family:monospace;resize:vertical}
button{background:#2d6cdf;border:0;cursor:pointer}
button:hover{background:#3b78e7}
pre{white-space:pre-wrap;background:#050505;border:1px solid #333;padding:15px;min-height:120px}
.hidden{display:none}.row{display:flex;gap:10px}.row>*{flex:1}
.small{color:#aaa;font-size:.9rem}
</style>
</head>
<body>
<div class="wrap">
<div class="panel" id="login">
<h1>Northbridge Internal JS Editor</h1>
<p class="small">Internal service — 127.0.0.1:5555</p>
<form id="loginForm">
<label>Username</label><input id="username" autocomplete="username" required>
<label>Password</label><input id="password" type="password" autocomplete="current-password" required>
<button type="submit">Sign in</button>
</form><pre id="loginOut"></pre>
</div>
<div class="panel hidden" id="editor">
<div class="row"><div><h1>JavaScript Editor</h1><p class="small">Authenticated as robert</p></div><div><button id="logout">Log out</button></div></div>
<textarea id="code">console.log('Hello from the internal Node.js editor');</textarea>
<button id="run">Run JavaScript</button>
<h2>Output</h2><pre id="output"></pre>
</div>
</div>
<script>
const login=document.getElementById('login'), editor=document.getElementById('editor');
async function check(){const r=await fetch('/api/session'); const d=await r.json(); login.classList.toggle('hidden',d.authenticated); editor.classList.toggle('hidden',!d.authenticated);}
loginForm.onsubmit=async e=>{e.preventDefault();const r=await fetch('/login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:username.value,password:password.value})});const d=await r.json();loginOut.textContent=d.message||'';if(d.ok) check();};
run.onclick=async()=>{output.textContent='Running...';const r=await fetch('/api/execute',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({code:code.value})});const d=await r.json();output.textContent=d.output||d.error||'';if(r.status===401) check();};
logout.onclick=async()=>{await fetch('/logout',{method:'POST'});check();};
check();
</script>
</body></html>`;

function cookieToken(req){
  const m=(req.headers.cookie||'').match(/(?:^|; )sid=([^;]+)/);
  return m ? decodeURIComponent(m[1]) : '';
}
function authenticated(req){
  const token=cookieToken(req);
  return token && sessions.has(token);
}
function json(res,status,obj){
  res.writeHead(status,{'Content-Type':'application/json','Cache-Control':'no-store'});
  res.end(JSON.stringify(obj));
}
function readBody(req){
  return new Promise((resolve,reject)=>{
    let body='';
    req.on('data',c=>{body+=c.toString();if(body.length>1024*1024) req.destroy();});
    req.on('end',()=>resolve(body));
    req.on('error',reject);
  });
}
function runAsRoot(code){
  return new Promise((resolve)=>{
    const file='/tmp/js_editor_'+crypto.randomBytes(12).toString('hex')+'.js';
    try { fs.writeFileSync(file,code,{mode:0o600}); }
    catch(e){ resolve(String(e)); return; }
    execFile('node',[file],{timeout:10000,maxBuffer:1024*1024},(error,stdout,stderr)=>{
      try { fs.unlinkSync(file); } catch(e) {}
      let out=(stdout||'')+(stderr||'');
      if(error && !out) out=error.message;
      resolve(out);
    });
  });
}

const server=http.createServer(async (req,res)=>{
  try {
    if(req.method==='GET' && req.url==='/'){
      res.writeHead(200,{'Content-Type':'text/html; charset=utf-8','Cache-Control':'no-store'});res.end(html);return;
    }
    if(req.method==='POST' && req.url==='/login'){
      const data=JSON.parse(await readBody(req)||'{}');
      if(data.username==='robert' && data.password===ROBERT_PASSWORD){
        const token=crypto.randomBytes(32).toString('hex');
        sessions.set(token,{username:'robert',created:Date.now()});
        res.writeHead(200,{'Content-Type':'application/json','Set-Cookie':`sid=${token}; HttpOnly; SameSite=Strict; Path=/`});
        res.end(JSON.stringify({ok:true,message:'Authenticated'}));return;
      }
      json(res,401,{ok:false,message:'Invalid credentials'});return;
    }
    if(req.method==='POST' && req.url==='/logout'){
      const token=cookieToken(req); if(token) sessions.delete(token);
      res.writeHead(200,{'Content-Type':'application/json','Set-Cookie':'sid=; Max-Age=0; HttpOnly; SameSite=Strict; Path=/'});res.end(JSON.stringify({ok:true}));return;
    }
    if(req.method==='GET' && req.url==='/api/session'){
      json(res,200,{authenticated:!!authenticated(req)});return;
    }
    if(req.method==='POST' && req.url==='/api/execute'){
      if(!authenticated(req)){json(res,401,{error:'Authentication required'});return;}
      const data=JSON.parse(await readBody(req)||'{}');
      const code=String(data.code||'');
      if(code.length>200000){json(res,413,{error:'Code too large'});return;}
      const output=await runAsRoot(code);
      json(res,200,{output});return;
    }
    res.writeHead(404,{'Content-Type':'text/plain'});res.end('Not found\n');
  } catch(e) { json(res,400,{error:e.message}); }
});

server.listen(PORT,HOST,()=>console.log(`[*] Internal root Node.js editor listening on http://${HOST}:${PORT}`));
EOF

chown -R root:root /opt/internal_service
chmod 755 /opt/internal_service
chmod 644 /opt/internal_service/app.js

# Node.js is intentionally started by this root entrypoint and remains root.
echo "[+] Avvio editor Node.js interno privilegiato..."
nohup node /opt/internal_service/app.js > /var/log/internal_app.log 2>&1 &

# ============================================================
# SSH
# ============================================================

echo "[+] Avvio SSH..."

service ssh start

# ============================================================
# APACHE
# ============================================================

echo "[+] Avvio Apache..."

apache2-foreground
