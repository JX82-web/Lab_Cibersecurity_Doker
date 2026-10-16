FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    openssh-server \
    python3 \
    python3-flask \
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

RUN useradd -m -s /bin/bash richard && echo 'richard:colinasdeplata801' | chpasswd

COPY ssh/sshd_config /etc/ssh/sshd_config
RUN ssh-keygen -A

COPY src/ /var/www/html/
COPY internal_web/ /web/internal_web/

COPY entrypoint.sh /entrypoint.sh
RUN chmod +755 /entrypoint.sh

EXPOSE 80
EXPOSE 2224

ENTRYPOINT ["/entrypoint.sh"]
