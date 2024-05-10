#!/bin/bash

./scripts/drupal-fix-permissions --setgid -u=cms -g=apache -p=/var/www/cms/docroot
