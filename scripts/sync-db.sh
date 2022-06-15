#!/usr/bin/env bash

# Exit immediately if a command fails with a non-zero status code
set -e

# Allow script to be run anywhere in git repo
cd "$(git rev-parse --show-toplevel)"/.dumps

# On BRD, CMS_APP_NAME is set upstream in Devops/Ansible.
# If it is not set (local usage) assign 'cms' to it.
if [ -z "$CMS_APP_NAME" ]; then
  CMS_APP_NAME=cms
fi


# Download and unzip the sanitized database snapshot.
HOSTNAME="https://dsva-vagov-prod-${CMS_APP_NAME}-backup-sanitized.s3-us-gov-west-1.amazonaws.com"
FILEPATH="database/cms-prod-db-sanitized-latest.sql.gz"
echo "Downloading latest PROD database from: ${HOSTNAME}/${FILEPATH}"
curl --remote-name "${HOSTNAME}/${FILEPATH}"
gunzip --force cms-prod-db-sanitized-latest.sql.gz 2> /dev/null || true &&

echo "Downloaded PROD Database to .dumps/cms-prod-db-sanitized-latest.sql"

# BRD DEV, STAGING, PROD only
if [ -n "$CMS_IS_BRD" ]; then
    echo "Dropping existing database tables"
    drush sql-drop --yes
    echo "Database tables dropped"
    echo "Importing .dumps/cms-prod-db-sanitized-latest.sql"
    drush sql-cli < cms-prod-db-sanitized-latest.sql
    echo "Database import complete"
fi
# Local only
if [ -z "$CMS_IS_BRD" ]; then
echo "Purging devel configuration files."
    rm -f "$(git rev-parse --show-toplevel)"/config/sync/devel.settings.yml
    rm -f "$(git rev-parse --show-toplevel)"/config/sync/devel.toolbar.settings.yml
    rm -f "$(git rev-parse --show-toplevel)"/config/sync/system.menu.devel.yml
    rm -f "$(git rev-parse --show-toplevel)"/config/dev/devel.settings.yml
    rm -f "$(git rev-parse --show-toplevel)"/config/dev/devel.toolbar.settings.yml
    rm -f "$(git rev-parse --show-toplevel)"/config/dev/system.menu.devel.yml
    echo "Importing database."
    lando db-import cms-prod-db-sanitized-latest.sql
fi
