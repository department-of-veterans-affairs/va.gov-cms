#!/usr/bin/env bash

# Exit immediately if a command fails with a non-zero status code
set -e

# Allow script to be run anywhere in git repo
cd $(git rev-parse --show-toplevel)/.dumps

curl --remote-name https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/files/cms-prod-files-latest.tgz
rm -rf ../docroot/sites/default/files/*
echo "Extracting files to sites/default/files."
mkdir -p ../docroot/sites/default/files
tar --extract --gunzip --file cms-prod-files-latest.tgz --directory ../docroot/sites/default/files
echo "File sync from cms-prod-files-latest.tgz is complete."
