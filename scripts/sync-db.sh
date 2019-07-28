#!/usr/bin/env bash

# TODO check if socks proxy is running
# TODO check if lando is running

# Exit immediately if a command fails with a non-zero status code
set -e

# Allow script to be run anywhere in git repo
cd $(git rev-parse --show-toplevel)/.dumps

# Setup
# @todo check if Lando stack is running, if not return error message or just start it
echo "Downloading latest PROD database"
curl --remote-name --remote-header-name https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/database/cms-prod-db-sanitized-latest.sql.gz
gunzip --force cms-db-latest.sql.gz 2> /dev/null || true &&

echo "Downloaded PROD Database to .dumps/cms-db-latest.sql"

echo "Importing PROD database"
lando db-import cms-db-latest.sql
