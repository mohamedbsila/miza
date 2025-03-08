#!/bin/bash

# Install PHP and Composer
apt-get update
apt-get install -y php8.1-cli php8.1-common php8.1-curl php8.1-json php8.1-mbstring php8.1-mysql php8.1-xml php8.1-zip

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear and warm up cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod 