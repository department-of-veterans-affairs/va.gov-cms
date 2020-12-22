<?php

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

$tugboat_root = getenv('DOCROOT');
$autoloader = require $tugboat_root . '/autoload.php';
require_once $tugboat_root . '/core/includes/bootstrap.inc';
$request = Request::createFromGlobals();
Settings::initialize($tugboat_root, DrupalKernel::findSitePath($request), $autoloader);

$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod')->boot();

// @TODO create Application class
$i = 0;
while (1 == 1) {
  echo $i . PHP_EOL;
  error_log($i);
  $i++;
  sleep(2);
}

