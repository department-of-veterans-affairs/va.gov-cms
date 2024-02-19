#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Installs the content-build dependencies.

if [ ! -d next ]; then
  # Clone full so git information is available for content release form.
  # I don't think this should be necessary, but branch information was not
  # available in the content release form until I pulled down all information.
  git clone https://github.com/department-of-veterans-affairs/next-build.git next
else
  echo "Repo next-build already cloned."
fi

cd next
#repo_root="$(git rev-parse --show-toplevel)"
#pushd "${repo_root}" > /dev/null

nvm install 18.17.0
nvm use 18.17.0

# These steps caused the build to fail for me so I disabled temporarily.
#corepack enable
#corepack prepare yarn@stable --activate

echo "Node $(node -v)"
echo "NPM $(npm -v)"
echo "Yarn $(yarn -v)"

#not sure how popd works
#pushd "./next"
yarn install
#popd

#popd > /dev/null
