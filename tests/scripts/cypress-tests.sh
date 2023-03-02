#!/usr/bin/env bash

set -ex

# This runs the Cypress test suites.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

[ -d node_modules ] || npm install

workflow_id='cypress.yml'

curl -L \
  -X POST \
  -H "Accept: application/vnd.github+json" \
  -H "Authorization: Bearer <YOUR-TOKEN>"\
  -H "X-GitHub-Api-Version: 2022-11-28" \
  https://api.github.com/repos/${TUGBOAT_GITHUB_OWNER}/${TUGBOAT_GITHUB_REPO}/actions/workflows/${workflow_id}/dispatches \
  -d '{"ref":"'"${TUGBOAT_GITHUB_HEAD}"'","inputs":{"preview_url":"'"${TUGBOAT_DEFAULT_SERVICE_URL}"'"}}'

popd > /dev/null
