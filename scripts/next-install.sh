#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Installs the content-build dependencies.

if [ ! -d next ]; then
  git clone --single-branch --depth 1 https://github.com/department-of-veterans-affairs/next-build.git next
else
  echo "Repo next-build already cloned."
fi

cd next
#repo_root="$(git rev-parse --show-toplevel)"
#pushd "${repo_root}" > /dev/null

nvm install 18.17.0 
nvm use 18.17.0
corepack enable
corepack prepare yarn@stable --activate
echo "Node $(node -v)"
echo "NPM $(npm -v)"
echo "Yarn $(yarn -v)"

#not sure how popd works
#pushd "./next"
yarn install
#popd

#popd > /dev/null
