#!/usr/bin/env bash

# Compile the VA.gov Claro theme.
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

pushd ./bin
ln -sf ../docroot/libraries/yarn/bin/yarn ./yarn
popd

export NODE_EXTRA_CA_CERTS=/etc/pki/tls/certs/ca-bundle.crt
export PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=TRUE

pushd ./docroot/core
yarn install
yarn build:css
popd

pushd ./docroot/design-system
yarn install
yarn build:drupal
popd

pushd ./docroot/themes/custom/vagovclaro
yarn install
yarn build
popd

popd > /dev/null
