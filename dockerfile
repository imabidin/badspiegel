# WordPress mit Xdebug für Development
FROM wordpress:6.6-php8.2-fpm

# Xdebug installieren (neueste Version)
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Zusätzliche Tools für WordPress Development
RUN apt-get update && apt-get install -y \
    zip unzip \
    wget \
    less \
    && rm -rf /var/lib/apt/lists/*

# User für Berechtigungen erstellen
RUN groupadd -g 1000 devuser && \
    useradd -u 1000 -g 1000 -m -s /bin/bash devuser

# Verzeichnisse für den User vorbereiten
RUN chown -R 1000:1000 /var/www/html

# Working directory
WORKDIR /var/www/html
