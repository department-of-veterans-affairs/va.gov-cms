#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Installs the content-build dependencies.

if [ ! -d next ]; then
  git clone --filter=tree:0 https://github.com/department-of-veterans-affairs/next-build.git next
else
  echo "Repo next-build already cloned."
fi

pushd next

# Ensure we have the latest code for the given branch.
git fetch origin
git reset --hard origin/$(git rev-parse --abbrev-ref HEAD)

nvm install
nvm use
corepack enable
corepack prepare yarn@stable --activate
echo "Node $(node -v)"
echo "NPM $(npm -v)"
echo "Yarn $(yarn -v)"

yarn install
popd > /dev/null
