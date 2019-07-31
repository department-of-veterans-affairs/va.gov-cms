#!/usr/bin/env bash

# Exit immediately if a command fails with a non-zero status code
set -e

# Allow script to be run anywhere in git repo
cd $(git rev-parse --show-toplevel)/.dumps

curl --remote-name --remote-header-name https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/files/cms-prod-files-latest.tgz
rm --force --recursive ../docroot/sites/default/files/*
echo "Extracting files to sites/default/files."
tar --extract --gunzip --verbose --file cms-prod-files-latest.tgz --directory ../docroot/sites/default/files
echo "PROD file sync to LOCAL complete."
