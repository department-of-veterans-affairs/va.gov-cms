#!/usr/bin/env bash

# Install NVM.
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash

# Install node v14.15.0 since the frontend is still on that.
nvm install 14.15.0

# Install latest version of node.js v16
nvm install 16.19.1

echo "export NVM_DIR=\"$HOME/.nvm\"" >> $BASH_ENV
echo "[ -s \"$NVM_DIR/nvm.sh\" ] && \. \"$NVM_DIR/nvm.sh\"" >> $BASH_ENV
echo "[ -s \"$NVM_DIR/bash_completion\" ] && \. \"$NVM_DIR/bash_completion\"" >> $BASH_ENV
echo "nvm use 16.19.1" >> $BASH_ENV

# Verify node is installed.
node -v
