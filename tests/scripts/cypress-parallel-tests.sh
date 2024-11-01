#!/usr/bin/env bash

set -x

# This runs the Cypress test suites in parallel with some additional functionality for CI.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

: "${GITHUB_COMMENT_TYPE:=unset}"

[ -d node_modules ] || npm install
./node_modules/.bin/cypress install

export CYPRESS_VERIFY_TIMEOUT=100000
npm run test:cypress:verify

npm run test:cypress:parallel -- "${@}"
exit_code=$?

accessibility_violations=$(<cypress_accessibility_violations.json)
violations_count=$(jq length < cypress_accessibility_violations.json)
if [ "${GITHUB_COMMENT_TYPE}" == "pr" ]; then
  if [ "$violations_count" -ne 0 ]; then
    comment="$(printf 'Accessibility Violations Found:\n``` json\n%b\n```' "${accessibility_violations}")"
    github-commenter \
      -delete-comment-regex="Accessibility Violations Found" \
      -comment="${comment}"
  fi
fi

popd > /dev/null

exit "${exit_code}"
