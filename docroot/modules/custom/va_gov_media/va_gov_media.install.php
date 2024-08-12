<?php

/**
 * @file
 * Install file for Va Gov Media.
 */

/**
 * Install s3fs.
 */
function va_gov_media_update_10001(&$sandbox) {
  \Drupal::moduleHandler()->loadInclude('va_gov_db', 'install');
  // These have to be installed programatically, because there is a service
  // dependency that is not met when waiting on config import to install.
  $modules = [
    's3fs',
  ];

  return _va_gov_db_install_modules($modules);
}
