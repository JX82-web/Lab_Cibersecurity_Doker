FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       openssh-server sudo vim nano netcat-openbsd curl iproute2 procps iputils-ping \
       ca-certificates default-mysql-client \
    && docker-php-ext-install mysqli \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

RUN groupadd -f operator \
    && useradd -m -s /bin/bash tajani \
    && usermod -aG operator tajani \
    && echo 'tajani:tajani_lab_2026' | chpasswd \
    && mkdir -p /run/sshd /var/www/html /opt/maintenance/hooks /opt/legacy /root/flags

COPY composer.json /var/www/html/composer.json
COPY config/ /var/www/html/config/
COPY src/ /var/www/html/src/
COPY Models/ /var/www/html/Models/
COPY plugins/ /var/www/html/plugins/
COPY public/ /var/www/html/public/
COPY maintenance/ /opt/maintenance/
COPY ssh/sshd_config /etc/ssh/sshd_config
COPY tools/entrypoint.sh /usr/local/bin/lab-entrypoint
COPY flags/user.txt /home/tajani/user.txt
COPY flags/root.txt /root/flags/root.txt
COPY lab-secrets/maintenance.env /opt/legacy/maintenance.env

RUN chown -R root:root /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} + \
    && find /var/www/html -type f -exec chmod 644 {} + \
    && chmod 640 /opt/legacy/maintenance.env \
    && chown root:www-data /opt/legacy/maintenance.env \
    && chmod 600 /home/tajani/user.txt \
    && chown tajani:tajani /home/tajani/user.txt \
    && chmod 600 /root/flags/root.txt \
    && chown root:root /root/flags/root.txt \
    && chown -R root:operator /opt/maintenance \
    && chmod 755 /opt/maintenance/backup.sh \
    && chmod 775 /opt/maintenance/hooks \
    && chmod 775 /opt/maintenance/hooks/pre_backup.sh \
    && chmod 755 /usr/local/bin/lab-entrypoint \
    && printf '%s\n' 'tajani ALL=(root) NOPASSWD: /opt/maintenance/backup.sh' > /etc/sudoers.d/tajani-maintenance \
    && chmod 440 /etc/sudoers.d/tajani-maintenance \
    && printf '%s\n' 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

WORKDIR /var/www/html
RUN printf '%s\n' \
    '<VirtualHost *:80>' \
    '    DocumentRoot /var/www/html/public' \
    '    <Directory /var/www/html/public>' \
    '        Options FollowSymLinks' \
    '        AllowOverride All' \
    '        Require all granted' \
    '    </Directory>' \
    '    DirectoryIndex index.php' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default \
    && grep -q 'DocumentRoot /var/www/html/public' /etc/apache2/sites-available/000-default.conf \
    && grep -q 'Require all granted' /etc/apache2/sites-available/000-default.conf

EXPOSE 80 22
ENTRYPOINT ["/usr/local/bin/lab-entrypoint"]
