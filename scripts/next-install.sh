#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Installs the content-build dependencies.

if [ ! -d next/src ]; then
  git clone --filter=tree:0 https://va.ghe.com/software/next-build.git next
else
  echo "Repo next-build already cloned. Updating..."
  git -C next reset --hard
  git -C next pull origin $(git -C next rev-parse --abbrev-ref HEAD)
fi

pushd next

nvm install
nvm use
corepack enable
corepack prepare yarn@stable --activate
echo "Node $(node -v)"
echo "NPM $(npm -v)"
echo "Yarn $(yarn -v)"

yarn install-safe --immutable
popd > /dev/null
