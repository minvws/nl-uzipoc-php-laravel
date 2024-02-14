#!/bin/bash

APP_DIR=${APP_DIR:-/app}

# Needs to be 0.0.0.0 for Docker
ARTISAN_SERVE_HOST=0.0.0.0
# Needs to be the port that is exposed in the Dockerfile
ARTISAN_SERVE_PORT=8000
ARTISAN_SERVE_NO_RELOAD=${ARTISAN_SERVE_NO_RELOAD:-true}

cd $APP_DIR

# check if OIDC_DECRYPTION_KEY_CONTENT is set
if [ -n "$OIDC_DECRYPTION_KEY_CONTENT" ]; then
  # check if secrets directory exists
  if [ ! -d $APP_DIR/secrets ]; then
    # if not, create it
    mkdir $APP_DIR/secrets
  fi

  # if so, write it to a file
  echo $OIDC_DECRYPTION_KEY_CONTENT > $APP_DIR/secrets/oidc-decryption-key.pem

  # set OIDC_DECRYPTION_KEY_PATH
  OIDC_DECRYPTION_KEY_PATH=$APP_DIR/secrets/oidc-decryption-key.pem
  export OIDC_DECRYPTION_KEY_PATH
fi

# check if .env file exists
if [ ! -f $APP_DIR/.env ]; then
  # if not, copy the example file
  cp $APP_DIR/.env.ci $APP_DIR/.env
fi

# set no reload argument if ARTISAN_SERVE_NO_RELOAD is set to true
# with no reload the server will use all the environment variables
ARTISAN_NO_RELOAD_ARGUMENT=''
if [ "$ARTISAN_SERVE_NO_RELOAD" = "true" ]; then
  ARTISAN_NO_RELOAD_ARGUMENT='--no-reload'
fi

# run the application
# FYI: you should not use php artisan serve in production
php artisan serve --host=$ARTISAN_SERVE_HOST --port=$ARTISAN_SERVE_PORT $ARTISAN_NO_RELOAD_ARGUMENT
