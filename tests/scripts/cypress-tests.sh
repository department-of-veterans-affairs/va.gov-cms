#!/usr/bin/env bash

set -ex

# This runs the Cypress test suites.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

[ -d node_modules ] || npm install

./node_modules/.bin/cypress install

npm run test:cypress -- "${@}"

popd > /dev/null
