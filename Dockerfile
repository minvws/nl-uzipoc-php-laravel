# Dockerfile
FROM php:8.3.2-cli

RUN apt-get update -y \
  && apt-get install -y libmcrypt-dev \
  && apt-get clean \
	&& docker-php-ext-install bcmath \
  && rm -rf /var/lib/apt/lists/* /var/cache/apt/archives/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN composer install
RUN php artisan key:generate

EXPOSE 8000
CMD /scripts/startup.sh
