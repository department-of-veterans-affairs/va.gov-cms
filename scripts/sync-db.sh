#!/usr/bin/env bash

# Exit immediately if a command fails with a non-zero status code
set -e

# Allow script to be run anywhere in git repo
cd $(git rev-parse --show-toplevel)/.dumps

# Setup
# @todo check if Lando stack is running, if not return error message or just start it
echo "Downloading latest PROD database"
curl --remote-name --remote-header-name https://dsva-vagov-prod-cms-backup-sanitized.s3-us-gov-west-1.amazonaws.com/database/cms-prod-db-sanitized-latest.sql.gz
gunzip --force cms-prod-db-sanitized-latest.sql.gz 2> /dev/null || true &&

echo "Downloaded PROD Database to .dumps/cms-prod-db-sanitized-latest.sql"

# BRD DEV, STAGING, PROD only
if [ ! -z "$CMS_BRD" ]; then
    echo "Dropping existing database tables"
    drush sql-drop --yes
    echo "Database tables dropped"
    echo "Importing .dumps/cms-prod-db-sanitized-latest.sql"
    drush sql-cli < cms-prod-db-sanitized-latest.sql
    echo "Database import complete"
fi
# Local only
if [ -z "$CMS_BRD" ]; then
echo $CMS_BRD;
echo "Purging devel configuration files."
    rm -f $(git rev-parse --show-toplevel)/config/sync/devel.settings.yml
    rm -f $(git rev-parse --show-toplevel)/config/sync/devel.toolbar.settings.yml
    rm -f $(git rev-parse --show-toplevel)/config/sync/system.menu.devel.yml
    rm -f $(git rev-parse --show-toplevel)/config/dev/devel.settings.yml
    rm -f $(git rev-parse --show-toplevel)/config/dev/devel.toolbar.settings.yml
    rm -f $(git rev-parse --show-toplevel)/config/dev/system.menu.devel.yml
echo "Importing database."
    lando db-import cms-prod-db-sanitized-latest.sql
fi
