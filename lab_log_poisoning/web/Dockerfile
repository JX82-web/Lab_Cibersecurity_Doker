FROM php:8.1-apache

# Installazione dei tool di sistema, Python3 e MariaDB Server
RUN apt-get update && apt-get install -y \
    openssh-server \
    rsyslog \
    net-tools \
    iproute2 \
    mariadb-server \
    mariadb-client \
    netcat-traditional \
    wget \
    curl \
    python3 \
    python3-pip \    
    sudo \
    psmisc \
    gcc \
    nano \
    vim \
    && rm -rf /var/lib/apt/lists/*

# Installazione estensioni PHP per la connessione al database
RUN docker-php-ext-install pdo pdo_mysql

# Configurazione del database per ascoltare SOLO su 127.0.0.1
RUN sed -i 's/^bind-address.*/bind-address = 127.0.0.1/' /etc/mysql/mariadb.conf.d/50-server.cnf

# Copia dei file dell'applicazione e del database nel container
COPY internal_app.py /opt/internal_app.py
COPY init.sql /tmp/init.sql

# Configurazione del demone SSH
RUN mkdir -p /var/run/sshd
RUN grep -q '^#\?PermitRootLogin' /etc/ssh/sshd_config \
    && sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin yes/' /etc/ssh/sshd_config \
    || echo 'PermitRootLogin yes' >> /etc/ssh/sshd_config
RUN grep -q '^#\?PasswordAuthentication' /etc/ssh/sshd_config \
    && sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication yes/' /etc/ssh/sshd_config \
    || echo 'PasswordAuthentication yes' >> /etc/ssh/sshd_config

# Creazione dell'utente di sistema michael
RUN useradd -m -s /bin/bash -u 1001 michael
RUN echo 'michael:endlesslove' | chpasswd

# Creation Flag user.txt and root.txt
RUN openssl rand -hex 16 > /home/michael/user.txt && \
    chmod 600 /home/michael/user.txt && \
    chown michael:michael /home/michael/user.txt
RUN openssl rand -hex 16 > /root/root.txt && \
    chown root:root /root/root.txt && \
    chmod 600 /root/root.txt

# Rimuove eventuali vecchi entrypoint ereditati per non andare in conflitto
ENTRYPOINT []

# Generazione dello script di entrypoint modificato per salvare i log di python
RUN echo '#!/bin/bash\n\
set +e\n\
\n\
mkdir -p /run/mysqld\n\
chown mysql:mysql /run/mysqld\n\
\n\
if [ ! -d /var/lib/mysql/mysql ]; then\n\
  if command -v mariadb-install-db >/dev/null 2>&1; then\n\
    mariadb-install-db --auth-root-authentication-method=normal --user=mysql --datadir=/var/lib/mysql >/tmp/mariadb-install.log 2>&1\n\
  else\n\
    mysql_install_db --auth-root-authentication-method=normal --user=mysql --datadir=/var/lib/mysql >/tmp/mariadb-install.log 2>&1\n\
  fi\n\
fi\n\
\n\
mysqld_safe --datadir=/var/lib/mysql >/tmp/mariadb-run.log 2>&1 &\n\
\n\
echo "In attesa che MariaDB sia pronto..."\n\
DB_READY=0\n\
for i in $(seq 1 90); do\n\
  if mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null; then\n\
    DB_READY=1\n\
    break\n\
  fi\n\
  sleep 1\n\
done\n\
\n\
if [ "$DB_READY" = "1" ]; then\n\
  echo "MariaDB pronta dopo ${i}s, inizializzo il database..."\n\
  mysql -e "CREATE DATABASE IF NOT EXISTS ecommerce;" >>/tmp/mariadb-init.log 2>&1\n\
  mysql ecommerce < /tmp/init.sql >>/tmp/mariadb-init.log 2>&1\n\
  mysql -e "CREATE USER IF NOT EXISTS '"'"'root'"'"'@'"'"'127.0.0.1'"'"' IDENTIFIED BY '"'"'SuperSecureRootPassword2026!'"'"';" >>/tmp/mariadb-init.log 2>&1\n\
  mysql -e "ALTER USER '"'"'root'"'"'@'"'"'127.0.0.1'"'"' IDENTIFIED BY '"'"'SuperSecureRootPassword2026!'"'"';" >>/tmp/mariadb-init.log 2>&1\n\
  mysql -e "GRANT ALL PRIVILEGES ON ecommerce.* TO '"'"'root'"'"'@'"'"'127.0.0.1'"'"';" >>/tmp/mariadb-init.log 2>&1\n\
  mysql -e "ALTER USER '"'"'root'"'"'@'"'"'localhost'"'"' IDENTIFIED BY '"'"'SuperSecureRootPassword2026!'"'"';" >>/tmp/mariadb-init.log 2>&1\n\
  mysql -e "FLUSH PRIVILEGES;" >>/tmp/mariadb-init.log 2>&1\n\
  echo "Inizializzazione completata, vedi /tmp/mariadb-init.log per i dettagli."\n\
else\n\
  echo "ATTENZIONE: MariaDB non ha risposto al ping entro 90s. Controlla /tmp/mariadb-run.log dentro il container." >&2\n\
fi\n\
\n\
rsyslogd\n\
service ssh start\n\
\n\
rm -f /var/log/apache2/access.log /var/log/apache2/error.log /var/log/apache2/other_vhosts_access.log\n\
touch /var/log/auth.log /var/log/apache2/access.log /var/log/apache2/error.log\n\
chown www-data:www-data /var/log/auth.log /var/log/apache2/access.log /var/log/apache2/error.log\n\
chmod 666 /var/log/auth.log /var/log/apache2/access.log /var/log/apache2/error.log\n\
\n\
# MODIFICA: Reindirizziamo gli errori di Python su un file di log per poter fare debug se crasha\n\
PYTHONUNBUFFERED=1 python3 /opt/internal_app.py >/tmp/py_app.log 2>&1 &\n\
\n\
exec apache2-foreground' > /usr/local/bin/entrypoint.sh

# MODIFICA: Aggiunto eventlet per supportare WebSocket stabili e asincroni con Flask-SocketIO
RUN pip install flask flask-socketio eventlet --break-system-packages

RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
