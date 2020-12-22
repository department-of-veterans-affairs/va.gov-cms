<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once __DIR__ . '/../docroot/autoload.php';

$request = Request::createFromGlobals();
DrupalKernel::createFromRequest($request, $autoloader, 'prod');

$i = 0;
while (1 == 1) {
  error_log($i);
  $i++;
  sleep(2);
}

