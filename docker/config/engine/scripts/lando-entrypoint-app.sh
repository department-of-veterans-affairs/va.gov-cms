#!/bin/sh

set -e

# Executable all the helpers
if [ -d "/helpers" ] && [ -z ${LANDO_NO_SCRIPTS+x} ]; then
  chmod +x /helpers/* || true
fi;

# Run the usermap script if it exists
if [ -f "/helpers/user-perms.sh" ] && [ -z ${LANDO_NO_SCRIPTS+x} ]; then
  chmod +x /helpers/user-perms.sh || true
  /helpers/user-perms.sh
fi;

# Run any scripts that we've loaded into the mix for autorun
if [ -d "/scripts" ] && [ -z ${LANDO_NO_SCRIPTS+x} ]; then
  chmod +x /scripts/* || true
  find /scripts/ -type f -exec {} \;
fi;

# custom: render database settings from template
j2 /templates/settings.lando.php.tpl > /app/docroot/sites/default/settings/settings.lando.php

# Run post-deploy hooks
# Make sure backend services are up
/usr/bin/wait-for-it.sh -t 120 ${DRUPAL_DATABASE_HOST}:${DRUPAL_DATABASE_HOST_PORT}
cd ${LANDO_WEBROOT}/vendor/bin/
./drush cache:rebuild
./drush updatedb -y
./drush config:import -y
./drush cache:rebuild

# Sync drupal site/default/files
if [ "${SYNC_SITE_FILES}" = "yes" ] ; then
  backup_url=$(curl -L https://s3-us-gov-west-1.amazonaws.com/${S3_BACKUP_BUCKET_PUB}/files/latest_url)
  curl ${backup_url} -o /tmp/cmsapp_files.tgz
  mount -a
  [ -d /app/docroot/sites/default/files ] && echo "Site files directory exists, Not creating" || mkdir /app/docroot/sites/default/files
  rm -fR /app/docroot/sites/default/files/*
  tar -xzvf /tmp/cmsapp_files.tgz --directory /app/docroot/sites/default/files
else
  echo "Skipping site default files sync" ;
fi

# Run the COMMAND
echo "Running command $@"
"$@" || tail -f /dev/null
