<?php

$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => getenv('MARIADB_DATABASE'),
  'username' => getenv('MARIADB_USERNAME'),
  'password' => getenv('MARIADB_PASSWORD'),
  'prefix' => '',
  'host' => getenv('MARIADB_HOST'),
  'port' => getenv('MARIADB_PORT'),
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
);

$settings['va_gov_web_root'] = getenv('LAGOON_ROOT');
$settings['va_gov_app_root'] = getenv('LAGOON_ROOT') . '/web';
