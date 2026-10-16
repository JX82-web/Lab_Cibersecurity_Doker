FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    openssh-server \
    sudo \
    gcc \
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
    && docker-php-ext-install mysqli \
    && rm -rf /var/lib/apt/lists/*

RUN useradd -m -s /bin/bash michael && \
    echo 'michael:Migration_Archive_2026!' | chpasswd

RUN mkdir -p /run/sshd /var/www/html/uploads

COPY src/ /var/www/html/
COPY ssh/sshd_config /etc/ssh/sshd_config
COPY bin/backup-check.c /tmp/backup-check.c

RUN gcc /tmp/backup-check.c -O2 -o /usr/local/bin/backup-check && \
    chown root:root /usr/local/bin/backup-check && \
    chmod 4755 /usr/local/bin/backup-check && \
    rm /tmp/backup-check.c

RUN openssl rand -hex 16 > /home/michael/user.txt && \
    chown michael:michael /home/michael/user.txt && \
    chmod 600 /home/michael/user.txt

RUN openssl rand -hex 16 > /root/root.txt && \
    chown root:root /root/root.txt && \
    chmod 600 /root/root.txt

RUN chown -R www-data:www-data /var/www/html && \
    chmod 755 /var/www/html && \
    chmod 755 /var/www/html/uploads

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80 22

CMD ["/entrypoint.sh"]
