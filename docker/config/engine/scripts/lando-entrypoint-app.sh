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
j2 /templates/settings.lando.php.tpl > /app/docroot/sites/default/settings.lando.php

# Run post-deploy hooks
# Make sure backend services are up
/usr/bin/wait-for-it.sh ${DRUPAL_DATABASE_HOST}:${DRUPAL_DATABASE_HOST_PORT}
cd ${LANDO_WEBROOT}/vendor/bin/
./drush cache:rebuild
./drush updatedb -y
./drush config:import -y
./drush cache:rebuild

# Run the COMMAND
echo "Running command $@"
"$@" || tail -f /dev/null
