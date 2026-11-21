
FROM php:8.1-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
	openssh-server \
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
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Creazione utente
RUN useradd -m -s /bin/bash jack && \
	echo "jack:3ashe2ba7eb" | chpasswd && \
	echo "root:Supercalifragili" | chpasswd

# Generazione file
RUN openssl rand -hex 16 > /home/jack/user.txt && \
    chown jack:jack /home/jack/user.txt && \
    chmod 600 /home/jack/user.txt && \
    openssl rand -hex 16 > /root/root.txt && \
    chmod 600 /root/root.txt

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
