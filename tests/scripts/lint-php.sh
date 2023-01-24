#!/bin/bash

set -exo pipefail

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

# Lint PHP files using PHP's built-in linter.
find \
  docroot/modules/custom \
  docroot/themes \
  \( \
    -name '*.inc' \
    -o -name '*.php' \
    -o -name '*.module' \
    -o -name '*.install' \
  \) \
  -print0 \
  | xargs \
    -0 \
    -n1 php \
    -l \
    2>&1

popd > /dev/null
