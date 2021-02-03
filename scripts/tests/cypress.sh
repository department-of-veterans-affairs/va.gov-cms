#!/usr/bin/env bash

if [ -z "${1}" ]; then
  echo "Please enter the type of test to run, e.g. accessibility or behavioral."
  exit 1
fi

# Exit immediately if a command fails with a non-zero status code
set -e

# Allow script to be run anywhere in git repo
cd "$(git rev-parse --show-toplevel)"

# Do not attempt to run on BRD environment.
if [ -n "$CMS_IS_BRD" ]; then
  echo "Detected BRD environment, skipping cypress-axe tests."
  exit 0
fi

npm install
npm run test:${1}
