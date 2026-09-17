FROM php:8.3-apache

# System tools Composer needs
RUN apt-get update && apt-get install -y git unzip \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions Laravel needs beyond the bundled ones
RUN docker-php-ext-install pdo_mysql opcache

# Laravel needs URL rewriting (routes like /patients/5)
RUN a2enmod rewrite

# Serve the app from /public instead of the project root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Composer, copied from its own official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Vendor dependencies first — Docker caches this layer,
# so it only re-runs when composer.json/lock change
COPY composer.* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Now the application code
COPY . .
RUN composer dump-autoload --optimize

# Laravel must be able to write logs and cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Startup script (waits for DB, migrates, seeds)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
