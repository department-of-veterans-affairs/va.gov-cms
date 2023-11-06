#!/usr/bin/env bash

set -ex

# This runs a portion of the GraphQL content build process, stopping once the
# CMS's responsibilities have been fulfilled.
#
# This is intended to make tests of the CMS's content-build responsibilities
# faster and less complicated to perform.
#
# See also:
# - ../../scripts/web-build.sh
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

composer va:web:prepare-dotenv

touch ./.buildlock

build_type="vagovdev"
build_api_url="https://dev-api.va.gov"
web_path="./web"
build_path="${web_path}/build/${build_type}"

mkdir -p "${build_path}"
echo "{}" > "${build_path}/metalsmith-build-data.json"

# Patch the content-build process to log the time at which the specific tasks
# start and complete.
cp patches/content-build-gql-logging.patch "${web_path}/"
pushd "${web_path}"
git apply content-build-gql-logging.patch || echo 'patch failed'
popd

pushd "${web_path}"
export INSTALL_HOOKS=no
export NODE_ENV=production
nvm use
yarn build \
  --pull-drupal \
  --gql-queries-only \
  --nosymlink \
  --no-drupal-proxy \
  --api="${build_api_url}" \
  --buildtype="${build_type}"
popd

rm -f ./.buildlock

popd > /dev/null
