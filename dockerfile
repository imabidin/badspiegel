# WordPress mit Xdebug für Development
FROM wordpress:6.6-php8.2-fpm

# Xdebug, Redis und XHProf installieren, OPcache aktivieren
RUN pecl install xdebug redis xhprof && \
    docker-php-ext-enable xdebug redis opcache xhprof

# Zusätzliche Tools für WordPress Development und SOAP extension dependencies
RUN apt-get update && apt-get install -y \
    zip unzip \
    wget \
    less \
    procps \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# SOAP extension installieren
RUN docker-php-ext-install soap

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
