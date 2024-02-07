setup:
	cp .env.example .env
	composer install
	php artisan key:generate

run:
	php artisan serve

docker-build:
	docker build -t oidc-client .

docker-run:
	docker run -it -p 8000:8000 oidc-client
