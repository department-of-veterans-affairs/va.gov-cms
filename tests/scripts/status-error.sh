#!/usr/bin/env bash

set -exo pipefail

# This runs the status-error test.
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

result="$(drush core-requirements --format=json --ignore='update_core,update_contrib,\"update status\"' --severity=2 | jq '. | length')"
exit_code="${result}"
if [ "${exit_code}" -ne 0 ]; then
  drush core-requirements --severity=2
fi

popd > /dev/null
