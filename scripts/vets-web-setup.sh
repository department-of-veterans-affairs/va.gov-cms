#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Installs & builds vets-website dependencies for next-build preview.
#if [ ! -d docroot/vendor/va-gov/vets-website ]; then
if [ ! -d vets-website ]; then
  git clone --single-branch --depth 1 https://github.com/department-of-veterans-affairs/vets-website.git vets-website
else
  echo "Repo vets-website already cloned."
fi

#cd docroot/vendor/va-gov/vets-website
cd vets-website

nvm install 14.15.1
nvm use 14.15.1
npm install -g yarn

echo "Node $(node -v)"
echo "NPM $(npm -v)"
echo "Yarn $(yarn -v)"

export NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt
yarn install
yarn build
