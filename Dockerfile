FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM php:8.3-apache AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libicu-dev libonig-dev libsqlite3-dev libzip-dev \
    && docker-php-ext-install bcmath intl mbstring pdo_sqlite zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .

# The lockfile has not been generated on the host yet, so resolve the declared
# PHP dependencies during the image build.
RUN composer update --no-interaction --prefer-dist --optimize-autoloader \
    && rm -rf public/build \
    && mkdir -p public/build
COPY --from=frontend /app/public/build ./public/build

COPY docker/entrypoint.sh /usr/local/bin/portal-entrypoint
RUN chmod +x /usr/local/bin/portal-entrypoint \
    && mkdir -p /var/lib/portal \
    && chown -R www-data:www-data storage bootstrap/cache /var/lib/portal

EXPOSE 80
ENTRYPOINT ["portal-entrypoint"]
CMD ["apache2-foreground"]
