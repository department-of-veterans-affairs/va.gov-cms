#!/usr/bin/env bash

# This script will exit without an error if we should run the command directly,
# without any preamble.

if [ -n "${CMS_ENVIRONMENT_TYPE}" ]; then
  echo "Running in a ${CMS_ENVIRONMENT_TYPE} environment."
  # BRD, Local (DDEV), Tugboat, etc.
  exit 0
elif [ -n "${GITHUB_ACTION}" ]; then
  echo "Running in a ${GITHUB_ACTION} environment."
  # GitHub Action environment.
  exit 0
elif command -v ddev > /dev/null; then
  echo "Running in a host machine environment."
  # We're on a host machine running `ddev`.
  exit 1
fi

# Otherwise, assume we're in a build environment or something else and run
# commands directly.
exit 0
