#!/usr/bin/env bash

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"
source ~/.bashrc

# Performs a local content-build.
# See also:
# - ../tests/scripts/content-build-gql.sh
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

composer va:web:prepare-dotenv
touch ./.buildlock

build_type="vagovdev"
web_path="./web"
build_path="${web_path}/build/${build_type}"
assets_base_url="https://dev-va-gov-assets\.s3-us-gov-west-1\.amazonaws\.com"
rm -rf "${build_path}"

pushd "${web_path}"
export INSTALL_HOOKS=no
export NODE_ENV=production

nvm install
echo "Node $(node -v)"

yarn build \
  --pull-drupal \
  --no-drupal-proxy \
  --api="${build_api_url}" \
  --buildtype="${build_type}"
popd

echo "Replacing s3 address with local in generated files."
find \
  "${build_path}/generated" \
  -type f \
  -exec sed -i "s#${assets_base_url}##g" {} \+;

rm ./.buildlock

popd > /dev/null
