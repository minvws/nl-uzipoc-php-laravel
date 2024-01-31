setup:
	cp .env.example .env
	composer install
	php artisan key:generate

run:
	php artisan serve