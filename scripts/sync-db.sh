#!/usr/bin/env bash

# Meant to be run with `lando db-sync-stg`

# debug output
set -x

# Exit immediately if a command fails with a non-zero status code
set -e

# Setup
mkdir --parents .dumps
cd .dumps
# @todo check if Lando stack is running, if not return error message or just start it
echo "Downloading latest PROD database"
curl --remote-name --remote-header-name https://s3-us-gov-west-1.amazonaws.com/vagov-cms-backups-pub/mysql/db-latest.sql.gz
gunzip db-latest.sql.gz --force

echo "Downloaded PROD Database to ./.dumps/db-latest.sql"
