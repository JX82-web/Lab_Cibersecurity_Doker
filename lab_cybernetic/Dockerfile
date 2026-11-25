# Cybernetic Shop - Docker Lab Environment
FROM php:8.2-apache

# Installa dipendenze sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite zip

# Abilita mod_rewrite
RUN a2enmod rewrite

# Configura Apache per document root in public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Crea directory per dati e uploads
RUN mkdir -p /var/www/html/data /var/www/html/public/uploads/avatars /var/www/html/public/uploads/products /var/www/html/public/uploads/documents

# Copia file applicazione
COPY . /var/www/html/

# Imposta permessi
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/data /var/www/html/public/uploads /var/www/html/plugins

# Espone porta 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

CMD ["apache2-foreground"]
