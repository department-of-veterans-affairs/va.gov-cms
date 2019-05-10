#!/usr/bin/env bash

# Exit immediately if a command fails with a non-zero status code
set -e

echo "You must have your PROXY running with 'ssh socks -D 2001 -N &'"
# Setup
cd .dumps
# @todo check if Lando stack is running, if not return error message or just start it
echo "Downloading latest PROD database"
curl --remote-name --remote-header-name --proxy socks5h://127.0.0.1:2001 10.247.89.58:8000/cms-db-latest.sql.gz
gunzip --force cms-db-latest.sql.gz 2> /dev/null || true &&

echo "Downloaded PROD Database to .dumps/cms-db-latest.sql"

echo "Importing PROD database"
lando db-import cms-db-latest.sql
