FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock* ./
RUN if [ -f composer.json ]; then \
      composer install --no-dev --prefer-dist --no-progress --no-interaction; \
    fi


FROM php:8.2-cli

ENV TZ=Europe/Istanbul
WORKDIR /var/www/html


RUN apt-get update \
 && apt-get install -y --no-install-recommends tzdata sqlite3 libsqlite3-dev ca-certificates \
 && docker-php-ext-install pdo_sqlite \
 && rm -rf /var/lib/apt/lists/*


COPY . .

COPY --from=vendor /app/vendor ./vendor

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
