#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

cd next

# Start the dev server. Vets-website assets will be available to the preview server after content-build builds them.
# APP_ENV=tugboat yarn dev

# Start the dev server. Vets-website assets need to be in place prior to this build.
APP_ENV=tugboat yarn start
