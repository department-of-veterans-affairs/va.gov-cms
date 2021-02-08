#!/usr/bin/env bash
#
# Ensure that the available yarn version is compatible with the version
# required by vetsweb.
#

# Allow script to be run anywhere in git repo
cd "$(git rev-parse --show-toplevel)"

YARN_VERSION_INSTALLED="$( yarn --version )"
REQUIRED_YARN_VERSION=$( node --print --eval="require('./web/package.json').engines.yarn" | sed 's/[<>=~]*//' )
if [ "${REQUIRED_YARN_VERSION}" != "${YARN_VERSION_INSTALLED}" ]; then
  echo "Installed yarn version '${YARN_VERSION_INSTALLED}' does not match the required version: '${REQUIRED_YARN_VERSION}'!"
  exit 1
else
  echo "Installed yarn version '${YARN_VERSION_INSTALLED}' matches the required version '${REQUIRED_YARN_VERSION}'."
fi
