#!/usr/bin/env bash

set -x

# This runs the Cypress test suites in parallel with some additional functionality for CI.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

[ -d node_modules ] || npm install
./node_modules/.bin/cypress install

export CYPRESS_VERIFY_TIMEOUT=100000
npm run test:cypress:verify

npm run test:cypress:parallel -- "${@}"
exit_code=$?

popd > /dev/null

exit "${exit_code}"
