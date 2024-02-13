# Dockerfile
FROM php:8.3.2-cli

RUN apt-get update -y \
  && apt-get install -y libmcrypt-dev libzip-dev \
  && apt-get clean \
	&& docker-php-ext-install bcmath zip \
  && rm -rf /var/lib/apt/lists/* /var/cache/apt/archives/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN composer install

EXPOSE 8000
CMD /app/docker/scripts/startup.sh
