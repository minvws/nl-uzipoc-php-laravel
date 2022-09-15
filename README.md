# UziPoc Laravel / PHP OpenID connect client example
This client provides an example how to connect to the https://github.com/minvws/nl-uzipoc-max OIDC service.
Or another OpenID Connect service that uses PKCE flow.

## Requirements
This PHP example is tested with `php 8.0`.

After checkout, please run the following commands to install the dependencies:
```
cp .env.example .env
composer install
php artisan key:generate
```

## Registration
To use this client an RSA certificate needs to be provided to the
UziPoc OIDC service. The matching key needs te be configured in the .env.

Please configure the following fields in the .env file:
```
OIDC_ISSUER=""
OIDC_CLIENT_ID=""
OIDC_CLIENT_SECRET=""
OIDC_ADDITIONAL_SCOPES=""
OIDC_DECRYPTION_KEY_PATH=""
```

The `OIDC_DECRYPTION_KEY_PATH` needs to be the path to the private key file (e.g. `/secrets/key.pem`) if the user info is encrypted.

## Run locally
You can run this project by running the following command:
```
php artisan serve
```

To visit the login page, open the browser and navigate to `http://localhost:8000/login`.