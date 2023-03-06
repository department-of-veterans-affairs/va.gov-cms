#!/usr/bin/env bash

# Exit immediately if a command fails with a non-zero status code.
set -e

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}/.dumps" > /dev/null

# On BRD, CMS_APP_NAME is set upstream in Devops/Ansible.
# If it is not set (local usage) assign 'cms' to it.
if [ -z "$CMS_APP_NAME" ]; then
  CMS_APP_NAME=cms
fi

# Download and unzip the sanitized database snapshot.
base_url="https://dsva-vagov-prod-${CMS_APP_NAME}-backup-sanitized.s3-us-gov-west-1.amazonaws.com"
db_path="database/cms-prod-db-sanitized-latest.sql.gz"
echo "Downloading latest PROD database from: ${base_url}/${db_path}"
curl --remote-name "${base_url}/${db_path}"
gunzip --force cms-prod-db-sanitized-latest.sql.gz 2> /dev/null || true

echo "Downloaded PROD Database to .dumps/cms-prod-db-sanitized-latest.sql".
echo "Dropping existing database tables"
drush sql-drop --yes
echo "Database tables dropped"
echo "Importing .dumps/cms-prod-db-sanitized-latest.sql"
drush sql-cli < cms-prod-db-sanitized-latest.sql
echo "Database import complete"

popd > /dev/null
