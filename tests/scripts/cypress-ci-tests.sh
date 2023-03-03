#!/usr/bin/env bash

set -x

# This runs the Cypress test suites with some additional functionality for CI.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

workflow_id='cypress.yml'

curl -L \
  -X POST \
  -H "Accept: application/vnd.github+json" \
  -H "Authorization: Bearer ${GITHUB_TOKEN}" \
  -H "X-GitHub-Api-Version: 2022-11-28" \
  https://api.github.com/repos/${TUGBOAT_GITHUB_OWNER}/${TUGBOAT_GITHUB_REPO}/actions/workflows/${workflow_id}/dispatches \
  -d '{"ref":"main","inputs":{"preview_url":"'"${TUGBOAT_DEFAULT_SERVICE_URL}"'", "pull_request": "'"${TUGBOAT_GITHUB_PR}"'", "commit_sha":"'"${TUGBOAT_PREVIEW_SHA}"'"}}'

exit_code=$?
node tests/report_cypress_accessibility_errors.js

popd > /dev/null

exit "${exit_code}"
