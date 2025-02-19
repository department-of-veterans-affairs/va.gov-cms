#!/usr/bin/env bash
#preview

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

cd next

# Install the correct node version if necessary and use it.
nvm install && nvm use

APP_ENV=tugboat yarn build:preview
