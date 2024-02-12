#!/bin/bash

# set application directory
APP_DIR=${APP_DIR:-/app}

# set default app host and port when not set
APP_HOST=${APP_HOST:-0.0.0.0}
APP_PORT=${APP_PORT:-8000}

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

# run the application
# FYI: you should not use php artisan serve in production
php artisan serve --host=$APP_HOST --port=$APP_PORT
