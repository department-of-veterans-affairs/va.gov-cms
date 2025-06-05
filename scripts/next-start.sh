#!/usr/bin/env bash

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

cd ${ROOT}/next

# Kill any current running server.
# We can look for "/scripts/yarn/start.js" since that is what "yarn start" runs.
NEXT_SERVER_PIDS=$(ps aux | grep '[.]/scripts/yarn/start.js' | awk '{print $2}')

# In case we have multiple processes, loop through them.
for pid in ${NEXT_SERVER_PIDS}; do
    kill $pid
done

# Install the correct node version if necessary and use it.
nvm install && nvm use

# Start the dev server. Vets-website assets need to be in place prior to this build.
yarn start
