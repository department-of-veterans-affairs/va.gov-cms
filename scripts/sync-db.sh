#!/usr/bin/env bash

# Run outside of Lando, from within /scripts directory, e.g. ./sync-db.sh

# Exit immediately if a command fails with a non-zero status code
set -e

# Setup
source sync-db-var-config
mkdir --parents .sql-dumps
# @todo check if Lando stack is running, if not return error message or just start it

echo "Downloading latest STG database"

# `head -1` is to get the most recent container, we may switch to using a "latest" tag instead.
SQL_DUMP_CMD='docker exec $(docker ps --quiet --filter "name=appserver" | head -1) bash -c "cd /app && /app/docroot/vendor/bin/drush sql-dump"'
ssh -o ProxyCommand="ssh -W %h:%p ${BASTION_USER}@${BASTION_HOSTNAME}" ${STG_USER}@${STG_HOSTNAME} \
${SQL_DUMP_CMD} > .sql-dumps/db-dump-stg.sql

echo "Downloaded STG Database to /scripts/.sql-dumps/db-dump-stg.sql"

lando db-import .sql-dumps/db-dump-stg.sql
