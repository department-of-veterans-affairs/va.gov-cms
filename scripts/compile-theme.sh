#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Compile the VA.gov Claro theme.
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

pushd ./bin
ln -sf ../docroot/libraries/yarn/bin/yarn ./yarn
popd

export NODE_EXTRA_CA_CERTS=/etc/pki/tls/certs/ca-bundle.crt
export PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=TRUE

pushd ./docroot/core
yarn install
yarn build:css
popd

pushd ./docroot/design-system
nvm install
npm install
npm run build:drupal
popd

if [[ "${CMS_ENVIRONMENT_TYPE}" == "tugboat" ]]; then

pushd ./docroot/themes/custom/vagovclaro
nvm install
npm install
npm run test
popd

else

pushd ./docroot/themes/custom/vagovclaro
nvm install
npm install
npm run prod
popd

fi

popd > /dev/null
