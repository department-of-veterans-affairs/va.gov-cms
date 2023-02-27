#!/usr/bin/env bash

set -x

# This runs the Cypress test suites with some additional functionality for CI.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

./tests/scripts/cypress-tests.sh "${@}"
exit_code=$?
node tests/report_cypress_accessibility_errors.js

popd > /dev/null

exit "${exit_code}"
