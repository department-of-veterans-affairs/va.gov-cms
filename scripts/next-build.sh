#!/usr/bin/env bash
#preview

ROOT=${TUGBOAT_ROOT:-${DDEV_APPROOT:-/var/www/html}}
if [ -n "${IS_DDEV_PROJECT}" ]; then
    APP_ENV="local"
elif [ -n "${TUGBOAT_ROOT}" ]; then
    APP_ENV="tugboat"
else
    APP_ENV="tugboat"
fi

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

cd "${ROOT}/next"

APP_ENV=${APP_ENV} yarn build:preview

# Switch to the docroot to run drush commands.
cd "${ROOT}/docroot"

# Log the timestamp of the build for reporting purposes.
drush state:set next_build.status last_build_date "$(date)"
