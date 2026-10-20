FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    openssh-server \
    sudo \
    gcc \
    libzip-dev \
    zip \
    libc6-dev \
    procps \
    iproute2 \
    iputils-ping \
    net-tools \
    curl \
    wget \
    vim-tiny \
    nano \
    openssl \
    nodejs \
    npm \
    && docker-php-ext-install mysqli zip \
    && rm -rf /var/lib/apt/lists/*

# Configurazione SSH
RUN mkdir -p /var/run/sshd
COPY ssh/sshd_config /etc/ssh/sshd_config

# Creazione Utente e impostazione password di root
RUN useradd -m -s /bin/bash iury && \
    echo 'iury:johnsucks1' | chpasswd && \
    echo 'root:lovesucks1' | chpasswd

# Creazione Flag user e root.txt
RUN openssl rand -hex 16 > /home/iury/user.txt && \
    chown iury:iury /home/iury/user.txt && \
    chmod 600 /home/iury/user.txt
RUN openssl rand -hex 16 > /root/root.txt && \
    chown root:root /root/root.txt && \
    chmod 600 /root/root.txt

# Copia sorgenti (Corretti i percorsi basati sul contesto della cartella web/)
COPY app/ /var/www/html/
COPY app_internal/ /var/www/html/app_internal/

# Configurazione permessi e cartelle di runtime
RUN mkdir -p /var/www/html/uploads /var/www/html/plugin && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80 22

CMD ["/entrypoint.sh"]
