<?php

/**
 * @file
 * Enables modules and site configuration for the VA.gov profile.
 */

// Globally lock the config sync directory to be the ./config/sync directory in
// this repository. This is done here because sites/default/settings.php is
// overwritten when run with some scripts like `core/scripts/drupal quick-start`
// or `drush run-server`.
global $config_directories;
$config_directories['sync'] = '../config/sync';
