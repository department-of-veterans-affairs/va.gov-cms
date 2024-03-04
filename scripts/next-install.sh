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

cd ${ROOT}

if [ ! -d next ]; then
  # Clone full so git information is available for content release form.
  # I don't think this should be necessary, but branch information was not
  # available in the content release form until I pulled down all information.
  git clone https://github.com/department-of-veterans-affairs/next-build.git next
else
  echo "Repo next-build already cloned."
fi

cd next

nvm install 18.17.0
nvm use 18.17.0

# These steps caused the build to fail for me so I disabled temporarily.
#corepack enable
#corepack prepare yarn@stable --activate

echo "Node $(node -v)"
echo "NPM $(npm -v)"
echo "Yarn $(yarn -v)"

yarn install
