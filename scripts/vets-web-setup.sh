#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Installs & builds vets-website dependencies for next-build preview.
git config pull.rebase true
if [ ! -d vets-website ]; then
  git clone --filter=tree:0 https://va.ghe.com/software/vets-website.git vets-website
  cd vets-website
else
  cd vets-website
  echo "Repo vets-website already cloned. Updating..."
  git pull origin $(git rev-parse --abbrev-ref HEAD)
fi

nvm install && nvm use
npm install -g yarn

echo "Node $(node -v)"
echo "NPM $(npm -v)"
echo "Yarn $(yarn -v)"

export NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt
yarn install-safe
yarn build:webpack --env buildtype=vagovdev
