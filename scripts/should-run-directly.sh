#!/usr/bin/env bash

# This script will exit without an error if we should run a command without any
# preamble, e.g. `ddev`.

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

# Otherwise, assume we're on a host machine running DDEV, and that we should
# prefix our commands with `ddev`.
exit 1
