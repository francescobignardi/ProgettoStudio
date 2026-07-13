FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev && rm -rf /var/lib/apt/lists/* && docker-php-ext-install bcmath pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer