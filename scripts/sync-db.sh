#!/usr/bin/env bash

# Exit immediately if a command fails with a non-zero status code
set -e

# Setup
source sync-db-var-config
mkdir --parents .sql-dumps
# @todo 4 check if Lando stack is running

echo "Downloading latest database"

# @todo Ensure most recent container ID if more than one
SQL_DUMP_CMD='docker exec $(docker ps --quiet --filter "name=appserver") bash -c "cd /app && /app/docroot/vendor/bin/drush sql-dump"'
ssh -o ProxyCommand="ssh -W %h:%p ${BASTION_USER}@${BASTION_HOSTNAME}" ${STG_USER}@${STG_HOSTNAME} \
${SQL_DUMP_CMD} > .sql-dumps/db-dump-stg.sql

echo "Database downloaded to /scripts/.sql-dumps/db-dump-stg.sql"

lando db-import .sql-dumps/db-dump-stg.sql

# @todo 3 document
# @todo add all DEV's keys to STG instance
# @todo parameterize to accept DEV or STG
