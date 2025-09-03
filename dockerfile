# WordPress mit Xdebug für Development
FROM wordpress:6.6-php8.2-fpm

# Xdebug und Redis installieren, OPcache aktivieren
RUN pecl install xdebug redis && \
    docker-php-ext-enable xdebug redis opcache

# Zusätzliche Tools für WordPress Development
RUN apt-get update && apt-get install -y \
    zip unzip \
    wget \
    less \
    procps \
    && rm -rf /var/lib/apt/lists/*

# User für Berechtigungen erstellen
RUN groupadd -g 1000 devuser && \
    useradd -u 1000 -g 1000 -m -s /bin/bash devuser

# Verzeichnisse für den User vorbereiten
RUN chown -R 1000:1000 /var/www/html

# Log-Verzeichnis für PHP-FPM erstellen
RUN mkdir -p /var/log/wordpress && \
    chown -R www-data:www-data /var/log/wordpress

# Working directory
WORKDIR /var/www/html
