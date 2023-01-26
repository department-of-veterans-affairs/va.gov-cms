#!/usr/bin/env bash

set -ex

# A wrapper for tests specifically as performed in CI contexts.
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

: "${RETURN_EXIT_CODE:=0}"

set -o allexport
source "${repo_root}/.env"
set +o allexport

test_name="${1}"
composer_name="${2:-va:test:${test_name}}"
status_name="${3:-va/tests/${test_name}}"

echo "Test name: ${test_name}"
echo "Composer name: ${composer_name}"
echo "Status name: ${status_name}"

time composer "${composer_name}" 2>&1
exit_code=$?

if [ -n "${GITHUB_TOKEN}" ]; then 
  if [ "${exit_code}" -eq 0 ]; then
    github-status-updater \
      -action=update_state \
      -state=success \
      -context="${status_name}" \
      -description="Success"
  else
    github-status-updater \
      -action=update_state \
      -state=failure \
      -context="${status_name}" \
      -description="Failure"
  fi
fi

popd > /dev/null

if [ "${RETURN_EXIT_CODE}" -ne 0 ]; then
  exit "${exit_code}"
fi

exit 0
