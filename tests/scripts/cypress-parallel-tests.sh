#!/usr/bin/env bash

set -x

# This runs the Cypress test suites in parallel with some additional functionality for CI.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

[ -d node_modules ] || npm install
./node_modules/.bin/cypress install

export CYPRESS_VERIFY_TIMEOUT=100000
npm run test:cypress:verify

npm run test:cypress:parallel -- \
  --script test:cypress \
  --threads 3 \
  --strictMode false \
  --specsDir tests/cypress/integration \
  --weightsJson tests/cypress/integration/weights.json \
  --reporter cypress-multi-reporters \
  --reporter-options configFile=cypress.reporter.config.json \
  "${@}"
exit_code=$?

npx generate-mochawesome-report

popd > /dev/null

exit "${exit_code}"
