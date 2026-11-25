FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    default-mysql-server \
    default-mysql-client \
    openssh-server \
    python3 \
    python3-pip \
    nodejs \
    npm \
    nano \
    vim \
    netcat-openbsd \
    openssl \
    iputils-ping \
    jq \
    sed \
    gawk \
    net-tools \
    iproute2 \
    curl \
    wget \
    socat \
    procps \
    lsof \
    util-linux \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN mkdir -p /var/run/sshd

RUN useradd -m -s /bin/bash robert && \
    echo 'robert:carmelo15' | chpasswd

COPY web/ssh/sshd_config /etc/ssh/sshd_config

RUN ssh-keygen -A

COPY web/src/ /var/www/html/

RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads

COPY database/init.sql /database/init.sql

COPY web/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

EXPOSE 80
EXPOSE 22

ENTRYPOINT ["/entrypoint.sh"]
