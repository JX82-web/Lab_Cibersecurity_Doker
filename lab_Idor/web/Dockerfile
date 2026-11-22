FROM php:8.1-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
	openssh-server \
    libzip-dev \
    cron \
    sudo \
    default-mysql-client \
    vim \
    nano \
    netcat-openbsd \
    iproute2 \
    iputils-ping \
    net-tools \
    procps \
    psmisc \
    lsof \
    curl \
    wget \
    jq \
    unzip \
    zip \
    unzip \
    gcc \
    python3 \
    libcap2-bin \
    dnsutils \
    socat \
    less \
    file \
    findutils \
    sed \
    grep \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Creazione utente
RUN useradd -m -s /bin/bash jeffry && \
	echo "jeffry:daddysgirl" | chpasswd && \
	echo "root:Supercalifragili" | chpasswd

# Generazione file
RUN openssl rand -hex 16 > /home/jeffry/user.txt && \
    chown jeffry:jeffry /home/jeffry/user.txt && \
    chmod 600 /home/jeffry/user.txt && \
    openssl rand -hex 16 > /root/root.txt && \
    chmod 600 /root/root.txt

# Configura Apache per servire src/ e mappare ../assets/
RUN sed -i 's|/var/www/html|/var/www/html/src|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/src|g' /etc/apache2/apache2.conf \
    && echo "Alias /assets /var/www/html/assets" >> /etc/apache2/sites-available/000-default.conf

# Copia file web e imposta permessi per Zip-Upload
COPY src/ /var/www/html/src/
COPY assets/ /var/www/html/assets/
RUN mkdir -p /var/www/html/src/admin/uploads \
    && chown -R www-data:www-data /var/www/html/src/admin/uploads \
    && chmod -R 775 /var/www/html/src/admin/uploads


# Script Cron
COPY check_cron.sh /usr/local/bin/check_cron.sh
RUN chmod 755 /usr/local/bin/check_cron.sh

# Configurazione Cron
COPY crontab /etc/cron.d/pwn
RUN chmod 0644 /etc/cron.d/pwn

# EntryPoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod 755 /usr/local/bin/entrypoint.sh


# Prepare ssh
COPY sshd_config /etc/ssh/sshd_config
RUN mkdir -p /var/run/sshd && \
	ssh-keygen -A

EXPOSE 80 22

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
