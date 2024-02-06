setup:
	cp .env.example .env
	composer install
	php artisan key:generate

run:
	php artisan serve

run-test:
	cp .env.example .env
	sed -i '' -e 's|OIDC_ISSUER=""|OIDC_ISSUER=${URL_MAX}|g' .env
	sed -i '' -e 's/OIDC_CLIENT_ID=""/OIDC_CLIENT_ID=${OIDC_CLIENT_ID}/g' .env
	sed -i '' -e 's/OIDC_DECRYPTION_KEY_PATH=""/OIDC_DECRYPTION_KEY_PATH=key.pem/g' .env
	echo ${OIDC_DECRYPTION_KEY} > key.pem
	cat key.pem
	composer install
	php artisan key:generate
	php artisan serve &
