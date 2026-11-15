FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
    openssh-server sudo vim nano netcat-traditional netcat-openbsd \
    iproute2 iputils-ping net-tools procps psmisc lsof curl wget \
    jq unzip zip git gcc make python3 ca-certificates libcap2-bin \
    dnsutils socat less file findutils gawk sed grep \
    && docker-php-ext-install mysqli \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

# SSH / lateral-movement account
RUN groupadd -f jack && useradd -m -s /bin/bash -g jack jack \
    && echo 'jack:J4ck-Lab-2026!' | chpasswd \
    && mkdir -p /run/sshd /home/jack/tools /opt/lab/chroot \
    && chown -R jack:jack /home/jack /opt/lab/chroot \
    && chmod 700 /home/jack

# Lab marker and user flag
RUN printf 'USER_FLAG_%s\n' "$(head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n')" > /home/jack/user.txt \
    && chown jack:jack /home/jack/user.txt && chmod 600 /home/jack/user.txt

COPY src/ /var/www/html/
COPY assets/ /var/www/html/assets/
COPY ssh/sshd_config /etc/ssh/sshd_config
COPY privesc/build-vuln-coreutils.sh /usr/local/sbin/build-vuln-coreutils.sh
COPY privesc/nss-demo.c /opt/lab/nss-demo.c
COPY privesc/lab-suid-wrapper.sh /usr/local/sbin/lab-suid-wrapper.sh
COPY entrypoint.sh /entrypoint.sh

RUN chmod 755 /entrypoint.sh /usr/local/sbin/build-vuln-coreutils.sh /usr/local/sbin/lab-suid-wrapper.sh \
    && chmod 644 /opt/lab/nss-demo.c \
    && chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/uploads \
    && chown www-data:www-data /var/www/html/uploads \
    && chmod 750 /var/www/html/uploads

# Install the vulnerable uutils coreutils release during image build.
# The setup script downloads/builds version 0.8.0 and installs only chroot.
RUN /usr/local/sbin/build-vuln-coreutils.sh

# Root flag
RUN mkdir -p /root && printf 'ROOT_FLAG_%s\n' "$(head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n')" > /root/root.txt && chmod 600 /root/root.txt

EXPOSE 80 22
CMD ["/entrypoint.sh"]
