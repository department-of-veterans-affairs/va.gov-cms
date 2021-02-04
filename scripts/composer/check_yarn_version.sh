#!/usr/bin/env bash
#
# Ensure that the available yarn version is compatible with the version
# required by vetsweb.
#

# Allow script to be run anywhere in git repo
cd "$(git rev-parse --show-toplevel)"

YARN_VERSION_INSTALLED="$( yarn --version )"
REQUIRED_YARN_VERSION=$( node --print --eval="require('./web/package.json').engines.yarn" | sed 's/[<>=~]*//' )
if [ ! "$( printf '%s\n' "$REQUIRED_YARN_VERSION" "$YARN_VERSION_INSTALLED" | sort --version-sort | head --lines=1 )" = "$REQUIRED_YARN_VERSION" ]; then
  echo "Installed yarn version ${YARN_VERSION_INSTALLED} is older than the required version: ${REQUIRED_YARN_VERSION}!"
  exit 1
else
  echo "Installed yarn version ${YARN_VERSION_INSTALLED} is equal or greater to the required version ${REQUIRED_YARN_VERSION}."
fi
