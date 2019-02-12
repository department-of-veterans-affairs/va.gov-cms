#!/usr/bin/env bash

# Meant to be run with `lando db-sync-stg`

# debug output
set -x

# Exit immediately if a command fails with a non-zero status code
set -e

# Setup
mkdir --parents .sql-dumps
cd .sql-dumps
# @todo check if Lando stack is running, if not return error message or just start it
echo "Downloading latest STG database"
curl --remote-name --remote-header-name https://s3-us-gov-west-1.amazonaws.com/agile6-backups-pub/mysql/db-latest.sql.gz
gunzip db-latest.sql.gz

echo "Downloaded STG Database to ./.sql-dumps/db-latest.sql"
