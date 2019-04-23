<?php

/**
 * This file is only used for local Lando development environments.
 */

// @codingStandardsIgnoreFile

$databases['default']['default'] = array (
  'database' => 'drupal8',
  'username' => 'drupal8',
  'password' => 'drupal8',
  'prefix' => '',
  'host' => 'database',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
