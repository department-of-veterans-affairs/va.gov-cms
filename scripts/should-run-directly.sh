#!/usr/bin/env bash

# This script will exit without an error if we should run the command directly,
# without any preamble.

if command -v ddev > /dev/null; then 
  # We're on a host machine running `ddev`.
  exit 1
elif [ -n "${CMS_ENVIRONMENT_TYPE}" ]; then
  # BRD, Local (DDEV), Tugboat, etc.
  exit 0
elif [ -n "${GITHUB_ACTION}" ]; then
  # GitHub Action environment.
  exit 0
fi

# Otherwise, assume we're in a build environment or something else and run
# commands directly.
exit 0
