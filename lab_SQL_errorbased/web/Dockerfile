FROM php:8.2-apache
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y openssh-server \
	curl procps \
	python3 \
	python3-pip \
	sudo \
	gdb \
	net-tools \
	iproute2 \
	cron \
	util-linux \
	tar \
	gcc \
	vim \
	nano \
	netcat-openbsd \
	less \
	&& rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mysqli
COPY src/ /var/www/html/

COPY ssh/sshd_config /etc/ssh/sshd_config
COPY bin/suid-check.c /tmp/suid-check.c

RUN gcc /tmp/suid-check.c -o /usr/local/bin/suid-check && chown root:root /usr/local/bin/suid-check && chmod 4755 /usr/local/bin/suid-check

RUN useradd -m -s /bin/bash taylor && echo 'taylor:sage123' | chpasswd 

RUN printf '#!/bin/sh\n/usr/sbin/sshd\nexec apache2-foreground\n' > /entrypoint.sh && chmod 755 /entrypoint.sh

RUN openssl rand -hex 16 > /home/taylor/user.txt && chown -R taylor:taylor /home/taylor/user.txt
RUN chmod 600 /home/taylor/user.txt
RUN openssl rand -hex 16 > /root/root.txt && chown -R root:root /root/root.txt
RUN chmod 600 /root/root.txt

CMD ["/entrypoint.sh"]
