#!/bin/bash

/var/www/cms/scripts/drupal_fix_permissions.sh --setgid -u=cms -g=apache -p=/var/www/cms/docroot
