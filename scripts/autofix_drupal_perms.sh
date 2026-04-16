#!/bin/bash

# This script is used to fix the permissions of a Drupal installation.
/var/www/cms/scripts/drupal_fix_permissions.sh --setgid -u=cms -g=apache -p=/var/www/cms/docroot
