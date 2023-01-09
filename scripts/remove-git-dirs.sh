#!/usr/bin/env bash

repo_root="$(git rev-parse --show-toplevel)";
pushd "${repo_root}" > /dev/null;

find . \
  -mindepth 2 \
  -type d \
  -name .git \
  -not \
  \( \
    -path './docroot/vendor/va-gov/content-build/.git' \
  \) \
  | xargs rm -rf

popd > /dev/null;
