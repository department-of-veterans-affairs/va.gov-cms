#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Installs the content-build dependencies.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

pushd "./bin"
ln -sf ../docroot/libraries/yarn/bin/yarn ./yarn
popd
nvm install
echo "Node $(node -v)"
echo "NPM $(npm -v)"
echo "Yarn $(yarn -v)"

./scripts/composer/check_yarn_version.sh

pushd "./web"
nvm install
yarn run install-repos
export NODE_EXTRA_CA_CERTS=/etc/pki/tls/certs/ca-bundle.crt
export PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=TRUE
yarn install
popd

popd > /dev/null
