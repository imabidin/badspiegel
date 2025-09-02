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

# Working directory
WORKDIR /var/www/html
