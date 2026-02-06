#!/usr/bin/env bash

set -x

# This runs the Cypress test suites in shards with CI-friendly setup.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

[ -d node_modules ] || npm install
./node_modules/.bin/cypress install

export CYPRESS_VERIFY_TIMEOUT=100000
npm run test:cypress:verify

npm run test:cypress:shard:sh -- "${@}"
exit_code=$?

node tests/report_cypress_accessibility_errors.js

popd > /dev/null

exit "${exit_code}"
