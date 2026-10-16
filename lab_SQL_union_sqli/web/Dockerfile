FROM php:8.2-apache

# Installazione pacchetti di utility e dipendenze per CTF
RUN apt-get update && apt-get install -y \
    curl procps netcat-traditional ncat gcc nano vim python3 python3-pip \
    openssh-server ltrace gdb net-tools iproute2 cron util-linux tar supervisor \
    && rm -rf /var/lib/apt/lists/*

# Abilitazione moduli PHP per il database
RUN docker-php-ext-install pdo pdo_mysql

# Configurazione SSH demone
RUN mkdir -p /var/run/sshd && sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config

# --------------------------------------------------
# Configurazione Utenti e Password
# --------------------------------------------------
# L'utente 'user' userà la password che si ottiene dal cracking del JWT
RUN useradd -m -s /bin/bash user && echo 'user:password123' | chpasswd

# L'utente root ha una password blindata: costringe a usare l'attacco di tar
RUN echo 'root:6a7caed3358066feea9b5ba3bbcf9432' | chpasswd

# Generazione flag dinamiche per il gioco
RUN openssl rand -hex 16 > /home/user/user.txt && chmod 600 /home/user/user.txt && chown user:user /home/user/user.txt
RUN openssl rand -hex 16 > /root/root.txt && chmod 600 /root/root.txt

# Dipendenze Flask e JWT per l'applicazione interna
RUN pip3 install --break-system-packages flask pyjwt

# Creazione API interna Flask vulnerabile (JWT non verificato)
RUN mkdir -p /opt/internal_admin
RUN cat > /opt/internal_admin/admin_app.py << '_EOF'
import jwt
from flask import Flask, request, jsonify

app = Flask(__name__)
SECRET_KEY = "super_secret_admin_key_2026"

@app.route('/api/auth/guest', methods=['GET'])
def guest_login():
    return jsonify({"token": jwt.encode({"user": "guest", "role": "viewer"}, SECRET_KEY, algorithm="HS256")})

@app.route('/api/admin/credentials', methods=['GET'])
def get_root_creds():
    auth_header = request.headers.get('Authorization')
    if not auth_header or not auth_header.startswith('Bearer '):
        return jsonify({"error": "Autenticazione richiesta"}), 401
    token = auth_header.split(" ")[1]
    try:
        # VULNERABILITÀ CRITICA: La firma non viene verificata
        payload = jwt.decode(token, options={"verify_signature": False})
        if payload.get("role") == "admin":
            return jsonify({
                "status": "Access Granted",
                "admin_md5_hash": "6a7caed3358066feea9b5ba3bbcf9432", 
                "note": "system admin hash"
            })
        return jsonify({"error": "Richiesto ruolo admin"}), 403
    except:
        return jsonify({"error": "Token non valido"}), 400

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=8080)
_EOF

# --------------------------------------------------
# Task di Cron per Movimento Laterale (Wildcard Tar Injection)
# --------------------------------------------------
RUN cat > /usr/local/bin/backup_cron.sh << '_EOF'
#!/bin/bash
cd /var/www/html/backup_tasks
/bin/tar -cf /home/user/backup.tar *
_EOF

# Il cronjob viene eseguito da 'user' per permettere il movimento laterale intermedio
RUN chmod 755 /usr/local/bin/backup_cron.sh \
    && echo '*/2 * * * * user /usr/local/bin/backup_cron.sh' > /etc/cron.d/ctf-union-cron \
    && chmod 644 /etc/cron.d/ctf-union-cron

# Configurazione di Supervisor per gestire l'API Flask in background
RUN mkdir -p /etc/supervisor/conf.d
RUN cat > /etc/supervisor/conf.d/internal_jwt_api.conf << '_EOF'
[program:internal_jwt_api]
command=python3 /opt/internal_admin/admin_app.py
user=root
autostart=true
autorestart=true
_EOF

# --------------------------------------------------
# Configurazione Apache (Risolve il Permission Denied sui file creati da MySQL)
# --------------------------------------------------
RUN echo "<Directory /var/www/html/uploads>\n\tOptions Indexes FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n</Directory>" >> /etc/apache2/apache2.conf

# Permette ad Apache di eseguire i file ereditando i privilegi di lettura corretti dal volume
RUN sed -i 's/Group ${APACHE_RUN_GROUP}/Group root/g' /etc/apache2/apache2.conf

# Copia e permessi dell'entrypoint dello scenario
COPY ctf-entrypoint.sh /usr/local/bin/ctf-entrypoint.sh
RUN chmod 755 /usr/local/bin/ctf-entrypoint.sh

EXPOSE 80 22
ENTRYPOINT ["/usr/local/bin/ctf-entrypoint.sh"]
