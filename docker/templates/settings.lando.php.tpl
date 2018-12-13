<?php

/**
 * Override database settings, all variables are injected from docker env vars.
 */

$databases['default']['default'] = array (
  'database' => '{{ DRUPAL_DATABASE_NAME }}',
  'username' => '{{ DRUPAL_DATABASE_USER }}',
  'password' => '{{ DRUPAL_DATABASE_PASSWORD }}',
  'prefix' => '',
  'host' => '{{ DRUPAL_DATABASE_HOST }}',
  'port' => '{{ DRUPAL_DATABASE_HOST_PORT }}',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
