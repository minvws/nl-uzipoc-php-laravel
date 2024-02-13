setup:
	cp .env.example .env
	composer install
	php artisan key:generate

run:
	php artisan serve

docker-build:
	docker build -t minvws/nl-uzipoc-php-laravel -f docker/Dockerfile .

docker-run:
	docker run -it --volume "${PWD}/key.pem:/var/key.pem" -p 8000:8000 minvws/nl-uzipoc-php-laravel
