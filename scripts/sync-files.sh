#!/usr/bin/env bash

# Exit immediately if a command fails with a non-zero status code
set -e

# Allow script to be run anywhere in git repo
cd $(git rev-parse --show-toplevel)/.dumps

echo "You must have your PROXY running e.g. 'ssh socks -D 2001 -N &'"
curl --remote-name --remote-header-name --proxy socks5h://127.0.0.1:2001 10.247.89.58:8000/cms-app-files-latest.tgz
rm --force --recursive /app/docroot/sites/default/files/*
echo "Extracting files to sites/default/files."
tar --extract --gunzip --verbose --file cms-app-files-latest.tgz --directory ../docroot/sites/default/files
echo "PROD file sync to LOCAL complete."
