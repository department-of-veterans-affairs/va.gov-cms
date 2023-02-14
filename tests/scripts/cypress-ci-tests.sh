#!/usr/bin/env bash

set -ex

# This runs the Cypress test suites with some additional functionality for CI.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

./tests/scripts/cypress-tests.sh
exit_code=$?
if [ "${exit_code}" -ne 0 ]; then
  node tests/report_cypress_accessibility_errors.js
fi

popd > /dev/null
