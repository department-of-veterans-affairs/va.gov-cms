#!/usr/bin/env bash

# Connect to SOCKS proxy.
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

ssh socks -D 2001 -N

popd > /dev/null
