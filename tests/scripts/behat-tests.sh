#!/usr/bin/env bash

set -ex

# This runs the Behat test suites.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

[ -d node_modules ] || npm install

pushd ./tests/behat
behat "${@}"
popd

popd > /dev/null
