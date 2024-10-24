#!/usr/bin/env bash
#preview

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

cd next

# Make sure the correct node version is installed and in use
nvm install 18.17.0
nvm use 18.17.0

APP_ENV=tugboat yarn build:preview
