FROM serversideup/php:8.2-fpm-apache

# Copy the current directory to the container
COPY . /var/www/html

RUN --mount=type=secret,id=composer.auth,target=${COMPOSER_HOME}/auth.json \
    composer install --no-dev --no-interaction --no-progress --no-suggest --optimize-autoloader
