#!/bin/bash
set -e

# Ensure nvm is loaded
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Install and use the Node version from .nvmrc
nvm install
nvm use

# Print Node and npm versions for debugging
node -v
npm -v

# Install dependencies
npm install

# Add any additional build or test commands below, e.g.:
# npx cypress run
# npm run build
