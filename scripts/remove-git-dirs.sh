#!/usr/bin/env bash

set -ex

# Remove .git subdirectories added by e.g. `composer install`.
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

find . \
  -mindepth 2 \
  -type d \
  -name .git \
  -not \
  \( \
    -path './docroot/vendor/va-gov/content-build/.git' \
    -or -path './docroot/vendor/va-gov/vets-website/.git' \
    -or -path './next/.git' \
  \) \
  -print \
  -exec rm -rf {} +

popd > /dev/null
